<template>
  <div class="mb-4">
    <label class="block text-sm font-medium mb-1" v-if="label">{{ label }}</label>
    <component
      :is="componentTag"
      v-bind="inputAttrs"
      v-model="internalValue"
      @change="onChange"
      class="w-full p-2 border rounded"
    >
      <template v-if="type === 'textarea'" #default>
        {{ internalValue }}
      </template>
    </component>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
  label: { type: String, default: '' },
  modelValue: { required: true },
  type: { type: String, default: 'text' }, // text, textarea, file, checkbox
  placeholder: { type: String, default: '' }
});

const emit = defineEmits(['update:modelValue']);

const internalValue = ref(props.modelValue);

watch(() => props.modelValue, (val) => {
  internalValue.value = val;
});

watch(internalValue, (val) => {
  emit('update:modelValue', val);
});

const componentTag = computed(() => {
  if (props.type === 'textarea') return 'textarea';
  if (props.type === 'file') return 'input';
  if (props.type === 'checkbox') return 'input';
  return 'input';
});

const inputAttrs = computed(() => {
  const attrs = {};
  if (props.type === 'file') {
    attrs.type = 'file';
    attrs.accept = 'image/*';
  } else if (props.type === 'checkbox') {
    attrs.type = 'checkbox';
    attrs.class = '';
  } else {
    attrs.type = props.type;
    if (props.placeholder) attrs.placeholder = props.placeholder;
  }
  return attrs;
});

function onChange(event) {
  if (props.type === 'file') {
    internalValue.value = event.target.files[0] || null;
  } else if (props.type === 'checkbox') {
    internalValue.value = event.target.checked;
  } else {
    internalValue.value = event.target.value;
  }
}
</script>

<style scoped>
/* No extra styles – Tailwind classes are applied */
</style>
