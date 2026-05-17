<template>
  <MainLayout>
    <div class="p-6 space-y-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manage Availability</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">Set your free slots so students can book consultations with you.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Create Slot Form -->
        <div class="lg:col-span-1 space-y-6">
          <div class="bg-white dark:bg-[#1C252E] p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <Clock class="w-5 h-5 text-primary" />
              Add New Slot
            </h2>
            <form @submit.prevent="createSlot" class="space-y-4">
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Select Date</label>
                <input 
                  type="date" 
                  v-model="form.slot_date" 
                  required
                  :min="today"
                  class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 outline-none"
                >
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Start Time</label>
                  <input 
                    type="time" 
                    v-model="form.start_time" 
                    required
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 outline-none"
                  >
                </div>
                <div>
                  <label class="block text-xs font-bold text-gray-500 uppercase mb-1">End Time</label>
                  <input 
                    type="time" 
                    v-model="form.end_time" 
                    required
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 outline-none"
                  >
                </div>
              </div>
              <button 
                type="submit" 
                :disabled="submitting"
                class="w-full bg-primary text-white font-bold py-3 rounded-xl hover:bg-primary/90 transition-all shadow-lg shadow-primary/20 disabled:opacity-50"
              >
                {{ submitting ? 'Creating...' : 'Create Slot' }}
              </button>
            </form>
          </div>
        </div>

        <!-- Slots List -->
        <div class="lg:col-span-2">
          <div class="bg-white dark:bg-[#1C252E] border border-gray-100 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
              <h2 class="text-lg font-bold text-gray-900 dark:text-white">Your Created Slots</h2>
              <button @click="fetchSlots" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition-colors">
                <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
              </button>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-left">
                <thead>
                  <tr class="bg-gray-50 dark:bg-[#141A21] border-b border-gray-100 dark:border-gray-800">
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Time Range</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-right">Action</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/50">
                  <tr v-for="slot in slots" :key="slot.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                    <td class="px-6 py-4">
                      <div class="font-bold text-gray-900 dark:text-white">{{ slot.slot_date }}</div>
                      <div class="text-[10px] font-bold text-gray-400 uppercase">{{ slot.day_of_week }}</div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-sm font-medium text-gray-600 dark:text-gray-300">
                        {{ formatTime(slot.start_time) }} - {{ formatTime(slot.end_time) }}
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <span 
                        class="px-2 py-1 text-[10px] font-bold rounded-lg border uppercase"
                        :class="getStatusClass(slot.status)"
                      >
                        {{ slot.status }}
                      </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                      <button 
                        @click="deleteSlot(slot.id)" 
                        v-if="slot.status === 'available' || slot.status === 'expired'"
                        class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                        title="Delete Slot"
                      >
                        <Trash2 class="w-4 h-4" />
                      </button>
                      <span v-else-if="slot.status === 'booked'" class="text-xs text-blue-500 font-bold italic">Booked</span>
                    </td>
                  </tr>
                  <tr v-if="slots.length === 0">
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">No slots created yet.</td>
                  </tr>
                </tbody>
              </table>
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
import MainLayout from '@/layouts/MainLayout.vue';
import { Clock, Trash2, RefreshCw } from 'lucide-vue-next';
import { useToastStore } from '@/stores/toast';

const toast = useToastStore();
const slots = ref([]);
const loading = ref(false);
const submitting = ref(false);
const today = new Date().toISOString().split('T')[0];

const form = ref({
  slot_date: '',
  start_time: '',
  end_time: ''
});

const fetchSlots = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/booking/consultant/my-claimed-slots');
    slots.value = res.data.data || [];
  } catch (error) {
    console.error('Error fetching slots:', error);
  } finally {
    loading.value = false;
  }
};

const createSlot = async () => {
  submitting.value = true;
  try {
    await axios.post('/booking/consultant/available-slots', form.value);
    toast.success('Availability slot created successfully');
    form.value = { slot_date: '', start_time: '', end_time: '' };
    fetchSlots();
  } catch (error) {
    const msg = error.response?.data?.message || 'Failed to create slot';
    toast.error(msg);
  } finally {
    submitting.value = false;
  }
};

const deleteSlot = async (id) => {
  if (!confirm('Are you sure you want to delete this slot?')) return;
  try {
    await axios.delete('/booking/consultant/release-slot', { data: { schedule_id: id } });
    toast.success('Slot deleted successfully');
    fetchSlots();
  } catch (error) {
    toast.error('Failed to delete slot');
  }
};

const formatTime = (time) => {
  if (!time) return '';
  const [hours, minutes] = time.split(':');
  const h = parseInt(hours);
  const ampm = h >= 12 ? 'PM' : 'AM';
  const hh = h % 12 || 12;
  return `${hh}:${minutes} ${ampm}`;
};

const getStatusClass = (status) => {
  switch (status) {
    case 'available': return 'bg-green-50 text-green-600 border-green-100';
    case 'booked': return 'bg-blue-50 text-blue-600 border-blue-100';
    case 'expired': return 'bg-gray-100 text-gray-500 border-gray-200';
    default: return 'bg-gray-50 text-gray-600 border-gray-100';
  }
};

onMounted(fetchSlots);
</script>
