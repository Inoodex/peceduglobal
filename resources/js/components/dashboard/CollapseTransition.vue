<template>
  <Transition
    @before-enter="onBeforeEnter"
    @enter="onEnter"
    @before-leave="onBeforeLeave"
    @leave="onLeave"
  >
    <slot></slot>
  </Transition>
</template>

<script setup>
/**
 * Reusable Collapse Transition component mimicking Material UI <Collapse>.
 * Wraps elements in a smooth, dynamic-height slide transition.
 */

const onBeforeEnter = (el) => {
  el.style.height = '0';
};

const onEnter = (el, done) => {
  // MUI Collapse standard entrance easing
  el.style.transition = 'height 250ms cubic-bezier(0.4, 0, 0.2, 1)';
  el.style.height = el.scrollHeight + 'px';
  el.addEventListener('transitionend', () => {
    el.style.height = 'auto'; // Reset so inner content can dynamically resize later if needed
    done();
  }, { once: true });
};

const onBeforeLeave = (el) => {
  el.style.height = el.scrollHeight + 'px';
};

const onLeave = (el, done) => {
  // Slightly faster exit like MUI
  el.style.transition = 'height 200ms cubic-bezier(0.4, 0, 0.2, 1)';
  // Force a reflow so the browser catches the starting height before sliding down
  void el.offsetHeight;
  el.style.height = '0';
  el.addEventListener('transitionend', done, { once: true });
};
</script>
