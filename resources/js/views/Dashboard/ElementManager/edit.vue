<template>
  <MainLayout>
    <div v-if="loading && !form" class="flex items-center justify-center min-h-screen">
      <div class="flex flex-col items-center gap-2">
        <Loader2 class="w-8 h-8 animate-spin text-primary" />
        <p class="text-gray-500 dark:text-gray-400">Loading element data...</p>
      </div>
    </div>
    <div v-else class="max-w-3xl mx-auto pb-20">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Edit Element</h1>
        <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
          <ChevronRight class="w-4 h-4" />
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard/element-manager')">Element Manager</span>
          <ChevronRight class="w-4 h-4" />
          <span class="text-gray-900 dark:text-white">Edit</span>
        </nav>
      </div>

      <form @submit.prevent="save">
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 mb-4 overflow-hidden">
          <button type="button" @click="toggleSection('details')" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white">Details</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Update element content and assignment</p>
            </div>
            <ChevronDown class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': sections.details }" />
          </button>
          <div v-show="sections.details" class="p-4 pt-0 border-t border-gray-200 dark:border-gray-700/50 space-y-4">
            <div v-if="form" class="flex flex-col">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Target Block <span class="text-red-500">*</span></label>
              <CustomSelect 
                v-model="form.page_block_id" 
                :options="blocks.map(b => ({ label: `${b.page?.title} - ${b.block_type} (${b.section_title || 'No Title'})`, value: b.id }))"
                label="Target Block"
                placeholder="Select Block"
                label-key="label"
                value-key="value"
                required
              />
            </div>
            <div v-if="form">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ selectedBlockType === 'faq' ? 'Question' : 'Element Title' }}</label>
              <input v-model="form.element_title" type="text" :placeholder="selectedBlockType === 'faq' ? 'e.g. What are the requirements?' : 'e.g. Top Ranked University'" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
            </div>
            <div v-if="form" class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Sort Order</label>
                <input v-model="form.sort_order" type="number" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Link URL</label>
                <input v-model="form.link_url" type="text" placeholder="https://example.com" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
              </div>
            </div>
            <div v-if="form">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ selectedBlockType === 'faq' ? 'Answer' : 'Element Body' }}</label>
              <AppEditor v-model="form.element_body" />
            </div>
            <div v-if="form">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Images (Multiple)</label>
              <FileUpload
                v-model="imageUrls"
                :show-alt-input="false"
                :multiple="true"
                placeholder="Drop or select multiple images"
                hint="JPEG, PNG, GIF, WebP, AVIF, SVG, BMP, ICO, TIFF (Max 5MB)"
                @select="handleFileSelect"
                @remove="handleFileRemove"
              />
            </div>
          </div>
        </div>

        <div class="flex items-center justify-between">
          <button type="button" @click="$router.push('/dashboard/element-manager')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Cancel</button>
          <button type="submit" :disabled="loading" class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-xl hover:bg-primary-hover transition-colors shadow-sm flex items-center gap-2">
            <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
            {{ loading ? 'Updating...' : 'Update Element' }}
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
import CustomSelect from '@/components/Form/CustomSelect.vue';
import { ChevronRight, ChevronDown, Loader2, Upload } from 'lucide-vue-next';
import { clearCache } from '@/utils/cacheHelper';
import { useToastStore } from '@/stores/toast';

export default {
  name: 'ElementEdit',
  components: { MainLayout, AppEditor, FileUpload, CustomSelect, ChevronRight, ChevronDown, Loader2, Upload },
  setup() {
    const toast = useToastStore();
    return { toast };
  },
  data() {
    return {
      sections: { details: true },
      loading: false,
      blocks: [],
      form: null,
      imageUrls: [],
    };
  },
  computed: {
    selectedBlockType() {
      const block = this.blocks.find(b => b.id === this.form?.page_block_id);
      return block ? block.block_type : '';
    }
  },
  mounted() {
    this.fetchBlocks();
    this.fetchElement();
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
    async fetchElement() {
      this.loading = true;
      try {
        const response = await axios.get(`/auth/admin/elements/${this.$route.params.id}`);
        const element = response.data.data;
        this.form = {
          page_block_id: element.page_block_id,
          element_title: element.element_title,
          element_body: element.element_body,
          images: [],
          link_url: element.link_url,
          sort_order: element.sort_order,
        };
        if (element.image_paths && element.image_paths.length > 0) {
          this.imageUrls = element.image_paths;
        }
      } catch (error) {
        console.error('Error fetching element:', error);
      } finally {
        this.loading = false;
      }
    },
    handleFileSelect(files) {
      this.form.images = files;
      this.imageUrls = files.map(file => URL.createObjectURL(file));
    },
    handleFileRemove(index) {
      this.form.images.splice(index, 1);
      this.imageUrls.splice(index, 1);
    },
    async save() {
      this.loading = true;
      try {
        const formData = new FormData();
        Object.keys(this.form).forEach(key => {
          if (key === 'images') {
            this.form.images.forEach((file, index) => {
              formData.append('images[]', file);
            });
          } else if (this.form[key] !== null && this.form[key] !== '') {
            formData.append(key, this.form[key]);
          }
        });
        formData.append('_method', 'PUT');

        await axios.post(`/auth/admin/elements/${this.$route.params.id}`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        clearCache('/auth/admin/elements');
        this.toast.success('Element updated successfully.');
        this.$router.push('/dashboard/element-manager');
      } catch (error) {
        console.error('Error updating element:', error);
        this.toast.error('Failed to update element. Please check your input.');
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>