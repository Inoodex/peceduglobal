<template>
  <MainLayout>
    <div class="max-w-4xl mx-auto pb-20">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ isEdit ? 'Edit University' : 'Create a new university' }}</h1>
        <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
          <ChevronRight class="w-4 h-4" />
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard/university-manager')">Universities</span>
          <ChevronRight class="w-4 h-4" />
          <span class="text-gray-900 dark:text-white">{{ isEdit ? 'Edit' : 'Create' }}</span>
        </nav>
      </div>

      <form @submit.prevent="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Left Column: Form Fields -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info Section -->
            <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h3>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">University Name <span class="text-red-500">*</span></label>
                  <input v-model="form.name" type="text" placeholder="e.g. University of Oxford" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" required />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Country <span class="text-red-500">*</span></label>
                    <select v-model="form.country_id" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" required>
                      <option value="" disabled>Select Country</option>
                      <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.name }}</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Location</label>
                    <input v-model="form.location" type="text" placeholder="e.g. Oxford, UK" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" />
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Website URL</label>
                  <input v-model="form.website" type="url" placeholder="https://www.ox.ac.uk" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" />
                </div>
              </div>
            </div>

            <!-- Details Section -->
            <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Additional Details</h3>
              <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">World Ranking</label>
                    <input v-model="form.ranking" type="text" placeholder="e.g. 1" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tuition Range</label>
                    <input v-model="form.tuition_range" type="text" placeholder="e.g. £20,000 - £40,000" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" />
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Intake Months</label>
                  <input v-model="form.intake_months" type="text" placeholder="e.g. September, January" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Description</label>
                  <textarea v-model="form.description" rows="4" placeholder="Tell something about this university..." class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"></textarea>
                </div>
              </div>
            </div>

            <!-- Social Links Section -->
            <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Social Links</h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Facebook</label>
                  <input v-model="form.social_links.facebook" type="url" placeholder="https://facebook.com/..." class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Twitter / X</label>
                  <input v-model="form.social_links.twitter" type="url" placeholder="https://twitter.com/..." class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">LinkedIn</label>
                  <input v-model="form.social_links.linkedin" type="url" placeholder="https://linkedin.com/..." class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Instagram</label>
                  <input v-model="form.social_links.instagram" type="url" placeholder="https://instagram.com/..." class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" />
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column: Media & Actions -->
          <div class="space-y-6">
            <!-- Logo Section -->
            <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">University Logo</h3>
              <FileUpload
                v-model="logoUrl"
                :show-alt-input="false"
                placeholder="Select Logo"
                hint="Max 2MB, JPG/PNG/WEBP/SVG"
                @select="(file) => handleFileSelect(file, 'logo')"
                @remove="() => handleFileRemove('logo')"
              />
            </div>

            <!-- Banner Section -->
            <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">University Banner</h3>
              <FileUpload
                v-model="bannerUrl"
                :show-alt-input="false"
                placeholder="Select Banner"
                hint="Max 4MB, JPG/PNG/WEBP"
                @select="(file) => handleFileSelect(file, 'banner')"
                @remove="() => handleFileRemove('banner')"
              />
            </div>

            <!-- Status Section -->
            <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Visibility & Status</h3>
              <div class="space-y-4">
                <label class="flex items-center gap-3 cursor-pointer group">
                  <input v-model="form.is_popular" type="checkbox" class="w-5 h-5 rounded-lg border-gray-300 text-primary focus:ring-primary/20" />
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">Mark as Popular</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer group">
                  <input v-model="form.is_partner" type="checkbox" class="w-5 h-5 rounded-lg border-gray-300 text-primary focus:ring-primary/20" />
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">Official Partner</span>
                </label>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col gap-3">
              <button type="submit" :disabled="saving" class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-primary hover:bg-primary-hover text-white font-bold rounded-xl transition-all shadow-lg shadow-primary/20 disabled:opacity-50">
                <Loader2 v-if="saving" class="w-5 h-5 animate-spin" />
                {{ isEdit ? 'Update University' : 'Create University' }}
              </button>
              <button type="button" @click="$router.push('/dashboard/university-manager')" class="w-full px-6 py-3 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all">
                Cancel
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import FileUpload from '@/components/FileUpload.vue';
import { ChevronRight, Loader2 } from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();

const isEdit = computed(() => !!route.params.id);
const countries = ref([]);
const saving = ref(false);
const logoUrl = ref('');
const bannerUrl = ref('');

const form = ref({
  name: '',
  country_id: '',
  location: '',
  website: '',
  ranking: '',
  tuition_range: '',
  intake_months: '',
  description: '',
  is_popular: false,
  is_partner: false,
  social_links: {
    facebook: '',
    twitter: '',
    linkedin: '',
    instagram: ''
  },
  logo: null,
  banner: null
});

const handleFileSelect = (file, type) => {
  if (type === 'logo') {
    form.value.logo = file;
    logoUrl.value = URL.createObjectURL(file);
  } else if (type === 'banner') {
    form.value.banner = file;
    bannerUrl.value = URL.createObjectURL(file);
  }
};

const handleFileRemove = (type) => {
  if (type === 'logo') {
    form.value.logo = null;
    logoUrl.value = '';
  } else if (type === 'banner') {
    form.value.banner = null;
    bannerUrl.value = '';
  }
};

const fetchCountries = async () => {
  try {
    const response = await axios.get('/auth/admin/countries');
    countries.value = response.data.data?.data || response.data.data || [];
  } catch (e) {
    console.error('Failed to fetch countries', e);
  }
};

const fetchUniversity = async () => {
  if (!isEdit.value) return;
  try {
    const response = await axios.get(`/auth/admin/universities/${route.params.id}`);
    const data = response.data.data;
    
    // Merge social links with defensive parsing
    let social = data.social_links || {};
    if (typeof social === 'string') {
      try {
        social = JSON.parse(social);
      } catch (e) {
        social = {};
      }
    }
    
    form.value = {
      ...data,
      is_popular: !!data.is_popular,
      is_partner: !!data.is_partner,
      social_links: {
        facebook: social.facebook || '',
        twitter: social.twitter || '',
        linkedin: social.linkedin || '',
        instagram: social.instagram || ''
      },
      logo: null,
      banner: null
    };
    
    if (data.logo) logoUrl.value = data.logo;
    if (data.banner) bannerUrl.value = data.banner;
  } catch (e) {
    console.error('Failed to fetch university', e);
  }
};

const save = async () => {
  saving.value = true;
  try {
    const formData = new FormData();
    
    Object.keys(form.value).forEach(key => {
      const value = form.value[key];
      if (value !== null && value !== undefined) {
        if (key === 'social_links') {
          // Append each social link as part of an array for PHP to parse as JSON
          Object.keys(value).forEach(socialKey => {
            if (value[socialKey]) {
              formData.append(`social_links[${socialKey}]`, value[socialKey]);
            }
          });
        } else if (typeof value === 'boolean') {
          formData.append(key, value ? 1 : 0);
        } else if (value instanceof File) {
          formData.append(key, value);
        } else if (key !== 'logo' && key !== 'banner') {
          formData.append(key, value);
        }
      }
    });

    if (isEdit.value) {
      formData.append('_method', 'PUT');
      await axios.post(`/auth/admin/universities/${route.params.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    } else {
      await axios.post('/auth/admin/universities', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    }
    router.push('/dashboard/university-manager');
  } catch (e) {
    console.error('Save failed', e);
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchCountries();
  fetchUniversity();
});
</script>
