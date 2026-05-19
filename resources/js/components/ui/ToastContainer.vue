<template>
  <div class="fixed top-6 right-6 z-[9999] flex flex-col items-end gap-2 pointer-events-none w-full max-w-sm">
    <TransitionGroup
      tag="div"
      class="flex flex-col items-center gap-2 w-full"
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 translate-x-8 scale-95"
      enter-to-class="opacity-100 translate-x-0 scale-100"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="opacity-100 translate-x-0 scale-100"
      leave-to-class="opacity-0 translate-x-8 scale-95"
    >
      <div
        v-for="toast in toastStore.toasts"
        :key="toast.id"
        class="pointer-events-auto w-full flex items-center gap-3 px-4 py-3 rounded-2xl shadow-2xl border border-white/10 backdrop-blur-xl"
        :class="bgClass(toast.type)"
      >
        <!-- Icon -->
        <div class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center" :class="iconBgClass(toast.type)">
          <CheckCircle v-if="toast.type === 'success'" class="w-4 h-4 text-white" />
          <XCircle v-else-if="toast.type === 'error'" class="w-4 h-4 text-white" />
          <AlertTriangle v-else-if="toast.type === 'warning'" class="w-4 h-4 text-white" />
          <Info v-else class="w-4 h-4 text-white" />
        </div>

        <!-- Message -->
        <p class="flex-1 text-sm font-medium text-white leading-snug">{{ toast.message }}</p>

        <!-- Close -->
        <button
          @click="toastStore.remove(toast.id)"
          class="shrink-0 w-6 h-6 rounded-full flex items-center justify-center bg-white/10 hover:bg-white/20 transition-colors"
        >
          <X class="w-3.5 h-3.5 text-white/70" />
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { useToastStore } from '@/stores/toast';
import { CheckCircle, XCircle, AlertTriangle, Info, X } from 'lucide-vue-next';

const toastStore = useToastStore();

const bgClass = (type) => {
  if (type === 'success') return 'bg-gray-900/90 dark:bg-gray-950/90';
  if (type === 'error')   return 'bg-gray-900/90 dark:bg-gray-950/90';
  if (type === 'warning') return 'bg-gray-900/90 dark:bg-gray-950/90';
  return 'bg-gray-900/90 dark:bg-gray-950/90';
};

const iconBgClass = (type) => {
  if (type === 'success') return 'bg-emerald-500';
  if (type === 'error')   return 'bg-red-500';
  if (type === 'warning') return 'bg-amber-500';
  return 'bg-blue-500';
};
</script>
