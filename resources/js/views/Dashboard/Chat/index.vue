<template>
  <MainLayout>
    <div class="h-[calc(100vh-120px)] flex flex-col md:flex-row bg-white dark:bg-[#0D1117] rounded-2xl border border-gray-100 dark:border-gray-800/60 overflow-hidden shadow-sm transition-all duration-300">

      <!-- Sidebar: hidden on mobile when a chat is open (slide-in effect) -->
      <div
        class="w-full md:w-80 lg:w-96 flex-shrink-0 h-full"
        :class="chat.activeConversation ? 'hidden md:block' : 'block'"
      >
        <ChatSidebar
          :chat="chat"
          :mobile-open="!!chat.activeConversation"
          @select="onSelect"
          @back="onBack"
        />
      </div>

      <!-- Right pane: Chat window or empty state -->
      <div class="flex-1 flex flex-col h-full min-w-0">
        <ChatWindow
          v-if="chat.activeConversation"
          :conversation="chat.activeConversation"
          @close="closeActiveChat"
          @back="onBack"
        />
        <EmptyChatState v-else />
      </div>

    </div>
  </MainLayout>
</template>

<script setup>
import { onMounted, onBeforeUnmount } from 'vue';
import MainLayout from '@/layouts/MainLayout.vue';
import { useToastStore } from '@/stores/toast';
import { useChatStore } from '@/stores/chat';
import ChatSidebar from './components/ChatSidebar.vue';
import ChatWindow from './components/ChatWindow.vue';
import EmptyChatState from './components/EmptyChatState.vue';

// Setup Toast and Chat Stores
const toast = useToastStore();
const chat = useChatStore();

/* ----------------------------- Events ----------------------------- */
const onSelect = async (conv) => {
  await chat.openConversation(conv.id);
};

const onBack = () => {
  // Mobile: leave the conversation open in store data but return to list.
  // We just clear the active pointer so the sidebar slides back in.
  chat.clearActiveConversation();
};

const closeActiveChat = async () => {
  const ok = await chat.closeActiveConversation();
  if (ok) {
    toast.success('Conversation session marked as closed.');
  } else {
    toast.error('Failed to close conversation session.');
  }
};

/* --------------------------- Lifecycle --------------------------- */
// On mount: ensure the store has the conversation list ready.
// The global listener is started by MainLayout, so the inbox itself only
// needs to make sure the list is loaded when this page is the entry point.
onMounted(async () => {
  if (chat.conversations.length === 0) {
    await chat.fetchConversations();
  }
});

// Clear active conversation pointer when navigating away from chat inbox
// so subsequent messages are treated as unread and trigger notifications correctly.
onBeforeUnmount(() => {
  chat.clearActiveConversation();
});
</script>
