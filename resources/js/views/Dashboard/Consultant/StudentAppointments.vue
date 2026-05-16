<template>
  <MainLayout>
    <div class="p-6 space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Student Appointments</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">View and manage appointments booked by students.</p>
      </div>

      <div class="bg-white dark:bg-[#1C252E] border border-gray-100 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="bg-gray-50 dark:bg-[#141A21] border-b border-gray-100 dark:border-gray-800">
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Student</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Date & Time</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Type</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Status</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800/50">
              <tr v-for="app in appointments" :key="app.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-black uppercase shadow-sm border border-primary/20">
                      {{ app.student?.full_name?.charAt(0) || 'S' }}
                    </div>
                    <div>
                      <div class="font-bold text-gray-900 dark:text-white">{{ app.student?.full_name }}</div>
                      <div class="text-[10px] text-gray-500 font-bold uppercase">{{ app.student?.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-bold text-gray-900 dark:text-white">{{ app.date }}</div>
                  <div class="text-xs text-gray-500">{{ formatTime(app.start_time) }} - {{ formatTime(app.end_time) }}</div>
                </td>
                <td class="px-6 py-4">
                  <span 
                    class="px-2 py-1 text-[10px] font-bold rounded-lg border uppercase"
                    :class="app.meeting_type === 'online' ? 'bg-indigo-50 text-indigo-600 border-indigo-100' : 'bg-orange-50 text-orange-600 border-orange-100'"
                  >
                    {{ app.meeting_type }}
                  </span>
                </td>
                <td class="px-6 py-4">
                   <span class="px-2 py-1 text-[10px] font-bold rounded-lg border uppercase bg-green-50 text-green-600 border-green-100">
                     {{ app.status }}
                   </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <button @click="viewDetails(app)" class="p-2 text-primary hover:bg-primary/5 rounded-lg transition-all">
                    <Eye class="w-5 h-5" />
                  </button>
                </td>
              </tr>
              <tr v-if="appointments.length === 0">
                <td colspan="5" class="px-6 py-12 text-center text-gray-500">No appointments found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Appointment Details Modal -->
    <div v-if="selectedApp" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
      <div class="bg-white dark:bg-[#1C252E] w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-[#141A21]">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white">Appointment Details</h3>
          <button @click="selectedApp = null" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-full transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>
        <div class="p-6 space-y-6">
          <div class="flex items-center gap-4 p-4 bg-primary/5 rounded-2xl border border-primary/10">
            <div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center text-white text-xl font-black">
               {{ selectedApp.student?.full_name?.charAt(0) }}
            </div>
            <div>
              <p class="text-lg font-black text-gray-900 dark:text-white">{{ selectedApp.student?.full_name }}</p>
              <p class="text-sm font-bold text-primary">{{ selectedApp.student?.email }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
             <div class="p-4 bg-gray-50 dark:bg-[#141A21] rounded-2xl border border-gray-100 dark:border-gray-800">
                <p class="text-[10px] uppercase font-black text-gray-400 mb-1">Date</p>
                <p class="text-sm font-bold">{{ selectedApp.date }}</p>
             </div>
             <div class="p-4 bg-gray-50 dark:bg-[#141A21] rounded-2xl border border-gray-100 dark:border-gray-800">
                <p class="text-[10px] uppercase font-black text-gray-400 mb-1">Time</p>
                <p class="text-sm font-bold">{{ formatTime(selectedApp.start_time) }}</p>
             </div>
          </div>

          <div v-if="selectedApp.student_notes" class="space-y-2">
             <p class="text-[10px] uppercase font-black text-gray-400 px-1">Student Notes</p>
             <div class="p-4 bg-yellow-50/50 dark:bg-yellow-900/10 border border-yellow-100 dark:border-yellow-900/20 rounded-2xl text-sm italic text-gray-600 dark:text-gray-300">
                "{{ selectedApp.student_notes }}"
             </div>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 flex justify-end">
           <button @click="selectedApp = null" class="px-6 py-2.5 bg-gray-900 dark:bg-white dark:text-gray-900 text-white font-black rounded-xl hover:opacity-90 transition-all text-sm">Close</button>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import { Eye, X } from 'lucide-vue-next';

const appointments = ref([]);
const selectedApp = ref(null);

const fetchAppointments = async () => {
  try {
    const res = await axios.get('/booking/consultant/student-appointments');
    appointments.value = res.data.data || [];
  } catch (error) {
    console.error('Error fetching consultant appointments:', error);
  }
};

const viewDetails = (app) => {
  selectedApp.value = app;
};

const formatTime = (time) => {
  if (!time) return '';
  const [hours, minutes] = time.split(':');
  const h = parseInt(hours);
  const ampm = h >= 12 ? 'PM' : 'AM';
  const hh = h % 12 || 12;
  return `${hh}:${minutes} ${ampm}`;
};

onMounted(fetchAppointments);
</script>
