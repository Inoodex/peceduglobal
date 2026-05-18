<template>
  <div 
    :class="[
      'relative overflow-hidden bg-gray-100 dark:bg-gray-800/40',
      shapeClass,
      width,
      height,
      customClass
    ]"
  >
    <!-- Hardware-Accelerated Shimmer Light Sweep -->
    <div class="shimmer-sweep"></div>
  </div>
</template>

<script>
export default {
  name: 'Skeleton',
  props: {
    variant: {
      type: String,
      default: 'text', // Options: 'text', 'circular', 'rectangular', 'rounded'
    },
    width: {
      type: String,
      default: 'w-full'
    },
    height: {
      type: String,
      default: 'h-4'
    },
    customClass: {
      type: String,
      default: ''
    }
  },
  computed: {
    shapeClass() {
      switch (this.variant) {
        case 'circular':
          return 'rounded-full';
        case 'rectangular':
          return 'rounded-none';
        case 'rounded':
          return 'rounded-xl';
        case 'text':
        default:
          return 'rounded';
      }
    }
  }
}
</script>

<style scoped>
.shimmer-sweep {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  transform: translateX(-150%);
  background: linear-gradient(
    90deg,
    rgba(255, 255, 255, 0) 0%,
    rgba(255, 255, 255, 0.4) 30%,
    rgba(255, 255, 255, 0.75) 50%,
    rgba(255, 255, 255, 0.4) 70%,
    rgba(255, 255, 255, 0) 100%
  );
  animation: shimmer 1.5s infinite ease-in-out;
}

@keyframes shimmer {
  0% {
    transform: translateX(-150%);
  }
  50% {
    transform: translateX(150%);
  }
  100% {
    transform: translateX(150%);
  }
}
</style>

<style>
.dark .shimmer-sweep {
  background: linear-gradient(
    90deg,
    rgba(255, 255, 255, 0) 0%,
    rgba(255, 255, 255, 0.03) 25%,
    rgba(255, 255, 255, 0.12) 50%,
    rgba(255, 255, 255, 0.03) 75%,
    rgba(255, 255, 255, 0) 100%
  ) !important;
}
</style>
