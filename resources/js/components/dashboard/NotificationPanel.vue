<template>
  <!-- Backdrop: click anywhere outside closes the panel -->
  <div
    v-if="notification.isOpen"
    class="fixed inset-0 z-[60]"
    @click="notification.closePanel()"
  ></div>

  <transition
    enter-active-class="transition duration-150 ease-out"
    enter-from-class="opacity-0 -translate-y-2 scale-95"
    enter-to-class="opacity-100 translate-y-0 scale-100"
    leave-active-class="transition duration-100 ease-in"
    leave-from-class="opacity-100 scale-100"
    leave-to-class="opacity-0 scale-95"
  >
    <div
      v-if="notification.isOpen"
      class="absolute right-0 top-12 z-[61] w-[22rem] max-w-[calc(100vw-2rem)] origin-top-right"
    >
      <div class="rounded-2xl border border-gray-200/60 dark:border-gray-700/50 bg-white/90 dark:bg-[#161B22]/90 backdrop-blur-xl shadow-2xl overflow-hidden">
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800/60 flex items-center justify-between">
          <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <Bell class="w-4 h-4 text-primary" />
            Notifications
            <span
              v-if="notification.unreadTotal > 0"
              class="min-w-[18px] h-[18px] px-1.5 rounded-full bg-primary text-white text-[10px] font-bold flex items-center justify-center"
            >
              {{ notification.unreadTotal > 99 ? '99+' : notification.unreadTotal }}
            </span>
          </h3>
          <button
            @click="notification.closePanel()"
            class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
          >
            <X class="w-4 h-4 text-gray-400" />
          </button>
        </div>

        <!-- Tabs -->
        <div class="px-3 py-2 flex gap-1 border-b border-gray-100 dark:border-gray-800/60">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="notification.activeTab = tab.key"
            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
            :class="[
              notification.activeTab === tab.key
                ? 'bg-primary/10 text-primary'
                : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'
            ]"
          >
            {{ tab.label }}
            <span
              v-if="tab.key === 'messages' && notification.messageNotifications.length"
              class="ml-1 text-[10px] opacity-70"
            >({{ notification.messageNotifications.length }})</span>
          </button>
        </div>

        <!-- Body -->
        <div class="max-h-[24rem] overflow-y-auto">
          <div v-if="notification.loading" class="flex flex-col items-center justify-center py-10 gap-2">
            <Loader2 class="w-5 h-5 animate-spin text-primary" />
            <span class="text-xs text-gray-400">Loading...</span>
          </div>

          <div
            v-else-if="notification.visibleNotifications.length === 0"
            class="flex flex-col items-center justify-center py-10 px-4 text-center"
          >
            <BellOff class="w-7 h-7 text-gray-300 dark:text-gray-600 mb-2" />
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">No notifications</span>
            <span class="text-[10px] text-gray-400 mt-1">You're all caught up!</span>
          </div>

          <!-- Notification list -->
          <div v-else class="py-1">
            <div
              v-for="item in notification.visibleNotifications"
              :key="item.id"
              @click="handleClick(item)"
              class="group flex items-start gap-3 px-3 py-2.5 cursor-pointer transition-all border-l-2"
              :class="[
                item.is_read
                  ? 'border-transparent hover:bg-gray-50 dark:hover:bg-gray-800/40'
                  : 'border-primary bg-primary/5 hover:bg-primary/10'
              ]"
            >
              <!-- Avatar / Icon -->
              <div class="flex-shrink-0">
                <div
                  v-if="item.type === 'chat_message'"
                  class="w-9 h-9 rounded-full bg-gradient-to-tr from-primary/20 to-primary/10 flex items-center justify-center font-bold text-primary text-sm"
                >
                  {{ getInitial(item) }}
                </div>
                <div
                  v-else
                  class="w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center"
                >
                  <Info class="w-4 h-4 text-gray-500" />
                </div>
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                  <p
                    class="text-xs font-semibold truncate flex-1"
                    :class="item.is_read
                      ? 'text-gray-700 dark:text-gray-300'
                      : 'text-gray-900 dark:text-white'"
                  >
                    {{ item.title }}
                  </p>
                  <span class="text-[9px] text-gray-400 shrink-0">{{ formatTime(item.created_at) }}</span>
                </div>
                <p
                  v-if="item.body"
                  class="text-[11px] text-gray-500 dark:text-gray-400 truncate mt-0.5"
                >
                  {{ item.body }}
                </p>
              </div>

              <!-- Unread dot -->
              <div v-if="!item.is_read" class="w-2 h-2 rounded-full bg-primary shrink-0 mt-1.5"></div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div
          v-if="notification.notifications.length > 0"
          class="px-3 py-2 border-t border-gray-100 dark:border-gray-800/60 flex items-center justify-between bg-gray-50/50 dark:bg-[#0D1117]/40"
        >
          <button
            @click="notification.markAllRead()"
            :disabled="notification.unreadTotal === 0"
            class="text-[11px] font-semibold text-primary hover:text-primary-dark disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
          >
            Mark all read
          </button>
          <button
            @click="goToChat"
            class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 hover:text-primary transition-colors"
          >
            Open chat →
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import { useNotificationStore } from '@/stores/notification';
import { useChatStore } from '@/stores/chat';
import { Bell, BellOff, X, Loader2, Info } from 'lucide-vue-next';

const router = useRouter();
const notification = useNotificationStore();
const chat = useChatStore();

const tabs = [
  { key: 'messages', label: 'Messages' },
  { key: 'all', label: 'All' },
];

const getInitial = (item) => {
  const title = item.title || '';
  const match = title.match(/from\s+(\S+)/i);
  if (match && match[1]) return match[1].charAt(0).toUpperCase();
  return title.charAt(0).toUpperCase() || 'N';
};

const formatTime = (timeString) => {
  if (!timeString) return '';
  const date = new Date(timeString);
  const now = new Date();
  const diffMs = now - date;
  const diffMin = Math.floor(diffMs / 60000);
  if (diffMin < 1) return 'now';
  if (diffMin < 60) return `${diffMin}m`;
  const diffHr = Math.floor(diffMin / 60);
  if (diffHr < 24) return `${diffHr}h`;
  const diffDay = Math.floor(diffHr / 24);
  if (diffDay < 7) return `${diffDay}d`;
  return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
};

const handleClick = async (item) => {
  if (!item.is_read) {
    await notification.markRead(item.id);
  }
  notification.closePanel();

  // Navigate to the chat and open the specific conversation (if applicable).
  if (item.action_url === '/dashboard/chat') {
    const conversationId = item.action_params?.conversationId;
    await router.push('/dashboard/chat');
    if (conversationId) {
      // openConversation is async; fire it after navigation.
      chat.openConversation(Number(conversationId)).catch(() => {});
    }
  } else if (item.action_url) {
    router.push(item.action_url);
  }
};

const goToChat = () => {
  notification.closePanel();
  router.push('/dashboard/chat');
};

// Close the panel when Escape is pressed.
const onKeydown = (e) => {
  if (e.key === 'Escape') notification.closePanel();
};

onMounted(() => {
  document.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
  document.removeEventListener('keydown', onKeydown);
});
</script>
