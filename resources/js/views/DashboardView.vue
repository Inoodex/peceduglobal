<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import { 
  TrendingUp, 
  Users, 
  GraduationCap, 
  Clock, 
  FileText, 
  User, 
  ChevronRight,
  ClipboardList
} from 'lucide-vue-next';

const authStore = useAuthStore();

const isStudent = computed(() => authStore.user?.role === 'student');

const hasPermission = (permission) => {
  if (!permission) return true;
  if (authStore.user?.role === 'admin') return true;
  const userPermissions = authStore.user?.permissions?.map((p) => p.slug) || [];
  return userPermissions.includes(permission);
};

const welcomeMessage = computed(() => {
  if (authStore.user?.role === 'student') {
    return 'Your admission pipeline is active! Check your academic completeness score and advisory timeline below.';
  } else if (authStore.user?.role === 'admin') {
    return 'Manage university partners, student applications, pages, countries, and consultant details seamlessly from your control center.';
  } else if (authStore.user?.role === 'consultant') {
    return 'Check your upcoming student counseling appointments, adjust your availability schedule, and monitor your students\' documents.';
  }
  return 'Access your platform overview, manage settings, and view key statistics.';
});

const welcomeActionLink = computed(() => {
  if (authStore.user?.role === 'student') return '/dashboard/profile';
  if (authStore.user?.role === 'admin') return '/dashboard/user-management';
  if (authStore.user?.role === 'consultant') return '/dashboard/consultant/appointments';
  return '#';
});

const welcomeActionText = computed(() => {
  if (authStore.user?.role === 'student') return 'Manage Profile';
  if (authStore.user?.role === 'admin') return 'Manage Users';
  if (authStore.user?.role === 'consultant') return 'View Appointments';
  return 'Go now';
});

const totalApplicationsCount = ref(0);
const totalAppointmentsCount = ref(0);
const latestApplication = ref(null);
const studentProfile = ref(null);
const appointmentsList = ref([]);

const completenessPercentage = computed(() => {
  if (!studentProfile.value) return 25; // Base registration completeness
  let score = 25;
  if (studentProfile.value.phone) score += 25;
  if (studentProfile.value.address) score += 25;
  if (studentProfile.value.cgpa || studentProfile.value.ielts_score) score += 25;
  return score;
});

const checklistItems = computed(() => {
  const profile = studentProfile.value;
  return [
    { title: 'Personal Information', description: 'Basic details and contact information.', completed: !!profile?.phone },
    { title: 'Contact Address', description: 'Permanent and present residential details.', completed: !!profile?.address },
    { title: 'Academic Metrics', description: 'CGPA or educational credentials added.', completed: !!profile?.cgpa },
    { title: 'Language Competency', description: 'IELTS/TOEFL scores uploaded.', completed: !!profile?.ielts_score },
  ];
});

const statusOrder = [
  'document_submitted',
  'application',
  'offer_letter',
  'deposit_received',
  'enrollment_confirmed',
  'visa_processing',
  'enrolled',
];

const statusIndex = computed(() => {
  const s = latestApplication.value?.status || 'document_submitted';
  const idx = statusOrder.indexOf(s);
  return idx >= 0 ? idx : 0;
});

const funnelSteps = computed(() => {
  const current = statusIndex.value;
  
  const steps = [
    { label: 'Submitted',    icon: FileText,      active: current >= 0 },
    { label: 'Applied',      icon: GraduationCap, active: current >= 1 },
    { label: 'Offer Letter', icon: TrendingUp,    active: current >= 2 },
    { label: 'Deposited',    icon: Users,         active: current >= 3 },
    { label: 'Enrolled',     icon: User,          active: current >= 4 },
    { label: 'Visa',         icon: Clock,         active: current >= 5 },
    { label: 'Completed',    icon: ClipboardList, active: current >= 6 },
  ];

  return steps;
});

const funnelProgressWidth = computed(() => {
  const activeCount = funnelSteps.value.filter(s => s.active).length;
  if (activeCount <= 1) return '0%';
  return `${((activeCount - 1) / (funnelSteps.value.length - 1)) * 100}%`;
});

const fetchStudentStats = async () => {
  if (!isStudent.value) return;
  try {
    const appRes = await axios.get('/auth/student/applications');
    const appsRaw = appRes.data.data;
    const appsArray = appsRaw?.data || appsRaw || [];
    totalApplicationsCount.value = appsArray.length;
    if (appsArray.length > 0) {
      latestApplication.value = appsArray[0];
    }
  } catch (e) {
    console.error('Failed to load applications count', e);
  }

  try {
    const apptRes = await axios.get('/auth/booking/student/my-appointments');
    appointmentsList.value = apptRes.data.data || [];
    totalAppointmentsCount.value = appointmentsList.value.length;
  } catch (e) {
    console.error('Failed to load appointments count', e);
  }

  try {
    const profileRes = await axios.get('/auth/student/profile');
    studentProfile.value = profileRes.data.data;
  } catch (e) {
    console.error('Failed to load profile details', e);
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
};

const formatTime = (timeString) => {
  if (!timeString) return 'Session time';
  try {
    const [hours, minutes] = timeString.split(':');
    const h = parseInt(hours);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const displayHour = h % 12 || 12;
    return `${displayHour}:${minutes} ${ampm}`;
  } catch (e) {
    return timeString;
  }
};

onMounted(() => {
  fetchStudentStats();
});
</script>

<template>
  <MainLayout>
    <div class="space-y-8">
      <!-- Welcome Banner with animated floating glassmorphism shapes -->
      <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#00383C] to-[#005c61] p-10 flex items-center justify-between text-white shadow-xl min-h-[220px]">
        <!-- Decorative glowing shapes in the background with animations -->
        <div class="absolute -right-10 -top-10 w-44 h-44 bg-teal-500/10 rounded-full blur-3xl animate-float-1"></div>
        <div class="absolute right-1/4 bottom-0 w-36 h-36 bg-emerald-400/15 rounded-full blur-2xl animate-float-2"></div>
        <div class="absolute right-12 top-1/2 -translate-y-1/2 hidden md:flex w-44 h-44 bg-white/5 border border-white/10 rounded-full backdrop-blur-md animate-float-1 shadow-2xl items-center justify-center overflow-hidden">
          <GraduationCap class="w-16 h-16 text-teal-300 animate-pulse-glow" />
        </div>

        <div class="z-10 max-w-xl">
          <h1 class="text-3xl font-extrabold mb-3 tracking-tight">Welcome back 👋 <br class="md:hidden" /> <span class="text-teal-300">{{ authStore.user?.full_name || authStore.user?.name || 'Jaydon Frankie' }}</span></h1>
          <p class="text-white/80 mb-6 text-sm md:text-base leading-relaxed max-w-md">
            {{ welcomeMessage }}
          </p>
          <router-link 
            :to="welcomeActionLink"
            class="inline-flex items-center gap-2 bg-primary hover:bg-primary/95 text-white px-5 py-2.5 rounded-xl font-bold transition-all transform hover:-translate-y-0.5 shadow-lg shadow-primary/20 text-sm"
          >
            {{ welcomeActionText }} <ChevronRight class="w-4 h-4" />
          </router-link>
        </div>
      </div>

      <!-- Student specific dashboard sections -->
      <div v-if="isStudent" class="space-y-8">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Profile Completeness Card -->
          <div class="p-6 rounded-3xl transition-all shadow-card dark:shadow-card-dark bg-paper-light dark:bg-paper-dark border border-transparent hover:border-primary/20 flex flex-col justify-between hover:shadow-lg group">
            <div class="flex items-center justify-between mb-4">
              <div class="flex flex-col">
                <span class="text-xs font-bold opacity-60 uppercase tracking-wider mb-1">Completeness</span>
                <span class="text-3xl font-extrabold text-emerald-500 group-hover:scale-105 transition-transform duration-300">{{ completenessPercentage }}%</span>
              </div>
              <div class="p-3.5 rounded-2xl bg-emerald-500/5 text-emerald-500 transition-colors group-hover:bg-emerald-500/10">
                <User :size="24" />
              </div>
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Interactive tracker of your checklist.</div>
          </div>

          <!-- Applications Card -->
          <div class="p-6 rounded-3xl transition-all shadow-card dark:shadow-card-dark bg-paper-light dark:bg-paper-dark border border-transparent hover:border-primary/20 flex flex-col justify-between hover:shadow-lg group">
            <div class="flex items-center justify-between mb-4">
              <div class="flex flex-col">
                <span class="text-xs font-bold opacity-60 uppercase tracking-wider mb-1">Applications</span>
                <span class="text-3xl font-extrabold text-blue-500 group-hover:scale-105 transition-transform duration-300">{{ totalApplicationsCount }}</span>
              </div>
              <div class="p-3.5 rounded-2xl bg-blue-500/5 text-blue-500 transition-colors group-hover:bg-blue-500/10">
                <FileText :size="24" />
              </div>
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Total university submissions loaded.</div>
          </div>

          <!-- Appointments Card -->
          <div class="p-6 rounded-3xl transition-all shadow-card dark:shadow-card-dark bg-paper-light dark:bg-paper-dark border border-transparent hover:border-primary/20 flex flex-col justify-between hover:shadow-lg group">
            <div class="flex items-center justify-between mb-4">
              <div class="flex flex-col">
                <span class="text-xs font-bold opacity-60 uppercase tracking-wider mb-1">Advisory Meetings</span>
                <span class="text-3xl font-extrabold text-purple-500 group-hover:scale-105 transition-transform duration-300">{{ totalAppointmentsCount }}</span>
              </div>
              <div class="p-3.5 rounded-2xl bg-purple-500/5 text-purple-500 transition-colors group-hover:bg-purple-500/10">
                <Clock :size="24" />
              </div>
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Registered counseling sessions.</div>
          </div>
        </div>

        <!-- Funnel Card -->
        <div class="bg-white dark:bg-[#1C252E] p-8 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm relative overflow-hidden transition-all duration-300 hover:shadow-md">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
              <h3 class="text-lg font-bold text-gray-900 dark:text-white">Active Application Tracker</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" v-if="latestApplication">
                Tracking application <span class="font-semibold text-primary">#{{ latestApplication.application_number }}</span> for <span class="font-semibold text-gray-900 dark:text-white">{{ latestApplication.university?.name }}</span>
              </p>
              <p class="text-sm text-gray-500 dark:text-gray-400" v-else>
                No active applications currently in progress.
              </p>
            </div>
            <router-link 
              v-if="latestApplication" 
              to="/dashboard/applications" 
              class="text-xs font-bold text-primary hover:underline flex items-center gap-1 self-start md:self-auto"
            >
              All Applications <ChevronRight class="w-4 h-4" />
            </router-link>
          </div>

          <div v-if="latestApplication" class="relative mt-10 mb-6 px-4">
            <!-- Progress Line base -->
            <div class="absolute top-1/2 left-0 right-0 h-1 bg-gray-100 dark:bg-gray-800/80 transform -translate-y-1/2 z-0 rounded-full"></div>
            <!-- Glowing Filled Line -->
            <div 
              class="absolute top-1/2 left-0 h-1 bg-gradient-to-r from-primary to-emerald-500 transform -translate-y-1/2 z-0 rounded-full transition-all duration-700 shadow-lg shadow-primary/20"
              :style="{ width: funnelProgressWidth }"
            ></div>

            <div class="relative z-10 flex justify-between items-center">
              <div 
                v-for="(step, index) in funnelSteps" 
                :key="index"
                class="flex flex-col items-center group/step"
              >
                <!-- Step Ring -->
                <div 
                  :class="[
                    'w-11 h-11 rounded-full flex items-center justify-center border-2 transition-all duration-500',
                    step.active 
                      ? 'bg-primary text-white border-primary shadow-lg shadow-primary/30 scale-110 group-hover/step:scale-115' 
                      : 'bg-white dark:bg-[#1C252E] text-gray-400 dark:text-gray-500 border-gray-200 dark:border-gray-800'
                  ]"
                >
                  <component :is="step.icon" class="w-5 h-5" />
                </div>
                <!-- Step Label -->
                <span 
                  :class="[
                    'text-[11px] font-bold mt-4 text-center transition-colors duration-300 hidden sm:block',
                    step.active ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500'
                  ]"
                >
                  {{ step.label }}
                </span>
              </div>
            </div>
          </div>
          <div v-else class="py-10 text-center text-gray-500 dark:text-gray-400 flex flex-col items-center justify-center gap-2">
            <FileText class="w-10 h-10 text-gray-300" />
            <p class="text-sm">You haven't submitted any university applications yet.</p>
          </div>
        </div>

        <!-- Analytical Journey Checklists & Chronicle timeline -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Journey Checklist Widget -->
          <div class="bg-white dark:bg-[#1C252E] p-8 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col justify-between hover:shadow-md transition-all duration-300">
            <div>
              <div class="flex items-center justify-between mb-6">
                <div>
                  <h3 class="text-lg font-bold text-gray-900 dark:text-white">Admission Checklist</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Mandatory tasks to unlock offer letters quickly.</p>
                </div>
                <div class="text-right">
                  <span class="text-2xl font-black text-primary">{{ completenessPercentage }}%</span>
                  <p class="text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-wider font-semibold">Done</p>
                </div>
              </div>

              <!-- Checklist items -->
              <div class="space-y-4">
                <div 
                  v-for="(item, idx) in checklistItems" 
                  :key="idx"
                  class="flex items-center gap-3 p-3 rounded-2xl bg-gray-50 dark:bg-[#151C24]/40 transition-all hover:bg-gray-100/50 dark:hover:bg-[#151C24]/80 group/item"
                >
                  <div 
                    :class="[
                      'w-6 h-6 rounded-full flex items-center justify-center border transition-all duration-300',
                      item.completed 
                        ? 'bg-emerald-500 border-emerald-500 text-white shadow-md shadow-emerald-500/20' 
                        : 'border-gray-300 dark:border-gray-700 bg-white dark:bg-transparent group-hover/item:border-primary/50'
                    ]"
                  >
                    <svg v-if="item.completed" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                  </div>
                  <div class="flex-1">
                    <p :class="['text-xs md:text-sm font-bold', item.completed ? 'text-gray-400 dark:text-gray-500 line-through opacity-70' : 'text-gray-900 dark:text-white']">
                      {{ item.title }}
                    </p>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">{{ item.description }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Profile completion progress bar -->
            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800">
              <div class="h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                <div 
                  class="h-full bg-gradient-to-r from-primary to-emerald-500 rounded-full transition-all duration-700"
                  :style="{ width: `${completenessPercentage}%` }"
                ></div>
              </div>
            </div>
          </div>

          <!-- Chronological Advisory Timeline Widget -->
          <div class="bg-white dark:bg-[#1C252E] p-8 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between mb-6">
                <div>
                  <h3 class="text-lg font-bold text-gray-900 dark:text-white">Advisory Timeline</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Real-time chronologies of counseling sessions.</p>
                </div>
                <router-link to="/dashboard/student/appointments" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                  View All <ChevronRight class="w-4 h-4" />
                </router-link>
              </div>

              <!-- Timeline -->
              <div v-if="appointmentsList.length > 0" class="relative pl-6 border-l-2 border-gray-100 dark:border-gray-800 space-y-6">
                <div 
                  v-for="(appt, idx) in appointmentsList.slice(0, 3)" 
                  :key="appt.id"
                  class="relative transition-all duration-300 hover:translate-x-1"
                >
                  <!-- Timeline indicator node -->
                  <div class="absolute -left-[32px] top-1.5 w-4 h-4 rounded-full border-4 border-white dark:border-[#1C252E] bg-primary ring-4 ring-primary/10"></div>
                  
                  <div class="flex items-start justify-between gap-4">
                    <div>
                      <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ appt.schedule?.consultant?.full_name || 'Expert Counselor' }}</h4>
                      <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">
                        {{ formatDate(appt.schedule?.slot_date || appt.date) }} • {{ formatTime(appt.schedule?.start_time) }}
                      </p>
                      <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-2 italic bg-gray-50 dark:bg-[#151C24]/30 px-3 py-2 rounded-xl border border-gray-100/50 dark:border-gray-800/10 max-w-sm line-clamp-2">
                        "{{ appt.student_notes || appt.remarks || 'No remarks provided.' }}"
                      </p>
                    </div>
                    <span 
                      :class="[
                        'px-2 py-0.5 rounded-lg text-[9px] font-bold uppercase tracking-wider',
                        appt.status === 'confirmed' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/10 dark:text-emerald-500' : 'bg-gray-50 text-gray-700'
                      ]"
                    >
                      {{ appt.status }}
                    </span>
                  </div>
                </div>
              </div>
              <div v-else class="py-12 text-center text-gray-500 dark:text-gray-400 flex flex-col items-center justify-center gap-2">
                <Clock class="w-10 h-10 text-gray-300" />
                <p class="text-sm">No consultation sessions booked yet.</p>
              </div>
            </div>

            <!-- Dashboard redirection links -->
            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 text-center">
              <span class="text-xs text-gray-400 dark:text-gray-500">Need immediate help? Reach out to support advisors.</span>
            </div>
          </div>
        </div>

        <!-- Student Quick Navigation links -->
        <div>
          <h2 class="text-xl font-bold mb-6 text-gray-900 dark:text-white">Quick Shortcuts</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Profile Link -->
            <router-link to="/dashboard/profile" class="group p-6 rounded-3xl transition-all shadow-card dark:shadow-card-dark bg-paper-light dark:bg-paper-dark border border-transparent hover:border-primary/20 flex flex-col justify-between h-44 hover:shadow-lg">
              <div>
                <div class="flex items-center justify-between mb-2">
                  <div class="p-3 rounded-2xl bg-emerald-500/5 text-emerald-500 group-hover:bg-emerald-500/10 transition-colors">
                    <User :size="24" />
                  </div>
                  <ChevronRight class="w-5 h-5 text-gray-400 group-hover:text-primary transition-colors transform group-hover:translate-x-1" />
                </div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1 group-hover:text-primary transition-colors">My Profile</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 leading-relaxed">Update your educational history, personal info, and manage required documents.</p>
              </div>
            </router-link>

            <!-- Applications Link -->
            <router-link to="/dashboard/applications" class="group p-6 rounded-3xl transition-all shadow-card dark:shadow-card-dark bg-paper-light dark:bg-paper-dark border border-transparent hover:border-primary/20 flex flex-col justify-between h-44 hover:shadow-lg">
              <div>
                <div class="flex items-center justify-between mb-2">
                  <div class="p-3 rounded-2xl bg-blue-500/5 text-blue-500 group-hover:bg-blue-500/10 transition-colors">
                    <FileText :size="24" />
                  </div>
                  <ChevronRight class="w-5 h-5 text-gray-400 group-hover:text-primary transition-colors transform group-hover:translate-x-1" />
                </div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1 group-hover:text-primary transition-colors">My Applications</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 leading-relaxed">Monitor your status, upload certificates, and follow your application progress.</p>
              </div>
            </router-link>

            <!-- Appointments Link -->
            <router-link to="/dashboard/student/appointments" class="group p-6 rounded-3xl transition-all shadow-card dark:shadow-card-dark bg-paper-light dark:bg-paper-dark border border-transparent hover:border-primary/20 flex flex-col justify-between h-44 hover:shadow-lg">
              <div>
                <div class="flex items-center justify-between mb-2">
                  <div class="p-3 rounded-2xl bg-purple-500/5 text-purple-500 group-hover:bg-purple-500/10 transition-colors">
                    <Clock :size="24" />
                  </div>
                  <ChevronRight class="w-5 h-5 text-gray-400 group-hover:text-primary transition-colors transform group-hover:translate-x-1" />
                </div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1 group-hover:text-primary transition-colors">My Appointments</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 leading-relaxed">Check your upcoming advisory meetings and view schedule timestamps.</p>
              </div>
            </router-link>
          </div>
        </div>
      </div>

      <!-- Admin & Consultant Management Cards -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <router-link v-if="hasPermission('manage_pages')" to="/dashboard/page-manager" class="p-6 rounded-3xl transition-all shadow-card dark:shadow-card-dark bg-paper-light dark:bg-paper-dark border border-transparent hover:border-primary/20 block">
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

        <router-link v-if="hasPermission('manage_pages')" to="/dashboard/block-manager" class="p-6 rounded-3xl transition-all shadow-card dark:shadow-card-dark bg-paper-light dark:bg-paper-dark border border-transparent hover:border-primary/20 block">
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

        <router-link v-if="hasPermission('manage_countries')" to="/dashboard/country-manager" class="p-6 rounded-3xl transition-all shadow-card dark:shadow-card-dark bg-paper-light dark:bg-paper-dark border border-transparent hover:border-primary/20 block">
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

        <router-link v-if="hasPermission('manage_blogs')" to="/blog-post" class="p-6 rounded-3xl transition-all shadow-card dark:shadow-card-dark bg-paper-light dark:bg-paper-dark border border-transparent hover:border-primary/20 block">
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

        <router-link v-if="hasPermission('manage_education')" to="/dashboard/university-manager" class="p-6 rounded-3xl transition-all shadow-card dark:shadow-card-dark bg-paper-light dark:bg-paper-dark border border-transparent hover:border-primary/20 block">
          <div class="flex items-center justify-between mb-4">
            <div class="text-lg font-bold">University Manager</div>
            <div class="p-3 rounded-2xl bg-pink-500/5 text-pink-500">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
              </svg>
            </div>
          </div>
          <p class="text-sm opacity-70">Manage universities & partners</p>
        </router-link>
      </div>
    </div>
  </MainLayout>
</template>

<style scoped>
@keyframes float-1 {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-12px) rotate(3deg); }
}
@keyframes float-2 {
  0%, 100% { transform: translateY(0px) rotate(0deg) scale(1); }
  50% { transform: translateY(15px) rotate(-6deg) scale(1.05); }
}
@keyframes pulse-glow {
  0%, 100% { opacity: 0.7; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.03); }
}
.animate-float-1 {
  animation: float-1 6s ease-in-out infinite;
}
.animate-float-2 {
  animation: float-2 8s ease-in-out infinite;
}
.animate-pulse-glow {
  animation: pulse-glow 2.5s ease-in-out infinite;
}
</style>
