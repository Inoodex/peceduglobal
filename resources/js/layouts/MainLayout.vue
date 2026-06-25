<template>
  <div 
    :class="[
      'min-h-screen transition-colors duration-300',
      layout.isDarkMode ? 'bg-bg-dark text-white' : 'bg-bg-light text-text-primary-light'
    ]"
  >
    <!-- Sidebar (Desktop) -->
    <DashboardSidebar />

    <!-- Sidebar (Mobile) -->
    <MobileSidebar />

    <!-- Header -->
    <DashboardHeader />

    <!-- Settings Drawer -->
    <SettingsDrawer />

    <!-- Profile Drawer -->
    <ProfileDrawer />

    <!-- Main Content -->
    <main
      :class="[
        'transition-all duration-300 pt-24 px-4 lg:px-8 pb-12',
        layout.isSidebarCollapsed ? 'lg:ml-[88px]' : 'lg:ml-[280px]'
      ]"
    >
      <slot />
    </main>

    <!-- Global Components -->
    <ToastContainer />
    <GlobalConfirm />
  </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount } from 'vue';
import { useLayoutStore } from '@/stores/layout';
import { useChatStore } from '@/stores/chat';
import { useNotificationStore } from '@/stores/notification';
import { useAuthStore } from '@/stores/auth';
import DashboardSidebar from '@/components/dashboard/DashboardSidebar.vue';
import MobileSidebar from '@/components/dashboard/MobileSidebar.vue';
import DashboardHeader from '@/components/dashboard/DashboardHeader.vue';
import SettingsDrawer from '@/components/dashboard/SettingsDrawer.vue';
import ProfileDrawer from '@/components/dashboard/ProfileDrawer.vue';
import ToastContainer from '@/components/ui/ToastContainer.vue';
import GlobalConfirm from '@/components/ui/GlobalConfirm.vue';

const layout = useLayoutStore();
const chat = useChatStore();
const notification = useNotificationStore();
const auth = useAuthStore();

onMounted(async () => {
  layout.applyAllSettings();

  // Ensure the current user profile is loaded before subscribing to channels
  if (!auth.user) {
    await auth.fetchUser().catch(() => {});
  }

  // Start the global chat listener
  try {
    await chat.fetchConversations();
    await chat.subscribeGlobal();
  } catch (e) {
    console.error('Chat global listener init failed', e);
  }

  // Load existing notifications + unread badge on every dashboard page.
  try {
    await Promise.all([
      notification.fetchNotifications(),
      notification.fetchUnreadCount(),
    ]);
  } catch (e) {
    console.error('Notification store init failed', e);
  }

  // Subscribe to the private Pusher channel for real-time notifications
  // (appointments, applications, inquiries, etc.)
  // Reuses the chat store's Echo connection — same Pusher instance, no extra connection.
  if (auth.user?.id && chat.echo) {
    notification.subscribeToUserChannel(auth.user.id, chat.echo);
  }

  // Politely request desktop-notification permission (used when the tab is
  // hidden and a new guest message arrives). No-op if unsupported/denied.
  try {
    if (typeof Notification !== 'undefined' && Notification.permission === 'default') {
      Notification.requestPermission();
    }
  } catch (_) { /* ignore */ }
});

onBeforeUnmount(() => {
  // Leave the notification channel when dashboard unmounts (logout).
  if (auth.user?.id) {
    notification.unsubscribeFromUserChannel(auth.user.id, chat.echo);
  }
});
</script>
