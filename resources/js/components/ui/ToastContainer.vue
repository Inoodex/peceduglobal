<template>
  <div class="fixed top-6 right-6 z-[9999] flex flex-col gap-3 pointer-events-none max-w-sm w-full">
    <TransitionGroup 
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div 
        v-for="toast in toastStore.toasts" 
        :key="toast.id"
        class="pointer-events-auto w-full overflow-hidden rounded-2xl shadow-2xl border backdrop-blur-md"
        :class="[
          toast.type === 'success' ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-600 dark:text-emerald-400' : '',
          toast.type === 'error' ? 'bg-red-500/10 border-red-500/20 text-red-600 dark:text-red-400' : '',
          toast.type === 'warning' ? 'bg-amber-500/10 border-amber-500/20 text-amber-600 dark:text-amber-400' : ''
        ]"
      >
        <div class="p-4 flex items-start gap-3">
          <div class="shrink-0 pt-0.5">
            <CheckCircle v-if="toast.type === 'success'" class="w-5 h-5" />
            <AlertCircle v-else-if="toast.type === 'error'" class="w-5 h-5" />
            <AlertTriangle v-else class="w-5 h-5" />
          </div>
          <div class="flex-1">
            <p class="text-sm font-bold leading-tight uppercase tracking-tight opacity-50 mb-0.5">{{ toast.type }}</p>
            <p class="text-[15px] font-medium leading-relaxed">{{ toast.message }}</p>
          </div>
          <button @click="toastStore.remove(toast.id)" class="shrink-0 hover:opacity-70 transition-opacity">
            <X class="w-4 h-4 opacity-40" />
          </button>
        </div>
        <!-- Progress Bar (Optional visual touch) -->
        <div 
          class="h-1 bg-current opacity-20 transition-all duration-300"
          style="width: 100%"
        ></div>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { useToastStore } from '@/stores/toast';
import { CheckCircle, AlertCircle, AlertTriangle, X } from 'lucide-vue-next';

const toastStore = useToastStore();
</script>
