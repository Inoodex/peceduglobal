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

    <!-- Main Content -->
    <main
      :class="[
        'transition-all duration-300 pt-24 px-4 lg:px-8 pb-12',
        layout.isSidebarCollapsed ? 'lg:ml-[88px]' : 'lg:ml-[280px]'
      ]"
    >
      <slot />
    </main>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useLayoutStore } from '@/stores/layout';
import DashboardSidebar from '@/components/dashboard/DashboardSidebar.vue';
import MobileSidebar from '@/components/dashboard/MobileSidebar.vue';
import DashboardHeader from '@/components/dashboard/DashboardHeader.vue';
import SettingsDrawer from '@/components/dashboard/SettingsDrawer.vue';

const layout = useLayoutStore();

onMounted(() => {
  layout.updateBodyClass();
});
</script>
