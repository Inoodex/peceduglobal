<template>
  <aside
    :class="[
      'fixed top-0 left-0 h-screen transition-all duration-300 z-50 border-r border-dashed hidden lg:block',
      layout.isDarkMode ? 'bg-[#161C24] border-gray-800' : 'bg-white border-gray-200',
      layout.isSidebarCollapsed ? 'w-[88px]' : 'w-[280px]'
    ]"
  >
    <!-- Toggle Button: Darker, smaller, and placed exactly on the dashed line -->
    <button 
      v-ripple
      @click="layout.toggleSidebar"
      class="absolute top-6 -right-[11px] w-[22px] h-[22px] rounded-full flex items-center justify-center transition-all z-50 border border-dashed hover:scale-110 active:scale-95"
      :class="[
        layout.isDarkMode 
          ? 'bg-[#161C24] border-gray-700 text-gray-500 hover:text-primary shadow-lg' 
          : 'bg-white border-gray-200 text-gray-400 hover:text-primary shadow-md'
      ]"
    >
      <ChevronLeft v-if="!layout.isSidebarCollapsed" :size="10" stroke-width="4" />
      <ChevronRight v-else :size="10" stroke-width="4" />
    </button>

    <!-- Nav Content -->
    <NavContent :is-collapsed="layout.isSidebarCollapsed" />
  </aside>
</template>

<script setup>
import { useLayoutStore } from '@/stores/layout';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import NavContent from './NavContent.vue';

const layout = useLayoutStore();
</script>

<style scoped>
/* No specific styles needed anymore as they are in NavContent */
</style>
