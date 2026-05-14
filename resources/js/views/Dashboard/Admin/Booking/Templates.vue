<template>
  <MainLayout>
    <div class="p-6 space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Schedule Templates</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">Define recurring weekly patterns for your consultancy sessions.</p>
        </div>
        <button 
          @click="showAddModal = true"
          class="px-4 py-2 bg-primary text-white rounded-xl text-sm font-semibold hover:shadow-lg transition-all flex items-center gap-2"
        >
          <Plus class="w-4 h-4" /> Add Template
        </button>
      </div>

      <div class="bg-white dark:bg-[#1C252E] border border-gray-100 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-50/50 dark:bg-[#151C24]/50 border-b border-gray-100 dark:border-gray-800">
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Day</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Working Hours</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Break Time</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Duration</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            <tr v-for="t in templates" :key="t.id" class="hover:bg-gray-50/80 dark:hover:bg-[#151C24] transition-colors group">
              <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ t.day_of_week }}</td>
              <td class="px-6 py-4 text-sm">{{ formatTime(t.start_time) }} - {{ formatTime(t.end_time) }}</td>
              <td class="px-6 py-4 text-sm text-gray-500">
                <span v-if="t.break_start">{{ formatTime(t.break_start) }} - {{ formatTime(t.break_end) }}</span>
                <span v-else>No break</span>
              </td>
              <td class="px-6 py-4 text-sm">
                <span class="px-2 py-1 bg-blue-50 text-blue-600 rounded-md text-xs font-bold">{{ t.slot_duration }}m</span>
                <span v-if="t.buffer_time" class="ml-2 px-2 py-1 bg-orange-50 text-orange-600 rounded-md text-xs font-bold">+{{ t.buffer_time }}m gap</span>
              </td>
              <td class="px-6 py-4 text-right">
                <button @click="deleteTemplate(t.id)" class="p-2 text-gray-400 hover:text-red-500 rounded-lg transition-all"><Trash2 class="w-4 h-4" /></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Add Modal -->
      <div v-if="showAddModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-[#1C252E] w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-800 animate-in fade-in zoom-in duration-200">
          <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
            <h3 class="text-xl font-bold">Add Schedule Template</h3>
            <button @click="showAddModal = false" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full"><X class="w-5 h-5" /></button>
          </div>
          <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2 space-y-1">
                <label class="text-xs font-bold uppercase text-gray-500">Day of Week</label>
                <select v-model="newTemplate.day_of_week" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#151C24] border border-gray-100 dark:border-gray-800 rounded-xl outline-none">
                  <option v-for="day in ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']" :key="day" :value="day">{{ day }}</option>
                </select>
              </div>
              <div class="space-y-1">
                <label class="text-xs font-bold uppercase text-gray-500">Start Time</label>
                <input v-model="newTemplate.start_time" type="time" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#151C24] border border-gray-100 dark:border-gray-800 rounded-xl outline-none">
              </div>
              <div class="space-y-1">
                <label class="text-xs font-bold uppercase text-gray-500">End Time</label>
                <input v-model="newTemplate.end_time" type="time" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#151C24] border border-gray-100 dark:border-gray-800 rounded-xl outline-none">
              </div>
              <div class="space-y-1">
                <label class="text-xs font-bold uppercase text-gray-500">Slot Duration (m)</label>
                <input v-model="newTemplate.slot_duration" type="number" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#151C24] border border-gray-100 dark:border-gray-800 rounded-xl outline-none">
              </div>
              <div class="space-y-1">
                <label class="text-xs font-bold uppercase text-gray-500">Buffer Time (m)</label>
                <input v-model="newTemplate.buffer_time" type="number" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#151C24] border border-gray-100 dark:border-gray-800 rounded-xl outline-none">
              </div>
            </div>
          </div>
          <div class="p-6 bg-gray-50 dark:bg-[#151C24] border-t border-gray-100 dark:border-gray-800 flex justify-end gap-3">
            <button @click="showAddModal = false" class="px-6 py-2.5 text-sm font-bold text-gray-500">Cancel</button>
            <button @click="saveTemplate" class="px-6 py-2.5 bg-primary text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/20">Save Template</button>
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
import { useConfirmStore } from '@/stores/confirm';
import MainLayout from '@/layouts/MainLayout.vue';
import { Plus, Trash2, X } from 'lucide-vue-next';

const toast = useToastStore();
const confirm = useConfirmStore();
const templates = ref([]);
const showAddModal = ref(false);

const newTemplate = ref({
  day_of_week: 'Monday',
  start_time: '09:00',
  end_time: '17:00',
  slot_duration: 30,
  buffer_time: 5,
  is_active: true
});

const fetchTemplates = async () => {
  try {
    const res = await axios.get('/auth/admin/booking/admin/schedule-templates');
    templates.value = res.data.data || [];
  } catch (e) { console.error(e); }
};

const saveTemplate = async () => {
  try {
    await axios.post('/auth/admin/booking/admin/schedule-templates', newTemplate.value);
    toast.success('Template saved successfully');
    showAddModal.value = false;
    fetchTemplates();
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to save template');
  }
};

const deleteTemplate = async (id) => {
  const ok = await confirm.ask({ title: 'Delete Template?', message: 'This will not affect already generated slots.' });
  if (ok) {
    try {
      await axios.delete(`/auth/admin/booking/admin/schedule-templates/${id}`);
      toast.success('Deleted successfully');
      fetchTemplates();
    } catch (e) { toast.error('Error deleting'); }
  }
};

const formatTime = (time) => time.substring(0, 5);

onMounted(fetchTemplates);
</script>
