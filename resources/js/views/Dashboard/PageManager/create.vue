<template>
  <MainLayout>
    <div class="max-w-3xl mx-auto pb-20">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Create a new page</h1>
        <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
          <ChevronRight class="w-4 h-4" />
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard/page-manager')">Pages</span>
          <ChevronRight class="w-4 h-4" />
          <span class="text-gray-900 dark:text-white">Create</span>
        </nav>
      </div>

      <form @submit.prevent="save">
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 mb-4 overflow-hidden">
          <button type="button" @click="toggleSection('details')" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white">Details</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Page title, parent page and thumbnail</p>
            </div>
            <ChevronDown class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': sections.details }" />
          </button>
          <div v-show="sections.details" class="p-4 pt-0 border-t border-gray-200 dark:border-gray-700/50 space-y-4">
            <div class="flex flex-col gap-1.5">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Page Type</label>
              <select v-model="form.page_type" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                <option value="">Regular Page</option>
                <option value="home">Home Page</option>
                <option value="about">About Us</option>
                <option value="about_the_company">About the Company</option>
                <option value="faq">FAQ Page</option>
                <option value="country_guide">Country Guide</option>
                <option value="why_choose_us">Why Choose Us</option>
                <option value="services">Services</option>
                <option value="statistics">Statistics</option>
                <option value="privacy">Privacy Policy</option>
                <option value="terms">Terms & Conditions</option>
                <option value="comparison">Comparison</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Title <span class="text-red-500">*</span></label>
              <input v-model="form.title" type="text" placeholder="Page title" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Country</label>
              <select v-model="form.country_id" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                <option value="">Select a country (optional)</option>
                <option v-for="country in countries" :key="country.id" :value="country.id">
                  {{ country.name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Parent Page (for nested pages)</label>
              <select v-model="form.parent_id" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                <option value="">No parent (Main page)</option>
                <option v-for="page in availablePages" :key="page.id" :value="page.id">
                  → {{ page.title }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Thumbnail</label>
              <FileUpload
                v-model="thumbnailUrl"
                :show-alt-input="false"
                placeholder="Drop or select a thumbnail"
                hint="PNG, JPG, WEBP up to 2MB"
                @select="handleFileSelect"
                @remove="handleFileRemove"
              />
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 mb-4 overflow-hidden">
          <button type="button" @click="toggleSection('properties')" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white">SEO & Status</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Meta tags and page status</p>
            </div>
            <ChevronDown class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': sections.properties }" />
          </button>
          <div v-show="sections.properties" class="p-4 pt-0 border-t border-gray-200 dark:border-gray-700/50 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Meta Title</label>
              <input v-model="form.meta_title" type="text" placeholder="Meta title for search engines" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Meta Description</label>
              <textarea
                v-model="form.meta_description"
                rows="4"
                placeholder="Meta description for search engines..."
                class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              ></textarea>
            </div>
            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-[#141A21] rounded-xl border border-gray-200 dark:border-gray-700/50">
              <input v-model="form.is_active" type="checkbox" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary" />
              <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Publish this page</span>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-between">
          <button type="button" @click="$router.push('/dashboard/page-manager')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Cancel</button>
          <button type="submit" :disabled="saving" class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-xl hover:bg-primary-hover transition-colors shadow-sm flex items-center gap-2">
            <Loader2 v-if="saving" class="w-4 h-4 animate-spin" />
            {{ saving ? 'Creating...' : 'Create page' }}
          </button>
        </div>
      </form>
    </div>
  </MainLayout>
</template>

<script>
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import FileUpload from '@/components/FileUpload.vue';
import { ChevronRight, ChevronDown, Loader2 } from 'lucide-vue-next';

export default {
  name: 'PageCreate',
  components: { MainLayout, FileUpload, ChevronRight, ChevronDown, Loader2 },
  data() {
    return {
      sections: { details: true, properties: true },
      saving: false,
      thumbnailUrl: '',
      countries: [],
      pages: [],
      form: {
        title: '',
        page_type: '',
        country_id: '',
        parent_id: '',
        meta_title: '',
        meta_description: '',
        thumbnail: null,
        is_active: true,
      },
    };
  },
  computed: {
    availablePages() {
      return this.pages.filter(p => !p.parent_id);
    },
  },
  mounted() {
    this.fetchCountries();
    this.fetchPages();
  },
  methods: {
    toggleSection(section) {
      this.sections[section] = !this.sections[section];
    },
    async fetchCountries() {
      try {
        const response = await axios.get('/auth/admin/countries');
        this.countries = response.data.data?.data || response.data.data || [];
      } catch (error) {
        console.error('Error fetching countries:', error);
      }
    },
    async fetchPages() {
      try {
        const response = await axios.get('/auth/admin/pages');
        this.pages = response.data.data || [];
      } catch (error) {
        console.error('Error fetching pages:', error);
      }
    },
    handleFileSelect(file) {
      this.form.thumbnail = file;
      this.thumbnailUrl = URL.createObjectURL(file);
    },
    handleFileRemove() {
      this.form.thumbnail = null;
      this.thumbnailUrl = '';
    },
    async save() {
      this.saving = true;
      try {
        const formData = new FormData();
        Object.keys(this.form).forEach(key => {
          if (this.form[key] !== null && this.form[key] !== '') {
            formData.append(key, this.form[key]);
          }
        });
        await axios.post('/auth/admin/pages', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });
        this.$router.push('/dashboard/page-manager');
      } catch (error) {
        console.error('Error saving page:', error);
        alert('Error creating page. Please check your input.');
      } finally {
        this.saving = false;
      }
    },
  },
};
</script>
