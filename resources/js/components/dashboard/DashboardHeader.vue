<template>
  <header
    :class="[
      'fixed top-0 right-0 h-20 transition-all duration-300 z-40 flex items-center justify-between px-4 lg:px-6 glass',
      layout.isDarkMode ? 'bg-bg-dark/80 text-white' : 'bg-bg-light/80 text-text-primary-light',
      layout.isSidebarCollapsed ? 'left-0 lg:left-[88px]' : 'left-0 lg:left-[280px]'
    ]"
  >
    <!-- Left: Mobile Menu Toggle (Only visible on small screens) -->
    <div class="flex items-center gap-4">
      <button 
        v-ripple
        @click="layout.toggleMobileMenu"
        class="p-2 rounded-full hover:bg-gray-500/10 transition-colors lg:hidden"
      >
        <Menu :size="24" class="text-gray-500" />
      </button>
    </div>

    <!-- Right: Icons & Search -->
    <div class="flex items-center gap-1 sm:gap-2 lg:gap-4">
      <!-- Search: Icon on small screens, Badge on large screens -->
      <button v-ripple class="flex items-center gap-2 p-2 lg:px-3 lg:py-1.5 rounded-full lg:rounded-lg bg-gray-500/10 hover:bg-gray-500/30 transition-colors duration-200">
        <Search :size="20" class="text-gray-500 hover:text-primary" />
        <div class="hidden lg:flex items-center gap-1 px-1.5 py-0.5 rounded bg-gray-500/15 text-[10px] font-bold text-gray-500 border border-gray-500/10">
          <span class="text-[12px]">⌘</span>K
        </div>
      </button>

      <!-- Notifications (dropdown panel) -->
      <div class="relative">
        <button
          v-ripple
          @click.stop="notification.togglePanel()"
          class="p-2 rounded-full hover:bg-gray-500/10 transition-colors relative"
          title="Notifications"
        >
          <Bell :size="22" class="text-gray-500" />
          <span
            v-if="notification.unreadTotal > 0"
            class="absolute top-1 right-1 min-w-[16px] h-4 px-1 bg-red-500 text-white text-[10px] font-bold flex items-center justify-center rounded-full border-2 border-bg-dark animate-pulse"
          >
            {{ notification.unreadTotal > 99 ? '99+' : notification.unreadTotal }}
          </span>
        </button>

        <!-- Notification dropdown -->
        <NotificationPanel />
      </div>

      <!-- Settings -->
      <button 
        v-ripple
        @click="layout.toggleSettings"
        class="p-2 rounded-full hover:bg-gray-500/10 transition-colors animate-spin-slow"
      >
        <Settings :size="22" class="text-gray-500" />
      </button>

      <!-- User Profile -->
      <div 
        v-ripple 
        @click="layout.toggleProfile"
        class="ml-1 sm:ml-2 w-10 h-10 rounded-full border-2 border-primary/20 p-0.5 cursor-pointer hover:bg-primary/10 transition-all overflow-hidden"
      >
        <img 
          :src="auth.user?.profile_photo_url || `https://api.dicebear.com/7.x/avataaars/svg?seed=${auth.user?.full_name}`" 
          alt="User" 
          class="w-full h-full rounded-full bg-gray-200 object-cover"
        />
      </div>
    </div>
  </header>
</template>

<script setup>
import { useLayoutStore } from '@/stores/layout';
import { useAuthStore } from '@/stores/auth';
import { useChatStore } from '@/stores/chat';
import { useNotificationStore } from '@/stores/notification';
import NotificationPanel from '@/components/dashboard/NotificationPanel.vue';
import { Search, Bell, Users, Settings, Menu } from 'lucide-vue-next';

const layout = useLayoutStore();
const auth = useAuthStore();
const chat = useChatStore();
const notification = useNotificationStore();
</script>

<style scoped>
.animate-spin-slow {
  animation: spin 8s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
