<template>
  <MainLayout>
    <div v-if="loading && !form.page_id" class="flex items-center justify-center min-h-screen">
      <div class="flex flex-col items-center gap-2">
        <Loader2 class="w-8 h-8 animate-spin text-primary" />
        <p class="text-gray-500 dark:text-gray-400">Loading block data...</p>
      </div>
    </div>
    <div v-else class="max-w-3xl mx-auto pb-20">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Edit Block</h1>
        <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
          <ChevronRight class="w-4 h-4" />
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard/block-manager')">Block Manager</span>
          <ChevronRight class="w-4 h-4" />
          <span class="text-gray-900 dark:text-white">Edit</span>
        </nav>
      </div>

      <form @submit.prevent="save">
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 mb-4 overflow-hidden">
          <button type="button" @click="toggleSection('details')" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white">Details</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Block type, page and basic info</p>
            </div>
            <ChevronDown class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': sections.details }" />
          </button>
          <div v-show="sections.details" class="p-4 pt-0 border-t border-gray-200 dark:border-gray-700/50 space-y-4">
            <div v-if="form">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Target Page <span class="text-red-500">*</span></label>
              <select v-model="form.page_id" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" required>
                <option value="">Select Page</option>
                <option v-for="page in pages" :key="page.id" :value="page.id">{{ page.country?.iso_code }} ({{ page.country?.name }}) - {{ page.title }}</option>
              </select>
            </div>
            <div v-if="form">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Block Type <span class="text-red-500">*</span></label>
              <input v-model="form.block_type" type="text" placeholder="e.g. Hero, Features" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" required />
            </div>
            <div v-if="form" class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Section Title</label>
                <input v-model="form.section_title" type="text" placeholder="Section title" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Sort Order</label>
                <input v-model="form.sort_order" type="number" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
              </div>
            </div>
            <div v-if="form && form.settings">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Subtitle / Badge</label>
              <input v-model="form.settings.subtitle" type="text" placeholder="e.g. WHY CHOOSE US?" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
            </div>

            <!-- Block Settings: Section Image -->
            <div v-if="form && form.settings">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Section Image</label>
              <FileUpload
                v-model="form.settings.section_image"
                :show-alt-input="false"
                :multiple="false"
                :uploading="uploadingImage"
                placeholder="Drop or select section image"
                hint="JPEG, PNG, GIF, WebP, SVG (Max 2MB)"
                @select="uploadBlockImage"
                @remove="removeBlockImage"
              />
            </div>
            <div v-if="form">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Section Description</label>
              <AppEditor v-model="form.section_description" />
            </div>
          </div>
        </div>

        <div class="flex items-center justify-between">
          <button type="button" @click="$router.push('/dashboard/block-manager')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Cancel</button>
          <button type="submit" :disabled="loading" class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-xl hover:bg-primary-hover transition-colors shadow-sm flex items-center gap-2">
            <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
            {{ loading ? 'Updating...' : 'Update Block' }}
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
import FileUpload from '@/components/MultipleFileUpload.vue';
import { ChevronRight, ChevronDown, Loader2 } from 'lucide-vue-next';
import { clearCache } from '@/utils/cacheHelper';

export default {
  name: 'BlockEdit',
  components: { MainLayout, AppEditor, FileUpload, ChevronRight, ChevronDown, Loader2 },
  data() {
    return {
      sections: { details: true },
      loading: false,
      uploadingImage: false,
      pages: [],
      form: null,
    };
  },
  mounted() {
    this.fetchPages();
    this.fetchBlock();
  },
  methods: {
    toggleSection(section) {
      this.sections[section] = !this.sections[section];
    },
    async uploadBlockImage(file) {
      if (!file) return;

      const formData = new FormData();
      formData.append('image', file);

      this.uploadingImage = true;
      try {
        const response = await axios.post('/auth/admin/editor/upload', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        if (!this.form.settings) {
          this.form.settings = { subtitle: '', section_image: '' };
        }
        this.form.settings.section_image = response.data.url;
      } catch (error) {
        console.error('Error uploading block image:', error);
        alert('Failed to upload image. Please try again.');
      } finally {
        this.uploadingImage = false;
      }
    },
    removeBlockImage() {
      if (this.form && this.form.settings) {
        this.form.settings.section_image = '';
      }
    },
    async fetchPages() {
      try {
        const response = await axios.get('/auth/admin/pages');
        this.pages = response.data.data?.data || response.data.data || [];
      } catch (error) {
        console.error('Error fetching pages:', error);
      }
    },
    async fetchBlock() {
      this.loading = true;
      try {
        const response = await axios.get(`/auth/admin/blocks/${this.$route.params.id}`);
        this.form = response.data.data;
        // Make sure settings is initialized as object if null or empty
        if (!this.form.settings || typeof this.form.settings !== 'object') {
          this.form.settings = { subtitle: '', section_image: '' };
        }
      } catch (error) {
        console.error('Error fetching block:', error);
      } finally {
        this.loading = false;
      }
    },
    async save() {
      this.loading = true;
      try {
        await axios.put(`/auth/admin/blocks/${this.$route.params.id}`, this.form);
        clearCache('/auth/admin/blocks');
        this.$router.push('/dashboard/block-manager');
      } catch (error) {
        console.error('Error updating block:', error);
        alert('Error updating block. Please check your input.');
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>