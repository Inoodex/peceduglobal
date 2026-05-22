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
        <!-- Student / Guest -->
        <template #cell(student)="{ item: app }">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full flex items-center justify-center font-black text-sm border shrink-0"
              :class="app.is_guest ? 'bg-orange-50 border-orange-200 text-orange-600' : 'bg-primary/10 border-primary/20 text-primary'">
              {{ (app.booker_name || 'G').charAt(0).toUpperCase() }}
            </div>
            <div>
              <div class="flex items-center gap-1.5">
                <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ app.booker_name }}</div>
                <span v-if="app.is_guest" class="px-1.5 py-0.5 text-[9px] font-black uppercase bg-orange-100 text-orange-600 rounded">Guest</span>
              </div>
              <div class="text-xs text-gray-500">{{ app.booker_email }}</div>
              <div v-if="app.is_guest && app.booker_phone && app.booker_phone !== '-'" class="text-[10px] text-gray-400">📞 {{ app.booker_phone }}</div>
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
        <!-- <template #cell(type)="{ item: app }">
          <div :class="[
            'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border inline-block',
            app.meeting_type === 'online' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-purple-50 text-purple-600 border-purple-100'
          ]">
            {{ app.meeting_type }}
          </div>
        </template> -->
        <!-- Status -->
        <template #cell(status)="{ item: app }">
          <span :class="[
            'px-2.5 py-1 rounded-full text-[10px] font-black uppercase border tracking-wider',
            app.status === 'confirmed' ? 'bg-blue-50 border-blue-100 text-blue-600' : '',
            app.status === 'pending' ? 'bg-yellow-50 border-yellow-100 text-yellow-600' : '',
            app.status === 'completed' ? 'bg-green-50 border-green-100 text-green-600' : '',
            app.status === 'cancelled' ? 'bg-red-50 border-red-100 text-red-600' : ''
          ]">
            {{ app.status }}
          </span>
        </template>
        <!-- Actions -->
        <template #cell(actions)="{ item: app }">
          <div class="flex items-center justify-end">
            <button 
              @click="openStatusModal(app)"
              class="px-3 py-1.5 text-[10px] font-black uppercase bg-primary text-white rounded-lg hover:bg-primary/90 transition-all"
            >
              Update Status
            </button>
          </div>
        </template>
      </DataTable>

      <!-- Status Update Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-[#1C252E] border border-gray-100 dark:border-gray-800 rounded-2xl w-full max-w-md shadow-2xl p-6 relative">
          <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
            Update Appointment
          </h2>
          <p class="text-xs text-gray-500 mb-4">
            Updating appointment for <strong>{{ selectedApp?.booker_name }}</strong> on {{ formatDate(selectedApp?.date) }}.
          </p>

          <div class="space-y-4">
            <div>
              <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">
                Status
              </label>
              <select 
                v-model="selectedStatus" 
                class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-xl p-3 bg-white dark:bg-[#1C252E] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
              >
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">
                Consultant Notes / Notes
              </label>
              <textarea 
                v-model="notes" 
                rows="4" 
                class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-xl p-3 bg-transparent text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                placeholder="Enter feedback or cancellation reason..."
              ></textarea>
            </div>

            <div class="flex justify-end gap-3">
              <button 
                @click="closeModal" 
                class="px-4 py-2 text-xs font-bold text-gray-500 bg-gray-100 dark:bg-gray-800 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition"
              >
                Cancel
              </button>
              <button 
                @click="submitStatusUpdate" 
                :disabled="submitting"
                class="px-4 py-2 text-xs font-bold text-white bg-primary rounded-xl hover:bg-primary/95 transition flex items-center gap-1 disabled:opacity-50"
              >
                {{ submitting ? 'Updating...' : 'Save Changes' }}
              </button>
            </div>
          </div>
        </div>
      </div>

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

// Modal states
const showModal = ref(false);
const selectedApp = ref(null);
const selectedStatus = ref('');
const notes = ref('');
const submitting = ref(false);

const columns = [
  { key: 'student', label: 'Student' },
  { key: 'datetime', label: 'Date & Time' },
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

const openStatusModal = (app) => {
  selectedApp.value = app;
  selectedStatus.value = app.status;
  notes.value = app.consultant_notes || '';
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedApp.value = null;
  selectedStatus.value = '';
  notes.value = '';
};

const submitStatusUpdate = async () => {
  if (!selectedApp.value) return;
  submitting.value = true;
  try {
    const res = await axios.patch(`/auth/booking/consultant/appointments/${selectedApp.value.id}/status`, {
      status: selectedStatus.value,
      consultant_notes: notes.value
    });
    
    if (res.data.success) {
      toast.success(res.data.message || 'Status updated successfully.');
      closeModal();
      fetchAppointments(pagination.value?.current_page || 1);
    } else {
      toast.error(res.data.message || 'Failed to update status.');
    }
  } catch (err) {
    console.error(err);
    toast.error(err.response?.data?.message || 'Something went wrong.');
  } finally {
    submitting.value = false;
  }
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
