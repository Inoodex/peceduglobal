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
          <div v-if="loadingConversations" class="flex flex-col items-center justify-center py-12 space-y-3">
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
            v-for="chat in filteredConversations"
            :key="chat.id"
            @click="selectConversation(chat)"
            class="group flex items-center gap-3 p-3 rounded-xl cursor-pointer transition-all duration-200"
            :class="[
              activeConversation?.id === chat.id 
                ? 'bg-primary/10 border-primary/20 dark:bg-primary/15' 
                : 'hover:bg-gray-100 dark:hover:bg-gray-800/40 border-transparent'
            ]"
          >
            <!-- User Avatar Initial -->
            <div class="relative flex-shrink-0">
              <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-primary/20 to-primary/10 flex items-center justify-center font-bold text-primary text-sm dark:from-primary/30 dark:to-primary/20">
                {{ chat.guest?.email ? chat.guest.email.charAt(0).toUpperCase() : 'G' }}
              </div>
              <span 
                v-if="chat.status === 'open'" 
                class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white dark:border-[#0D1117] rounded-full"
                title="Active"
              ></span>
            </div>

            <!-- Chat Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-900 dark:text-white truncate">
                  {{ chat.guest?.email || 'Guest Visitor' }}
                </span>
                <span class="text-[10px] text-gray-400 shrink-0">
                  {{ formatTime(chat.updated_at) }}
                </span>
              </div>
              <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5" :class="{'font-semibold text-gray-900 dark:text-white': hasUnread(chat)}">
                {{ chat.last_message?.message || 'Start chatting...' }}
              </p>
            </div>

            <!-- Unread Badge / Status -->
            <div class="flex flex-col items-end gap-1.5">
              <span 
                v-if="chat.status === 'closed'" 
                class="text-[9px] px-1.5 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 font-medium"
              >
                Closed
              </span>
              <span 
                v-if="hasUnread(chat)" 
                class="w-2.5 h-2.5 rounded-full bg-primary animate-pulse"
              ></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Pane: Active Chat Window -->
      <div class="flex-1 flex flex-col h-full bg-white dark:bg-[#0D1117] relative">
        <div v-if="!activeConversation" class="flex-1 flex flex-col items-center justify-center text-center p-8 bg-gray-50/20 dark:bg-[#0D1117]">
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
                {{ activeConversation.guest?.email ? activeConversation.guest.email.charAt(0).toUpperCase() : 'G' }}
              </div>
              <div>
                <div class="text-sm font-bold text-gray-900 dark:text-white">
                  {{ activeConversation.guest?.email }}
                </div>
                <div class="flex items-center gap-1.5 mt-0.5">
                  <span 
                    class="w-2 h-2 rounded-full" 
                    :class="activeConversation.status === 'open' ? 'bg-emerald-500' : 'bg-gray-400'"
                  ></span>
                  <span class="text-[10px] text-gray-500 dark:text-gray-400 capitalize">
                    {{ activeConversation.status }} Conversation
                  </span>
                </div>
              </div>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-2">
              <button
                v-if="activeConversation.status === 'open'"
                @click="closeActiveChat"
                :disabled="closingChat"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 dark:border-red-900/40 hover:bg-red-50 dark:hover:bg-red-950/20 text-xs font-semibold text-red-600 dark:text-red-400 transition-all disabled:opacity-55"
              >
                <XCircle class="w-3.5 h-3.5" />
                Close Session
              </button>
            </div>
          </div>

          <!-- Message History -->
          <div ref="messageHistory" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50/30 dark:bg-[#0D1117]/10">
            <div v-if="loadingHistory" class="flex items-center justify-center py-12">
              <Loader2 class="w-6 h-6 animate-spin text-primary" />
            </div>

            <div v-else-if="messages.length === 0" class="flex flex-col items-center justify-center py-12 text-center text-gray-400">
              <Send class="w-8 h-8 mb-2 opacity-50" />
              <span class="text-xs">No messages yet. Send a response below to start conversation!</span>
            </div>

            <div
              v-else
              v-for="msg in messages"
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
                :disabled="activeConversation.status === 'closed' || sendingReply"
                placeholder="Type your response..."
                class="flex-1 bg-gray-50 dark:bg-[#161B22] border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all disabled:opacity-55 disabled:cursor-not-allowed"
              />
              <button
                type="submit"
                :disabled="activeConversation.status === 'closed' || sendingReply || !replyMessage.trim()"
                class="bg-primary hover:bg-primary-dark text-white rounded-xl p-3 flex items-center justify-center transition-all disabled:opacity-50 disabled:cursor-not-allowed shrink-0 shadow-sm"
              >
                <Send v-if="!sendingReply" class="w-4 h-4" />
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
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import { useToastStore } from '@/stores/toast';
import { useAuthStore } from '@/stores/auth';
import { MessageSquare, Search, Inbox, Loader2, Send, XCircle } from 'lucide-vue-next';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Setup Toast and Auth Stores
const toast = useToastStore();
const authStore = useAuthStore();

// Navigation States
const searchQuery = ref('');
const conversations = ref([]);
const activeConversation = ref(null);
const messages = ref([]);
const replyMessage = ref('');

// Loading states
const loadingConversations = ref(false);
const loadingHistory = ref(false);
const sendingReply = ref(false);
const closingChat = ref(false);

// Refs for UI
const messageHistory = ref(null);

// Echo & socket references
let echoInstance = null;
let currentChannel = null;

// Computed Property to Filter Conversations
const filteredConversations = computed(() => {
  if (!searchQuery.value.trim()) return conversations.value;
  const q = searchQuery.value.toLowerCase();
  return conversations.value.filter(chat => 
    chat.guest?.email?.toLowerCase().includes(q)
  );
});

// Check if message sender is admin/consultant
const isAdminSender = (msg) => {
  // Check if sender_type is User (admin/consultant)
  return msg.sender_type.includes('User');
};

// Check if conversation has unread messages (e.g. last message is guest and is_read is false)
const hasUnread = (chat) => {
  if (!chat.last_message) return false;
  return !chat.last_message.is_read && !chat.last_message.sender_type.includes('User');
};

// Scroll Chat area to bottom
const scrollToBottom = async () => {
  await nextTick();
  if (messageHistory.value) {
    messageHistory.value.scrollTop = messageHistory.value.scrollHeight;
  }
};

// Formatting Helper for updated time
const formatTime = (timeString) => {
  if (!timeString) return '';
  const date = new Date(timeString);
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

// Fetch all Inbox Conversations
const fetchConversations = async () => {
  loadingConversations.value = true;
  try {
    const response = await axios.get('/auth/admin/chat/conversations');
    if (response.data.success) {
      conversations.value = response.data.data;
    }
  } catch (error) {
    console.error('Failed to load conversations', error);
    toast.error('Could not load chat conversations.');
  } finally {
    loadingConversations.value = false;
  }
};

// Initialize Laravel Echo using dynamic configuration
const setupEchoConnection = async () => {
  try {
    const response = await axios.get('/api/public/chat/settings');
    if (response.data.success) {
      const settings = response.data.data;
      
      // Make sure Pusher runs on custom or official endpoint
      window.Pusher = Pusher;
      const echoOptions = {
        broadcaster: 'pusher',
        key: settings.pusher_key,
        forceTLS: settings.pusher_scheme === 'https',
        disableStats: true,
        enabledTransports: ['ws', 'wss'],
      };

      if (settings.pusher_driver === 'custom') {
        echoOptions.wsHost = settings.pusher_host;
        echoOptions.wsPort = parseInt(settings.pusher_port) || 6001;
        echoOptions.wssPort = parseInt(settings.pusher_port) || 6001;
      } else {
        echoOptions.cluster = settings.pusher_cluster;
      }

      echoInstance = new Echo(echoOptions);
      console.log('Echo connection set up successfully', echoInstance);
    }
  } catch (error) {
    console.error('Failed to setup Echo connection', error);
  }
};

// Select a Chat Conversation & load messages
const selectConversation = async (chat) => {
  activeConversation.value = chat;
  loadingHistory.value = true;
  messages.value = [];

  // Reset/Clear unread indicator locally
  if (chat.last_message) {
    chat.last_message.is_read = true;
  }

  try {
    const response = await axios.get(`/auth/admin/chat/conversations/${chat.id}/messages`);
    if (response.data.success) {
      messages.value = response.data.data;
      scrollToBottom();
      
      // Hook up Echo channel for real-time messages
      listenToConversationChannel(chat.id);
    }
  } catch (error) {
    console.error('Failed to load history', error);
    toast.error('Failed to fetch message history.');
  } finally {
    loadingHistory.value = false;
  }
};

// Listen to conversation channels
const listenToConversationChannel = (id) => {
  if (!echoInstance) return;

  // Leave previous channel if any
  if (currentChannel) {
    currentChannel.leave(`chat.${id}`);
  }

  currentChannel = echoInstance.channel(`chat.${id}`);
  
  // Listen to message sent events
  currentChannel.listen('.message.sent', (data) => {
    // Only add if not already in list
    if (!messages.value.some(m => m.id === data.message.id)) {
      messages.value.push(data.message);
      
      // Update last message in sidebar list
      const chatIndex = conversations.value.findIndex(c => c.id === id);
      if (chatIndex !== -1) {
        conversations.value[chatIndex].last_message = data.message;
        conversations.value[chatIndex].updated_at = new Date().toISOString();
        
        // Move chat to top of list
        const [movedChat] = conversations.value.splice(chatIndex, 1);
        conversations.value.unshift(movedChat);
      }

      scrollToBottom();
    }
  });

  // Listen to session close events
  currentChannel.listen('.conversation.closed', (data) => {
    if (activeConversation.value && activeConversation.value.id === id) {
      activeConversation.value.status = 'closed';
    }
    const chatIndex = conversations.value.findIndex(c => c.id === id);
    if (chatIndex !== -1) {
      conversations.value[chatIndex].status = 'closed';
    }
  });
};

// Send Reply response to guest
const sendResponse = async () => {
  if (!replyMessage.value.trim() || !activeConversation.value) return;
  sendingReply.value = true;
  const originalMessage = replyMessage.value;
  replyMessage.value = '';

  try {
    const response = await axios.post(`/auth/admin/chat/conversations/${activeConversation.value.id}/reply`, {
      message: originalMessage
    });

    if (response.data.success) {
      const newMsg = response.data.data;
      if (!messages.value.some(m => m.id === newMsg.id)) {
        messages.value.push(newMsg);
      }

      // Update sidebar last message
      const chatIndex = conversations.value.findIndex(c => c.id === activeConversation.value.id);
      if (chatIndex !== -1) {
        conversations.value[chatIndex].last_message = newMsg;
        conversations.value[chatIndex].updated_at = new Date().toISOString();
        
        // Move chat to top of list
        const [movedChat] = conversations.value.splice(chatIndex, 1);
        conversations.value.unshift(movedChat);
      }

      scrollToBottom();
    }
  } catch (error) {
    console.error('Failed to send reply', error);
    replyMessage.value = originalMessage; // Restore draft
    toast.error('Failed to deliver message.');
  } finally {
    sendingReply.value = false;
  }
};

// Close Chat Session
const closeActiveChat = async () => {
  if (!activeConversation.value) return;
  closingChat.value = true;

  try {
    const response = await axios.post(`/auth/admin/chat/conversations/${activeConversation.value.id}/close`);
    if (response.data.success) {
      activeConversation.value.status = 'closed';
      
      const chatIndex = conversations.value.findIndex(c => c.id === activeConversation.value.id);
      if (chatIndex !== -1) {
        conversations.value[chatIndex].status = 'closed';
      }
      
      toast.success('Conversation session marked as closed.');
    }
  } catch (error) {
    console.error('Failed to close session', error);
    toast.error('Failed to close conversation session.');
  } finally {
    closingChat.value = false;
  }
};

// Lifecycle hooks
onMounted(async () => {
  await fetchConversations();
  await setupEchoConnection();
});

onBeforeUnmount(() => {
  if (echoInstance) {
    if (activeConversation.value && currentChannel) {
      currentChannel.leave(`chat.${activeConversation.value.id}`);
    }
    echoInstance.disconnect();
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
