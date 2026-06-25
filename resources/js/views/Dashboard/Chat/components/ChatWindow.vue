<template>
  <div class="flex-1 flex flex-col h-full bg-white dark:bg-[#0D1117] relative min-w-0">
    <!-- Header -->
    <ChatHeader
      :conversation="conversation"
      :closing="chat.closingChat"
      :is-typing="isTyping"
      @close="$emit('close')"
      @back="$emit('back')"
    />

    <!-- Message history -->
    <div ref="scrollEl" class="flex-1 overflow-y-auto p-4 space-y-1 bg-gray-50/30 dark:bg-[#0D1117]/10">
      <!-- Loading -->
      <div v-if="chat.loadingHistory" class="flex items-center justify-center py-12">
        <Loader2 class="w-6 h-6 animate-spin text-primary" />
      </div>

      <!-- Empty -->
      <div
        v-else-if="chat.activeMessages.length === 0"
        class="flex flex-col items-center justify-center py-12 text-center text-gray-400"
      >
        <Send class="w-8 h-8 mb-2 opacity-50" />
        <span class="text-xs">No messages yet. Send a response below to start the conversation!</span>
      </div>

      <!-- Messages -->
      <template v-else>
        <template v-for="(group, gi) in groupedMessages" :key="group.dateKey">
          <DateSeparator :date="group.date" />

          <template v-for="msg in group.items" :key="msg.id">
            <ChatMessageBubble :message="msg" :is-admin="isAdminSender(msg)" />
          </template>
        </template>

        <!-- Inline typing dots -->
        <TypingIndicator
          v-if="isTyping"
          :guest-email="conversation.guest?.email"
        />
      </template>
    </div>

    <!-- Input -->
    <ChatInput
      v-model="draft"
      :disabled="conversation.status === 'closed'"
      :sending="chat.sendingReply"
      @send="handleSend"
      @typing="handleTyping"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Send, Loader2 } from 'lucide-vue-next';
import axios from '@/plugins/axios';
import { useChatStore } from '@/stores/chat';
import ChatHeader from './ChatHeader.vue';
import ChatMessageBubble from './ChatMessageBubble.vue';
import TypingIndicator from './TypingIndicator.vue';
import DateSeparator from './DateSeparator.vue';
import ChatInput from './ChatInput.vue';

const props = defineProps({
  conversation: { type: Object, required: true },
});

const emit = defineEmits(['close', 'back']);

const chat = useChatStore();

const scrollEl = ref(null);
const draft = ref('');
const isTyping = ref(false);

let typingTimer = null;
let typingChannel = null;
const TYPING_TIMEOUT = 4000; // hide typing indicator after 4s of silence

/* ----------------------------- Helpers ----------------------------- */
const isAdminSender = (msg) => {
  return Boolean(msg.sender_type && msg.sender_type.includes('User'));
};

const dayKey = (dateStr) => {
  const d = new Date(dateStr);
  return `${d.getFullYear()}-${d.getMonth()}-${d.getDate()}`;
};

// Group consecutive messages by day so we only render one DateSeparator per day.
const groupedMessages = computed(() => {
  const groups = [];
  let current = null;
  for (const msg of chat.activeMessages) {
    const key = dayKey(msg.created_at);
    if (!current || current.dateKey !== key) {
      current = { dateKey: key, date: msg.created_at, items: [] };
      groups.push(current);
    }
    current.items.push(msg);
  }
  return groups;
});

const scrollToBottom = async (smooth = false) => {
  // Double nextTick: wait for the v-for messages to render, then for any
  // layout shift (e.g. typing indicator appearing) to settle before scrolling.
  // A single tick was racing the DOM and landing mid-list on long histories.
  await nextTick();
  await nextTick();
  if (scrollEl.value) {
    scrollEl.value.scrollTo({
      top: scrollEl.value.scrollHeight,
      behavior: smooth ? 'smooth' : 'auto',
    });
  }
};

/* --------------------------- Typing channel --------------------------- */
/**
 * Subscribe to the per-conversation private-ish channel for typing + read events.
 * The store's GLOBAL admin-chat handler already appends messages + updates the
 * sidebar, so we ONLY listen for typing/read-receipt signals here to avoid
 * duplicate message rendering.
 */
const subscribeTyping = () => {
  unsubscribeTyping();
  if (!chat.echo || !props.conversation?.id) return;

  const channelName = `chat.${props.conversation.id}`;
  try {
    typingChannel = chat.echo.channel(channelName);

    // Guest is typing (backend endpoint lands in step 4)
    typingChannel.listen('.typing', (raw) => {
      const payload = typeof raw === 'string' ? safeJson(raw) : raw;
      const senderType = payload?.sender_type || '';
      // Only react to GUEST typing (admin's own typing is irrelevant here)
      if (senderType && !senderType.includes('User')) {
        isTyping.value = true;
        resetTypingTimer();
      }
    });

    // Read receipts broadcast (backend lands in step 4)
    typingChannel.listen('.message.read', (raw) => {
      const payload = typeof raw === 'string' ? safeJson(raw) : raw;
      // Only flip our ✓→✓✓ when the GUEST has read our messages.
      // The admin-side mark-read endpoint no longer emits this event (it never
      // should have), but guard defensively: ignore echoes where the reader is
      // the admin/User themselves.
      const readerType = payload?.reader_type || '';
      if (readerType && readerType.includes('User')) return;

      // Mark all admin messages in this conversation as read locally
      chat.activeMessages.forEach((m) => {
        if (isAdminSender(m)) {
          m.is_read = true;
          if (payload?.read_at && !m.read_at) m.read_at = payload.read_at;
        }
      });
    });
  } catch (e) {
    console.warn('⚠️ [ChatWindow] typing channel subscribe failed', e);
  }
};

const unsubscribeTyping = () => {
  if (typingChannel && chat.echo) {
    try {
      chat.echo.leave(`chat.${props.conversation.id}`);
    } catch (_) { /* best effort */ }
  }
  typingChannel = null;
  clearTypingTimer();
  isTyping.value = false;
};

const resetTypingTimer = () => {
  clearTypingTimer();
  typingTimer = setTimeout(() => {
    isTyping.value = false;
  }, TYPING_TIMEOUT);
};

const clearTypingTimer = () => {
  if (typingTimer) {
    clearTimeout(typingTimer);
    typingTimer = null;
  }
};

const safeJson = (str) => {
  try { return JSON.parse(str); } catch (e) { return null; }
};

/* ------------------------------ Actions ------------------------------ */
const handleSend = async (text) => {
  // Clear the input + typing state INSTANTLY — don't wait for the API round-trip.
  // The optimistic bubble is already pushed into the store synchronously, so the
  // only thing making this feel slow was the textarea keeping its text until the
  // network responded. We restore the draft only if the send actually fails.
  draft.value = '';
  isTyping.value = false; // guest typing no longer relevant once we reply

  const ok = await chat.sendReply(text);
  if (ok) {
    await scrollToBottom(true);
  } else {
    draft.value = text; // restore draft on failure
  }
};

/**
 * Admin is typing — broadcast to the guest via the typing endpoint.
 * Throttling is handled inside ChatInput (one event per TYPING_THROTTLE_MS);
 * we just fire-and-forget the POST here. Silent failure (guest offline etc.).
 */
const handleTyping = () => {
  if (!props.conversation?.id) return;
  axios
    .post(`/auth/admin/chat/conversations/${props.conversation.id}/typing`)
    .catch(() => { /* best effort */ });
};

/* ------------------------------ Lifecycle ------------------------------ */
// Scroll to bottom whenever the message list grows (new incoming/outgoing msg).
watch(
  () => chat.activeMessages.length,
  () => scrollToBottom()
);

// KEY FIX: the history load is async. When ChatWindow mounts, messages are
// usually still empty (chat.loadingHistory === true). So onMounted's scroll
// hits an empty container and stays at the top. We must wait until the history
// FINISHES loading, then jump to the latest message.
watch(
  () => chat.loadingHistory,
  (loading, wasLoading) => {
    if (wasLoading && !loading) {
      // History just finished loading → jump to newest message.
      scrollToBottom();
    }
  }
);

// Scroll when guest typing indicator toggles on
watch(isTyping, (val) => {
  if (val) scrollToBottom(true);
});

onMounted(() => {
  subscribeTyping();
  // If history is already loaded by the time we mount, scroll now. Otherwise
  // the loadingHistory watcher above will handle it.
  if (!chat.loadingHistory && chat.activeMessages.length > 0) {
    scrollToBottom();
  }
});

onBeforeUnmount(() => {
  unsubscribeTyping();
});

// Re-subscribe whenever the active conversation changes
watch(
  () => props.conversation?.id,
  () => {
    subscribeTyping();
    // Don't scroll here directly — loadingHistory will flip to true→false as
    // the new conversation's history loads, and that watcher scrolls. But if
    // messages are somehow already present, scroll defensively.
    if (!chat.loadingHistory && chat.activeMessages.length > 0) {
      scrollToBottom();
    }
  }
);
</script>
