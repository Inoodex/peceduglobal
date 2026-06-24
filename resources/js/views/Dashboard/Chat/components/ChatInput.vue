<template>
  <div class="p-4 border-t border-gray-100 dark:border-gray-800/60 bg-white dark:bg-[#0D1117]">
    <form @submit.prevent="handleSubmit" class="flex items-end gap-2">
      <!-- Textarea (auto-resizing) -->
      <div class="flex-1 relative">
        <textarea
          ref="textareaRef"
          v-model="text"
          :rows="1"
          :disabled="disabled"
          :placeholder="disabled ? 'This conversation is closed.' : 'Type your response...'"
          @keydown="onKeydown"
          @input="onInput"
          class="w-full resize-none bg-gray-50 dark:bg-[#161B22] border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all disabled:opacity-55 disabled:cursor-not-allowed max-h-32 leading-relaxed"
          style="min-height: 46px;"
        ></textarea>
      </div>

      <!-- Send button -->
      <button
        type="submit"
        :disabled="disabled || sending || !text.trim()"
        class="bg-primary hover:bg-primary-dark text-white rounded-xl p-3 flex items-center justify-center transition-all disabled:opacity-50 disabled:cursor-not-allowed shrink-0 shadow-sm h-[46px] w-[46px]"
        :title="canSend ? 'Send (Enter)' : 'Type a message'"
      >
        <Loader2 v-if="sending" class="w-4 h-4 animate-spin" />
        <Send v-else class="w-4 h-4" />
      </button>
    </form>

    <!-- Hint -->
    <div class="flex items-center justify-between mt-1.5 px-1">
      <span class="text-[9px] text-gray-400 dark:text-gray-500">
        <kbd class="px-1 py-0.5 rounded bg-gray-100 dark:bg-gray-800 font-mono">Enter</kbd> to send ·
        <kbd class="px-1 py-0.5 rounded bg-gray-100 dark:bg-gray-800 font-mono">Shift + Enter</kbd> for new line
      </span>
      <span v-if="text.length > 0" class="text-[9px]" :class="text.length > 1000 ? 'text-red-400' : 'text-gray-400'">
        {{ text.length }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, nextTick, onMounted } from 'vue';
import { Send, Loader2 } from 'lucide-vue-next';

const props = defineProps({
  modelValue: { type: String, default: '' },
  disabled: { type: Boolean, default: false }, // conversation closed
  sending: { type: Boolean, default: false },  // store.sendingReply
});

const emit = defineEmits(['update:modelValue', 'send', 'typing']);

const textareaRef = ref(null);
const text = ref(props.modelValue);

// Throttling for typing events — avoid flooding the server/transport.
let lastTypingAt = 0;
const TYPING_THROTTLE_MS = 2000;

/* --------------------------- Auto-resize logic --------------------------- */
const autoResize = async () => {
  await nextTick();
  const el = textareaRef.value;
  if (!el) return;
  el.style.height = 'auto';
  // Cap at ~5 lines (max-h-32 ≈ 128px)
  el.style.height = Math.min(el.scrollHeight, 128) + 'px';
};

const onInput = () => {
  emit('update:modelValue', text.value);
  autoResize();

  // Typing event (throttled). The actual endpoint wiring lands in step 4;
  // emitting here keeps the component ready.
  const now = Date.now();
  if (text.value.trim() && now - lastTypingAt > TYPING_THROTTLE_MS) {
    lastTypingAt = now;
    emit('typing');
  }
};

/* ----------------------------- Key handling ----------------------------- */
const onKeydown = (e) => {
  // Enter = send, Shift+Enter (or mobile) = newline
  if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
    e.preventDefault();
    handleSubmit();
  }
};

const handleSubmit = () => {
  if (props.disabled || props.sending || !text.value.trim()) return;
  emit('send', text.value.trim());
};

/* ----------------------- Sync external modelValue ----------------------- */
watch(
  () => props.modelValue,
  (val) => {
    if (val !== text.value) text.value = val;
    autoResize();
  }
);

// Clear local text when the parent resets the modelValue to ''
watch(text, (val) => {
  if (!val) autoResize();
});

onMounted(() => autoResize());

// Expose focus + clear for the parent (ChatWindow)
defineExpose({
  focus: () => textareaRef.value?.focus(),
  clear: () => {
    text.value = '';
    emit('update:modelValue', '');
    autoResize();
  },
});
</script>
