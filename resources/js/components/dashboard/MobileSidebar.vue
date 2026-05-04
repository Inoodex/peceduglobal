<template>
  <div class="lg:hidden">
    <!-- Backdrop -->
    <Transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-300"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div 
        v-if="layout.isMobileMenuOpen" 
        @click="layout.toggleMobileMenu"
        class="fixed inset-0 z-[70] bg-black/40"
      ></div>
    </Transition>

    <!-- Drawer -->
    <aside
      :class="[
        'fixed top-0 left-0 h-screen w-[280px] z-[80] transition-transform duration-300 transform border-r border-dashed',
        layout.isDarkMode ? 'bg-[#161C24] border-gray-800' : 'bg-white border-gray-200',
        layout.isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <NavContent />
    </aside>
  </div>
</template>

<script setup>
import { useLayoutStore } from '@/stores/layout';
import NavContent from './NavContent.vue';

const layout = useLayoutStore();
</script>
