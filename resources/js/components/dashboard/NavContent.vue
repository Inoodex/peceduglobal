<template>
  <div class="flex flex-col h-full">
    <!-- Logo Section -->
    <div
      class="py-5 flex items-center transition-all duration-300"
      :class="isCollapsed ? 'justify-center' : 'px-6 justify-between'"
    >
      <div class="flex items-center gap-3">
        <a class="flex items-center" aria-label="Logo" href="/">
          <svg :width="isCollapsed ? '28' : '40'" :height="isCollapsed ? '28' : '40'" viewBox="0 0 512 512" fill="none" xmlns="http://www.w3.org/2000/svg" class="transition-all duration-300">
            <defs>
              <linearGradient id="_r_4_-1" x1="152" y1="167.79" x2="65.523" y2="259.624" gradientUnits="userSpaceOnUse"><stop stop-color="var(--palette-primary-dark)"></stop><stop offset="1" stop-color="var(--palette-primary-main)"></stop></linearGradient>
              <linearGradient id="_r_4_-2" x1="86" y1="128" x2="86" y2="384" gradientUnits="userSpaceOnUse"><stop stop-color="var(--palette-primary-light)"></stop><stop offset="1" stop-color="var(--palette-primary-main)"></stop></linearGradient>
              <linearGradient id="_r_4_-3" x1="402" y1="288" x2="402" y2="384" gradientUnits="userSpaceOnUse"><stop stop-color="var(--palette-primary-light)"></stop><stop offset="1" stop-color="var(--palette-primary-main)"></stop></linearGradient>
            </defs>
            <path fill="url(#_r_4_-1)" d="M86.352 246.358C137.511 214.183 161.836 245.017 183.168 285.573C165.515 317.716 153.837 337.331 148.132 344.418C137.373 357.788 125.636 367.911 111.202 373.752C80.856 388.014 43.132 388.681 14 371.048L86.352 246.358Z"></path>
            <path fill="url(#_r_4_-2)" fill-rule="evenodd" clip-rule="evenodd" d="M444.31 229.726C398.04 148.77 350.21 72.498 295.267 184.382C287.751 198.766 282.272 226.719 270 226.719V226.577C257.728 226.577 252.251 198.624 244.735 184.24C189.79 72.356 141.96 148.628 95.689 229.584C92.207 235.69 88.862 241.516 86 246.58C192.038 179.453 183.11 382.247 270 383.858V384C356.891 382.389 347.962 179.595 454 246.72C451.139 241.658 447.794 235.832 444.31 229.726Z"></path>
            <path fill="url(#_r_4_-3)" fill-rule="evenodd" clip-rule="evenodd" d="M450 384C476.509 384 498 362.509 498 336C498 309.491 476.509 288 450 288C423.491 288 402 309.491 402 336C402 362.509 423.491 384 450 384Z"></path>
          </svg>
        </a>
        <span v-if="!isCollapsed" class="font-bold text-lg tracking-tight" :class="layout.isDarkMode ? 'text-white' : 'text-[#212B36]'">
          <!-- Minimals -->
        </span>
      </div>
    </div>

    <!-- Navigation -->
    <div class="flex-1 px-2 overflow-y-auto hide-scrollbar">
      <div v-for="(section, idx) in navigation" :key="idx" class="mb-2">
        <h3
          v-if="!isCollapsed"
          class="px-4 mb-2 text-[10px] font-bold uppercase tracking-wider text-gray-500 opacity-60"
        >
          {{ section.title }}
        </h3>

        <div class="space-y-1 relative">
          <NavItem
            v-for="item in section.items"
            :key="item.name"
            :item="item"
            :is-collapsed="isCollapsed"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, provide } from 'vue';
import { useLayoutStore } from '@/stores/layout';
import { useAuthStore } from '@/stores/auth';
import NavItem from './NavItem.vue';
import {
  LayoutDashboard,
  Users,
  BookOpen,
  School,
  Globe,
  GraduationCap,
  FileText,
  Calendar,
  Clock
} from 'lucide-vue-next';

defineProps({
  isCollapsed: {
    type: Boolean,
    default: false
  }
});

const layout = useLayoutStore();

const auth = useAuthStore();

// Navigation with role-based access
const navigationData = [
  {
    title: 'Overview',
    roles: ['admin', 'counselor', 'student'],
    items: [
      { name: 'Dashboard', icon: LayoutDashboard, path: '/dashboard' },
    ]
  },
  {
    title: 'Content',
    roles: ['admin', 'counselor'],
    items: [
      {
        name: 'Page Manager',
        icon: BookOpen,
        permission: 'manage_pages',
        children: [
          { name: 'Pages', path: '/dashboard/page-manager' },
          { name: 'Blocks', path: '/dashboard/block-manager' },
          { name: 'Elements', path: '/dashboard/element-manager' }
        ]
      },
      {
        name: 'Country Manager',
        icon: Globe,
        permission: 'manage_countries',
        children: [
          { name: 'Countries', path: '/dashboard/country-manager' }
        ]
      },
      {
        name: 'Education',
        icon: School,
        permission: 'manage_education',
        children: [
          { name: 'Universities', path: '/dashboard/university-manager' },
          { name: 'Courses', path: '/dashboard/course-manager' },
          { name: 'Course Intakes', path: '/dashboard/course-intakes' },
          { name: 'Course Levels', path: '/dashboard/course-level-manager' },
        ]
      },
    ]
  },
  {
    title: 'Consultancy',
    roles: ['admin','counselor'],
    items: [
      
      {
        name: 'Students',
        icon: Users,
        children: [
          { name: 'Register Student', path: '/dashboard/students/register' },
          { name: 'Student List', path: '/dashboard/students' },
          { name: 'Add Student', path: '/dashboard/students/create' }
        ]
      },
      {
        name: 'Applications',
        icon: FileText,
        children: [
          { name: 'Application List', path: '/dashboard/applications' },
          { name: 'Add Application', path: '/dashboard/applications/create' }
        ]
      }
    ]
  },
  {
    title: 'Booking Management',
    roles: ['admin'],
    items: [
      { name: 'Schedule Templates', path: '/dashboard/booking/templates', icon: Calendar },
      { name: 'Generate Slots', path: '/dashboard/booking/generate', icon: Clock },
    ]
  },
  {
    title: 'Consultant Services',
    roles: ['counselor'],
    items: [
      { name: 'Manage Availability', path: '/dashboard/consultant/availability', icon: Clock },
      { name: 'Student Appointments', path: '/dashboard/consultant/appointments', icon: Calendar },
    ]
  },
  {
    title: 'Student Portal',
    roles: ['student'],
    items: [
      {
        name: 'My Profile',
        icon: GraduationCap,
        path: '/dashboard/profile'
      },
      {
        name: 'My Applications',
        icon: FileText,
        path: '/dashboard/applications'
      },
      {
        name: 'My Appointments',
        icon: Clock,
        path: '/dashboard/student/appointments'
      }
    ]
  },
  {
    title: 'Management',
    roles: ['admin'],
    items: [
      {
        name: 'User Management',
        icon: Users,
        path: '/dashboard/user-management'
      },
    ]
  },
  {
    title: 'Blog',
    roles: ['admin', 'counselor'],
    items: [
      {
        name: 'Blog',
        icon: BookOpen,
        permission: 'manage_blogs',
        children: [
          { name: 'Posts', path: '/blog-post' },
          { name: 'Create Post', path: '/blog-post-create' },
          { name: 'Categories', path: '/blog-category' }
        ]
      },
    ]
  }
];

const hasUserPermission = (permission) => {
  if (!permission) return true;
  if (auth.user?.role === 'admin') return true;
  const userPermissions = auth.user?.permissions?.map((perm) => perm.slug) || [];
  return userPermissions.includes(permission);
};

const canAccessItem = (item, userRole) => {
  if (item.roles && !item.roles.includes(userRole)) {
    return false;
  }
  return hasUserPermission(item.permission);
};

const navigation = computed(() => {
  const userRole = auth.user?.role || 'student';
  return navigationData
    .filter(section => !section.roles || section.roles.includes(userRole))
    .map(section => ({
      ...section,
      items: section.items.filter(item => canAccessItem(item, userRole))
    }))
    .filter(section => section.items.length > 0);
});

// Collect all available paths in the navigation to help NavItem decide on "longest match"
const allNavPaths = computed(() => {
  const paths = [];
  const extractPaths = (items) => {
    items.forEach(item => {
      if (item.path) paths.push(item.path);
      if (item.children) extractPaths(item.children);
    });
  };
  navigation.value.forEach(section => extractPaths(section.items));
  return paths;
});

provide('allNavPaths', allNavPaths);
</script>

<style scoped>
.hide-scrollbar {
  -ms-overflow-style: none;  /* IE and Edge */
  scrollbar-width: none;  /* Firefox */
}
.hide-scrollbar::-webkit-scrollbar {
  display: none; /* Chrome, Safari and Opera */
}
</style>
