<template>
  <MainLayout>
    <div class="p-6 max-w-5xl mx-auto pb-24">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Profile</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Manage your personal information and account security separately.</p>
      </div>

      <!-- Loading State -->
      <div v-if="loadingProfile" class="flex flex-col items-center justify-center py-20 bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50">
        <Loader2 class="w-10 h-10 text-primary animate-spin mb-4" />
        <p class="text-gray-500 dark:text-gray-400">Loading your profile...</p>
      </div>

      <!-- Main Layout -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left: Avatar Card + Photo Upload -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Avatar Summary -->
          <div class="bg-white dark:bg-[#1C252E] p-6 rounded-2xl border border-gray-200 dark:border-gray-700/50 shadow-sm text-center">
            <div class="relative w-36 h-36 mx-auto mb-5">
              <img
                :src="photoPreview || auth.user?.profile_photo_url || `https://api.dicebear.com/7.x/avataaars/svg?seed=${auth.user?.full_name}`"
                class="w-full h-full rounded-full border-4 border-primary/10 object-cover shadow-md"
                alt="Profile Avatar"
              />
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ auth.user?.full_name }}</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider mt-1">{{ auth.user?.role }}</p>
            <p class="text-sm text-gray-400 mt-2">{{ auth.user?.email }}</p>
            <div v-if="auth.user?.role === 'student'" class="mt-5 pt-5 border-t border-gray-100 dark:border-gray-800 flex justify-around">
              <div class="text-center">
                <div class="text-lg font-bold text-gray-800 dark:text-white">{{ profileForm.cgpa || '—' }}</div>
                <div class="text-[10px] text-gray-500 uppercase font-bold">CGPA</div>
              </div>
              <div class="text-center">
                <div class="text-lg font-bold text-gray-800 dark:text-white">{{ profileForm.ielts_score || '—' }}</div>
                <div class="text-[10px] text-gray-500 uppercase font-bold">IELTS</div>
              </div>
            </div>
          </div>

          <!-- Photo Upload Card -->
          <div class="bg-white dark:bg-[#1C252E] p-6 rounded-2xl border border-gray-200 dark:border-gray-700/50 shadow-sm">
            <h4 class="font-bold text-gray-900 dark:text-white mb-4 text-sm uppercase tracking-wider">Change Photo</h4>
            <FileUpload
              v-model="photoUrl"
              :show-alt-input="false"
              placeholder="Drop or select a photo"
              hint="PNG, JPG, WEBP, SVG, AVIF up to 2MB"
              @select="handleFileSelect"
              @remove="handleFileRemove"
            />
            <p v-if="profileErrors.image" class="mt-2 text-xs text-red-500 font-medium">{{ profileErrors.image[0] }}</p>
          </div>
        </div>

        <!-- Right: Two Separate Cards -->
        <div class="lg:col-span-2 space-y-6">

          <!-- ── CARD 1: General Info ── -->
          <form @submit.prevent="saveProfile">
            <div class="bg-white dark:bg-[#1C252E] p-8 rounded-2xl border border-gray-200 dark:border-gray-700/50 shadow-sm">
              <h3 class="text-lg font-bold mb-1 flex items-center gap-2 text-gray-900 dark:text-white">
                <Settings :size="20" class="text-primary" /> General Information
              </h3>
              <p class="text-xs text-gray-400 mb-6">Update your name, contact details, and academic scores.</p>


              <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- First Name -->
                <div class="space-y-1.5">
                  <label class="text-xs font-bold text-gray-500 uppercase">First Name <span class="text-red-500">*</span></label>
                  <input type="text" v-model="profileForm.first_name" placeholder="John"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                    :class="{ 'border-red-500': profileErrors.first_name }" />
                  <p v-if="profileErrors.first_name" class="text-xs text-red-500">{{ profileErrors.first_name[0] }}</p>
                </div>

                <!-- Last Name -->
                <div class="space-y-1.5">
                  <label class="text-xs font-bold text-gray-500 uppercase">Last Name <span class="text-red-500">*</span></label>
                  <input type="text" v-model="profileForm.last_name" placeholder="Doe"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                    :class="{ 'border-red-500': profileErrors.last_name }" />
                  <p v-if="profileErrors.last_name" class="text-xs text-red-500">{{ profileErrors.last_name[0] }}</p>
                </div>

                <!-- Email -->
                <div class="space-y-1.5">
                  <label class="text-xs font-bold text-gray-500 uppercase">Email <span class="text-red-500">*</span></label>
                  <input type="email" v-model="profileForm.email" placeholder="name@example.com"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                    :class="{ 'border-red-500': profileErrors.email }" />
                  <p v-if="profileErrors.email" class="text-xs text-red-500">{{ profileErrors.email[0] }}</p>
                </div>

                <!-- Phone -->
                <div class="space-y-1.5">
                  <label class="text-xs font-bold text-gray-500 uppercase">Phone</label>
                  <input type="text" v-model="profileForm.phone" placeholder="+880 1700 000000"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                    :class="{ 'border-red-500': profileErrors.phone }" />
                  <p v-if="profileErrors.phone" class="text-xs text-red-500">{{ profileErrors.phone[0] }}</p>
                </div>

                <!-- Country -->
                <div class="space-y-1.5">
                  <label class="text-xs font-bold text-gray-500 uppercase">Country</label>
                  <input type="text" v-model="profileForm.country" placeholder="e.g. Bangladesh"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                </div>

                <!-- Nationality -->
                <div class="space-y-1.5">
                  <label class="text-xs font-bold text-gray-500 uppercase">Nationality</label>
                  <input type="text" v-model="profileForm.nationality" placeholder="e.g. Bangladeshi"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                </div>

                <!-- Address -->
                <div class="space-y-1.5 sm:col-span-2">
                  <label class="text-xs font-bold text-gray-500 uppercase">Address</label>
                  <textarea v-model="profileForm.address" rows="2" placeholder="Your residential address..."
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"></textarea>
                </div>

                <!-- CGPA — student only -->
                <div v-if="auth.user?.role === 'student'" class="space-y-1.5">
                  <label class="text-xs font-bold text-gray-500 uppercase">CGPA</label>
                  <input type="number" step="0.01" v-model="profileForm.cgpa" placeholder="e.g. 3.85"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                  <p v-if="profileErrors.cgpa" class="text-xs text-red-500">{{ profileErrors.cgpa[0] }}</p>
                </div>

                <!-- IELTS — student only -->
                <div v-if="auth.user?.role === 'student'" class="space-y-1.5">
                  <label class="text-xs font-bold text-gray-500 uppercase">IELTS Score</label>
                  <input type="number" step="0.5" v-model="profileForm.ielts_score" placeholder="e.g. 7.5"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                  <p v-if="profileErrors.ielts_score" class="text-xs text-red-500">{{ profileErrors.ielts_score[0] }}</p>
                </div>
              </div>

              <div class="flex justify-end mt-6">
                <button type="submit" :disabled="savingProfile"
                  class="px-8 py-3 bg-primary text-white rounded-xl font-bold shadow-lg shadow-primary/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2 text-sm cursor-pointer">
                  <Loader2 v-if="savingProfile" class="w-4 h-4 animate-spin" />
                  {{ savingProfile ? 'Saving...' : 'Save Profile' }}
                </button>
              </div>
            </div>
          </form>

          <!-- ── CARD 2: Change Password (Separate) ── -->
          <form @submit.prevent="savePassword">
            <div class="bg-white dark:bg-[#1C252E] p-8 rounded-2xl border border-gray-200 dark:border-gray-700/50 shadow-sm">
              <h3 class="text-lg font-bold mb-1 flex items-center gap-2 text-gray-900 dark:text-white">
                <Lock :size="20" class="text-primary" /> Change Password
              </h3>
              <p class="text-xs text-gray-400 mb-6">Update your account security by setting a new password.</p>


              <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <!-- Current Password -->
                <div class="space-y-1.5 sm:col-span-3">
                  <label class="text-xs font-bold text-gray-500 uppercase">Current Password <span class="text-red-500">*</span></label>
                  <input type="password" v-model="passwordForm.current_password" placeholder="Enter your current password"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                    :class="{ 'border-red-500': passwordErrors.current_password }" />
                  <p v-if="passwordErrors.current_password" class="text-xs text-red-500">{{ passwordErrors.current_password[0] }}</p>
                </div>

                <!-- New Password -->
                <div class="space-y-1.5 sm:col-span-3 sm:grid sm:grid-cols-2 sm:gap-5">
                  <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-500 uppercase">New Password <span class="text-red-500">*</span></label>
                    <input type="password" v-model="passwordForm.password" placeholder="Minimum 8 characters"
                      class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                      :class="{ 'border-red-500': passwordErrors.password }" />
                    <p v-if="passwordErrors.password" class="text-xs text-red-500">{{ passwordErrors.password[0] }}</p>
                  </div>
                  <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-500 uppercase">Confirm Password <span class="text-red-500">*</span></label>
                    <input type="password" v-model="passwordForm.password_confirmation" placeholder="Re-enter new password"
                      class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                  </div>
                </div>
              </div>

              <div class="flex justify-end mt-6">
                <button type="submit" :disabled="savingPassword"
                  class="px-8 py-3 bg-gray-900 dark:bg-gray-700 text-white rounded-xl font-bold hover:scale-105 active:scale-95 transition-all flex items-center gap-2 text-sm cursor-pointer">
                  <Loader2 v-if="savingPassword" class="w-4 h-4 animate-spin" />
                  {{ savingPassword ? 'Updating...' : 'Update Password' }}
                </button>
              </div>
            </div>
          </form>

        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import MainLayout from '@/layouts/MainLayout.vue';
import FileUpload from '@/components/FileUpload.vue';
import axios from '@/plugins/axios';
import { Settings, Lock, Loader2 } from 'lucide-vue-next';

const auth = useAuthStore();

// ── Shared: Loading & Photo ─────────────────────────────────────
const loadingProfile = ref(true);
const photoUrl       = ref('');
const photoPreview   = ref('');
const selectedFile   = ref(null);

// ── Profile Form ────────────────────────────────────────────────
const savingProfile  = ref(false);
const profileErrors  = ref({});

const profileForm = ref({
  first_name:  '',
  last_name:   '',
  email:       '',
  phone:       '',
  country:     '',
  nationality: '',
  address:     '',
  cgpa:        '',
  ielts_score: '',
});

// ── Password Form ───────────────────────────────────────────────
const savingPassword  = ref(false);
const passwordErrors  = ref({});

const passwordForm = ref({
  current_password:      '',
  password:              '',
  password_confirmation: '',
});

// ── Fetch Profile ───────────────────────────────────────────────
const fetchProfile = async () => {
  loadingProfile.value = true;

  // ① Instant pre-fill from Pinia auth.user (no wait for API)
  if (auth.user) {
    const nameParts = (auth.user.full_name || '').split(' ');
    profileForm.value.first_name  = nameParts[0] || '';
    profileForm.value.last_name   = nameParts.slice(1).join(' ') || '';
    profileForm.value.email       = auth.user.email       || '';
    profileForm.value.phone       = auth.user.phone       || '';
    profileForm.value.country     = auth.user.country_of_origin || '';
    profileForm.value.nationality = auth.user.nationality || '';
    if (auth.user.profile_photo_url) {
      photoUrl.value     = auth.user.profile_photo_url;
      photoPreview.value = auth.user.profile_photo_url;
    }
  }

  // ② Then override with full profile data from API (includes academic fields etc.)
  try {
    const res     = await axios.get('/auth/profile');
    const profile = res.data.data;

    profileForm.value.first_name  = profile.first_name  || profileForm.value.first_name;
    profileForm.value.last_name   = profile.last_name   || profileForm.value.last_name;
    profileForm.value.email       = profile.email       || profileForm.value.email;
    profileForm.value.phone       = profile.phone       || profileForm.value.phone;
    profileForm.value.country     = profile.country     || profileForm.value.country;
    profileForm.value.nationality = profile.nationality || profileForm.value.nationality;
    profileForm.value.address     = profile.address     || '';
    profileForm.value.cgpa        = profile.cgpa        || '';
    profileForm.value.ielts_score = profile.ielts_score || '';

    if (profile.profile_photo_url) {
      photoUrl.value     = profile.profile_photo_url;
      photoPreview.value = profile.profile_photo_url;
    }
  } catch (err) {
    console.error('Failed to load profile', err);
  } finally {
    loadingProfile.value = false;
  }
};

// ── Image Upload Handlers ───────────────────────────────────────
const handleFileSelect = (file) => {
  selectedFile.value = file;
  photoPreview.value = URL.createObjectURL(file);
};
const handleFileRemove = () => {
  selectedFile.value = null;
  photoPreview.value = '';
  photoUrl.value     = '';
};

// ── Save Profile ────────────────────────────────────────────────
const saveProfile = async () => {
  savingProfile.value = true;
  profileErrors.value = {};

  try {
    const fd = new FormData();
    fd.append('_method',      'PUT');
    fd.append('first_name',   profileForm.value.first_name);
    fd.append('last_name',    profileForm.value.last_name);
    fd.append('email',        profileForm.value.email);
    fd.append('phone',        profileForm.value.phone        || '');
    fd.append('country',      profileForm.value.country      || '');
    fd.append('nationality',  profileForm.value.nationality  || '');
    fd.append('address',      profileForm.value.address      || '');
    fd.append('cgpa',         profileForm.value.cgpa         || '');
    fd.append('ielts_score',  profileForm.value.ielts_score  || '');

    if (selectedFile.value) {
      fd.append('image', selectedFile.value);
    }

    await axios.post('/auth/profile', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    await auth.fetchUser();
    await fetchProfile();
  } catch (err) {
    if (err.response?.status === 422) {
      profileErrors.value = err.response.data.errors || {};
    } else {
      console.error('Profile update failed:', err.response?.data?.message);
    }
  } finally {
    savingProfile.value = false;
  }
};

// ── Save Password ────────────────────────────────────────────────
const savePassword = async () => {
  savingPassword.value = true;
  passwordErrors.value = {};

  try {
    await axios.put('/auth/profile/password', {
      current_password:      passwordForm.value.current_password,
      password:              passwordForm.value.password,
      password_confirmation: passwordForm.value.password_confirmation,
    });

    // Clear the password fields on success
    passwordForm.value.current_password      = '';
    passwordForm.value.password              = '';
    passwordForm.value.password_confirmation = '';
  } catch (err) {
    if (err.response?.status === 422) {
      passwordErrors.value = err.response.data.errors || {};
    } else {
      console.error('Password update failed:', err.response?.data?.message);
    }
  } finally {
    savingPassword.value = false;
  }
};

onMounted(() => {
  fetchProfile();
});
</script>
