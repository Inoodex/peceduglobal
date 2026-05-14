<template>
  <MainLayout>
    <div class="p-6 space-y-8">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manage My Availability</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">Claim time slots to let students book sessions with you.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Left: Claim Slots -->
        <div class="space-y-6">
          <div class="bg-white dark:bg-[#1C252E] p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm space-y-6">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-bold flex items-center gap-2">
                <Calendar class="w-5 h-5 text-primary" /> Available for Claim
              </h3>
              <input 
                v-model="claimDate" 
                type="date" 
                class="px-4 py-2 bg-gray-50 dark:bg-[#151C24] border border-gray-100 dark:border-gray-800 rounded-xl outline-none text-sm font-semibold"
                @change="fetchAvailableForClaim"
              >
            </div>

            <div v-if="loadingAvailable" class="grid grid-cols-2 gap-4">
              <div v-for="i in 4" :key="i" class="h-12 bg-gray-50 dark:bg-[#151C24] rounded-xl animate-pulse"></div>
            </div>
            <div v-else-if="availableSlots.length > 0" class="grid grid-cols-2 gap-4">
              <div 
                v-for="slot in availableSlots" 
                :key="slot.id"
                class="p-3 rounded-xl border border-gray-100 dark:border-gray-800 flex items-center justify-between group hover:border-primary/50 transition-all"
              >
                <div class="text-sm font-medium">
                  {{ formatTime(slot.start_time) }} - {{ formatTime(slot.end_time) }}
                </div>
                <button 
                  v-if="!slot.is_claimed"
                  class="px-3 py-1 bg-primary/10 text-primary text-xs font-bold rounded-lg hover:bg-primary hover:text-white transition-all"
                  @click="claimSlot(slot.id)"
                >
                  Claim
                </button>
                <span v-else class="text-[10px] font-bold text-green-500 uppercase">Already Claimed</span>
              </div>
            </div>
            <div v-else class="text-center py-12 text-gray-500 italic text-sm">
              No open slots found for this date. Ask admin to generate slots.
            </div>
          </div>
        </div>

        <!-- Right: My Claimed Slots -->
        <div class="space-y-6">
          <div class="bg-white dark:bg-[#1C252E] p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm space-y-6">
            <h3 class="text-lg font-bold flex items-center gap-2">
              <CheckCircle class="w-5 h-5 text-green-500" /> My Active Slots
            </h3>

            <div v-if="loadingMy" class="space-y-3">
              <div v-for="i in 3" :key="i" class="h-16 bg-gray-50 dark:bg-[#151C24] rounded-xl animate-pulse"></div>
            </div>
            <div v-else-if="mySlots.length > 0" class="space-y-3">
              <div 
                v-for="s in mySlots" 
                :key="s.id"
                class="p-4 rounded-xl border border-gray-100 dark:border-gray-800 flex items-center justify-between"
              >
                <div>
                  <div class="text-sm font-bold">{{ formatDate(s.date) }}</div>
                  <div class="text-xs text-gray-500">{{ formatTime(s.start_time) }} - {{ formatTime(s.end_time) }}</div>
                </div>
                <div class="flex items-center gap-3">
                  <span :class="[
                    'px-2 py-1 text-[10px] font-bold uppercase rounded-md border',
                    s.status === 'booked' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-green-50 text-green-600 border-green-100'
                  ]">
                    {{ s.status }}
                  </span>
                  <button 
                    v-if="s.status !== 'booked'"
                    class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all"
                    @click="releaseSlot(s.id)"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-12 text-gray-500 italic text-sm">
              You haven't claimed any slots yet.
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
import { Calendar, CheckCircle, Clock, Trash2, Loader2 } from 'lucide-vue-next';

const toast = useToastStore();
const claimDate = ref(new Date().toISOString().split('T')[0]);
const availableSlots = ref([]);
const mySlots = ref([]);
const loadingAvailable = ref(false);
const loadingMy = ref(false);

const fetchAvailableForClaim = async () => {
  loadingAvailable.value = true;
  try {
    const res = await axios.get('/auth/consultant/booking/consultant/available-slots', { params: { date: claimDate.value } });
    availableSlots.value = res.data.data || [];
  } catch (e) {
    toast.error('Failed to load available slots');
  } finally {
    loadingAvailable.value = false;
  }
};

const fetchMySlots = async () => {
  loadingMy.value = true;
  try {
    const res = await axios.get('/auth/consultant/booking/consultant/my-claimed-slots');
    mySlots.value = res.data.data || [];
  } catch (e) {
    console.error(e);
  } finally {
    loadingMy.value = false;
  }
};

const claimSlot = async (slotId) => {
  try {
    await axios.post('/auth/consultant/booking/consultant/claim-slot', { slot_id: slotId });
    toast.success('Slot claimed successfully');
    fetchAvailableForClaim();
    fetchMySlots();
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to claim slot');
  }
};

const releaseSlot = async (availabilityId) => {
  try {
    await axios.delete('/auth/consultant/booking/consultant/release-slot', { params: { availability_id: availabilityId } });
    toast.success('Slot released successfully');
    fetchAvailableForClaim();
    fetchMySlots();
  } catch (error) {
    toast.error('Failed to release slot');
  }
};

const formatTime = (time) => time.substring(0, 5);
const formatDate = (date) => new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

onMounted(() => {
  fetchAvailableForClaim();
  fetchMySlots();
});
</script>
