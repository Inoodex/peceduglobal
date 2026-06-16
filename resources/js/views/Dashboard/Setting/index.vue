<template>
  <MainLayout>
    <!-- Loading State: Premium Sweeping Shimmer Track -->
    <div v-if="loading && !form" class="min-h-[75vh] flex flex-col items-center justify-center bg-transparent animate-fade-in">
      <div class="relative w-64 h-[3px] bg-gray-200 dark:bg-gray-800/80 rounded-full overflow-hidden">
        <div class="absolute inset-0 line-shimmer-sweep"></div>
      </div>
      <p class="text-xs text-gray-400 dark:text-gray-500 mt-4 tracking-wider uppercase">Loading configurations...</p>
    </div>

    <!-- Loaded State -->
    <div v-else class="max-w-6xl mx-auto pb-20 animate-fade-in">
      <!-- Header with Breadcrumbs & Save Button -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-10">
        <div>
          <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">System Settings</h1>
          <nav class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mt-2 font-medium">
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer transition-colors" @click="$router.push('/dashboard')">Dashboard</span>
            <ChevronRight class="w-3.5 h-3.5" />
            <span class="text-gray-900 dark:text-white">Settings</span>
          </nav>
        </div>

        <button 
          @click="saveSettings" 
          :disabled="saving"
          class="inline-flex items-center justify-center gap-2.5 px-6 py-3.5 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-primary/20 hover:shadow-primary/30 active:scale-[0.98] disabled:opacity-60 disabled:pointer-events-none"
        >
          <Loader2 v-if="saving" class="w-4 h-4 animate-spin" />
          <Save v-else class="w-4 h-4" />
          {{ saving ? 'Saving Changes...' : 'Save Settings' }}
        </button>
      </div>

      <!-- Modern Sidebar Layout (2 Columns) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left Side: Card-styled Vertical Tabs -->
        <div class="lg:col-span-4 space-y-3">
          <div class="bg-white dark:bg-[#1C252E] rounded-3xl border border-gray-200/50 dark:border-gray-800/80 p-3 shadow-md">
            <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-4 pt-3 pb-2">Configuration Groups</p>
            <div class="space-y-1">
              <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                class="w-full flex items-center gap-4 p-4 rounded-2xl text-left transition-all duration-200 group relative overflow-hidden"
                :class="activeTab === tab.id 
                  ? 'bg-primary/10 text-primary dark:bg-primary/15' 
                  : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/30 hover:text-gray-900 dark:hover:text-white'"
              >
                <!-- Active Indicator Border -->
                <div 
                  class="absolute left-0 top-0 bottom-0 w-1 bg-primary transition-all duration-200"
                  :class="activeTab === tab.id ? 'opacity-100' : 'opacity-0'"
                ></div>

                <!-- Icon Wrapper -->
                <div 
                  class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors"
                  :class="activeTab === tab.id 
                    ? 'bg-primary/20 text-primary' 
                    : 'bg-gray-100 dark:bg-gray-800 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-200'"
                >
                  <component :is="tab.icon" class="w-5 h-5" />
                </div>

                <!-- Labels -->
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-bold truncate">{{ tab.label }}</p>
                  <p class="text-xs text-gray-400 dark:text-gray-500 truncate mt-0.5">{{ tab.desc }}</p>
                </div>
              </button>
            </div>
          </div>
        </div>

        <!-- Right Side: Content Card -->
        <div class="lg:col-span-8 bg-white dark:bg-[#1C252E] rounded-3xl border border-gray-200/50 dark:border-gray-800/80 p-6 md:p-8 shadow-md">
          <form @submit.prevent="saveSettings">
            
            <!-- CHAT SETTINGS PANEL -->
            <div v-show="activeTab === 'chat'" class="space-y-6 animate-slide-up">
              <div class="border-b border-gray-100 dark:border-gray-800/60 pb-5">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Chat System Configuration</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Configure Pusher credentials for real-time messaging. These settings are used by both Admin and Student interfaces.</p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Pusher App ID -->
                <div class="space-y-2">
                  <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Pusher App ID</label>
                  <input
                    v-model="form.pusher_app_id"
                    type="text"
                    placeholder="e.g. 1234567"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                  />
                </div>

                <!-- Pusher Key -->
                <div class="space-y-2">
                  <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Pusher Key</label>
                  <input
                    v-model="form.pusher_key"
                    type="text"
                    placeholder="e.g. abc123xyz..."
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                  />
                </div>

                <!-- Pusher Secret -->
                <div class="space-y-2">
                  <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Pusher Secret</label>
                  <input
                    v-model="form.pusher_secret"
                    type="password"
                    placeholder="••••••••••••"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                  />
                </div>

                <!-- Pusher Cluster -->
                <div class="space-y-2">
                  <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Pusher Cluster</label>
                  <input
                    v-model="form.pusher_cluster"
                    type="text"
                    placeholder="e.g. mt1"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                  />
                </div>
              </div>
            </div>

            <!-- GENERAL SETTINGS PANEL -->
            <div v-show="activeTab === 'general'" class="space-y-6 animate-slide-up">

              <div class="border-b border-gray-100 dark:border-gray-800/60 pb-5">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">General Information</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Basic public branding details, primary contact points, and geolocation mappings.</p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Site Name -->
                <div class="space-y-2">
                  <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Site Name</label>
                  <input
                    v-model="form.site_name"
                    type="text"
                    placeholder="e.g. Pecedu Global"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                  />
                </div>

                <!-- Contact Email -->
                <div class="space-y-2">
                  <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Contact Email</label>
                  <input
                    v-model="form.contact_email"
                    type="email"
                    placeholder="e.g. info@pecedu.com"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                  />
                </div>

                <!-- Contact Phone -->
                <div class="space-y-2">
                  <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Contact Phone</label>
                  <input
                    v-model="form.contact_phone"
                    type="text"
                    placeholder="e.g. +880 1234-567890"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                  />
                </div>

                <!-- Map URL -->
                <div class="space-y-2">
                  <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Google Map URL</label>
                  <input
                    v-model="form.map_url"
                    type="text"
                    placeholder="https://google.com/maps/embed/..."
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                  />
                </div>

                <!-- Contact Address -->
                <div class="space-y-2 md:col-span-2">
                  <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Contact Address</label>
                  <textarea
                    v-model="form.contact_address"
                    rows="3"
                    placeholder="Physical address..."
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none"
                  ></textarea>
                </div>
              </div>
            </div>

            <!-- EMAIL CONFIGURATION PANEL -->
            <div v-show="activeTab === 'email'" class="space-y-6 animate-slide-up">
              <div class="border-b border-gray-100 dark:border-gray-800/60 pb-5">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Email Server Settings</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Configure SMTP, mail transport drivers, and global sender templates.</p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Mail Mailer -->
                <div class="space-y-2">
                  <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Mail Driver</label>
                  <select
                    v-model="form.mail_mailer"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                  >
                    <option value="smtp">SMTP (Recommended)</option>
                    <option value="sendmail">Sendmail</option>
                    <option value="mailgun">Mailgun</option>
                    <option value="ses">Amazon SES</option>
                  </select>
                </div>

                <!-- Mail Host -->
                <div class="space-y-2">
                  <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">SMTP Host</label>
                  <input
                    v-model="form.mail_host"
                    type="text"
                    placeholder="smtp.mailtrap.io"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                  />
                </div>

                <!-- Mail Port -->
                <div class="space-y-2">
                  <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">SMTP Port</label>
                  <input
                    v-model="form.mail_port"
                    type="text"
                    placeholder="e.g. 587 or 465"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                  />
                </div>

                <!-- Mail Encryption -->
                <div class="space-y-2">
                  <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Encryption Protocol</label>
                  <select
                    v-model="form.mail_encryption"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                  >
                    <option value="">None</option>
                    <option value="tls">TLS</option>
                    <option value="ssl">SSL</option>
                  </select>
                </div>

                <!-- Username -->
                <div class="space-y-2">
                  <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">SMTP Username</label>
                  <input
                    v-model="form.mail_username"
                    type="text"
                    placeholder="SMTP Account Username"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                  />
                </div>

                <!-- Password -->
                <div class="space-y-2">
                  <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">SMTP Password</label>
                  <div class="relative">
                    <input
                      v-model="form.mail_password"
                      :type="showPassword ? 'text' : 'password'"
                      placeholder="SMTP Account Password"
                      class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-800 rounded-xl pl-4 pr-10 py-3.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                    />
                    <button
                      type="button"
                      @click="showPassword = !showPassword"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                    >
                      <Eye v-if="!showPassword" class="w-4.5 h-4.5" />
                      <EyeOff v-else class="w-4.5 h-4.5" />
                    </button>
                  </div>
                </div>

                <!-- Sender Email -->
                <div class="space-y-2">
                  <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Sender Email (From)</label>
                  <input
                    v-model="form.mail_from_address"
                    type="email"
                    placeholder="noreply@pecedu.com"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                  />
                </div>

                <!-- Sender Name -->
                <div class="space-y-2">
                  <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Sender Name (From)</label>
                  <input
                    v-model="form.mail_from_name"
                    type="text"
                    placeholder="Pecedu Global Admin"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                  />
                </div>
              </div>
            </div>

            <!-- LOGO & FAVICON PANEL -->
            <div v-show="activeTab === 'branding'" class="space-y-6 animate-slide-up">
              <div class="border-b border-gray-100 dark:border-gray-800/60 pb-5">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Branding Assets</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Upload brand logo mark and modern favicon shortcut icons.</p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Logo File Upload -->
                <div class="space-y-3">
                  <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Brand Logo</label>
                  <FileUpload
                    v-model="logoUrl"
                    :show-alt-input="false"
                    placeholder="Drop or select Brand Logo"
                    hint="SVG, PNG, JPEG or WebP (Max 2MB)"
                    @select="handleLogoSelect"
                    @remove="logoFile = null; logoUrl = ''"
                  />
                </div>

                <!-- Favicon File Upload -->
                <div class="space-y-3">
                  <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Favicon Icon</label>
                  <FileUpload
                    v-model="faviconUrl"
                    :show-alt-input="false"
                    accept="image/*,.ico"
                    placeholder="Drop or select Favicon"
                    hint="PNG, ICO, SVG or Apple Icon (Max 1MB)"
                    @select="handleFaviconSelect"
                    @remove="faviconFile = null; faviconUrl = ''"
                  />
                </div>
              </div>
            </div>

            <!-- Action Section Inside Panel -->
            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 flex justify-end">
              <button 
                type="button"
                @click="saveSettings" 
                :disabled="saving"
                class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-xl transition-all shadow-md disabled:opacity-60 disabled:pointer-events-none"
              >
                <Loader2 v-if="saving" class="w-4 h-4 animate-spin" />
                <Save v-else class="w-4 h-4" />
                Save {{ tabs.find(t => t.id === activeTab)?.label }}
              </button>
            </div>
            
          </form>
        </div>

      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import FileUpload from '@/components/FileUpload.vue';
import { useToastStore } from '@/stores/toast';
import { 
  ChevronRight, 
  Loader2, 
  Settings, 
  Mail, 
  Image as ImageIcon, 
  Save, 
  Eye, 
  EyeOff 
} from 'lucide-vue-next';

// Stores
const toast = useToastStore();

// Sidebar Navigation Tabs
const tabs = [
  { id: 'general', label: 'General Setting', desc: 'Site branding, contacts, map', icon: Settings },
  { id: 'email', label: 'Email Config', desc: 'SMTP protocols and driver setup', icon: Mail },
  { id: 'branding', label: 'Logo & Favicon', desc: 'Public identity images', icon: ImageIcon },
];
const activeTab = ref('general');

// Loading States
const loading = ref(true);
const saving = ref(false);
const showPassword = ref(false);

// Forms & Files
const form = ref({
  site_name: '',
  contact_email: '',
  contact_phone: '',
  contact_address: '',
  map_url: '',
  mail_mailer: 'smtp',
  mail_host: '',
  mail_port: '',
  mail_username: '',
  mail_password: '',
  mail_encryption: '',
  mail_from_address: '',
  mail_from_name: '',
});

const logoUrl = ref('');
const faviconUrl = ref('');
const logoFile = ref(null);
const faviconFile = ref(null);

// Fetch settings configuration
const fetchSettings = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/auth/admin/settings');
    if (response.data.success) {
      const data = response.data.data;
      
      // Map basic values
      Object.keys(form.value).forEach(key => {
        if (data[key] !== undefined) {
          form.value[key] = data[key] || '';
        }
      });
      
      // Set image previews
      logoUrl.value = data.logo || '';
      faviconUrl.value = data.favicon || '';
    }
  } catch (error) {
    console.error('Failed to load settings', error);
    toast.error('Failed to retrieve server configurations.');
  } finally {
    loading.value = false;
  }
};

// File handlers
const handleLogoSelect = (file) => {
  logoFile.value = file;
  logoUrl.value = URL.createObjectURL(file);
};

const handleFaviconSelect = (file) => {
  faviconFile.value = file;
  faviconUrl.value = URL.createObjectURL(file);
};

// Update and Save settings configuration
const saveSettings = async () => {
  saving.value = true;
  try {
    const formData = new FormData();
    formData.append('group', activeTab.value);
    
    // Append fields conditionally based on the active tab
    if (activeTab.value === 'general') {
      formData.append('site_name', form.value.site_name || '');
      formData.append('contact_email', form.value.contact_email || '');
      formData.append('contact_phone', form.value.contact_phone || '');
      formData.append('contact_address', form.value.contact_address || '');
      formData.append('map_url', form.value.map_url || '');
    } else if (activeTab.value === 'email') {
      formData.append('mail_mailer', form.value.mail_mailer || 'smtp');
      formData.append('mail_host', form.value.mail_host || '');
      formData.append('mail_port', form.value.mail_port || '');
      formData.append('mail_username', form.value.mail_username || '');
      formData.append('mail_password', form.value.mail_password || '');
      formData.append('mail_encryption', form.value.mail_encryption || '');
      formData.append('mail_from_address', form.value.mail_from_address || '');
      formData.append('mail_from_name', form.value.mail_from_name || '');
    } else if (activeTab.value === 'branding') {
      if (logoFile.value) {
        formData.append('logo', logoFile.value);
      }
      if (faviconFile.value) {
        formData.append('favicon', faviconFile.value);
      }
    }

    const response = await axios.post('/auth/admin/settings', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    if (response.data.success) {
      const activeLabel = tabs.find(t => t.id === activeTab.value)?.label || 'Settings';
      toast.success(`${activeLabel} saved successfully!`);
      
      // Update local state with latest response
      const updatedData = response.data.data;
      logoUrl.value = updatedData.logo || '';
      faviconUrl.value = updatedData.favicon || '';
      logoFile.value = null;
      faviconFile.value = null;
    }
  } catch (error) {
    console.error('Failed to save settings', error);
    let errorMsg = 'Failed to sync settings with server. Please try again.';
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors;
      const errorList = Object.values(errors).flat();
      if (errorList.length > 0) {
        errorMsg = errorList.join(' ');
      }
    } else if (error.response?.data?.message) {
      errorMsg = error.response.data.message;
    }
    toast.error(errorMsg);
  } finally {
    saving.value = false;
  }
};

// Lifecycle
onMounted(fetchSettings);
</script>

<style scoped>
/* Sleek Shimmer Line Animation */
.line-shimmer-sweep {
  background: linear-gradient(
    90deg,
    rgba(0, 171, 85, 0.05) 0%,
    rgba(0, 171, 85, 0.6) 50%,
    rgba(0, 171, 85, 0.05) 100%
  );
  animation: shimmerSweep 1.8s infinite ease-in-out;
}

@keyframes shimmerSweep {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}

/* Page Micro-Transitions */
.animate-fade-in {
  animation: fadeIn 0.4s ease-out forwards;
}

.animate-slide-up {
  animation: slideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
