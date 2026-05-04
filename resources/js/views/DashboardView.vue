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
      </div> -->

      <!-- Placeholder for more content -->
      <!-- <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
         <div class="h-80 rounded-3xl bg-paper-light dark:bg-paper-dark shadow-card dark:shadow-card-dark border border-transparent"></div>
         <div class="h-80 rounded-3xl bg-paper-light dark:bg-paper-dark shadow-card dark:shadow-card-dark border border-transparent"></div>
      </div> -->
    </div>
  </MainLayout>
</template>
