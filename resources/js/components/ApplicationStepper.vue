<template>
  <div class="w-full">
    <!-- Mobile / narrow: compact vertical-friendly progress bar -->
    <div class="md:hidden">
      <div class="flex items-center justify-between mb-2">
        <span class="text-xs font-bold text-gray-700 dark:text-gray-200">
          Step {{ currentStep + 1 }} <span class="text-gray-400">/ {{ TOTAL_STEPS }}</span>
        </span>
        <span class="text-xs font-semibold" :class="activeTextClass">{{ activeLabel }}</span>
      </div>
      <div class="h-2 w-full rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
        <div class="h-full rounded-full transition-all duration-500" :class="fillClass" :style="{ width: percent + '%' }"></div>
      </div>
      <div class="mt-3 flex flex-wrap gap-1.5">
        <span
          v-for="(step, idx) in steps"
          :key="step.value"
          :class="[
            'inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold border',
            idx <= currentStep ? dotActiveClass(step.color) : 'bg-gray-50 text-gray-400 border-gray-100 dark:bg-gray-800/60 dark:text-gray-500 dark:border-gray-800'
          ]"
        >
          <Check v-if="idx < currentStep" class="w-2.5 h-2.5" />
          <span v-else class="w-1.5 h-1.5 rounded-full" :class="idx === currentStep ? dotBgClass(step.color) : 'bg-gray-300 dark:bg-gray-600'"></span>
          {{ step.shortLabel || step.label }}
        </span>
      </div>
    </div>

    <!-- Desktop: horizontal stepper -->
    <div class="hidden md:block">
      <div class="px-2">
        <!-- Background connector line -->
        <div class="relative">
          <div class="absolute top-[13px] h-0.5 bg-gray-200 dark:bg-gray-700" style="left: 6.66%; right: 6.66%;"></div>
          <!-- Progress connector line — fills between first and current step -->
          <div class="absolute top-[13px] left-[6.66%] h-0.5 transition-all duration-500 rounded-full" :class="fillClass" :style="{ width: linePercent + '%' }"></div>

          <!-- Step nodes — spread evenly with justify-between -->
          <div class="relative flex justify-between">
            <div
              v-for="(step, idx) in steps"
              :key="step.value"
              class="flex flex-col items-center"
              style="flex: 1 1 0%; max-width: 100px;"
            >
              <div
                :class="[
                  'w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold border-2 transition-all duration-300 z-10',
                  idx < currentStep
                    ? nodeDoneClass(step.color)
                    : idx === currentStep
                      ? nodeActiveClass(step.color)
                      : 'border-gray-200 text-gray-400 bg-white dark:border-gray-600 dark:text-gray-600 dark:bg-[#1C252E]'
                ]"
              >
                <Check v-if="idx < currentStep" class="w-3.5 h-3.5" />
                <span v-else>{{ idx + 1 }}</span>
              </div>
              <p
                :class="[
                  'mt-1.5 text-[9px] font-semibold text-center leading-tight transition-colors whitespace-nowrap',
                  idx <= currentStep ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-600'
                ]"
              >
                {{ step.shortLabel || step.label }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Check } from 'lucide-vue-next';
import { APPLICATION_STATUSES, getStepIndex, TOTAL_STEPS, findStatus } from '@/utils/applicationStatuses';

const props = defineProps({
  status: { type: String, default: '' },
  steps: { type: Array, default: () => APPLICATION_STATUSES },
});

const currentStep = computed(() => {
  const idx = getStepIndex(props.status);
  return idx < 0 ? -1 : idx;
});

const activeDef = computed(() => findStatus(props.status) || props.steps[0]);
const activeLabel = computed(() => activeDef.value?.label || '—');

const percent = computed(() => {
  if (currentStep.value < 0) return 0;
  return Math.round(((currentStep.value + 1) / props.steps.length) * 100);
});

// Progress line width as % of the line area (between first and last node centers).
// Line area spans from ~6.66% to ~93.34% (i.e. the inner 86.67%).
// Each step spans 86.67 / (N-1) percent of the container.
const linePercent = computed(() => {
  if (currentStep.value <= 0) return 0;
  return (currentStep.value / Math.max(props.steps.length - 1, 1)) * 86.67;
});

const color = computed(() => activeDef.value?.color || 'primary');

const fillClass = computed(() => bgClass(color.value));
const activeTextClass = computed(() => textClass(color.value));

const nodeActiveClass = (c) => `${borderClass(c)} ${textClass(c)} bg-white dark:bg-[#1C252E]`;
const nodeDoneClass = (c) => `${bgClass(c)} border-transparent text-white`;
const dotActiveClass = (c) => `${bgSoftClass(c)} ${textClass(c)} border-transparent`;
const dotBgClass = (c) => bgClass(c);

function bgClass(c) {
  return ({
    blue: 'bg-blue-500', indigo: 'bg-indigo-500', cyan: 'bg-cyan-500', amber: 'bg-amber-500',
    teal: 'bg-teal-500', purple: 'bg-purple-500', green: 'bg-green-500', red: 'bg-red-500',
    primary: 'bg-primary',
  })[c] || 'bg-primary';
}
function bgSoftClass(c) {
  return ({
    blue: 'bg-blue-50 dark:bg-blue-900/20', indigo: 'bg-indigo-50 dark:bg-indigo-900/20',
    cyan: 'bg-cyan-50 dark:bg-cyan-900/20', amber: 'bg-amber-50 dark:bg-amber-900/20',
    teal: 'bg-teal-50 dark:bg-teal-900/20', purple: 'bg-purple-50 dark:bg-purple-900/20',
    green: 'bg-green-50 dark:bg-green-900/20', red: 'bg-red-50 dark:bg-red-900/20',
    primary: 'bg-primary/10',
  })[c] || 'bg-primary/10';
}
function textClass(c) {
  return ({
    blue: 'text-blue-600 dark:text-blue-400', indigo: 'text-indigo-600 dark:text-indigo-400',
    cyan: 'text-cyan-600 dark:text-cyan-400', amber: 'text-amber-600 dark:text-amber-400',
    teal: 'text-teal-600 dark:text-teal-400', purple: 'text-purple-600 dark:text-purple-400',
    green: 'text-green-600 dark:text-green-400', red: 'text-red-600 dark:text-red-400',
    primary: 'text-primary',
  })[c] || 'text-primary';
}
function borderClass(c) {
  return ({
    blue: 'border-blue-500', indigo: 'border-indigo-500', cyan: 'border-cyan-500', amber: 'border-amber-500',
    teal: 'border-teal-500', purple: 'border-purple-500', green: 'border-green-500', red: 'border-red-500',
    primary: 'border-primary',
  })[c] || 'border-primary';
}
</script>
