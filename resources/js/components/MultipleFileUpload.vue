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
        :multiple="multiple"
        class="hidden"
        @change="handleFileSelect"
      />

      <!-- Empty State -->
      <div v-if="!hasFiles" class="space-y-3">
        <div class="w-20 h-20 mx-auto bg-gray-100 dark:bg-gray-800 rounded-2xl flex items-center justify-center">
          <ImageIcon class="w-10 h-10 text-gray-400" />
        </div>
        <div>
          <p class="text-gray-900 dark:text-white font-medium">{{ placeholder }}</p>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Drag files here, or <span class="text-primary hover:underline">browse</span> your device
          </p>
        </div>
        <p v-if="hint" class="text-xs text-gray-400 dark:text-gray-500">{{ hint }}</p>
      </div>

      <!-- Preview State -->
      <div v-else class="space-y-3">
        <!-- Single File Preview -->
        <div v-if="!multiple && previewUrl" class="relative">
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

        <!-- Multiple Files Preview -->
        <div v-else-if="multiple && previewUrls.length > 0" class="grid grid-cols-2 md:grid-cols-3 gap-3">
          <div
            v-for="(url, index) in previewUrls"
            :key="index"
            class="relative group"
          >
            <img
              v-if="isImageUrl(url)"
              :src="url"
              :alt="`File ${index + 1}`"
              class="w-full h-24 rounded-lg object-cover border border-gray-200 dark:border-gray-700"
              :class="{ 'opacity-50': uploading }"
            />
            <div v-else class="w-full h-24 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center border border-gray-200 dark:border-gray-700">
              <FileIcon class="w-6 h-6 text-gray-400" />
            </div>

            <!-- Remove Button -->
            <button
              v-if="!uploading"
              type="button"
              @click.stop="removeFile(index)"
              class="absolute -top-2 -right-2 p-1 bg-red-500 hover:bg-red-600 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
            >
              <X class="w-3 h-3" />
            </button>
          </div>
        </div>

        <!-- Uploading Indicator for Multiple -->
        <div v-if="uploading" class="flex items-center justify-center py-4">
          <div class="bg-gray-900/70 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            <span class="text-sm">Uploading...</span>
          </div>
        </div>
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
      type: [String, Array],
      default: () => [],
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
      default: 'Drop or select files',
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
    multiple: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['update:modelValue', 'update:altText', 'select', 'remove'],
  data() {
    return {
      isDragging: false,
      localPreviews: [],
      localFileNames: [],
      localFileSizes: [],
    };
  },
  computed: {
    hasFiles() {
      if (this.multiple) {
        return this.modelValue && this.modelValue.length > 0;
      }
      return this.modelValue || this.localPreviews.length > 0;
    },
    previewUrl() {
      if (this.multiple) return '';
      return this.modelValue || this.localPreviews[0] || '';
    },
    previewUrls() {
      if (!this.multiple) return [];
      if (this.modelValue && Array.isArray(this.modelValue)) {
        return this.modelValue;
      }
      return this.localPreviews;
    },
    isImage() {
      if (this.accept.startsWith('image/')) return true;
      const url = this.previewUrl;
      if (!url) return false;
      return /\.(jpg|jpeg|png|gif|webp|svg|bmp|ico)$/i.test(url) || url.startsWith('blob:');
    },
    fileName() {
      return this.localFileNames[0] || '';
    },
    fileSize() {
      return this.localFileSizes[0] || '';
    },
  },
  methods: {
    handleFileSelect(event) {
      const files = Array.from(event.target.files);
      if (files.length > 0) {
        if (this.multiple) {
          this.processMultipleFiles(files);
        } else {
          this.processFile(files[0]);
        }
      }
    },
    handleDrop(event) {
      this.isDragging = false;
      const files = Array.from(event.dataTransfer.files);
      if (files.length > 0) {
        if (this.multiple) {
          const validFiles = files.filter(file =>
            this.accept === 'image/*' ? file.type.startsWith('image/') : true
          );
          if (validFiles.length > 0) {
            this.processMultipleFiles(validFiles);
          }
        } else {
          const file = files[0];
          if (this.accept === 'image/*' && !file.type.startsWith('image/')) return;
          this.processFile(file);
        }
      }
    },
    processFile(file) {
      this.localFileNames = [file.name];
      this.localFileSizes = [this.formatSize(file.size)];
      if (file.type.startsWith('image/')) {
        this.localPreviews = [URL.createObjectURL(file)];
      }
      this.$emit('select', file);
    },
    processMultipleFiles(files) {
      const previews = [];
      const names = [];
      const sizes = [];

      files.forEach(file => {
        names.push(file.name);
        sizes.push(this.formatSize(file.size));
        if (file.type.startsWith('image/')) {
          previews.push(URL.createObjectURL(file));
        } else {
          previews.push(null);
        }
      });

      this.localPreviews = previews;
      this.localFileNames = names;
      this.localFileSizes = sizes;
      this.$emit('select', files);
    },
    removeFile(index = null) {
      if (this.multiple) {
        if (index !== null) {
          // Remove specific file
          this.localPreviews.splice(index, 1);
          this.localFileNames.splice(index, 1);
          this.localFileSizes.splice(index, 1);
          this.$emit('remove', index);
        } else {
          // Remove all files
          this.localPreviews = [];
          this.localFileNames = [];
          this.localFileSizes = [];
          this.$refs.fileInput.value = '';
          this.$emit('update:modelValue', []);
          this.$emit('update:altText', '');
          this.$emit('remove');
        }
      } else {
        this.localPreviews = [];
        this.localFileNames = [];
        this.localFileSizes = [];
        this.$refs.fileInput.value = '';
        this.$emit('update:modelValue', '');
        this.$emit('update:altText', '');
        this.$emit('remove');
      }
    },
    isImageUrl(url) {
      if (!url) return false;
      return /\.(jpg|jpeg|png|gif|webp|svg|bmp|ico)$/i.test(url) || url.startsWith('blob:');
    },
    formatSize(bytes) {
      if (bytes < 1024) return bytes + ' B';
      if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
      return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    },
  },
};
</script>
