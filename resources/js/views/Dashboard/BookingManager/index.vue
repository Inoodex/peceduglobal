<template>
  <MainLayout>
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Booking & Appointment Manager</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">Monitor all consultant schedules and student appointments.</p>
        </div>
      </div>

      <!-- Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4" v-if="stats">
        <div class="bg-white dark:bg-[#1C252E] p-5 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
          <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Slots</p>
          <p class="text-2xl font-black mt-2 dark:text-white">{{ stats.overview.total_slots }}</p>
        </div>
        <div class="bg-white dark:bg-[#1C252E] p-5 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
          <p class="text-xs font-bold text-blue-500 uppercase tracking-wider">Available Slots</p>
          <p class="text-2xl font-black mt-2 dark:text-white">{{ stats.overview.available_slots }}</p>
        </div>
        <div class="bg-white dark:bg-[#1C252E] p-5 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
          <p class="text-xs font-bold text-green-500 uppercase tracking-wider">Booked Slots</p>
          <p class="text-2xl font-black mt-2 dark:text-white">{{ stats.overview.booked_slots }}</p>
        </div>
        <div class="bg-white dark:bg-[#1C252E] p-5 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
          <p class="text-xs font-bold text-primary uppercase tracking-wider">Confirmed Apps</p>
          <p class="text-2xl font-black mt-2 dark:text-white">{{ stats.overview.confirmed_appointments }}</p>
        </div>
      </div>

      <!-- Main Tabs/Content -->
      <div class="bg-white dark:bg-[#1C252E] border border-gray-100 dark:border-gray-800/50 rounded-2xl overflow-hidden shadow-sm">
        <div class="flex border-b border-gray-100 dark:border-gray-800">
          <button 
            @click="activeTab = 'consultants'" 
            :class="activeTab === 'consultants' ? 'border-primary text-primary' : 'border-transparent text-gray-500'"
            class="px-6 py-4 text-sm font-bold border-b-2 transition-all"
          >
            Consultant Status
          </button>
          <button 
            @click="activeTab = 'appointments'" 
            :class="activeTab === 'appointments' ? 'border-primary text-primary' : 'border-transparent text-gray-500'"
            class="px-6 py-4 text-sm font-bold border-b-2 transition-all"
          >
            Confirmed Apps
          </button>
          <button 
            @click="activeTab = 'schedules'" 
            :class="activeTab === 'schedules' ? 'border-primary text-primary' : 'border-transparent text-gray-500'"
            class="px-6 py-4 text-sm font-bold border-b-2 transition-all"
          >
            All Slots (Schedules)
          </button>
        </div>

        <!-- Consultant Status Table -->
        <div v-if="activeTab === 'consultants'" class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="bg-gray-50 dark:bg-[#141A21] border-b border-gray-100 dark:border-gray-800">
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Consultant</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Total Slots</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Booked</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Available</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-right">Usage</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800/50">
              <tr v-for="c in stats?.consultants" :key="c.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                <td class="px-6 py-4">
                   <div class="font-bold text-gray-900 dark:text-white">{{ c.name }}</div>
                </td>
                <td class="px-6 py-4 text-sm text-center font-bold">{{ c.total_slots }}</td>
                <td class="px-6 py-4 text-sm text-center text-green-600 font-bold">{{ c.booked_slots }}</td>
                <td class="px-6 py-4 text-sm text-center text-blue-600 font-bold">{{ c.available_slots }}</td>
                <td class="px-6 py-4">
                   <div class="flex items-center gap-2 justify-end">
                      <div class="w-24 bg-gray-100 dark:bg-gray-800 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-primary h-full" :style="{ width: (c.booked_slots / (c.total_slots || 1) * 100) + '%' }"></div>
                      </div>
                      <span class="text-[10px] font-bold text-gray-500">{{ Math.round(c.booked_slots / (c.total_slots || 1) * 100) }}%</span>
                   </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- All Schedules (Slots) Table -->
        <div v-if="activeTab === 'schedules'" class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="bg-gray-50 dark:bg-[#141A21] border-b border-gray-100 dark:border-gray-800">
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Consultant</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Date & Time</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Status</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Booked By</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800/50">
              <tr v-for="slot in allSchedules" :key="slot.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                <td class="px-6 py-4">
                  <div class="font-bold text-gray-900 dark:text-white">{{ slot.consultant?.full_name }}</div>
                  <div class="text-[10px] text-gray-400 font-bold uppercase">{{ slot.consultant?.email }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-bold text-gray-900 dark:text-white">{{ slot.slot_date }}</div>
                  <div class="text-xs text-gray-500">{{ formatTime(slot.start_time) }} - {{ formatTime(slot.end_time) }}</div>
                </td>
                <td class="px-6 py-4 text-center">
                   <span class="px-2 py-1 text-[10px] font-bold rounded-lg border uppercase"
                     :class="slot.status === 'booked' ? 'bg-green-50 text-green-600 border-green-100' : 'bg-blue-50 text-blue-600 border-blue-100'">
                     {{ slot.status }}
                   </span>
                </td>
                <td class="px-6 py-4">
                   <div v-if="slot.appointment" class="flex items-center gap-2">
                     <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black border"
                       :class="slot.appointment.is_guest ? 'bg-orange-50 border-orange-200 text-orange-600' : 'bg-primary/10 border-primary/20 text-primary'">
                       {{ (slot.appointment.booker_name || 'G').charAt(0).toUpperCase() }}
                     </div>
                     <div>
                       <span class="text-sm font-bold text-gray-900 dark:text-white">{{ slot.appointment.booker_name }}</span>
                       <span v-if="slot.appointment.is_guest" class="ml-1 px-1.5 py-0.5 text-[9px] font-black uppercase bg-orange-100 text-orange-600 rounded">Guest</span>
                       <div class="text-[10px] text-gray-400">{{ slot.appointment.booker_email }}</div>
                     </div>
                   </div>
                   <span v-else class="text-xs text-gray-400 italic font-medium">No booking yet</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- All Appointments Table -->
        <div v-if="activeTab === 'appointments'" class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="bg-gray-50 dark:bg-[#141A21] border-b border-gray-100 dark:border-gray-800">
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Booker</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Consultant</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Date & Time</th>
                <!-- <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Type</th> -->
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800/50">
              <tr v-for="app in appointments" :key="app.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black border shrink-0"
                      :class="app.is_guest ? 'bg-orange-50 border-orange-200 text-orange-600' : 'bg-primary/10 border-primary/20 text-primary'">
                      {{ (app.booker_name || 'G').charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <div class="flex items-center gap-1">
                        <span class="font-bold text-gray-900 dark:text-white text-sm">{{ app.booker_name }}</span>
                        <span v-if="app.is_guest" class="px-1.5 py-0.5 text-[9px] font-black uppercase bg-orange-100 text-orange-600 rounded">Guest</span>
                      </div>
                      <div class="text-[10px] text-gray-500 uppercase font-bold">{{ app.booker_email }}</div>
                      <div v-if="app.is_guest && app.booker_phone && app.booker_phone !== '-'" class="text-[10px] text-gray-400">📞 {{ app.booker_phone }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="font-bold text-primary">{{ app.schedule?.consultant?.full_name }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-bold text-gray-900 dark:text-white">{{ app.schedule?.slot_date }}</div>
                  <div class="text-xs text-gray-500">{{ formatTime(app.schedule?.start_time) }} - {{ formatTime(app.schedule?.end_time) }}</div>
                </td>
                  <!-- <td class="px-6 py-4">
                   <span class="px-2 py-1 text-[10px] font-bold rounded-lg border uppercase" 
                     :class="app.meeting_type === 'online' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-orange-50 text-orange-600 border-orange-100'">
                     {{ app.meeting_type }}
                   </span>
                </td> -->
                <td class="px-6 py-4">
                   <span class="px-2 py-1 text-[10px] font-bold rounded-lg border uppercase"
                     :class="app.status === 'confirmed' ? 'bg-green-50 text-green-600 border-green-100' : 'bg-yellow-50 text-yellow-600 border-yellow-100'">
                     {{ app.status }}
                   </span>
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
import { ref, onMounted, watch } from 'vue';
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import { saveFiltersState, restoreFiltersState } from '@/utils/filterHelper';

const savedTab = restoreFiltersState('booking_manager', { activeTab: 'consultants' });
const activeTab = ref(savedTab.activeTab);
watch(activeTab, (val) => saveFiltersState('booking_manager', { activeTab: val }));
const stats = ref(null);
const appointments = ref([]);
const allSchedules = ref([]);
const loading = ref(false);

const fetchData = async () => {
  loading.value = true;
  try {
    const [statsRes, appRes, schedRes] = await Promise.all([
      axios.get('/auth/admin/booking-stats'),
      axios.get('/auth/admin/appointments'),
      axios.get('/auth/admin/all-schedules')
    ]);
    stats.value = statsRes.data.data;
    appointments.value = appRes.data.data.data || [];
    allSchedules.value = schedRes.data.data.data || [];
  } catch (error) {
    console.error('Error fetching admin booking data:', error);
  } finally {
    loading.value = false;
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

onMounted(fetchData);
</script>
