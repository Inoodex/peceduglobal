<template>
  <MainLayout>
    <div class="p-6 max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Profile</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">Manage your personal information and account settings.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Left: Profile Summary -->
        <div class="md:col-span-1 space-y-6">
          <div class="bg-white dark:bg-[#1C252E] p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm text-center">
            <div class="relative w-32 h-32 mx-auto mb-4">
              <img 
                :src="auth.user?.profile_photo_url || `https://api.dicebear.com/7.x/avataaars/svg?seed=${auth.user?.full_name}`" 
                class="w-full h-full rounded-full border-4 border-primary/10 object-cover"
                alt="Avatar"
              />
              <button class="absolute bottom-1 right-1 p-2 bg-primary text-white rounded-full shadow-lg hover:scale-110 transition-transform">
                <Camera :size="16" />
              </button>
            </div>
            <h3 class="text-lg font-bold">{{ auth.user?.full_name }}</h3>
            <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mt-1">{{ auth.user?.role }}</p>
            
            <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-800 flex justify-around">
              <div class="text-center">
                <div class="text-xl font-bold">12</div>
                <div class="text-[10px] text-gray-500 uppercase font-bold">Sessions</div>
              </div>
              <div class="text-center">
                <div class="text-xl font-bold">4</div>
                <div class="text-[10px] text-gray-500 uppercase font-bold">Active</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Edit Form -->
        <div class="md:col-span-2">
          <div class="bg-white dark:bg-[#1C252E] p-8 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
            <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
              <Settings :size="20" class="text-primary" /> General Information
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-500 uppercase">Full Name</label>
                <input 
                  type="text" 
                  v-model="form.full_name"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-[#151C24] outline-none focus:ring-2 focus:ring-primary/20 transition-all"
                />
              </div>
              <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-500 uppercase">Email Address</label>
                <input 
                  type="email" 
                  v-model="form.email"
                  disabled
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-[#151C24]/50 text-gray-400 cursor-not-allowed outline-none"
                />
              </div>
              <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-500 uppercase">Phone Number</label>
                <input 
                  type="text" 
                  v-model="form.phone"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-[#151C24] outline-none focus:ring-2 focus:ring-primary/20 transition-all"
                />
              </div>
              <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-500 uppercase">Nationality</label>
                <input 
                  type="text" 
                  v-model="form.nationality"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-[#151C24] outline-none focus:ring-2 focus:ring-primary/20 transition-all"
                />
              </div>
            </div>

            <div class="mt-8 flex justify-end">
              <button class="px-8 py-3 bg-primary text-white rounded-xl font-bold shadow-lg shadow-primary/20 hover:scale-105 active:scale-95 transition-all">
                Save Changes
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import MainLayout from '@/layouts/MainLayout.vue';
import { Camera, Settings, Mail, Phone, Globe } from 'lucide-vue-next';

const auth = useAuthStore();

const form = ref({
  full_name: auth.user?.full_name || '',
  email: auth.user?.email || '',
  phone: auth.user?.phone || '',
  nationality: auth.user?.nationality || '',
});
</script>
