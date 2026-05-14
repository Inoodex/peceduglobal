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
      <div v-if="confirmStore.show" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
        <div 
          class="bg-white dark:bg-[#1C252E] rounded-[32px] p-8 max-w-md w-full shadow-2xl border border-gray-100 dark:border-gray-800"
        >
          <div class="flex flex-col items-center text-center">
            <div 
              class="w-20 h-20 rounded-full flex items-center justify-center mb-6 border-4"
              :class="confirmStore.variant === 'danger' ? 'bg-red-50 dark:bg-red-900/20 border-red-100 dark:border-red-900/30 text-red-600 dark:text-red-400' : 'bg-amber-50 dark:bg-amber-900/20 border-amber-100 dark:border-amber-900/30 text-amber-600 dark:text-amber-400'"
            >
              <AlertTriangle v-if="confirmStore.variant === 'danger'" class="w-10 h-10" />
              <HelpCircle v-else class="w-10 h-10" />
            </div>
            
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ confirmStore.title }}</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-8 leading-relaxed">
              {{ confirmStore.message }}
            </p>
            
            <div class="flex gap-3 w-full">
              <button 
                @click="confirmStore.cancel" 
                class="flex-1 px-6 py-3.5 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-2xl transition-all"
              >
                {{ confirmStore.cancelText }}
              </button>
              <button 
                @click="confirmStore.confirm" 
                class="flex-1 px-6 py-3.5 text-sm font-semibold text-white rounded-2xl transition-all flex items-center justify-center gap-2"
                :class="confirmStore.variant === 'danger' ? 'bg-red-600 hover:bg-red-700 hover:shadow-lg hover:shadow-red-500/30' : 'bg-amber-600 hover:bg-amber-700 hover:shadow-lg hover:shadow-amber-500/30'"
              >
                <span>{{ confirmStore.confirmText }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { useConfirmStore } from '@/stores/confirm';
import { AlertTriangle, HelpCircle } from 'lucide-vue-next';

const confirmStore = useConfirmStore();
</script>
