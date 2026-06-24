<template>
  <div
    @click="$emit('select', conv)"
    class="group flex items-center gap-3 p-3 rounded-xl cursor-pointer transition-all duration-200 select-none"
    :class="[
      isActive
        ? 'bg-primary/10 border-primary/20 dark:bg-primary/15'
        : 'hover:bg-gray-100 dark:hover:bg-gray-800/40 border-transparent'
    ]"
  >
    <!-- Avatar -->
    <div class="relative flex-shrink-0">
      <div
        class="w-10 h-10 rounded-full bg-gradient-to-tr from-primary/20 to-primary/10 flex items-center justify-center font-bold text-primary text-sm dark:from-primary/30 dark:to-primary/20"
      >
        {{ avatarInitial }}
      </div>
      <span
        v-if="conv.status === 'open'"
        class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white dark:border-[#0D1117] rounded-full"
        title="Active"
      ></span>
    </div>

    <!-- Chat Info -->
    <div class="flex-1 min-w-0">
      <div class="flex items-center justify-between">
        <span
          class="text-xs truncate"
          :class="[
            hasUnread
              ? 'font-bold text-gray-900 dark:text-white'
              : 'font-semibold text-gray-700 dark:text-gray-300'
          ]"
        >
          {{ conv.guest?.email || 'Guest Visitor' }}
        </span>
        <span class="text-[10px] text-gray-400 shrink-0 ml-2">
          {{ formatTime(conv.updated_at) }}
        </span>
      </div>
      <p
        class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5"
        :class="{ 'font-semibold text-gray-900 dark:text-white': hasUnread }"
      >
        {{ lastMessageText }}
      </p>
    </div>

    <!-- Status / Unread badge -->
    <div class="flex flex-col items-end gap-1.5">
      <span
        v-if="conv.status === 'closed'"
        class="text-[9px] px-1.5 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 font-medium"
      >
        Closed
      </span>
      <span
        v-if="hasUnread"
        class="min-w-[18px] h-[18px] px-1.5 rounded-full bg-primary text-white text-[10px] font-bold flex items-center justify-center animate-pulse"
      >
        {{ conv.unread_count > 99 ? '99+' : conv.unread_count }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  conv: { type: Object, required: true },
  isActive: { type: Boolean, default: false },
});

defineEmits(['select']);

const avatarInitial = computed(() => {
  const email = props.conv.guest?.email;
  return email ? email.charAt(0).toUpperCase() : 'G';
});

const hasUnread = computed(() => Number(props.conv.unread_count || 0) > 0);

const lastMessageText = computed(() => {
  return props.conv.last_message?.message
    || props.conv.lastMessage?.message
    || 'Start chatting...';
});

const formatTime = (timeString) => {
  if (!timeString) return '';
  const date = new Date(timeString);
  const now = new Date();
  const diffMs = now - date;
  const diffMin = Math.floor(diffMs / 60000);
  if (diffMin < 1) return 'now';
  if (diffMin < 60) return `${diffMin}m`;
  const diffHr = Math.floor(diffMin / 60);
  if (diffHr < 24) return `${diffHr}h`;
  const diffDay = Math.floor(diffHr / 24);
  if (diffDay < 7) return `${diffDay}d`;
  return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
};
</script>
