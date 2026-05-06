<script setup>
import { useAuthStore } from '@/stores/auth';
import MainLayout from '@/layouts/MainLayout.vue';
import { TrendingUp, Users, ShoppingBag, Download } from 'lucide-vue-next';

const authStore = useAuthStore();

const stats = [
  { name: 'Total active users', value: '18,765', change: '+2.6%', icon: Users, color: 'text-green-500' },
  { name: 'Total installed', value: '4,876', change: '+0.2%', icon: ShoppingBag, color: 'text-blue-500' },
  { name: 'Total downloads', value: '678', change: '-0.1%', icon: Download, color: 'text-orange-500' },
];
</script>

<template>
  <MainLayout>
    <div class="space-y-8">
      <!-- Welcome Banner -->
      <div class="relative overflow-hidden rounded-3xl bg-[#004B50] p-10 flex items-center justify-between text-white shadow-xl">
        <div class="z-10 max-w-lg">
          <h1 class="text-3xl font-bold mb-4">Welcome back 👋 <br /> {{ authStore.user?.name || 'Jaydon Frankie' }}</h1>
          <p class="text-white/70 mb-8 leading-relaxed">
            If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything.
          </p>
          <button class="bg-primary hover:bg-primary-hover px-6 py-2.5 rounded-xl font-bold transition-colors">
            Go now
          </button>
        </div>
        <div class="hidden lg:block relative z-10 w-64 h-64">
           <img src="https://minimals.cc/assets/illustrations/characters/character_1.png" alt="Illustration" class="w-full h-full object-contain" />
        </div>
        <!-- Decorative shape -->
        <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-l from-primary/10 to-transparent"></div>
      </div>

      <!-- Stats Grid -->
      <!-- <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div 
          v-for="stat in stats" 
          :key="stat.name"
          class="p-6 rounded-3xl transition-all shadow-card dark:shadow-card-dark bg-paper-light dark:bg-paper-dark border border-transparent hover:border-primary/20"
        >
          <div class="flex items-center justify-between mb-4">
             <div class="flex flex-col">
                <span class="text-sm font-bold opacity-70 mb-1">{{ stat.name }}</span>
                <span class="text-2xl font-bold">{{ stat.value }}</span>
             </div>
             <div :class="['p-3 rounded-2xl bg-gray-500/5', stat.color]">
                <component :is="stat.icon" :size="24" />
             </div>
          </div>
          <div class="flex items-center gap-1 text-sm">
            <TrendingUp :size="16" :class="stat.change.startsWith('+') ? 'text-green-500' : 'text-red-500'" />
            <span class="font-bold">{{ stat.change }}</span>
            <span class="opacity-50 font-medium">last 7 days</span>
          </div>
        </div>
      <!-- Manager Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <router-link to="/dashboard/page-manager" class="p-6 rounded-3xl transition-all shadow-card dark:shadow-card-dark bg-paper-light dark:bg-paper-dark border border-transparent hover:border-primary/20 block">
          <div class="flex items-center justify-between mb-4">
            <div class="text-lg font-bold">Page Manager</div>
            <div class="p-3 rounded-2xl bg-blue-500/5 text-blue-500">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </div>
          </div>
          <p class="text-sm opacity-70">Manage website pages</p>
        </router-link>

        <router-link to="/dashboard/block-manager" class="p-6 rounded-3xl transition-all shadow-card dark:shadow-card-dark bg-paper-light dark:bg-paper-dark border border-transparent hover:border-primary/20 block">
          <div class="flex items-center justify-between mb-4">
            <div class="text-lg font-bold">Block Manager</div>
            <div class="p-3 rounded-2xl bg-green-500/5 text-green-500">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
              </svg>
            </div>
          </div>
          <p class="text-sm opacity-70">Manage page blocks</p>
        </router-link>

        <router-link to="/dashboard/country-manager" class="p-6 rounded-3xl transition-all shadow-card dark:shadow-card-dark bg-paper-light dark:bg-paper-dark border border-transparent hover:border-primary/20 block">
          <div class="flex items-center justify-between mb-4">
            <div class="text-lg font-bold">Country Manager</div>
            <div class="p-3 rounded-2xl bg-purple-500/5 text-purple-500">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
          </div>
          <p class="text-sm opacity-70">Manage countries</p>
        </router-link>

        <router-link to="/dashboard/blog" class="p-6 rounded-3xl transition-all shadow-card dark:shadow-card-dark bg-paper-light dark:bg-paper-dark border border-transparent hover:border-primary/20 block">
          <div class="flex items-center justify-between mb-4">
            <div class="text-lg font-bold">Blog Manager</div>
            <div class="p-3 rounded-2xl bg-orange-500/5 text-orange-500">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
              </svg>
            </div>
          </div>
          <p class="text-sm opacity-70">Manage blog posts</p>
        </router-link>
      </div>
    </div>
  </MainLayout>
</template>
