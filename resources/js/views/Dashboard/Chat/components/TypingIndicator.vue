<template>
  <!-- Bubble-style typing dots shown inline at the bottom of the message list -->
  <div class="flex items-end gap-2 max-w-[80%]">
    <!-- Guest avatar (mirrors ChatMessageBubble alignment) -->
    <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-primary/20 to-primary/10 flex items-center justify-center font-bold text-primary text-[11px] dark:from-primary/30 dark:to-primary/20 flex-shrink-0 mb-1">
      {{ avatarInitial }}
    </div>

    <div
      class="px-4 py-3 rounded-2xl rounded-tl-md bg-white dark:bg-[#161B22] border border-gray-100 dark:border-gray-800 shadow-sm"
    >
      <div class="flex items-center gap-1">
        <span
          v-for="(dot, i) in 3"
          :key="i"
          class="w-1.5 h-1.5 rounded-full bg-gray-400 dark:bg-gray-500 animate-bounce"
          :style="{ animationDelay: `${i * 150}ms` }"
        ></span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  guestEmail: { type: String, default: '' },
});

const avatarInitial = computed(() => {
  return props.guestEmail ? props.guestEmail.charAt(0).toUpperCase() : 'G';
});
</script>
