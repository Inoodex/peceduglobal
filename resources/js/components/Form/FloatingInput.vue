<template>
  <div class="relative w-full" ref="inputRef">
    <!-- Input Wrapper -->
    <div
      class="relative flex items-center w-full min-h-[48px] px-3.5 bg-white dark:bg-[#1C252E] border rounded-xl transition-all duration-200"
      :class="[
        isFocused 
          ? 'border-primary ring-2 ring-primary/10' 
          : 'border-gray-200 dark:border-gray-700/80 hover:border-gray-300 dark:hover:border-gray-600',
        hasValue ? 'border-gray-200 dark:border-gray-700/80' : ''
      ]"
    >
      <!-- Floating Label -->
      <span
        class="absolute left-3 px-1 font-semibold pointer-events-none transition-all duration-200 bg-white dark:bg-[#1C252E] text-xs"
        :class="[
          (isFocused || hasValue) 
            ? '-top-2.5 text-primary' 
            : 'top-3.5 text-gray-500 dark:text-gray-400'
        ]"
      >
        {{ label }}
      </span>

      <!-- Actual Input -->
      <input
        v-model="internalValue"
        :type="type"
        :placeholder="hasValue ? '' : placeholder"
        class="w-full bg-transparent border-none outline-none pt-2 pb-1 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-0 caret-primary transition-all"
        :class="{ 'pt-3.5': !isFocused && !hasValue }"
        @focus="isFocused = true"
        @blur="isFocused = false"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, required: true },
  placeholder: { type: String, default: '' },
  type: { type: String, default: 'text' },
});

const emit = defineEmits(['update:modelValue']);

const isFocused = ref(false);
const inputRef = ref(null);

const internalValue = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
});

const hasValue = computed(() => {
  return props.modelValue !== null && props.modelValue !== undefined && props.modelValue !== '';
});
</script>

<style scoped>
/* Custom transition for the floating label */
.transition-all {
  transition: all 0.2s ease-in-out;
}
</style>
