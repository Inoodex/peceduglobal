<template>
  <MainLayout>
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Student Appointments</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">View and manage your scheduled sessions with students.</p>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-[#1C252E] p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500">
              <Calendar :size="24" />
            </div>
            <div>
              <div class="text-2xl font-bold">{{ appointments.length }}</div>
              <div class="text-xs text-gray-500 uppercase font-bold tracking-wider">Total Sessions</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white dark:bg-[#1C252E] p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-500/10 flex items-center justify-center text-green-500">
              <Video :size="24" />
            </div>
            <div>
              <div class="text-2xl font-bold">{{ appointments.filter(a => a.meeting_type === 'online').length }}</div>
              <div class="text-xs text-gray-500 uppercase font-bold tracking-wider">Online Meets</div>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-[#1C252E] p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-500">
              <Users :size="24" />
            </div>
            <div>
              <div class="text-2xl font-bold">{{ appointments.filter(a => a.meeting_type === 'physical').length }}</div>
              <div class="text-xs text-gray-500 uppercase font-bold tracking-wider">Physical Meets</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Appointments List -->
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-800">
          <h3 class="font-bold text-lg">Upcoming Sessions</h3>
        </div>

        <div v-if="loading" class="p-12 space-y-4">
          <div v-for="i in 3" :key="i" class="h-20 bg-gray-50 dark:bg-[#151C24] rounded-2xl animate-pulse"></div>
        </div>

        <div v-else-if="appointments.length > 0" class="divide-y divide-gray-100 dark:divide-gray-800">
          <div 
            v-for="app in appointments" 
            :key="app.id"
            class="p-6 hover:bg-gray-50 dark:hover:bg-[#151C24] transition-colors flex flex-col md:flex-row md:items-center justify-between gap-6"
          >
            <!-- Student Info -->
            <div class="flex items-center gap-4">
              <img 
                :src="app.student?.profile_photo_url || `https://api.dicebear.com/7.x/avataaars/svg?seed=${app.student?.full_name}`" 
                class="w-12 h-12 rounded-full bg-gray-100 object-cover"
                alt="Student"
              />
              <div>
                <div class="font-bold text-gray-900 dark:text-white">{{ app.student?.full_name }}</div>
                <div class="text-sm text-gray-500">{{ app.student?.email }}</div>
              </div>
            </div>

            <!-- Time & Date -->
            <div class="flex flex-col gap-1">
              <div class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-200">
                <Calendar :size="16" class="text-primary" />
                {{ formatDate(app.date) }}
              </div>
              <div class="flex items-center gap-2 text-xs text-gray-500">
                <Clock :size="14" />
                {{ formatTime(app.start_time) }} - {{ formatTime(app.end_time) }}
              </div>
            </div>

            <!-- Type & Status -->
            <div class="flex items-center gap-3">
              <div :class="[
                'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border',
                app.meeting_type === 'online' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-purple-50 text-purple-600 border-purple-100'
              ]">
                {{ app.meeting_type }}
              </div>
              <div class="px-3 py-1 rounded-full bg-green-50 text-green-600 border border-green-100 text-[10px] font-bold uppercase tracking-wider">
                {{ app.status }}
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2">
              <button 
                v-if="app.meeting_type === 'online'"
                class="p-2.5 rounded-xl bg-primary text-white hover:bg-primary/90 transition-all flex items-center gap-2 text-sm font-bold"
              >
                <Video :size="18" /> Start Meeting
              </button>
              <button class="p-2.5 rounded-xl bg-gray-500/10 text-gray-500 hover:bg-gray-500/20 transition-all">
                <MoreVertical :size="18" />
              </button>
            </div>
          </div>
        </div>

        <div v-else class="p-20 text-center">
          <div class="w-16 h-16 rounded-full bg-gray-500/10 flex items-center justify-center text-gray-400 mx-auto mb-4">
            <Calendar :size="32" />
          </div>
          <h3 class="text-lg font-bold text-gray-500">No appointments found</h3>
          <p class="text-sm text-gray-400">When students book sessions with you, they will appear here.</p>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import { Calendar, Clock, Video, Users, MoreVertical } from 'lucide-vue-next';

const appointments = ref([]);
const loading = ref(true);

const fetchAppointments = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/auth/booking/consultant/student-appointments');
    appointments.value = res.data.data || [];
  } catch (error) {
    console.error('Failed to load appointments', error);
  } finally {
    loading.value = false;
  }
};

const formatTime = (time) => time.substring(0, 5);
const formatDate = (date) => new Date(date).toLocaleDateString('en-US', { 
  weekday: 'short', 
  month: 'short', 
  day: 'numeric', 
  year: 'numeric' 
});

onMounted(() => {
  fetchAppointments();
});
</script>
