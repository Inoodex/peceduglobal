<template>
  <MainLayout>
    <div class="max-w-3xl mx-auto pb-20">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Create Element</h1>
        <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
          <ChevronRight class="w-4 h-4" />
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard/element-manager')">Element Manager</span>
          <ChevronRight class="w-4 h-4" />
          <span class="text-gray-900 dark:text-white">Create</span>
        </nav>
      </div>

      <form @submit.prevent="save">
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 mb-4 overflow-hidden">
          <button type="button" @click="toggleSection('details')" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white">Details</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Assign block and add content</p>
            </div>
            <ChevronDown class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': sections.details }" />
          </button>
          <div v-show="sections.details" class="p-4 pt-0 border-t border-gray-200 dark:border-gray-700/50 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Target Block <span class="text-red-500">*</span></label>
              <select v-model="form.page_block_id" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" required>
                <option value="">Select Block</option>
                <option v-for="block in blocks" :key="block.id" :value="block.id">
                  {{ block.page?.title }} - {{ block.block_type }} ({{ block.section_title || 'No Title' }})
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Element Title</label>
              <input v-model="form.element_title" type="text" placeholder="e.g. Top Ranked University" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Sort Order</label>
                <input v-model="form.sort_order" type="number" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Link URL</label>
                <input v-model="form.link_url" type="text" placeholder="https://example.com" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Element Body</label>
              <AppEditor v-model="form.element_body" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Image</label>
              <div class="flex items-center gap-4">
                <input type="file" accept="image/*" @change="handleFileUpload" class="hidden" id="image-upload" />
                <label for="image-upload" class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors border border-gray-200 dark:border-gray-700">
                  <Upload class="w-4 h-4" /> Upload Image
                </label>
                <span v-if="form.image_path" class="text-xs text-green-600 font-medium">Image selected!</span>
              </div>
              <div v-if="imagePreview" class="mt-3 w-24 h-24 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
                <img :src="imagePreview" class="w-full h-full object-cover" />
              </div>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-between">
          <button type="button" @click="$router.push('/dashboard/element-manager')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Cancel</button>
          <button type="submit" :disabled="loading" class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-xl hover:bg-primary-hover transition-colors shadow-sm flex items-center gap-2">
            <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
            {{ loading ? 'Creating...' : 'Create Element' }}
          </button>
        </div>
      </form>
    </div>
  </MainLayout>
</template>

<script>
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import AppEditor from '@/components/AppEditor.vue';
import { ChevronRight, ChevronDown, Loader2, Upload } from 'lucide-vue-next';

export default {
  name: 'ElementCreate',
  components: { MainLayout, AppEditor, ChevronRight, ChevronDown, Loader2, Upload },
  data() {
    return {
      sections: { details: true },
      loading: false,
      blocks: [],
      imagePreview: null,
      form: {
        page_block_id: '',
        element_title: '',
        element_body: '',
        image_path: null,
        link_url: '',
        sort_order: 0
      },
    };
  },
  mounted() {
    this.fetchBlocks();
  },
  methods: {
    toggleSection(section) {
      this.sections[section] = !this.sections[section];
    },
    async fetchBlocks() {
      try {
        const response = await axios.get('/auth/admin/blocks');
        this.blocks = response.data.data?.data || response.data.data || [];
      } catch (error) {
        console.error('Error fetching blocks:', error);
      }
    },
    handleFileUpload(event) {
      const file = event.target.files[0];
      if (file) {
        this.form.image_path = file;
        this.imagePreview = URL.createObjectURL(file);
      }
    },
    async save() {
      this.loading = true;
      try {
        const formData = new FormData();
        for (const key in this.form) {
          formData.append(key, this.form[key]);
        }

        await axios.post('/auth/admin/elements', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        this.$router.push('/dashboard/element-manager');
      } catch (error) {
        console.error('Error creating element:', error);
        alert('Error creating element. Please check your input.');
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>