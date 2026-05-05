<template>
  <div class="relative w-full">
    <!-- Tree Branch Lines for children (drawn by the child itself) -->
    <template v-if="level > 0 && !isCollapsed">
      <!-- 1. Top half of the vertical trunk (always drawn) -->
      <div
        class="absolute border-l border-gray-500/40 pointer-events-none z-10"
        :style="{ left: `${branchLeft}px`, top: '-4px', height: '24px' }"
      ></div>

      <!-- 2. Bottom half of the vertical trunk (drawn if NOT last) -->
      <div
        v-if="!isLast"
        class="absolute border-l border-gray-500/40 pointer-events-none z-10"
        :style="{ left: `${branchLeft}px`, top: '20px', bottom: '0px' }"
      ></div>

      <!-- 3. Horizontal connection line -->
      <div
        class="absolute border-t border-gray-500/40 pointer-events-none z-10"
        :style="{ left: `${branchLeft}px`, top: '20px', width: '12px' }"
      ></div>

      <!-- 4. Tiny Dot at the end of the line -->
      <div
        class="absolute rounded-full pointer-events-none z-10 transition-colors"
        :class="item.active ? 'bg-primary' : 'bg-gray-500/40'"
        :style="{ left: `${branchLeft + 11}px`, top: '18.5px', width: '4px', height: '4px' }"
      ></div>
    </template>

    <!-- Hover Popover Wrapper -->
    <HoverPopover :disabled="!isCollapsed || level > 0">

      <!-- Trigger Slot -->
      <template #trigger="{ onMouseEnter, onMouseLeave }">
        <!-- The Button -->
        <button
          v-ripple
          @click="toggle"
          @mouseenter="onMouseEnter"
          @mouseleave="onMouseLeave"
          class="flex transition-all duration-200 relative group rounded-md"
          :class="[
            isCollapsed ? 'flex-col items-center justify-center py-2.5 px-1 w-full' : (level === 0 ? 'flex-row items-center px-4 py-2 w-full' : 'flex-row items-center py-2 pr-4'),
            item.active && level === 0
              ? 'text-(--nav-item-root-active-color) bg-(--nav-item-root-active-bg) hover:bg-(--nav-item-root-active-hover-bg) font-bold'
              : (item.active && level > 0
                  ? (layout.isDarkMode ? 'text-white bg-white/10 hover:bg-white/10 font-bold' : 'text-gray-900 bg-gray-500/10 hover:bg-gray-500/10 font-bold')
                  : (layout.isDarkMode ? 'text-gray-400 hover:bg-white/5' : 'text-gray-600 hover:bg-gray-500/5')),
            level > 0 ? 'h-10 text-sm' : ''
          ]"
          :style="level > 0 && !isCollapsed ? { marginLeft: `${itemMarginLeft}px`, width: `calc(100% - ${itemMarginLeft}px)`, paddingLeft: '11px' } : {}"
        >
          <!-- Icon (only level 0) -->
          <div v-if="level === 0" class="relative flex items-center justify-center w-[22px]" :class="isCollapsed ? '' : 'mr-4'">
            <component v-if="item.icon" :is="item.icon" :size="isCollapsed ? 22 : 22" :stroke-width="item.active ? 2.5 : 2" />

            <!-- Compact Mode Chevron indicator (right side of icon) -->
            <ChevronRight
              v-if="isCollapsed && hasChildren"
              :size="12"
              class="absolute -right-4 top-1/2 -translate-y-1/2 text-gray-500"
            />
          </div>

          <span
            class="transition-all text-center flex-1"
            :class="[
              isCollapsed ? 'text-[10px] mt-1 font-bold mr-0' : 'text-sm text-left',
              item.active && level > 0 ? 'font-bold' : '',
              item.active && level === 0 ? 'font-bold' : ''
            ]"
          >
            {{ item.name }}
          </span>

          <!-- Expanded Mode Chevron -->
          <ChevronRight
            v-if="!isCollapsed && hasChildren"
            :size="16"
            class="ml-auto transition-transform duration-200"
            :class="[ isOpen ? 'rotate-90 text-gray-400' : 'opacity-40' ]"
          />
        </button>
      </template>

      <!-- Content Slot -->
      <template #content>
        <!-- The actual Popover Menu (If has children) -->
        <div
          v-if="hasChildren"
          class="w-48 border shadow-[0_20px_40px_-4px_rgba(0,0,0,0.24)] rounded-xl p-1.5 flex flex-col gap-0.5 backdrop-blur-md"
          :class="layout.isDarkMode ? 'bg-[#212B36]/90 border-gray-500/10' : 'bg-white/90 border-gray-200'"
        >
          <button
            v-for="child in item.children"
            :key="child.name"
            @click.stop="navigate(child.path)"
            class="w-full text-left px-3 py-2 text-[14px] font-medium transition-colors rounded-lg"
            :class="child.active
              ? (layout.isDarkMode ? 'text-white bg-gray-500/20 font-semibold' : 'text-gray-900 bg-gray-500/10 font-semibold')
              : (layout.isDarkMode ? 'text-gray-400 hover:text-white hover:bg-gray-500/10' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-500/5')"
          >
            {{ child.name }}
          </button>
        </div>

        <!-- Simple Tooltip (If NO children) -->
        <div v-else class="px-2 py-1 text-white text-[11px] font-bold rounded shadow-2xl border border-white/5 whitespace-nowrap h-fit mt-3 backdrop-blur-md" :class="layout.isDarkMode ? 'bg-gray-800/90' : 'bg-gray-900/90'">
          {{ item.name }}
        </div>
      </template>

    </HoverPopover>

    <!-- Children with Smooth Slide Transition -->
    <CollapseTransition>
      <div v-if="!isCollapsed && hasChildren && isOpen" class="relative overflow-hidden">
        <div class="pt-1 space-y-1">
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
    </CollapseTransition>
  </div>
</template>

<script>
export default { name: 'NavItem' }
</script>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { ChevronRight } from 'lucide-vue-next';
import { useLayoutStore } from '@/stores/layout';
import CollapseTransition from './CollapseTransition.vue';
import HoverPopover from './HoverPopover.vue';

const props = defineProps({
  item: { type: Object, required: true },
  level: { type: Number, default: 0 },
  isCollapsed: { type: Boolean, default: false },
  isLast: { type: Boolean, default: false }
});

const layout = useLayoutStore();
const router = useRouter();
const isOpen = ref(props.item.open || false);
const hasChildren = computed(() => props.item.children && props.item.children.length > 0);

const toggle = () => {
  if (hasChildren.value) {
    isOpen.value = !isOpen.value;
  } else if (props.item.path) {
    router.push(props.item.path);
  }
};

const navigate = (path) => {
  if (path) {
    router.push(path);
  }
};

const branchLeft = computed(() => 27 + ((props.level - 1) * 20));
const itemMarginLeft = computed(() => branchLeft.value + 16);
</script>

<style scoped>
/* Inline dynamic styles handle tree lines flawlessly */
</style>
