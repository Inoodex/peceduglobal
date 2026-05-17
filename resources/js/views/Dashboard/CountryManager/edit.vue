<template>
  <MainLayout>
    <div class="max-w-3xl mx-auto pb-20 relative">
      <!-- Loading Overlay -->
      <div v-if="loading" class="absolute inset-0 bg-white/80 dark:bg-gray-900/80 z-50 flex items-center justify-center">
        <div class="flex flex-col items-center gap-3">
          <div class="w-10 h-10 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
          <p class="text-gray-700 dark:text-gray-300">Loading country...</p>
        </div>
      </div>

      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Edit country</h1>
        <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
          <ChevronRight class="w-4 h-4" />
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard/country-manager')">Countries</span>
          <ChevronRight class="w-4 h-4" />
          <span class="text-gray-900 dark:text-white">Edit</span>
        </nav>
      </div>

      <form @submit.prevent="save" :class="{ 'opacity-50 pointer-events-none': loading }">
        <!-- Details Section -->
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 mb-4 overflow-hidden">
          <button type="button" @click="toggleSection('details')" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white">Details</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Name, slug, thumbnail...</p>
            </div>
            <ChevronDown class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': sections.details }" />
          </button>
          <div v-show="sections.details" class="p-4 pt-0 border-t border-gray-200 dark:border-gray-700/50 space-y-4">
            <!-- Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Name <span class="text-red-500">*</span></label>
              <input v-model="form.name" type="text" placeholder="Country name" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" required />
            </div>
            <!-- Thumbnail -->
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
            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-[#141A21] rounded-xl border border-gray-200 dark:border-gray-700/50">
              <input v-model="form.is_popular" type="checkbox" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary" />
              <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Mark as Popular Destination</span>
            </div>
          </div>
        </div>

        <!-- Properties Section -->
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 mb-4 overflow-hidden">
          <button type="button" @click="toggleSection('properties')" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white">Properties</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">ISO code, phone code...</p>
            </div>
            <ChevronDown class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': sections.properties }" />
          </button>
          <div v-show="sections.properties" class="p-4 pt-0 border-t border-gray-200 dark:border-gray-700/50 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- ISO Code -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">ISO Code <span class="text-red-500">*</span></label>
                <input v-model="form.iso_code" type="text" placeholder="BD" maxlength="3" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all uppercase" required />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">2-3 letter country code</p>
              </div>
              <!-- Phone Code -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Phone Code <span class="text-red-500">*</span></label>
                <div class="relative">
                  <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">+</span>
                  <input v-model="form.phone_code" type="text" placeholder="880" maxlength="10" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl pl-8 pr-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" required />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
          <button type="button" @click="$router.push('/dashboard/country-manager')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Cancel</button>
          <div class="flex items-center gap-3">
            <button type="submit" :disabled="saving" class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-400 transition-colors shadow-sm flex items-center gap-2">
              <Loader2 v-if="saving" class="w-4 h-4 animate-spin" />
              {{ saving ? 'Updating...' : 'Update country' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </MainLayout>
</template>

<script>
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import { ChevronRight, ChevronDown, Loader2 } from 'lucide-vue-next';
import FileUpload from '@/components/FileUpload.vue';

export default {
  name: 'CountryEdit',
  components: { MainLayout, ChevronRight, ChevronDown, Loader2, FileUpload },
  data() {
    return {
      sections: { details: true, properties: true },
      loading: false,
      saving: false,
      thumbnailUrl: '',
      form: {
        name: '',
        slug: '',
        iso_code: '',
        phone_code: '',
        thumbnail: null,
        is_popular: false,
      },
    };
  },
  mounted() { this.fetchCountry(); },
  methods: {
    toggleSection(section) { this.sections[section] = !this.sections[section]; },
    async fetchCountry() {
      this.loading = true;
      try {
        const response = await axios.get(`/auth/admin/countries/${this.$route.params.id}`);
        const country = response.data.data;
        this.form = {
          name: country.name,
          slug: country.slug || '',
          iso_code: country.iso_code,
          phone_code: country.phone_code,
          thumbnail: null,
          is_popular: !!country.is_popular,
        };
        if (country.thumbnail) this.thumbnailUrl = country.thumbnail;
      } catch (e) {
        console.error('Failed to load country', e);
        this.$router.push('/dashboard/country-manager');
      } finally {
        this.loading = false;
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
          if (typeof this.form[key] === 'boolean') {
            formData.append(key, this.form[key] ? 1 : 0);
          } else if (this.form[key] !== null && this.form[key] !== '') {
            formData.append(key, this.form[key]);
          }
        });
        await axios.post(`/auth/admin/countries/${this.$route.params.id}`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
          params: { _method: 'PUT' }
        });
        this.$router.push('/dashboard/country-manager');
      } catch (e) {
        console.error('Update failed', e);
      } finally {
        this.saving = false;
      }
    },
  },
};
</script>
