<template>
  <MainLayout>
    <div class="p-6 space-y-8">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Generate Time Slots</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Convert your weekly templates into actual bookable dates for a specific month.</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Generator Form -->
        <div class="lg:col-span-1">
          <div class="bg-white dark:bg-[#1C252E] p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm space-y-6">
            <h3 class="text-lg font-bold flex items-center gap-2">
              <Zap class="w-5 h-5 text-yellow-500" /> New Generation
            </h3>

            <div class="space-y-4">
              <div class="space-y-1">
                <label class="text-xs font-bold uppercase text-gray-500">Year</label>
                <select v-model="form.year" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#151C24] border border-gray-100 dark:border-gray-800 rounded-xl outline-none">
                  <option v-for="y in dynamicYears" :key="y" :value="y">{{ y }}</option>
                </select>
              </div>

              <div class="space-y-1">
                <label class="text-xs font-bold uppercase text-gray-500">Month</label>
                <select v-model="form.month" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#151C24] border border-gray-100 dark:border-gray-800 rounded-xl outline-none">
                  <option v-for="(m, i) in months" :key="i" :value="i + 1">{{ m }}</option>
                </select>
              </div>

              <div class="p-4 bg-blue-50 dark:bg-blue-900/10 rounded-xl text-xs text-blue-600 dark:text-blue-400 leading-relaxed">
                <Info class="w-4 h-4 inline-block mr-1 mb-1" />
                This process will generate slots based on your active templates. Already existing slots for this period will be skipped.
              </div>

              <button 
                @click="generateSlots"
                class="w-full py-3 bg-primary text-white rounded-xl font-bold hover:shadow-lg hover:shadow-primary/30 transition-all disabled:opacity-50 flex items-center justify-center gap-2"
                :disabled="loading"
              >
                <Loader2 v-if="loading" class="w-5 h-5 animate-spin" />
                <Play v-else class="w-4 h-4" />
                Generate Now
              </button>
            </div>
          </div>
        </div>

        <!-- History / Generated Months -->
        <div class="lg:col-span-2">
          <div class="bg-white dark:bg-[#1C252E] p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm space-y-6">
            <h3 class="text-lg font-bold">Generation History</h3>

            <div v-if="history.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div 
                v-for="(h, i) in history" 
                :key="i"
                class="p-4 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-[#151C24]/50 flex items-center justify-between"
              >
                <div>
                  <div class="font-bold text-gray-900 dark:text-white">{{ h.month_name }} {{ h.year }}</div>
                  <div class="text-xs text-green-500 font-bold flex items-center gap-1">
                    <CheckCircle class="w-3 h-3" /> Active
                  </div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-white dark:bg-[#1C252E] flex items-center justify-center border border-gray-100 dark:border-gray-800">
                  <Calendar class="w-5 h-5 text-gray-400" />
                </div>
              </div>
            </div>
            <div v-else class="text-center py-12 text-gray-500 italic text-sm border-2 border-dashed border-gray-100 dark:border-gray-800 rounded-2xl">
              No history found. Generate your first month of slots.
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
import { Zap, Play, Info, Loader2, Calendar, CheckCircle } from 'lucide-vue-next';

const toast = useToastStore();
const loading = ref(false);
const history = ref([]);

const months = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December'
];

const currentYear = new Date().getFullYear();
const dynamicYears = [];
for (let y = 2001; y <= currentYear + 5; y++) {
  dynamicYears.push(y);
}

const form = ref({
  year: currentYear,
  month: new Date().getMonth() + 1
});

const fetchHistory = async () => {
  try {
    const res = await axios.get('/auth/admin/booking/admin/generated-months');
    history.value = res.data.data || [];
  } catch (e) { console.error(e); }
};

const generateSlots = async () => {
  loading.value = true;
  try {
    const res = await axios.post('/auth/admin/booking/admin/generate-slots', form.value);
    toast.success(res.data.message);
    fetchHistory();
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to generate slots');
  } finally {
    loading.value = false;
  }
};

onMounted(fetchHistory);
</script>
