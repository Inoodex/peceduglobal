<template>
  <div class="relative w-full" ref="dropdownRef">

    <!-- Trigger Box -->
    <div
      @click="toggleDropdown"
      class="relative flex items-center w-full min-h-[48px] px-3.5 bg-white dark:bg-[#1C252E] border rounded-xl cursor-pointer transition-all select-none"
      :class="isOpen
        ? 'border-primary ring-2 ring-primary/10'
        : 'border-gray-200 dark:border-gray-700/80 hover:border-gray-300 dark:hover:border-gray-600'"
    >
      <!-- Floating Label -->
      <span
        class="absolute left-3 px-1 font-semibold pointer-events-none transition-all bg-white dark:bg-[#1C252E] text-xs"
        :class="[
          '-top-2.5',
          isOpen ? 'text-primary' : 'text-gray-500 dark:text-gray-400'
        ]"
      >
        {{ label }}
      </span>

      <!-- Display Value -->
      <div class="flex-1 flex items-center gap-2 overflow-hidden pr-6">
        <!-- Search input when open -->
        <input
          v-if="searchable && isOpen"
          ref="searchInput"
          v-model="searchQuery"
          type="text"
          class="w-full bg-transparent border-none outline-none p-0 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-0 caret-primary"
          :placeholder="placeholder"
          @click.stop
        />
        <!-- Selected value display -->
        <span v-else-if="selectedOption" class="text-sm font-medium text-gray-900 dark:text-white truncate">
          {{ typeof labelKey === 'function' ? labelKey(selectedOption) : selectedOption[labelKey] }}
        </span>
        <!-- Placeholder -->
        <span v-else class="text-sm text-gray-400 dark:text-gray-500 truncate">{{ placeholder }}</span>
      </div>

      <!-- Right side icons -->
      <div class="absolute right-3 flex items-center gap-1">
        <button
          v-if="modelValue && clearable"
          @click.stop="clearSelection"
          class="p-0.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded transition-colors"
        >
          <X class="w-3.5 h-3.5" />
        </button>
        <ChevronUp v-if="isOpen" class="w-4 h-4 text-primary" />
        <ChevronDown v-else class="w-4 h-4 text-gray-400 dark:text-gray-500" />
      </div>
    </div>

    <!-- Dropdown -->
    <Transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="opacity-0 translate-y-1 scale-[0.98]"
      enter-to-class="opacity-100 translate-y-0 scale-100"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="opacity-100 translate-y-0 scale-100"
      leave-to-class="opacity-0 translate-y-1 scale-[0.98]"
    >
      <div
        v-if="isOpen"
        class="absolute z-60 w-full mt-1.5 bg-white dark:bg-[#1C252E] border border-gray-100 dark:border-gray-700/80 rounded-xl shadow-xl shadow-gray-200/30 dark:shadow-black/50 overflow-hidden"
      >
        <!-- Options List -->
        <ul class="py-1.5 max-h-64 overflow-y-auto custom-scrollbar">
          <!-- Empty -->
          <li
            v-if="filteredOptions.length === 0"
            class="flex flex-col items-center justify-center gap-2 py-8 text-sm text-gray-500 dark:text-gray-400"
          >
            <SearchX class="w-5 h-5 text-gray-300 dark:text-gray-600" />
            No results found
          </li>

          <!-- Items -->
          <li
            v-for="option in filteredOptions"
            :key="option[valueKey]"
            @click="selectOption(option)"
            class="flex items-center gap-3 mx-1.5 px-3 py-2.5 rounded-lg cursor-pointer transition-colors text-sm"
            :class="isSelected(option)
              ? 'bg-primary/8 dark:bg-primary/10 text-primary font-medium'
              : 'text-gray-800 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-[#141A21]'"
          >
            <!-- Optional image/icon -->
            <img
              v-if="imageKey && option[imageKey]"
              :src="option[imageKey]"
              class="w-5 h-5 rounded-full object-cover border border-gray-100 dark:border-gray-700/50 shrink-0"
            />

            <!-- Slot for custom option display -->
            <slot name="option" :option="option">
              <span class="truncate">{{ typeof labelKey === 'function' ? labelKey(option) : option[labelKey] }}</span>
            </slot>

            <!-- Checkmark for selected -->
            <span v-if="isSelected(option)" class="ml-auto shrink-0">
              <Check class="w-3.5 h-3.5 text-primary" />
            </span>
          </li>
        </ul>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { ChevronDown, ChevronUp, X, SearchX, Check } from 'lucide-vue-next';

const props = defineProps({
  modelValue: { type: [String, Number, Object], default: '' },
  options:    { type: Array, required: true, default: () => [] },
  label:      { type: String, required: true },
  placeholder:{ type: String, default: 'Select an option' },
  searchable: { type: Boolean, default: true },
  clearable:  { type: Boolean, default: true },
  labelKey:   { type: [String, Function], default: 'label' },
  valueKey:   { type: String, default: 'value' },
  imageKey:   { type: String, default: null },
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen     = ref(false);
const searchQuery= ref('');
const dropdownRef= ref(null);
const searchInput= ref(null);

const selectedOption = computed(() => {
  if (props.modelValue === null || props.modelValue === '') return null;
  return props.options.find(opt => String(opt[props.valueKey]) === String(props.modelValue)) || null;
});

const filteredOptions = computed(() => {
  if (!props.searchable || !searchQuery.value) return props.options;
  const q = searchQuery.value.toLowerCase();
  return props.options.filter(opt => {
    const label = typeof props.labelKey === 'function' ? props.labelKey(opt) : opt[props.labelKey];
    return String(label || '').toLowerCase().includes(q);
  });
});

const isSelected = (option) => String(option[props.valueKey]) === String(props.modelValue);

const toggleDropdown = async () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    searchQuery.value = '';
    if (props.searchable) {
      await nextTick();
      searchInput.value?.focus();
    }
  }
};

const selectOption = (option) => {
  emit('update:modelValue', option[props.valueKey]);
  emit('change', option);
  isOpen.value = false;
};

const clearSelection = () => {
  emit('update:modelValue', '');
  emit('change', null);
};

const handleClickOutside = (e) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    isOpen.value = false;
  }
};

onMounted(() => document.addEventListener('mousedown', handleClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside));
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #d1d5db; border-radius: 10px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #374151; }
</style>
