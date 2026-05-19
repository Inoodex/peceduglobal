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
      <DataTable 
        :columns="columns" 
        :data="appointments" 
        :loading="loading"
        :pagination="pagination"
        @page-change="fetchAppointments"
        @per-page-change="handlePerPageChange"
      >
        <!-- Student -->
        <template #cell(student)="{ item: app }">
          <div class="flex items-center gap-3">
            <img 
              :src="app.student?.profile_photo_url || `https://api.dicebear.com/7.x/avataaars/svg?seed=${app.student?.full_name}`" 
              class="w-10 h-10 rounded-full bg-gray-100 object-cover"
              alt="Student"
            />
            <div>
              <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ app.student?.full_name }}</div>
              <div class="text-xs text-gray-500">{{ app.student?.email }}</div>
            </div>
          </div>
        </template>
        <!-- Date & Time -->
        <template #cell(datetime)="{ item: app }">
          <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-200">
              <Calendar :size="14" class="text-primary" />
              {{ formatDate(app.date) }}
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-500">
              <Clock :size="12" />
              {{ formatTime(app.start_time) }} - {{ formatTime(app.end_time) }}
            </div>
          </div>
        </template>
        <!-- Type -->
        <template #cell(type)="{ item: app }">
          <div :class="[
            'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border inline-block',
            app.meeting_type === 'online' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-purple-50 text-purple-600 border-purple-100'
          ]">
            {{ app.meeting_type }}
          </div>
        </template>
        <!-- Status -->
        <template #cell(status)="{ item: app }">
          <div class="px-3 py-1 rounded-full bg-green-50 text-green-600 border border-green-100 text-[10px] font-bold uppercase tracking-wider inline-block">
            {{ app.status }}
          </div>
        </template>
        <!-- Actions -->
        <template #cell(actions)="{ item: app }">
          <div class="flex items-center justify-end gap-2">
            <button 
              v-if="app.meeting_type === 'online'"
              class="px-3 py-2 rounded-lg bg-primary text-white hover:bg-primary/90 transition-all flex items-center gap-2 text-xs font-bold"
            >
              <Video :size="14" /> Start Meeting
            </button>
            <button class="p-2 rounded-lg bg-gray-500/10 text-gray-500 hover:bg-gray-500/20 transition-all">
              <MoreVertical :size="14" />
            </button>
          </div>
        </template>
      </DataTable>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '@/plugins/axios';
import { useToastStore } from '@/stores/toast';
import MainLayout from '@/layouts/MainLayout.vue';
import DataTable from '@/components/Table/DataTable.vue';
import { fetchWithCache } from '@/utils/cacheHelper';
import { Calendar, Clock, Video, Users, MoreVertical } from 'lucide-vue-next';

const toast = useToastStore();
const appointments = ref([]);
const loading = ref(false);
const pagination = ref(null);
const perPage = ref(15);

const columns = [
  { key: 'student', label: 'Student' },
  { key: 'datetime', label: 'Date & Time' },
  { key: 'type', label: 'Type' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: 'Actions', align: 'right' }
];

const handlePerPageChange = (newPerPage) => {
  perPage.value = newPerPage;
  fetchAppointments(1);
};

const fetchAppointments = async (page = 1) => {
  await fetchWithCache({
    url: '/auth/booking/consultant/student-appointments',
    params: { page, per_page: perPage.value },
    loadingRef: loading,
    dataRef: appointments,
    paginationRef: pagination,
    toast
  });
};

const formatTime = (time) => time.substring(0, 5);
const formatDate = (date) => new Date(date).toLocaleDateString('en-US', { 
  weekday: 'short', 
  month: 'short', 
  day: 'numeric', 
  year: 'numeric' 
});

onMounted(() => fetchAppointments(1));
</script>
