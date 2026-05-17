<template>
  <MainLayout>
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Appointments</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">View your scheduled sessions with consultants.</p>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-[#1C252E] p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600">
              <Calendar class="w-6 h-6" />
            </div>
            <div>
              <p class="text-sm text-gray-500">Total Sessions</p>
              <h3 class="text-xl font-bold">{{ appointments.length }}</h3>
            </div>
          </div>
        </div>
        <div class="bg-white dark:bg-[#1C252E] p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-50 dark:bg-green-900/20 flex items-center justify-center text-green-600">
              <CheckCircle class="w-6 h-6" />
            </div>
            <div>
              <p class="text-sm text-gray-500">Confirmed</p>
              <h3 class="text-xl font-bold">{{ appointments.filter(a => a.status === 'confirmed').length }}</h3>
            </div>
          </div>
        </div>
        <div class="bg-white dark:bg-[#1C252E] p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center text-orange-600">
              <Clock class="w-6 h-6" />
            </div>
            <div>
              <p class="text-sm text-gray-500">Upcoming</p>
              <h3 class="text-xl font-bold">{{ upcomingCount }}</h3>
            </div>
          </div>
        </div>
      </div>

      <!-- Appointments List -->
      <div class="bg-white dark:bg-[#1C252E] border border-gray-100 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-gray-50/50 dark:bg-[#151C24]/50 border-b border-gray-100 dark:border-gray-800">
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Consultant</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date & Time</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Link / Remarks</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr v-if="loading" v-for="i in 3" :key="i" class="animate-pulse">
                <td colspan="5" class="px-6 py-4"><div class="h-12 bg-gray-100 dark:bg-gray-800 rounded-xl w-full"></div></td>
              </tr>
              <tr v-else-if="appointments.length === 0">
                <td colspan="5" class="px-6 py-12 text-center text-gray-500 italic">No appointments found.</td>
              </tr>
              <tr v-for="appointment in appointments" :key="appointment.id" class="hover:bg-gray-50/80 dark:hover:bg-[#151C24] transition-colors group">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                      {{ appointment.consultant?.full_name?.charAt(0) }}
                    </div>
                    <div>
                      <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ appointment.consultant?.full_name }}</div>
                      <div class="text-xs text-gray-500">{{ appointment.consultant?.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(appointment.date) }}</div>
                  <div class="text-xs text-gray-500">{{ formatTime(appointment.start_time) }} - {{ formatTime(appointment.end_time) }}</div>
                </td>
                <td class="px-6 py-4">
                  <span :class="[
                    'px-2 py-1 text-[10px] font-bold uppercase rounded-md border',
                    appointment.meeting_type === 'online' 
                      ? 'bg-purple-50 text-purple-600 border-purple-100 dark:bg-purple-900/20 dark:text-purple-400 dark:border-purple-800'
                      : 'bg-indigo-50 text-indigo-600 border-indigo-100 dark:bg-indigo-900/20 dark:text-indigo-400 dark:border-indigo-800'
                  ]">
                    {{ appointment.meeting_type }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span :class="[
                    'px-2 py-1 text-[10px] font-bold uppercase rounded-md border',
                    getStatusClass(appointment.status)
                  ]">
                    {{ appointment.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div v-if="appointment.status === 'confirmed' && appointment.meeting_link" class="flex justify-end">
                    <a :href="appointment.meeting_link" target="_blank" class="flex items-center gap-1 text-xs text-primary font-semibold hover:underline">
                      <Video class="w-3 h-3" /> Join Meeting
                    </a>
                  </div>
                  <div v-else class="text-xs text-gray-400">
                    {{ appointment.remarks || 'No remarks' }}
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import { Plus, Calendar, CheckCircle, Clock, Video, ChevronRight } from 'lucide-vue-next';

const appointments = ref([]);
const loading = ref(true);

const upcomingCount = computed(() => {
  const today = new Date().toISOString().split('T')[0];
  return appointments.value.filter(a => a.date >= today && a.status === 'confirmed').length;
});

const fetchAppointments = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/auth/booking/student/my-appointments');
    appointments.value = res.data.data || [];
  } catch (error) {
    console.error('Failed to fetch appointments', error);
  } finally {
    loading.value = false;
  }
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
};

const formatTime = (timeString) => {
  return timeString.substring(0, 5);
};

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-50 text-yellow-600 border-yellow-100 dark:bg-yellow-900/20 dark:text-yellow-400 dark:border-yellow-800',
    confirmed: 'bg-green-50 text-green-600 border-green-100 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800',
    cancelled: 'bg-red-50 text-red-600 border-red-100 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800',
    completed: 'bg-gray-50 text-gray-600 border-gray-100 dark:bg-gray-900/20 dark:text-gray-400 dark:border-gray-800'
  };
  return classes[status] || classes.pending;
};

onMounted(fetchAppointments);
</script>
