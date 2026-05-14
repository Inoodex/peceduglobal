<template>
  <div class="fixed inset-0 z-[60] overflow-hidden pointer-events-none">
    <!-- Overlay (Without Blur) -->
    <transition name="fade">
      <div
        v-if="layout.isProfileOpen"
        class="absolute inset-0 bg-[#000000]/40 transition-opacity pointer-events-auto"
        @click="layout.toggleProfile"
      ></div>
    </transition>

    <!-- Drawer Content (With Slide Animation) -->
    <transition name="slide">
      <div
        v-if="layout.isProfileOpen"
        class="absolute top-0 right-0 h-full w-[280px] sm:w-[300px] shadow-2xl pointer-events-auto flex flex-col border-l border-white/5"
        :class="[
          layout.isDarkMode ? 'bg-[#1C252E] text-white' : 'bg-white text-[#212B36]',
        ]"
      >
        <!-- Close Button (Top Left) -->
        <div class="p-3">
          <button 
            @click="layout.toggleProfile"
            class="p-1.5 rounded-full hover:bg-gray-500/10 transition-colors"
          >
            <X :size="18" class="text-[#919EAB]" />
          </button>
        </div>

        <!-- User Header Section -->
        <div class="px-5 pb-5 flex flex-col items-center text-center">
          <!-- Avatar with Thin Teal Ring -->
          <div class="relative mb-4 flex items-center justify-center">
            <div class="w-[88px] h-[88px] rounded-full flex items-center justify-center border-[2px] border-[#00A76F] p-[4px]">
              <img 
                :src="auth.user?.profile_photo_url || `https://api.dicebear.com/7.x/avataaars/svg?seed=${auth.user?.full_name}`" 
                class="w-full h-full rounded-full bg-gray-200 object-cover"
                alt="Profile"
              />
            </div>
          </div>

          <h3 class="text-[15px] font-bold mb-0.5 leading-tight tracking-tight">{{ auth.user?.full_name || 'Jaydon Frankie' }}</h3>
          <p class="text-[13px] text-[#919EAB] font-normal leading-tight">{{ auth.user?.email || 'demo@minimals.cc' }}</p>

          <!-- Account Switcher -->
          <!-- <div class="flex items-center gap-2 mt-4">
            <div v-for="i in 3" :key="i" class="w-8 h-8 rounded-full border border-[#919EAB]/20 overflow-hidden cursor-pointer hover:opacity-80 transition-opacity">
              <img :src="`https://api.dicebear.com/7.x/avataaars/svg?seed=Avatar${i}`" alt="Switch" class="w-full h-full" />
            </div>
            <button class="w-8 h-8 rounded-full border border-dashed border-[#919EAB]/30 flex items-center justify-center text-[#919EAB] hover:border-primary hover:text-primary transition-all">
              <Plus :size="16" />
            </button>
          </div> -->
        </div>

        <!-- Dashed Divider -->
        <div class="border-t border-dashed border-[#919EAB]/20 w-full my-1"></div>

        <!-- Navigation Menu -->
        <div class="px-4 flex-1 overflow-y-auto py-1 scrollbar-hide">
          <div class="space-y-0.5">
            <router-link 
              v-for="item in menuItems" 
              :key="item.name"
              :to="item.path"
              class="flex items-center gap-4 px-3 py-2.5 rounded-lg hover:bg-[#919EAB]/8 transition-all group"
              @click="layout.toggleProfile"
            >
              <div class="w-6 flex justify-center">
                <component :is="item.icon" :size="22" class="text-[#919EAB] group-hover:text-white transition-colors" />
              </div>
              <span class="text-[14px] font-medium flex-1 text-[#919EAB] group-hover:text-white transition-colors">{{ item.name }}</span>
              <span v-if="item.badge" class="px-1.5 py-0.5 rounded bg-[#FF5630]/20 text-[#FF5630] text-[11px] font-bold">
                {{ item.badge }}
              </span>
            </router-link>
          </div>
        </div>

        <!-- Logout Button -->
        <div class="p-5">
          <button 
            @click="handleLogout"
            class="w-full py-2.5 bg-[#FF5630]/10 text-[#FFAC82] hover:bg-[#FF5630]/20 text-[15px] font-bold rounded-lg transition-all duration-300"
          >
            Logout
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { useLayoutStore } from '@/stores/layout';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import { 
  X, Home, User, LayoutGrid, CreditCard, Plus 
} from 'lucide-vue-next';

const layout = useLayoutStore();
const auth = useAuthStore();
const router = useRouter();

const menuItems = [
  { name: 'Home', icon: Home, path: '/dashboard' },
  { name: 'Profile', icon: User, path: '/dashboard/profile' },
  // { name: 'Projects', icon: LayoutGrid, path: '/dashboard/projects', badge: '3' },
  // { name: 'Subscription', icon: CreditCard, path: '/dashboard/subscription' },
];

const handleLogout = async () => {
  try {
    await auth.logout();
    layout.isProfileOpen = false;
    router.push('/login');
  } catch (error) {
    console.error('Logout failed', error);
  }
};
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

/* Slide Animation */
.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s ease-in-out;
}

.slide-enter-from,
.slide-leave-to {
  transform: translateX(100%);
}

/* Fade Animation for Overlay */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease-in-out;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
