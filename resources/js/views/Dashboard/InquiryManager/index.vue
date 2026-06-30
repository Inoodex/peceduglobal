<template>
  <MainLayout>
    <div class="p-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ pageTitle }}</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">Manage {{ pageDescription }}</p>
        </div>
      </div>

      <DataTable
        :columns="columns"
        :data="inquiries"
        :loading="loading"
        :pagination="pagination"
        @page-change="fetchInquiries"
        @per-page-change="handlePerPageChange"
      >
        <template #toolbar>
          <select v-model="filters.status" @change="fetchInquiries(1)" class="bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20">
            <option value="">All Status</option>
            <option value="new">New</option>
            <option value="contacted">Contacted</option>
            <option value="pending">Pending</option>
            <option value="closed">Closed</option>
          </select>
        </template>

        <template #cell(student)="{ item }">
          <div class="font-bold text-gray-900 dark:text-white">{{ item.first_name }} {{ item.last_name }}</div>
          <div class="text-xs text-gray-500 mt-1">{{ formatDate(item.created_at) }}</div>
        </template>

        <template #cell(contact)="{ item }">
          <div class="text-sm text-gray-900 dark:text-white font-medium">{{ item.email }}</div>
          <div class="text-sm text-gray-500">{{ item.phone }}</div>
        </template>

        <template #cell(type)="{ item }">
          <span class="px-3 py-1 text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-full uppercase tracking-tight">
            {{ item.type.replace('_', ' ') }}
          </span>
        </template>

        <template #cell(status)="{ item }">
          <div class="relative inline-block">
            <select
              v-model="item.status"
              @change="updateStatus(item)"
              :class="[
                'appearance-none pl-3 pr-8 py-1 text-[10px] font-bold rounded-lg border cursor-pointer transition-all focus:outline-none uppercase tracking-wider shadow-sm',
                statusStyles(item.status)
              ]"
            >
              <option value="new">NEW</option>
              <option value="contacted">CONTACTED</option>
              <option value="pending">PENDING</option>
              <option value="closed">CLOSED</option>
            </select>
            <div class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none opacity-40">
              <ChevronDown class="w-2.5 h-2.5" />
            </div>
          </div>
        </template>

        <template #cell(actions)="{ item }">
          <div class="flex items-center justify-end gap-2">
            <button @click="showInquiryDetails(item)" class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="View Details">
              <Eye class="w-5 h-5" />
            </button>
            <button @click="deleteInquiry(item.id)" class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors" title="Delete">
              <Trash2 class="w-5 h-5" />
            </button>
          </div>
        </template>
      </DataTable>

      <!-- Modal for Inquiry Details -->
      <div v-if="selectedInquiry" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-[#1C252E] w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-[#141A21]">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Inquiry Details</h3>
            <button @click="selectedInquiry = null" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-full transition-colors">
              <X class="w-5 h-5" />
            </button>
          </div>

          <div class="p-6 max-h-[70vh] overflow-y-auto">
            <div class="grid grid-cols-2 gap-6 mb-8">
              <div>
                <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Student Name</p>
                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ selectedInquiry.first_name }} {{ selectedInquiry.last_name }}</p>
              </div>
              <div>
                <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Inquiry Type</p>
                <p class="text-sm font-bold text-primary capitalize">{{ selectedInquiry.type.replace('_', ' ') }}</p>
              </div>
              <div>
                <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Email Address</p>
                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ selectedInquiry.email }}</p>
              </div>
              <div>
                <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Phone Number</p>
                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ selectedInquiry.phone }}</p>
              </div>
            </div>

            <div class="bg-gray-50 dark:bg-[#141A21] rounded-2xl p-6 border border-gray-100 dark:border-gray-800">
              <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <FileText class="w-4 h-4 text-primary" />
                Additional Information
              </h4>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div v-for="(value, key) in selectedInquiry.additional_info" :key="key" class="p-3 bg-white dark:bg-[#1C252E] rounded-xl border border-gray-200 dark:border-gray-700/50">
                  <p class="text-[10px] uppercase font-bold text-gray-400 mb-0.5">{{ formatKey(key) }}</p>
                  <div v-if="key === 'uploaded_file' && value" class="flex items-center gap-3">
                    <a :href="value" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-primary underline flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"/></svg>
                      <span>{{ extractFilename(value) }}</span>
                    </a>
                    <button @click.prevent="downloadFile(value, extractFilename(value))" class="px-3 py-1 text-xs font-semibold bg-gray-100 dark:bg-gray-800 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700">Download</button>
                  </div>
                  <p v-else class="text-sm font-medium text-gray-900 dark:text-gray-200">{{ formatValue(value) }}</p>
                </div>
                <div v-if="selectedInquiry.additional_info_file_url" class="p-3 bg-white dark:bg-[#1C252E] rounded-xl border border-gray-200 dark:border-gray-700/50">
                  <p class="text-[10px] uppercase font-bold text-gray-400 mb-0.5">Uploaded File</p>
                  <div class="flex items-center gap-3">
                    <a :href="selectedInquiry.additional_info_file_url" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-primary underline flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"/></svg>
                      <span>{{ selectedInquiry.additional_info_file_name || extractFilename(selectedInquiry.additional_info_file_url) }}</span>
                    </a>
                    <button @click.prevent="downloadFile(selectedInquiry.additional_info_file_url, selectedInquiry.additional_info_file_name || extractFilename(selectedInquiry.additional_info_file_url))" class="px-3 py-1 text-xs font-semibold bg-gray-100 dark:bg-gray-800 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700">Download</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-6">
              <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Admin Notes</label>
              <textarea v-model="selectedInquiry.admin_notes" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-2xl p-4 text-sm focus:ring-2 focus:ring-primary/20 focus:outline-none" rows="3" placeholder="Add internal notes here..."></textarea>
            </div>
          </div>

          <div class="px-6 py-4 bg-gray-50 dark:bg-[#141A21] border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
            <button @click="selectedInquiry = null" class="px-5 py-2 text-sm font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-xl transition-colors">Close</button>
            <button @click="saveNotes" class="px-5 py-2 text-sm font-bold bg-primary text-white hover:bg-primary/90 rounded-xl transition-colors shadow-lg shadow-primary/20">Save Changes</button>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import DataTable from '@/components/Table/DataTable.vue';
import { useToastStore } from '@/stores/toast';
import { useConfirmStore } from '@/stores/confirm';
import { fetchWithCache, clearCache } from '@/utils/cacheHelper';
import { saveFiltersState, restoreFiltersState } from '@/utils/filterHelper';
import { Trash2, Eye, FileText, X, ChevronDown } from 'lucide-vue-next';

const toast = useToastStore();
const confirm = useConfirmStore();

const props = defineProps({
  type: { type: String, default: '' }
});

const inquiries = ref([]);
const loading = ref(false);
const selectedInquiry = ref(null);
const pagination = ref(null);
const perPage = ref(15);
const filters = ref({ status: '', type: props.type || '' });

const columns = [
  { key: 'student', label: 'Student' },
  { key: 'contact', label: 'Contact' },
  { key: 'type', label: 'Type' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: 'Actions', align: 'right' },
];

const pageTitle = computed(() => {
  switch (filters.value.type) {
    case 'university_apply': return 'Student Inquiries';
    case 'air_ticket': return 'Air Ticket Bookings';
    case 'agent_application':
    case 'agent_applciation': return 'Agent Applications';
    case 'career_opportunity':
    case 'career_oppurtunity': return 'Career Opportunities';
    case 'consultation': return 'Consultation Requests';
    default: return 'Leads & Inquiries';
  }
});

const pageDescription = computed(() => {
  switch (filters.value.type) {
    case 'university_apply': return 'student university applications and inquiries';
    case 'air_ticket': return 'flight details and ticket booking requests';
    case 'agent_application':
    case 'agent_applciation': return 'applications submitted by agents';
    case 'career_opportunity':
    case 'career_oppurtunity': return 'career opportunities submitted by students/leads';
    case 'consultation': return 'consultation and counseling appointments';
    default: return 'all types of student inquiries and leads';
  }
});

const fetchInquiries = async (page = 1) => {
  saveFiltersState('inquiry_manager', { page, status: filters.value.status });
  await fetchWithCache({
    url: '/auth/admin/inquiries',
    params: { page, per_page: perPage.value, ...filters.value },
    loadingRef: loading,
    dataRef: inquiries,
    paginationRef: pagination,
    toast
  });
};

const handlePerPageChange = (newPerPage) => {
  perPage.value = newPerPage;
  fetchInquiries(1);
};

watch(() => props.type, (newType) => {
  filters.value.type = newType || '';
  restoreFiltersState('inquiry_manager', { status: '' });
  fetchInquiries(1);
}, { immediate: true });

const showInquiryDetails = (inquiry) => {
  selectedInquiry.value = { ...inquiry };
};

const saveNotes = async () => {
  try {
    await axios.put(`/auth/admin/inquiries/${selectedInquiry.value.id}`, {
      admin_notes: selectedInquiry.value.admin_notes,
      status: selectedInquiry.value.status
    });
    toast.success('Notes saved successfully.');
    selectedInquiry.value = null;
    await fetchInquiries(pagination.value?.current_page || 1);
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to save changes');
  }
};

const updateStatus = async (inquiry) => {
  try {
    await axios.put(`/auth/admin/inquiries/${inquiry.id}`, { status: inquiry.status });
    toast.success('Status updated.');
  } catch (error) {
    toast.error('Failed to update status.');
  }
};

const deleteInquiry = async (id) => {
  const confirmed = await confirm.ask({
    title: 'Delete Inquiry',
    message: 'Are you sure you want to delete this inquiry? This action cannot be undone.',
    confirmText: 'Delete',
    variant: 'danger',
  });
  if (!confirmed) return;
  try {
    await axios.delete(`/auth/admin/inquiries/${id}`);
    clearCache('/auth/admin/inquiries');
    toast.success('Inquiry deleted successfully.');
    await fetchInquiries(pagination.value?.current_page || 1);
  } catch (error) {
    toast.error('Failed to delete inquiry.');
  }
};

const statusStyles = (status) => {
  const map = {
    new: 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-900/10 dark:text-blue-500 dark:border-blue-900/20',
    contacted: 'bg-indigo-50 text-indigo-700 border-indigo-100 dark:bg-indigo-900/10 dark:text-indigo-500 dark:border-indigo-900/20',
    pending: 'bg-yellow-50 text-yellow-700 border-yellow-100 dark:bg-yellow-900/10 dark:text-yellow-500 dark:border-yellow-900/20',
    closed: 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/10 dark:text-emerald-500 dark:border-emerald-900/20',
  };
  return map[status] || 'bg-gray-50 text-gray-700 border-gray-100';
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit',
  });
};

const formatKey = (key) => key.replace(/_/g, ' ');

const formatValue = (value) => {
  if (value === null || value === undefined || value === '') return 'N/A';
  if (typeof value === 'object') {
    try { return JSON.stringify(value); } catch (e) { return String(value); }
  }
  return String(value);
};

const extractFilename = (url) => {
  if (!url) return 'file';
  try {
    const parts = url.split('/');
    return parts[parts.length - 1] || url;
  } catch (e) { return url; }
};

const downloadFile = async (url, filename) => {
  try {
    const res = await fetch(url, { credentials: 'same-origin' });
    if (!res.ok) throw new Error('Network response was not ok');
    const blob = await res.blob();
    const blobUrl = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = blobUrl;
    a.download = filename || 'file';
    document.body.appendChild(a);
    a.click();
    a.remove();
    window.URL.revokeObjectURL(blobUrl);
  } catch (e) {
    window.open(url, '_blank');
  }
};

onMounted(() => {
  const state = restoreFiltersState('inquiry_manager', { status: '', page: 1 });
  filters.value.status = state.status;
  setTimeout(() => fetchInquiries(state.page), 50);
});
</script>