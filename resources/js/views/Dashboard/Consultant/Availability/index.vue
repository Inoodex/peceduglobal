<template>
  <MainLayout>
    <div class="p-6 space-y-8">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manage My Availability</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">Claim existing slots or create your own custom appointment times.</p>
        </div>
        <button 
          @click="showCreateModal = true"
          class="px-4 py-2 bg-primary text-white rounded-xl text-sm font-semibold hover:shadow-lg transition-all flex items-center gap-2"
        >
          <Plus class="w-4 h-4" /> Create Custom Slot
        </button>
      </div>

      <div class="space-y-6">
        <!-- Search and Stats -->
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
          <div class="relative w-full md:w-96">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Search by date (YYYY-MM-DD)..." 
              class="w-full pl-10 pr-4 py-2 bg-white dark:bg-[#1C252E] border border-gray-100 dark:border-gray-800 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 transition-all text-sm"
              @input="onSearch"
            >
          </div>
          <div class="flex items-center gap-4 text-sm font-medium text-gray-500">
            <span>Total Slots: <span class="text-gray-900 dark:text-white font-bold">{{ meta.total }}</span></span>
          </div>
        </div>

        <!-- My Appointment Slots Table -->
        <DataTable 
          :columns="columns" 
          :data="mySlots" 
          :loading="loadingMy"
          :pagination="pagination"
          @page-change="fetchMySlots"
          @per-page-change="handlePerPageChange"
        >
          <!-- Day & Date -->
          <template #cell(datetime)="{ item: s }">
            <div class="flex flex-col">
              <span class="text-sm font-bold text-gray-900 dark:text-white">{{ formatDate(s.slot_date) }}</span>
              <span class="text-[10px] text-primary font-bold uppercase">{{ s.day_of_week }}</span>
            </div>
          </template>
          <!-- Time Window -->
          <template #cell(window)="{ item: s }">
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <Clock class="w-3 h-3" />
              {{ formatTime(s.start_time) }} - {{ formatTime(s.end_time) }}
            </div>
          </template>
          <!-- Status -->
          <template #cell(status)="{ item: s }">
            <span :class="[
              'px-2.5 py-1 text-[10px] font-black uppercase rounded-md border inline-block',
              s.status === 'booked' ? 'bg-blue-50 text-blue-600 border-blue-100' : 
              s.status === 'expired' ? 'bg-gray-100 text-gray-500 border-gray-200' : 
              'bg-green-50 text-green-600 border-green-100'
            ]">
              {{ s.status }}
            </span>
          </template>
          <!-- Action -->
          <template #cell(action)="{ item: s }">
            <button 
              v-if="s.status !== 'booked'"
              class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/10 rounded-lg transition-all"
              @click="confirmReleaseSlot(s)"
              title="Delete Slot"
            >
              <Trash2 class="w-4 h-4" />
            </button>
            <span v-else class="text-[10px] font-bold text-gray-400 italic">Locked</span>
          </template>
        </DataTable>
      </div>

      <!-- Create Slot Modal -->
      <div v-if="showCreateModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-[#1C252E] w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-800 animate-in fade-in zoom-in duration-200">
          <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
            <h3 class="text-xl font-bold">Create Appointment Slot</h3>
            <button @click="showCreateModal = false" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full"><X class="w-5 h-5" /></button>
          </div>
          <div class="p-6 space-y-4">
            <div class="space-y-1">
              <label class="text-xs font-bold uppercase text-gray-500">Date</label>
              <input v-model="newSlot.slot_date" type="date" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#151C24] border border-gray-100 dark:border-gray-800 rounded-xl outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1">
                <label class="text-xs font-bold uppercase text-gray-500">Start Time</label>
                <input v-model="newSlot.start_time" type="time" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#151C24] border border-gray-100 dark:border-gray-800 rounded-xl outline-none">
              </div>
              <div class="space-y-1">
                <label class="text-xs font-bold uppercase text-gray-500">End Time</label>
                <input v-model="newSlot.end_time" type="time" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#151C24] border border-gray-100 dark:border-gray-800 rounded-xl outline-none">
              </div>
            </div>
          </div>
          <div class="p-6 bg-gray-50 dark:bg-[#151C24] border-t border-gray-100 dark:border-gray-800 flex justify-end gap-3">
            <button @click="showCreateModal = false" class="px-6 py-2.5 text-sm font-bold text-gray-500">Cancel</button>
            <button 
              @click="createSlot" 
              class="px-6 py-2.5 bg-primary text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/20 flex items-center gap-2"
              :disabled="creating"
            >
              <Loader2 v-if="creating" class="w-4 h-4 animate-spin" />
              Create Slot
            </button>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from '@/plugins/axios';
import { useToastStore } from '@/stores/toast';
import { useConfirmStore } from '@/stores/confirm';
import MainLayout from '@/layouts/MainLayout.vue';
import DataTable from '@/components/Table/DataTable.vue';
import { fetchWithCache, clearCache } from '@/utils/cacheHelper';
import { Calendar, CheckCircle, Clock, Trash2, Loader2, Plus, X, Search, ChevronLeft, ChevronRight } from 'lucide-vue-next';

const toast = useToastStore();
const confirm = useConfirmStore();
const mySlots = ref([]);
const loadingMy = ref(false);
const showCreateModal = ref(false);
const creating = ref(false);
const searchQuery = ref('');
const pagination = ref(null);
const perPage = ref(10);

const meta = computed(() => pagination.value || { total: 0 });

const columns = [
  { key: 'datetime', label: 'Day & Date' },
  { key: 'window', label: 'Time Window' },
  { key: 'status', label: 'Status', align: 'center' },
  { key: 'action', label: 'Action', align: 'right' }
];

const newSlot = ref({
  slot_date: new Date().toISOString().split('T')[0],
  start_time: '10:00',
  end_time: '11:00'
});

const handlePerPageChange = (newPerPage) => {
  perPage.value = newPerPage;
  fetchMySlots(1);
};

const fetchMySlots = async (page = 1) => {
  await fetchWithCache({
    url: '/auth/booking/consultant/my-claimed-slots',
    params: { page, search: searchQuery.value, per_page: perPage.value },
    loadingRef: loadingMy,
    dataRef: mySlots,
    paginationRef: pagination,
    toast
  });
};

const onSearch = () => {
  fetchMySlots(1);
};

const createSlot = async () => {
  creating.value = true;
  try {
    await axios.post('/auth/booking/consultant/available-slots', newSlot.value);
    toast.success('Slot created successfully');
    clearCache('/auth/booking/consultant/my-claimed-slots');
    showCreateModal.value = false;
    fetchMySlots(1);
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to create slot');
  } finally {
    creating.value = false;
  }
};

const claimSlot = async (slotId) => {
  try {
    await axios.post('/auth/booking/consultant/claim-slot', { slot_id: slotId });
    toast.success('Slot claimed successfully');
    fetchMySlots();
    fetchMySlots();
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to claim slot');
  }
};

const confirmReleaseSlot = async (slot) => {
  const ok = await confirm.ask({
    title: 'Delete Slot?',
    message: `Are you sure you want to delete this available slot?`
  });

  if (ok) {
    try {
      await axios.delete('/auth/booking/consultant/release-slot', { params: { schedule_id: slot.id } });
      clearCache('/auth/booking/consultant/my-claimed-slots');
      toast.success('Slot deleted successfully');
      fetchMySlots(1);
    } catch (error) {
      toast.error(error.response?.data?.message || 'Failed to delete slot');
    }
  }
};

const formatTime = (time) => {
  const [h, m] = time.split(':');
  const hour = parseInt(h);
  const ampm = hour >= 12 ? 'PM' : 'AM';
  const h12 = hour % 12 || 12;
  return `${h12}:${m} ${ampm}`;
};
const formatDate = (date) => new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

onMounted(() => fetchMySlots(1));
</script>
