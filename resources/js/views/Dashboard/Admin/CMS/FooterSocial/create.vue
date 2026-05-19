<template>
  <MainLayout>
    <div class="p-6 max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
      <button @click="$router.back()" class="p-2 bg-white dark:bg-[#1C252E] border border-gray-100 dark:border-gray-800 rounded-xl hover:bg-gray-50 dark:hover:bg-[#141A21] transition-colors">
        <ArrowLeft class="w-5 h-5 text-gray-600 dark:text-gray-400" />
      </button>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Add Social Link</h1>
    </div>

    <div class="bg-white dark:bg-[#1C252E] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-6">
      <form @submit.prevent="submitForm" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          
          <!-- Name -->
          <div class="space-y-2">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Platform Name <span class="text-red-500">*</span></label>
            <input 
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-2 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors text-gray-900 dark:text-white"
              placeholder="e.g. Facebook"
            />
          </div>

          <!-- URL -->
          <div class="space-y-2">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">URL <span class="text-red-500">*</span></label>
            <input 
              v-model="form.url"
              type="url"
              required
              class="w-full px-4 py-2 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors text-gray-900 dark:text-white"
              placeholder="https://facebook.com/yourpage"
            />
          </div>

          <!-- Serial No -->
          <div class="space-y-2">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Serial No</label>
            <input 
              v-model="form.serial_no"
              type="number"
              class="w-full px-4 py-2 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors text-gray-900 dark:text-white"
              placeholder="0"
            />
          </div>

          <!-- Status -->
          <div class="space-y-2">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Status</label>
            <select 
              v-model="form.status"
              class="w-full px-4 py-2 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors text-gray-900 dark:text-white"
            >
              <option :value="true">Active</option>
              <option :value="false">Inactive</option>
            </select>
          </div>

          <!-- Icon -->
          <div class="space-y-4 md:col-span-2">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Social Icon</label>
            
            <select 
              v-model="iconType"
              @change="form.icon = ''"
              class="w-full px-4 py-2 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors text-gray-900 dark:text-white"
            >
              <option value="preset">Preset Icon (Select from list)</option>
              <option value="custom">Custom SVG Code</option>
            </select>

            <div v-if="iconType === 'preset'">
              <select 
                v-model="form.icon"
                class="w-full px-4 py-2 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors text-gray-900 dark:text-white"
              >
                <option value="">Select an icon...</option>
                <option value="Facebook">Facebook</option>
                <option value="Twitter">Twitter / X</option>
                <option value="Instagram">Instagram</option>
                <option value="Linkedin">LinkedIn</option>
                <option value="Youtube">YouTube</option>
                <option value="Github">GitHub</option>
                <option value="Twitch">Twitch</option>
                <option value="Dribbble">Dribbble</option>
                <option value="Figma">Figma</option>
                <option value="Globe">Website</option>
                <option value="Mail">Email</option>
              </select>
              <p class="text-xs text-gray-500 mt-2">Select the icon that matches the platform.</p>
            </div>

            <div v-if="iconType === 'custom'">
              <textarea 
                v-model="form.icon"
                rows="4"
                class="w-full px-4 py-2 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors text-gray-900 dark:text-white font-mono text-sm"
                placeholder="Paste SVG code here..."
              ></textarea>
              <p class="text-xs text-gray-500 mt-2">Paste the raw SVG code for the icon. Recommended size is 24x24.</p>
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
            {{ saving ? 'Saving...' : 'Save Link' }}
          </button>
        </div>
      </form>
    </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue';
import MainLayout from '@/layouts/MainLayout.vue';
import { useRouter } from 'vue-router';
import axios from '@/plugins/axios';
import { useToastStore } from '@/stores/toast';
import { ArrowLeft, Save, Loader2 } from 'lucide-vue-next';

const router = useRouter();
const toast = useToastStore();
const saving = ref(false);
const iconType = ref('preset');

const form = ref({
  name: '',
  url: '',
  serial_no: 0,
  status: true,
  icon: ''
});

const submitForm = async () => {
  saving.value = true;
  try {
    const res = await axios.post('/auth/admin/footer-socials', form.value);
    if (res.data.success) {
      toast.success(res.data.message);
      router.push('/dashboard/footer-socials');
    }
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to save social link.');
  } finally {
    saving.value = false;
  }
};
</script>
