<template>
  <MainLayout>
    <div class="max-w-5xl mx-auto p-6 space-y-8">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Book an Appointment</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">Find a consultant and schedule your session in minutes.</p>
        </div>
        <button 
          class="text-sm font-semibold text-primary hover:underline flex items-center gap-1"
          @click="$router.push('/dashboard/appointments')"
        >
          View My Appointments <ChevronRight class="w-4 h-4" />
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Search & Consultant Selection -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Step 1: Search Consultant -->
          <div class="bg-white dark:bg-[#1C252E] p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm space-y-4">
            <h3 class="text-lg font-bold flex items-center gap-2">
              <span class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center text-sm">1</span>
              Find a Consultant
            </h3>
            
            <div class="relative">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input 
                v-model="searchQuery"
                type="text" 
                placeholder="Search by name or email..." 
                class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-[#151C24] border border-gray-100 dark:border-gray-800 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 transition-all"
                @input="searchConsultants"
              >
            </div>

            <!-- Consultant List -->
            <div v-if="searching" class="space-y-3">
              <div v-for="i in 3" :key="i" class="h-16 bg-gray-50 dark:bg-[#151C24] rounded-xl animate-pulse"></div>
            </div>
            <div v-else-if="consultants.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div 
                v-for="c in consultants" 
                :key="c.id"
                :class="[
                  'p-4 rounded-xl border cursor-pointer transition-all flex items-center gap-4 group',
                  selectedConsultant?.id === c.id 
                    ? 'border-primary bg-primary/5 ring-1 ring-primary' 
                    : 'border-gray-100 dark:border-gray-800 hover:border-primary/50 hover:bg-gray-50 dark:hover:bg-[#151C24]'
                ]"
                @click="selectConsultant(c)"
              >
                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold overflow-hidden">
                  <img v-if="c.profile_photo_url" :src="c.profile_photo_url" class="w-full h-full object-cover">
                  <span v-else>{{ c.full_name?.charAt(0) }}</span>
                </div>
                <div>
                  <h4 class="font-bold text-sm text-gray-900 dark:text-white group-hover:text-primary transition-colors">{{ c.full_name }}</h4>
                  <p class="text-xs text-gray-500">{{ c.email }}</p>
                </div>
              </div>
            </div>
            <div v-else-if="searchQuery" class="text-center py-8 text-gray-500 italic text-sm">
              No consultants found matching your search.
            </div>
          </div>

          <!-- Step 2: Select Date -->
          <div v-if="selectedConsultant" class="bg-white dark:bg-[#1C252E] p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm space-y-4">
            <h3 class="text-lg font-bold flex items-center gap-2">
              <span class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center text-sm">2</span>
              Select Date
            </h3>
            
            <div class="flex flex-wrap gap-3">
              <button 
                v-for="date in availableDates" 
                :key="date"
                :class="[
                  'px-4 py-2 rounded-xl border text-sm font-semibold transition-all',
                  selectedDate === date 
                    ? 'bg-primary text-white border-primary shadow-lg shadow-primary/20' 
                    : 'border-gray-100 dark:border-gray-800 hover:border-primary/50'
                ]"
                @click="fetchSlots(date)"
              >
                {{ formatDateShort(date) }}
              </button>
              <input 
                v-model="selectedDate"
                type="date" 
                class="px-4 py-2 bg-gray-50 dark:bg-[#151C24] border border-gray-100 dark:border-gray-800 rounded-xl outline-none text-sm font-semibold cursor-pointer"
                @change="fetchSlots(selectedDate)"
              >
            </div>
          </div>

          <!-- Step 3: Select Slot -->
          <div v-if="selectedDate" class="bg-white dark:bg-[#1C252E] p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm space-y-4">
            <h3 class="text-lg font-bold flex items-center gap-2">
              <span class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center text-sm">3</span>
              Select Available Time
            </h3>
            
            <div v-if="loadingSlots" class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div v-for="i in 4" :key="i" class="h-12 bg-gray-50 dark:bg-[#151C24] rounded-xl animate-pulse"></div>
            </div>
            <div v-else-if="slots.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <button 
                v-for="slot in slots" 
                :key="slot.availability_id"
                :class="[
                  'px-3 py-2 rounded-xl border text-sm font-medium transition-all text-center',
                  selectedSlot?.availability_id === slot.availability_id 
                    ? 'bg-primary text-white border-primary shadow-lg shadow-primary/20' 
                    : 'border-gray-100 dark:border-gray-800 hover:border-primary/50'
                ]"
                @click="selectedSlot = slot"
              >
                {{ formatTime(slot.start_time) }}
              </button>
            </div>
            <div v-else class="text-center py-6 text-gray-500 italic text-sm">
              No slots available for this date.
            </div>
          </div>
        </div>

        <!-- Right Column: Booking Summary & Final Action -->
        <div class="space-y-6">
          <div class="bg-white dark:bg-[#1C252E] p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-lg shadow-gray-100/50 dark:shadow-none sticky top-6">
            <h3 class="text-lg font-bold mb-6">Booking Summary</h3>
            
            <div class="space-y-6">
              <div class="flex items-start gap-4">
                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 shrink-0">
                  <User class="w-4 h-4" />
                </div>
                <div>
                  <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Consultant</p>
                  <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ selectedConsultant?.full_name || 'Not selected' }}</p>
                </div>
              </div>

              <div class="flex items-start gap-4">
                <div class="w-8 h-8 rounded-lg bg-green-50 dark:bg-green-900/20 flex items-center justify-center text-green-600 shrink-0">
                  <Calendar class="w-4 h-4" />
                </div>
                <div>
                  <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Schedule</p>
                  <p v-if="selectedSlot" class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ formatDateShort(selectedSlot.date) }}<br>
                    {{ formatTime(selectedSlot.start_time) }} - {{ formatTime(selectedSlot.end_time) }}
                  </p>
                  <p v-else class="text-sm font-semibold text-gray-500">No time selected</p>
                </div>
              </div>

              <div class="pt-6 border-t border-gray-100 dark:border-gray-800 space-y-4">
                <div class="space-y-2">
                  <label class="text-xs font-bold text-gray-500 uppercase">Meeting Type</label>
                  <div class="grid grid-cols-2 gap-2">
                    <button 
                      class="px-3 py-2 rounded-lg text-xs font-bold border transition-all"
                      :class="meetingType === 'online' ? 'bg-primary text-white border-primary' : 'bg-gray-50 dark:bg-[#151C24] border-gray-100 dark:border-gray-800'"
                      @click="meetingType = 'online'"
                    >
                      <Video class="w-3 h-3 inline-block mr-1" /> Online
                    </button>
                    <button 
                      class="px-3 py-2 rounded-lg text-xs font-bold border transition-all"
                      :class="meetingType === 'physical' ? 'bg-primary text-white border-primary' : 'bg-gray-50 dark:bg-[#151C24] border-gray-100 dark:border-gray-800'"
                      @click="meetingType = 'physical'"
                    >
                      <MapPin class="w-3 h-3 inline-block mr-1" /> Physical
                    </button>
                  </div>
                </div>

                <div class="space-y-2">
                  <label class="text-xs font-bold text-gray-500 uppercase">Remarks (Optional)</label>
                  <textarea 
                    v-model="remarks"
                    class="w-full px-4 py-2 bg-gray-50 dark:bg-[#151C24] border border-gray-100 dark:border-gray-800 rounded-xl outline-none text-sm h-24 focus:ring-1 focus:ring-primary/20"
                    placeholder="Anything you want to discuss?"
                  ></textarea>
                </div>
              </div>

              <button 
                class="w-full py-4 bg-primary text-white rounded-2xl font-bold hover:shadow-xl hover:shadow-primary/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                :disabled="!selectedSlot || loading"
                @click="bookNow"
              >
                <Loader2 v-if="loading" class="w-5 h-5 animate-spin" />
                <CheckCircle v-else class="w-5 h-5" />
                Confirm Booking
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
import { useRouter } from 'vue-router';
import MainLayout from '@/layouts/MainLayout.vue';
import { Search, User, Calendar, Clock, Video, MapPin, CheckCircle, Loader2, ChevronRight } from 'lucide-vue-next';

const toast = useToastStore();
const router = useRouter();

const searchQuery = ref('');
const consultants = ref([]);
const searching = ref(false);
const loading = ref(false);

const selectedConsultant = ref(null);
const availableDates = ref([]);
const selectedDate = ref('');
const slots = ref([]);
const loadingSlots = ref(false);
const selectedSlot = ref(null);

const meetingType = ref('online');
const remarks = ref('');

const searchConsultants = async () => {
  if (searchQuery.value.length < 2) {
    consultants.value = [];
    return;
  }
  searching.value = true;
  try {
    const res = await axios.post('/auth/student/booking/student/search-consultants', { name: searchQuery.value });
    consultants.value = res.data.data || [];
  } finally {
    searching.value = false;
  }
};

const selectConsultant = async (consultant) => {
  selectedConsultant.value = consultant;
  selectedDate.value = '';
  slots.value = [];
  selectedSlot.value = null;
  
  // Predict some dates (today and next 6 days)
  const dates = [];
  for (let i = 0; i < 7; i++) {
    const d = new Date();
    d.setDate(d.getDate() + i);
    dates.push(d.toISOString().split('T')[0]);
  }
  availableDates.value = dates;
  
  // Optionally fetch the very next available date from API
  try {
    const nextRes = await axios.get('/auth/student/booking/student/next-available-date', { params: { consultant_id: consultant.id } });
    if (nextRes.data.data?.next_available_date) {
      if (!availableDates.value.includes(nextRes.data.data.next_available_date)) {
        availableDates.value.unshift(nextRes.data.data.next_available_date);
      }
    }
  } catch (e) {}
};

const fetchSlots = async (date) => {
  selectedDate.value = date;
  selectedSlot.value = null;
  loadingSlots.value = true;
  try {
    const res = await axios.get('/auth/student/booking/student/consultant-slots', { 
      params: { 
        consultant_id: selectedConsultant.value.id,
        date: date
      } 
    });
    slots.value = res.data.data || [];
  } catch (error) {
    toast.error('Failed to load slots');
  } finally {
    loadingSlots.value = false;
  }
};

const bookNow = async () => {
  loading.value = true;
  try {
    await axios.post('/auth/student/booking/student/book-appointment', {
      availability_id: selectedSlot.value.availability_id,
      meeting_type: meetingType.value,
      remarks: remarks.value
    });
    toast.success('Appointment booked successfully!');
    router.push('/dashboard/appointments');
  } catch (error) {
    const msg = error.response?.data?.message || 'Failed to book appointment';
    toast.error(msg);
  } finally {
    loading.value = false;
  }
};

const formatDateShort = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric'
  });
};

const formatTime = (timeString) => {
  return timeString.substring(0, 5);
};
</script>
