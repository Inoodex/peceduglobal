<template>
  <div class="flex-1 flex flex-col items-center justify-center text-center p-8 bg-gray-50/20 dark:bg-[#0D1117]">
    <div class="relative mb-4">
      <div class="absolute inset-0 bg-primary/20 rounded-full blur-xl animate-pulse"></div>
      <div class="relative w-16 h-16 rounded-full bg-gradient-to-tr from-primary/15 to-primary/5 flex items-center justify-center border border-primary/20">
        <component :is="icon" class="w-8 h-8 text-primary" />
      </div>
    </div>
    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ title }}</h3>
    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mt-1">
      {{ subtitle }}
    </p>
    <div v-if="hint" class="mt-4 flex items-center gap-2 text-[10px] text-gray-400 dark:text-gray-500">
      <component :is="hintIcon" class="w-3 h-3" />
      <span>{{ hint }}</span>
    </div>
  </div>
</template>

<script setup>
import { MessageSquare, Hand } from 'lucide-vue-next';

defineProps({
  title: { type: String, default: 'Welcome to Chat Inbox' },
  subtitle: {
    type: String,
    default: 'Select a guest conversation from the sidebar to view message history, reply in real-time, or manage support sessions.',
  },
  hint: { type: String, default: 'New guest messages appear here instantly' },
});

const icon = MessageSquare;
const hintIcon = Hand;
</script>
