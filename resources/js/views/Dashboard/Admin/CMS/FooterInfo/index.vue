<template>
  <MainLayout>
    <div class="p-6 max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Footer Info Settings</h1>
    </div>

    <div class="bg-white dark:bg-[#1C252E] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-6">
      <div v-if="loading" class="flex justify-center items-center py-12">
        <Loader2 class="w-8 h-8 animate-spin text-primary" />
      </div>

      <form v-else @submit.prevent="saveSettings" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          
          <!-- Phone -->
          <div class="space-y-2">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Contact Phone</label>
            <div class="relative">
              <Phone class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input 
                v-model="form.phone"
                type="text"
                class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors text-gray-900 dark:text-white"
                placeholder="+1 234 567 890"
              />
            </div>
          </div>

          <!-- Email -->
          <div class="space-y-2">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Contact Email</label>
            <div class="relative">
              <Mail class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input 
                v-model="form.email"
                type="email"
                class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors text-gray-900 dark:text-white"
                placeholder="contact@company.com"
              />
            </div>
          </div>

          <!-- Address -->
          <div class="space-y-2 md:col-span-2">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Office Address</label>
            <div class="relative">
              <MapPin class="absolute left-3 top-4 w-5 h-5 text-gray-400" />
              <textarea 
                v-model="form.address"
                rows="3"
                class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors text-gray-900 dark:text-white resize-none"
                placeholder="Frederiksberggade 25A, 1459 København"
              ></textarea>
            </div>
          </div>

          <!-- Copyright -->
          <div class="space-y-2 md:col-span-2">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Copyright Text</label>
            <div class="relative">
              <Copyright class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input 
                v-model="form.copyright"
                type="text"
                class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors text-gray-900 dark:text-white"
                placeholder="© 2026 PecEduGlobal. All rights reserved."
              />
            </div>
          </div>

          <!-- Footer Logo -->
          <div class="space-y-2 md:col-span-2">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Footer Logo</label>
            <div class="flex items-center gap-6 mt-2">
              <div 
                class="w-32 h-16 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 flex items-center justify-center overflow-hidden bg-gray-50 dark:bg-[#141A21]"
              >
                <img v-if="previewLogo" :src="previewLogo" class="max-w-full max-h-full object-contain p-2" />
                <Image v-else class="w-8 h-8 text-gray-400" />
              </div>
              <div class="flex-1">
                <input 
                  type="file" 
                  ref="logoInput"
                  @change="handleLogoChange"
                  accept="image/*"
                  class="hidden"
                />
                <button 
                  type="button"
                  @click="$refs.logoInput.click()"
                  class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors"
                >
                  Choose New Logo
                </button>
                <p class="text-xs text-gray-500 mt-2">Recommended size: 200x80px. Max size: 2MB.</p>
              </div>
            </div>
          </div>

        </div>

        <div class="pt-6 border-t border-gray-100 dark:border-gray-800 flex justify-end">
          <button 
            type="submit" 
            :disabled="saving"
            class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-xl font-medium transition-colors flex items-center gap-2"
          >
            <Loader2 v-if="saving" class="w-4 h-4 animate-spin" />
            <Save v-else class="w-4 h-4" />
            {{ saving ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import MainLayout from '@/layouts/MainLayout.vue';
import axios from '@/plugins/axios';
import { useToastStore } from '@/stores/toast';
import { Loader2, Save, Phone, Mail, MapPin, Copyright, Image } from 'lucide-vue-next';

const toast = useToastStore();
const loading = ref(true);
const saving = ref(false);
const logoInput = ref(null);
const previewLogo = ref(null);

const form = ref({
  phone: '',
  email: '',
  address: '',
  copyright: '',
  logo: null
});

const fetchSettings = async () => {
  try {
    const response = await axios.get('/auth/admin/footer-info');
    if (response.data.success && response.data.data) {
      const data = response.data.data;
      form.value.phone = data.phone || '';
      form.value.email = data.email || '';
      form.value.address = data.address || '';
      form.value.copyright = data.copyright || '';
      if (data.logo) {
        previewLogo.value = data.logo;
      }
    }
  } catch (error) {
    toast.error('Failed to load footer info.');
  } finally {
    loading.value = false;
  }
};

const handleLogoChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.value.logo = file;
    const reader = new FileReader();
    reader.onload = (e) => previewLogo.value = e.target.result;
    reader.readAsDataURL(file);
  }
};

const saveSettings = async () => {
  saving.value = true;
  try {
    const formData = new FormData();
    formData.append('phone', form.value.phone);
    formData.append('email', form.value.email);
    formData.append('address', form.value.address);
    formData.append('copyright', form.value.copyright);
    
    if (form.value.logo instanceof File) {
      formData.append('logo', form.value.logo);
    }

    const response = await axios.post('/auth/admin/footer-info', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    if (response.data.success) {
      toast.success(response.data.message);
      if (response.data.data.logo) {
        previewLogo.value = response.data.data.logo;
      }
    }
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to save settings.');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchSettings();
});
</script>
