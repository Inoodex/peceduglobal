<template>
  <div class="relative w-full">
    <!-- Tree Branch Lines for children (drawn by the child itself) -->
    <template v-if="level > 0 && !isCollapsed">
      <!-- 1. The curve into this item's button -->
      <div 
        class="absolute border-l border-b border-gray-500/20 pointer-events-none"
        :style="{
          left: `${branchLeft}px`,
          top: '-4px',
          height: '24px',
          width: '15px', /* Exactly bridges branchLeft to itemMarginLeft */
          borderBottomLeftRadius: '8px'
        }"
      ></div>
      
      <!-- 2. The trunk continuing down to the next sibling (only if NOT last) -->
      <div 
        v-if="!isLast"
        class="absolute border-l border-gray-500/20 pointer-events-none"
        :style="{
          left: `${branchLeft}px`,
          top: '20px',
          bottom: '-4px'
        }"
      ></div>
    </template>

    <button
      v-ripple
      @click="toggle"
      class="flex transition-all duration-200 relative group rounded-md"
      :class="[
        isCollapsed ? 'flex-col items-center justify-center py-2.5 px-1 w-full' : (level === 0 ? 'flex-row items-center px-4 py-2 w-full' : 'flex-row items-center px-3 py-2'),
        item.active && level === 0
          ? 'text-[var(--nav-item-root-active-color)] bg-[var(--nav-item-root-active-bg)] hover:bg-[var(--nav-item-root-active-hover-bg)] font-bold'
          : (item.active && level > 0 
              ? (layout.isDarkMode ? 'text-white bg-white/5 hover:bg-white/10 font-bold' : 'text-gray-900 bg-gray-500/5 hover:bg-gray-500/10 font-bold') 
              : (layout.isDarkMode ? 'text-gray-500 hover:bg-white/5' : 'text-gray-600 hover:bg-gray-500/5')),
        level > 0 ? 'h-10 text-sm' : ''
      ]"
      :style="level > 0 && !isCollapsed ? { marginLeft: `${itemMarginLeft}px`, width: `calc(100% - ${itemMarginLeft}px)` } : {}"
    >
      <!-- Icon (only level 0) -->
      <div v-if="level === 0" class="flex items-center justify-center w-[22px] mr-4">
        <component v-if="item.icon" :is="item.icon" :size="isCollapsed ? 22 : 22" :stroke-width="item.active ? 2.5 : 2" />
      </div>

      <span 
        class="transition-all text-left flex-1"
        :class="[
          isCollapsed ? 'text-[10px] mt-1 font-bold text-center mr-0' : 'text-sm',
          item.active && level > 0 ? 'font-bold' : '',
          item.active && level === 0 ? 'font-bold' : ''
        ]"
      >
        {{ item.name }}
      </span>

      <!-- Tooltip for compact mode -->
      <div 
        v-if="isCollapsed && level === 0"
        class="absolute left-full ml-4 px-2 py-1 bg-gray-900 text-white text-[11px] font-bold rounded opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-[70] hidden lg:block shadow-2xl border border-white/5"
      >
        {{ item.name }}
      </div>

      <!-- Chevron for children -->
      <ChevronRight 
        v-if="!isCollapsed && hasChildren" 
        :size="16" 
        class="ml-auto transition-transform duration-200" 
        :class="[
          isOpen ? 'rotate-90 text-gray-400' : 'opacity-40'
        ]"
      />
    </button>

    <!-- Children -->
    <div v-if="!isCollapsed && hasChildren && isOpen" class="relative mt-1 space-y-1">
      <NavItem
        v-for="(child, idx) in item.children"
        :key="child.name"
        :item="child"
        :level="level + 1"
        :is-collapsed="isCollapsed"
        :is-last="idx === item.children.length - 1"
      />
    </div>
  </div>
</template>

<script>
// We declare the name to allow recursive rendering
export default {
  name: 'NavItem'
}
</script>

<script setup>
import { ref, computed } from 'vue';
import { ChevronRight } from 'lucide-vue-next';
import { useLayoutStore } from '@/stores/layout';

const props = defineProps({
  item: { type: Object, required: true },
  level: { type: Number, default: 0 },
  isCollapsed: { type: Boolean, default: false },
  isLast: { type: Boolean, default: false }
});

const layout = useLayoutStore();

// Default open if specified or if it contains an active child
const isOpen = ref(props.item.open || false);

const hasChildren = computed(() => props.item.children && props.item.children.length > 0);

const toggle = () => {
  if (hasChildren.value) {
    isOpen.value = !isOpen.value;
  }
};

// Math for tree lines:
// branchLeft is horizontally aligned to the center of the previous level's logical parent point.
const branchLeft = computed(() => 27 + ((props.level - 1) * 20));

// itemMarginLeft places the background box precisely at the end of the branch curve width (15px)
const itemMarginLeft = computed(() => 42 + ((props.level - 1) * 20));
</script>

<style scoped>
/* Inline dynamic styles handle tree lines flawlessly */
</style>
