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
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-gray-50/50 dark:bg-[#151C24]/50 border-b border-gray-100 dark:border-gray-800">
                <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 tracking-wider">Day & Date</th>
                <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 tracking-wider">Time Window</th>
                <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 tracking-wider text-center">Status</th>
                <th class="px-6 py-4 text-xs font-bold uppercase text-gray-500 tracking-wider text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr v-if="loadingMy" v-for="i in 5" :key="'loader-'+i">
                <td v-for="j in 4" :key="j" class="px-6 py-4"><div class="h-4 bg-gray-50 dark:bg-[#151C24] rounded animate-pulse w-full"></div></td>
              </tr>
              <tr 
                v-else-if="mySlots.length > 0" 
                v-for="s in mySlots" 
                :key="s.id"
                class="hover:bg-gray-50/50 dark:hover:bg-[#151C24]/30 transition-colors group"
              >
                <td class="px-6 py-4">
                  <div class="flex flex-col">
                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ formatDate(s.slot_date) }}</span>
                    <span class="text-[10px] text-primary font-bold uppercase">{{ s.day_of_week }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <Clock class="w-3 h-3" />
                    {{ formatTime(s.start_time) }} - {{ formatTime(s.end_time) }}
                  </div>
                </td>
                <td class="px-6 py-4 text-center">
                  <span :class="[
                    'px-2.5 py-1 text-[10px] font-black uppercase rounded-md border inline-block',
                    s.status === 'booked' ? 'bg-blue-50 text-blue-600 border-blue-100' : 
                    s.status === 'expired' ? 'bg-gray-100 text-gray-500 border-gray-200' : 
                    'bg-green-50 text-green-600 border-green-100'
                  ]">
                    {{ s.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <button 
                    v-if="s.status !== 'booked'"
                    class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/10 rounded-lg transition-all"
                    @click="releaseSlot(s.id)"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                  <span v-else class="text-[10px] font-bold text-gray-400 italic">Locked</span>
                </td>
              </tr>
              <tr v-else>
                <td colspan="4" class="px-6 py-12 text-center text-gray-500 italic text-sm">
                  No appointment slots found. Use the button above to add one.
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination Footer -->
          <div v-if="meta.last_page > 1" class="px-6 py-4 bg-gray-50/30 dark:bg-[#151C24]/30 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <span class="text-xs text-gray-500">Page {{ meta.current_page }} of {{ meta.last_page }}</span>
            <div class="flex gap-2">
              <button 
                @click="changePage(meta.current_page - 1)" 
                :disabled="meta.current_page === 1"
                class="p-2 border border-gray-100 dark:border-gray-800 rounded-lg hover:bg-white dark:hover:bg-[#1C252E] disabled:opacity-50 transition-all"
              >
                <ChevronLeft class="w-4 h-4" />
              </button>
              <button 
                @click="changePage(meta.current_page + 1)" 
                :disabled="meta.current_page === meta.last_page"
                class="p-2 border border-gray-100 dark:border-gray-800 rounded-lg hover:bg-white dark:hover:bg-[#1C252E] disabled:opacity-50 transition-all"
              >
                <ChevronRight class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
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
import { ref, onMounted } from 'vue';
import axios from '@/plugins/axios';
import { useToastStore } from '@/stores/toast';
import MainLayout from '@/layouts/MainLayout.vue';
import { Calendar, CheckCircle, Clock, Trash2, Loader2, Plus, X, Search, ChevronLeft, ChevronRight } from 'lucide-vue-next';

const toast = useToastStore();
const mySlots = ref([]);
const loadingMy = ref(false);
const showCreateModal = ref(false);
const creating = ref(false);
const searchQuery = ref('');

const meta = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 10
});

const newSlot = ref({
  slot_date: new Date().toISOString().split('T')[0],
  start_time: '10:00',
  end_time: '11:00'
});

const fetchMySlots = async (page = 1) => {
  loadingMy.value = true;
  try {
    const res = await axios.get('/auth/booking/consultant/my-claimed-slots', {
      params: { 
        page, 
        search: searchQuery.value,
        per_page: meta.value.per_page
      }
    });
    mySlots.value = res.data.data || [];
    meta.value = res.data.meta;
  } catch (e) {
    console.error(e);
  } finally {
    loadingMy.value = false;
  }
};

const onSearch = () => {
  fetchMySlots(1);
};

const changePage = (page) => {
  fetchMySlots(page);
};

const createSlot = async () => {
  creating.value = true;
  try {
    await axios.post('/auth/booking/consultant/available-slots', newSlot.value);
    toast.success('Slot created successfully');
    showCreateModal.value = false;
    fetchMySlots();
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

const releaseSlot = async (scheduleId) => {
  try {
    await axios.delete('/auth/booking/consultant/release-slot', { params: { schedule_id: scheduleId } });
    toast.success('Slot deleted successfully');
    fetchMySlots();
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to delete slot');
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

onMounted(() => {
  fetchMySlots();
});
</script>
