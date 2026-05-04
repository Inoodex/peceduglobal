<template>
  <!-- The trigger slot. User binds these events to their trigger element -->
  <slot name="trigger" :onMouseEnter="onTriggerEnter" :onMouseLeave="onTriggerLeave"></slot>

  <!-- The teleported popover -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="transform opacity-0 scale-95 translate-x-[-10px]"
      enter-to-class="transform opacity-100 scale-100 translate-x-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="transform opacity-100 scale-100 translate-x-0"
      leave-to-class="transform opacity-0 scale-95 translate-x-[-10px]"
    >
      <div 
        v-if="isVisible && !disabled"
        class="fixed z-[100] flex pointer-events-auto"
        :style="popoverStyle"
        @mouseenter="onPopoverEnter"
        @mouseleave="onPopoverLeave"
      >
        <!-- Transparent bridge prevents mouseleave event when cursor moves between trigger and popover -->
        <div class="absolute -left-4 top-0 w-4 h-full bg-transparent"></div>
        
        <slot name="content"></slot>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  disabled: {
    type: Boolean,
    default: false
  },
  offset: {
    type: Number,
    default: 12
  }
});

const isVisible = ref(false);
const popoverStyle = ref({});
let hoverTimeout = null;

const onTriggerEnter = (e) => {
  if (props.disabled) return;
  clearTimeout(hoverTimeout);
  
  // Calculate position dynamically relative to the viewport
  const rect = e.currentTarget.getBoundingClientRect();
  popoverStyle.value = {
    top: `${rect.top}px`,
    left: `${rect.right + props.offset}px`
  };
  
  isVisible.value = true;
};

const onTriggerLeave = () => {
  if (props.disabled) return;
  hoverTimeout = setTimeout(() => {
    isVisible.value = false;
  }, 150);
};

const onPopoverEnter = () => {
  clearTimeout(hoverTimeout);
};

const onPopoverLeave = () => {
  hoverTimeout = setTimeout(() => {
    isVisible.value = false;
  }, 150);
};
</script>
