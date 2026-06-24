<template>
  <div class="flex items-center justify-center my-3 select-none">
    <div class="flex-1 h-px bg-gray-200 dark:bg-gray-800"></div>
    <span class="px-3 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide whitespace-nowrap">
      {{ label }}
    </span>
    <div class="flex-1 h-px bg-gray-200 dark:bg-gray-800"></div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  date: { type: [String, Number, Date], default: null },
});

const label = computed(() => {
  if (!props.date) return '';
  const d = new Date(props.date);
  if (isNaN(d.getTime())) return '';

  const today = new Date();
  const yesterday = new Date();
  yesterday.setDate(today.getDate() - 1);

  const isSameDay = (a, b) =>
    a.getFullYear() === b.getFullYear() &&
    a.getMonth() === b.getMonth() &&
    a.getDate() === b.getDate();

  if (isSameDay(d, today)) return 'Today';
  if (isSameDay(d, yesterday)) return 'Yesterday';

  return d.toLocaleDateString([], {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    year: d.getFullYear() === today.getFullYear() ? undefined : 'numeric',
  });
});
</script>
