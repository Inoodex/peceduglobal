<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="show" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
        <div 
          class="bg-white dark:bg-[#1C252E] rounded-[32px] p-8 max-w-md w-full shadow-2xl border border-gray-100 dark:border-gray-800 transform transition-all"
          :class="show ? 'scale-100 opacity-100' : 'scale-95 opacity-0'"
        >
          <div class="flex flex-col items-center text-center">
            <div 
              class="w-20 h-20 rounded-full flex items-center justify-center mb-6 border-4"
              :class="variant === 'danger' ? 'bg-red-50 dark:bg-red-900/20 border-red-100 dark:border-red-900/30 text-red-600 dark:text-red-400' : 'bg-amber-50 dark:bg-amber-900/20 border-amber-100 dark:border-amber-900/30 text-amber-600 dark:text-amber-400'"
            >
              <AlertTriangle v-if="variant === 'danger'" class="w-10 h-10" />
              <HelpCircle v-else class="w-10 h-10" />
            </div>
            
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ title }}</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-8 leading-relaxed">
              {{ message }}
            </p>
            
            <div class="flex gap-3 w-full">
              <button 
                @click="$emit('cancel')" 
                class="flex-1 px-6 py-3.5 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-2xl transition-all"
              >
                {{ cancelText }}
              </button>
              <button 
                @click="$emit('confirm')" 
                :disabled="loading"
                class="flex-1 px-6 py-3.5 text-sm font-semibold text-white rounded-2xl transition-all flex items-center justify-center gap-2"
                :class="variant === 'danger' ? 'bg-red-600 hover:bg-red-700 hover:shadow-lg hover:shadow-red-500/30' : 'bg-amber-600 hover:bg-amber-700 hover:shadow-lg hover:shadow-amber-500/30'"
              >
                <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
                <span v-else>{{ confirmText }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { AlertTriangle, HelpCircle, Loader2 } from 'lucide-vue-next';

defineProps({
  show: Boolean,
  title: { type: String, default: 'Are you sure?' },
  message: { type: String, default: 'This action cannot be undone.' },
  confirmText: { type: String, default: 'Confirm' },
  cancelText: { type: String, default: 'Cancel' },
  variant: { type: String, default: 'danger' }, // 'danger' or 'warning'
  loading: Boolean
});

defineEmits(['confirm', 'cancel']);
</script>
