<template>
  <div>
    <!-- Drop Zone -->
    <div
      class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-2xl p-8 text-center hover:border-gray-400 dark:hover:border-gray-500 transition-colors cursor-pointer bg-gray-50 dark:bg-[#141A21]/50"
      :class="{ 'border-primary dark:border-primary': isDragging }"
      @click="$refs.fileInput.click()"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleDrop"
    >
      <input
        ref="fileInput"
        type="file"
        :accept="accept"
        class="hidden"
        @change="handleFileSelect"
      />

      <!-- Empty State -->
      <div v-if="!previewUrl" class="space-y-3">
        <div class="w-20 h-20 mx-auto bg-gray-100 dark:bg-gray-800 rounded-2xl flex items-center justify-center">
          <ImageIcon class="w-10 h-10 text-gray-400" />
        </div>
        <div>
          <p class="text-gray-900 dark:text-white font-medium">{{ placeholder }}</p>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Drag a file here, or <span class="text-primary hover:underline">browse</span> your device
          </p>
        </div>
        <p v-if="hint" class="text-xs text-gray-400 dark:text-gray-500">{{ hint }}</p>
      </div>

      <!-- Preview State -->
      <div v-else class="relative">
        <img
          v-if="isImage"
          :src="previewUrl"
          :alt="altText"
          class="max-h-48 mx-auto rounded-xl object-cover"
          :class="{ 'opacity-50': uploading }"
        />
        <div v-else class="flex items-center justify-center gap-3 py-4">
          <FileIcon class="w-8 h-8 text-gray-400" />
          <div class="text-left">
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ fileName }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ fileSize }}</p>
          </div>
        </div>

        <!-- Uploading Indicator -->
        <div v-if="uploading" class="absolute inset-0 flex items-center justify-center">
          <div class="bg-gray-900/70 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            <span class="text-sm">Uploading...</span>
          </div>
        </div>

        <!-- Remove Button -->
        <button
          v-if="!uploading"
          type="button"
          @click.stop="removeFile"
          class="absolute top-2 right-2 p-1.5 bg-gray-900/50 hover:bg-gray-900 text-white rounded-lg transition-colors"
        >
          <X class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Alt Text Input -->
    <input
      v-if="previewUrl && showAltInput"
      :value="altText"
      @input="$emit('update:altText', $event.target.value)"
      type="text"
      :placeholder="altPlaceholder"
      class="w-full mt-3 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm"
    />
  </div>
</template>

<script>
import { Image as ImageIcon, File as FileIcon, X } from 'lucide-vue-next';

export default {
  name: 'FileUpload',
  components: { ImageIcon, FileIcon, X },
  props: {
    modelValue: {
      type: String,
      default: '',
    },
    altText: {
      type: String,
      default: '',
    },
    accept: {
      type: String,
      default: 'image/*',
    },
    placeholder: {
      type: String,
      default: 'Drop or select a file',
    },
    hint: {
      type: String,
      default: '',
    },
    uploading: {
      type: Boolean,
      default: false,
    },
    showAltInput: {
      type: Boolean,
      default: true,
    },
    altPlaceholder: {
      type: String,
      default: 'Image alt text',
    },
  },
  emits: ['update:modelValue', 'update:altText', 'select', 'remove'],
  data() {
    return {
      isDragging: false,
      localPreview: null,
      localFileName: '',
      localFileSize: '',
    };
  },
  computed: {
    previewUrl() {
      return this.modelValue || this.localPreview || '';
    },
    isImage() {
      if (this.accept.startsWith('image/')) return true;
      const url = this.previewUrl;
      if (!url) return false;
      return /\.(jpg|jpeg|png|gif|webp|svg|bmp|ico)$/i.test(url) || url.startsWith('blob:');
    },
    fileName() {
      return this.localFileName;
    },
    fileSize() {
      return this.localFileSize;
    },
  },
  methods: {
    handleFileSelect(event) {
      const file = event.target.files[0];
      if (file) this.processFile(file);
    },
    handleDrop(event) {
      this.isDragging = false;
      const file = event.dataTransfer.files[0];
      if (file) {
        if (this.accept === 'image/*' && !file.type.startsWith('image/')) return;
        this.processFile(file);
      }
    },
    processFile(file) {
      this.localFileName = file.name;
      this.localFileSize = this.formatSize(file.size);
      if (file.type.startsWith('image/')) {
        this.localPreview = URL.createObjectURL(file);
      }
      this.$emit('select', file);
    },
    removeFile() {
      this.localPreview = null;
      this.localFileName = '';
      this.localFileSize = '';
      this.$refs.fileInput.value = '';
      this.$emit('update:modelValue', '');
      this.$emit('update:altText', '');
      this.$emit('remove');
    },
    formatSize(bytes) {
      if (bytes < 1024) return bytes + ' B';
      if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
      return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    },
  },
};
</script>
