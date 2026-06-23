<template>
  <MainLayout>
    <div class="h-[calc(100vh-120px)] flex flex-col md:flex-row bg-white dark:bg-[#0D1117] rounded-2xl border border-gray-100 dark:border-gray-800/60 overflow-hidden shadow-sm transition-all duration-300">

      <!-- Left Sidebar: Conversations List -->
      <div class="w-full md:w-80 lg:w-96 border-r border-gray-100 dark:border-gray-800/60 flex flex-col h-full bg-gray-50/50 dark:bg-[#161B22]/20">
        <!-- Search & Filter Header -->
        <div class="p-4 border-b border-gray-100 dark:border-gray-800/60 space-y-3">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <MessageSquare class="w-5 h-5 text-primary" />
            Chat Inbox
          </h3>
          <div class="relative">
            <Search class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search conversations..."
              class="w-full bg-white dark:bg-[#0D1117] border border-gray-200 dark:border-gray-800 rounded-xl pl-9 pr-4 py-2 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            />
          </div>
        </div>

        <!-- Conversations Scrollable Area -->
        <div class="flex-1 overflow-y-auto p-2 space-y-1 select-none">
          <div v-if="chat.loadingConversations" class="flex flex-col items-center justify-center py-12 space-y-3">
            <Loader2 class="w-6 h-6 animate-spin text-primary" />
            <span class="text-xs text-gray-400">Loading inbox...</span>
          </div>

          <div v-else-if="filteredConversations.length === 0" class="flex flex-col items-center justify-center py-12 text-center px-4">
            <Inbox class="w-8 h-8 text-gray-400 mb-2" />
            <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">No chats found</span>
            <span class="text-xs text-gray-400 mt-1">Inbox is empty or no matches found.</span>
          </div>

          <div
            v-else
            v-for="conv in filteredConversations"
            :key="conv.id"
            @click="selectConversation(conv)"
            class="group flex items-center gap-3 p-3 rounded-xl cursor-pointer transition-all duration-200"
            :class="[
              chat.activeConversationId === conv.id
                ? 'bg-primary/10 border-primary/20 dark:bg-primary/15'
                : 'hover:bg-gray-100 dark:hover:bg-gray-800/40 border-transparent'
            ]"
          >
            <!-- User Avatar Initial -->
            <div class="relative flex-shrink-0">
              <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-primary/20 to-primary/10 flex items-center justify-center font-bold text-primary text-sm dark:from-primary/30 dark:to-primary/20">
                {{ conv.guest?.email ? conv.guest.email.charAt(0).toUpperCase() : 'G' }}
              </div>
              <span
                v-if="conv.status === 'open'"
                class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white dark:border-[#0D1117] rounded-full"
                title="Active"
              ></span>
            </div>

            <!-- Chat Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-900 dark:text-white truncate">
                  {{ conv.guest?.email || 'Guest Visitor' }}
                </span>
                <span class="text-[10px] text-gray-400 shrink-0">
                  {{ formatTime(conv.updated_at) }}
                </span>
              </div>
              <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5" :class="{'font-semibold text-gray-900 dark:text-white': (conv.unread_count || 0) > 0}">
                {{ conv.last_message?.message || conv.lastMessage?.message || 'Start chatting...' }}
              </p>
            </div>

            <!-- Unread Badge / Status -->
            <div class="flex flex-col items-end gap-1.5">
              <span
                v-if="conv.status === 'closed'"
                class="text-[9px] px-1.5 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 font-medium"
              >
                Closed
              </span>
              <span
                v-if="(conv.unread_count || 0) > 0"
                class="min-w-[18px] h-[18px] px-1.5 rounded-full bg-primary text-white text-[10px] font-bold flex items-center justify-center animate-pulse"
              >
                {{ conv.unread_count > 99 ? '99+' : conv.unread_count }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Pane: Active Chat Window -->
      <div class="flex-1 flex flex-col h-full bg-white dark:bg-[#0D1117] relative">
        <div v-if="!chat.activeConversation" class="flex-1 flex flex-col items-center justify-center text-center p-8 bg-gray-50/20 dark:bg-[#0D1117]">
          <div class="relative mb-4">
            <div class="absolute inset-0 bg-primary/20 rounded-full blur-xl animate-pulse"></div>
            <div class="relative w-16 h-16 rounded-full bg-gradient-to-tr from-primary/15 to-primary/5 flex items-center justify-center border border-primary/20">
              <MessageSquare class="w-8 h-8 text-primary" />
            </div>
          </div>
          <h3 class="text-lg font-bold text-gray-900 dark:text-white">Welcome to Chat Inbox</h3>
          <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mt-1">
            Select a guest conversation from the sidebar to view message history, reply in real-time, or manage support sessions.
          </p>
        </div>

        <template v-else>
          <!-- Chat Header -->
          <div class="p-4 border-b border-gray-100 dark:border-gray-800/60 flex items-center justify-between bg-white dark:bg-[#0D1117]">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-primary/20 to-primary/10 flex items-center justify-center font-bold text-primary dark:from-primary/30 dark:to-primary/20">
                {{ chat.activeConversation.guest?.email ? chat.activeConversation.guest.email.charAt(0).toUpperCase() : 'G' }}
              </div>
              <div>
                <div class="text-sm font-bold text-gray-900 dark:text-white">
                  {{ chat.activeConversation.guest?.email }}
                </div>
                <div class="flex items-center gap-1.5 mt-0.5">
                  <span
                    class="w-2 h-2 rounded-full"
                    :class="chat.activeConversation.status === 'open' ? 'bg-emerald-500' : 'bg-gray-400'"
                  ></span>
                  <span class="text-[10px] text-gray-500 dark:text-gray-400 capitalize">
                    {{ chat.activeConversation.status }} Conversation
                  </span>
                </div>
              </div>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-2">
              <button
                v-if="chat.activeConversation.status === 'open'"
                @click="closeActiveChat"
                :disabled="chat.closingChat"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 dark:border-red-900/40 hover:bg-red-50 dark:hover:bg-red-950/20 text-xs font-semibold text-red-600 dark:text-red-400 transition-all disabled:opacity-55"
              >
                <XCircle class="w-3.5 h-3.5" />
                Close Session
              </button>
            </div>
          </div>

          <!-- Message History -->
          <div ref="messageHistory" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50/30 dark:bg-[#0D1117]/10">
            <div v-if="chat.loadingHistory" class="flex items-center justify-center py-12">
              <Loader2 class="w-6 h-6 animate-spin text-primary" />
            </div>

            <div v-else-if="chat.activeMessages.length === 0" class="flex flex-col items-center justify-center py-12 text-center text-gray-400">
              <Send class="w-8 h-8 mb-2 opacity-50" />
              <span class="text-xs">No messages yet. Send a response below to start conversation!</span>
            </div>

            <div
              v-else
              v-for="msg in chat.activeMessages"
              :key="msg.id"
              class="flex flex-col"
              :class="isAdminSender(msg) ? 'items-end' : 'items-start'"
            >
              <div
                class="max-w-[75%] px-4 py-2.5 rounded-2xl shadow-sm text-sm"
                :class="[
                  isAdminSender(msg)
                    ? 'bg-primary text-white rounded-tr-none'
                    : 'bg-white dark:bg-[#161B22] border border-gray-100 dark:border-gray-800 text-gray-900 dark:text-white rounded-tl-none'
                ]"
              >
                {{ msg.message }}
              </div>
              <span class="text-[9px] text-gray-400 mt-1 px-1">
                {{ formatTime(msg.created_at) }}
              </span>
            </div>
          </div>

          <!-- Message Input Box -->
          <div class="p-4 border-t border-gray-100 dark:border-gray-800/60 bg-white dark:bg-[#0D1117]">
            <form @submit.prevent="sendResponse" class="flex gap-2 items-center">
              <input
                v-model="replyMessage"
                type="text"
                :disabled="chat.activeConversation.status === 'closed' || chat.sendingReply"
                placeholder="Type your response..."
                class="flex-1 bg-gray-50 dark:bg-[#161B22] border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all disabled:opacity-55 disabled:cursor-not-allowed"
              />
              <button
                type="submit"
                :disabled="chat.activeConversation.status === 'closed' || chat.sendingReply || !replyMessage.trim()"
                class="bg-primary hover:bg-primary-dark text-white rounded-xl p-3 flex items-center justify-center transition-all disabled:opacity-50 disabled:cursor-not-allowed shrink-0 shadow-sm"
              >
                <Send v-if="!chat.sendingReply" class="w-4 h-4" />
                <Loader2 v-else class="w-4 h-4 animate-spin" />
              </button>
            </form>
          </div>
        </template>
      </div>

    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import MainLayout from '@/layouts/MainLayout.vue';
import { useToastStore } from '@/stores/toast';
import { useChatStore } from '@/stores/chat';
import { MessageSquare, Search, Inbox, Loader2, Send, XCircle } from 'lucide-vue-next';

// Setup Toast and Chat Stores
const toast = useToastStore();
const chat = useChatStore();

// Local UI state only
const searchQuery = ref('');
const replyMessage = ref('');

// Refs for UI
const messageHistory = ref(null);

// Computed Property to Filter Conversations
const filteredConversations = computed(() => {
  if (!searchQuery.value.trim()) return chat.conversations;
  const q = searchQuery.value.toLowerCase();
  return chat.conversations.filter(conv =>
    conv.guest?.email?.toLowerCase().includes(q)
  );
});

// Check if message sender is admin/consultant
const isAdminSender = (msg) => {
  return msg.sender_type && msg.sender_type.includes('User');
};

// Scroll Chat area to bottom
const scrollToBottom = async () => {
  await nextTick();
  if (messageHistory.value) {
    messageHistory.value.scrollTop = messageHistory.value.scrollHeight;
  }
};

// Formatting Helper for time
const formatTime = (timeString) => {
  if (!timeString) return '';
  const date = new Date(timeString);
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

// Select a Chat Conversation & load messages (via store)
const selectConversation = async (conv) => {
  await chat.openConversation(conv.id);
  scrollToBottom();
};

// Watch for new messages arriving in the open conversation and scroll
watch(
  () => chat.activeMessages.length,
  () => {
    scrollToBottom();
  }
);

// Send Reply response to guest (via store)
const sendResponse = async () => {
  if (!replyMessage.value.trim() || !chat.activeConversationId) return;
  const originalMessage = replyMessage.value;
  replyMessage.value = '';

  const sent = await chat.sendReply(originalMessage);
  if (!sent) {
    replyMessage.value = originalMessage; // Restore draft on failure
    toast.error('Failed to deliver message.');
  }
  scrollToBottom();
};

// Close Chat Session (via store)
const closeActiveChat = async () => {
  const ok = await chat.closeActiveConversation();
  if (ok) {
    toast.success('Conversation session marked as closed.');
  } else {
    toast.error('Failed to close conversation session.');
  }
};

// On mount: ensure the store has the conversation list ready.
// (The global listener is started by MainLayout, so the inbox itself only
// needs to make sure the list is loaded when this page is the entry point.)
onMounted(async () => {
  if (chat.conversations.length === 0) {
    await chat.fetchConversations();
  }
});
</script>

<style scoped>
/* Custom animations & fine details */
@keyframes slideUp {
  from {
    transform: translateY(8px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
.animate-slide-up {
  animation: slideUp 0.3s ease-out forwards;
}
</style>
