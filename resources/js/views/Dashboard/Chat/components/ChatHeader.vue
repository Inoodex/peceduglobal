<template>
  <div class="p-4 border-b border-gray-100 dark:border-gray-800/60 flex items-center justify-between bg-white dark:bg-[#0D1117]">
    <!-- Left: guest info -->
    <div class="flex items-center gap-3 min-w-0">
      <!-- Mobile back to sidebar -->
      <button
        @click="$emit('back')"
        class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
      >
        <ArrowLeft class="w-4 h-4 text-gray-500" />
      </button>

      <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-primary/20 to-primary/10 flex items-center justify-center font-bold text-primary dark:from-primary/30 dark:to-primary/20 flex-shrink-0">
        {{ avatarInitial }}
      </div>
      <div class="min-w-0">
        <div class="text-sm font-bold text-gray-900 dark:text-white truncate">
          {{ conversation.guest?.email || 'Guest Visitor' }}
        </div>
        <div class="flex items-center gap-1.5 mt-0.5">
          <span
            class="w-2 h-2 rounded-full"
            :class="conversation.status === 'open' ? 'bg-emerald-500' : 'bg-gray-400'"
          ></span>
          <span class="text-[10px] text-gray-500 dark:text-gray-400 capitalize">
            {{ conversation.status }} Conversation
          </span>
          <!-- Typing indicator -->
          <span
            v-if="isTyping"
            class="text-[10px] text-primary font-semibold ml-1 flex items-center gap-1"
          >
            typing
            <span class="flex gap-0.5">
              <span class="w-1 h-1 rounded-full bg-primary animate-bounce" style="animation-delay: 0ms"></span>
              <span class="w-1 h-1 rounded-full bg-primary animate-bounce" style="animation-delay: 150ms"></span>
              <span class="w-1 h-1 rounded-full bg-primary animate-bounce" style="animation-delay: 300ms"></span>
            </span>
          </span>
        </div>
      </div>
    </div>

    <!-- Right: actions -->
    <div class="flex items-center gap-2 flex-shrink-0">
      <button
        v-if="conversation.status === 'open'"
        @click="$emit('close')"
        :disabled="closing"
        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 dark:border-red-900/40 hover:bg-red-50 dark:hover:bg-red-950/20 text-xs font-semibold text-red-600 dark:text-red-400 transition-all disabled:opacity-55"
      >
        <XCircle class="w-3.5 h-3.5" />
        Close Session
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { ArrowLeft, XCircle } from 'lucide-vue-next';

const props = defineProps({
  conversation: { type: Object, required: true },
  closing: { type: Boolean, default: false },
  isTyping: { type: Boolean, default: false },
});

defineEmits(['close', 'back']);

const avatarInitial = computed(() => {
  const email = props.conversation.guest?.email;
  return email ? email.charAt(0).toUpperCase() : 'G';
});
</script>
