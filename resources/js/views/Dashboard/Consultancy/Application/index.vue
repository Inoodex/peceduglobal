<template>
  <MainLayout>
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Application List</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">Manage and track all student applications globally.</p>
        </div>
        <button
          v-if="!isStudent"
          class="px-4 py-2 bg-primary text-white rounded-xl text-sm font-semibold hover:shadow-lg hover:shadow-primary/30 transition-all flex items-center gap-2"
          @click="$router.push('/dashboard/applications/create')"
        >
          <Plus class="w-4 h-4" /> Add Application
        </button>
      </div>

      <!-- Stats Quick Overview -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div v-for="stat in stats" :key="stat.label" class="bg-white dark:bg-[#1C252E] p-4 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ stat.label }}</p>
          <p class="text-2xl font-bold mt-1 dark:text-white">{{ stat.value }}</p>
        </div>
      </div>

      <!-- Table Container -->
      <DataTable 
        :columns="columns" 
        :data="filteredApplications" 
        :loading="loading"
        :pagination="pagination"
        @page-change="loadApplications"
        @per-page-change="handlePerPageChange"
      >
        <!-- App Number -->
        <template #cell(app_number)="{ item: app }">
          <span class="text-sm font-bold text-primary bg-primary/5 px-2 py-1 rounded-lg">#{{ app.application_number }}</span>
        </template>
        <!-- Student -->
        <template #cell(student)="{ item: app }">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-xs font-bold text-gray-600 dark:text-gray-400 shadow-sm">
              {{ app.student?.user?.full_name?.charAt(0) || 'S' }}
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-primary transition-colors">{{ app.student?.user?.full_name }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ app.student?.user?.email }}</p>
            </div>
          </div>
        </template>
        <!-- University & Course -->
        <template #cell(university_course)="{ item: app }">
          <p class="text-sm font-medium text-gray-900 dark:text-white">{{ app.university?.name }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1">{{ app.course?.name || app.course_name }}</p>
        </template>
        <!-- Country -->
        <template #cell(country)="{ item: app }">
          <div class="flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400">
            <span class="w-1.5 h-1.5 rounded-full bg-primary/40"></span>
            {{ app.country?.name || 'N/A' }}
          </div>
        </template>
        <!-- Status -->
        <template #cell(status)="{ item: app }">
          <span :class="statusClass(app.status)" class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider border shadow-sm">
            {{ app.status?.replace('_', ' ') }}
          </span>
        </template>
        <!-- Actions -->
        <template #cell(actions)="{ item: app }">
          <div class="flex items-center justify-end gap-2">
            <button @click="edit(app)" class="p-2 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Edit"><Edit3 class="w-4 h-4" /></button>
            <button @click="confirmDelete(app)" class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete"><Trash2 class="w-4 h-4" /></button>
          </div>
        </template>
      </DataTable>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from '@/plugins/axios';
import { useToastStore } from '@/stores/toast';
import { useConfirmStore } from '@/stores/confirm';
import { useAuthStore } from '@/stores/auth';
import MainLayout from '@/layouts/MainLayout.vue';
import DataTable from '@/components/Table/DataTable.vue';
import { fetchWithCache, clearCache } from '@/utils/cacheHelper';
import { Plus, Edit3, Trash2, ChevronRight, FileText, Loader2 } from 'lucide-vue-next';

const router = useRouter();
const toast = useToastStore();
const confirm = useConfirmStore();
const authStore = useAuthStore();

const isStudent = computed(() => authStore.user?.role === 'student');

const applications = ref([]);
const loading = ref(false);
const deleteLoading = ref(false);
const pagination = ref(null);
const perPage = ref(15);
const searchQuery = ref('');

const columns = computed(() => {
  const cols = [
    { key: 'app_number', label: 'App Number' },
    { key: 'student', label: 'Student' },
    { key: 'university_course', label: 'University & Course' },
    { key: 'country', label: 'Country' },
    { key: 'status', label: 'Status' }
  ];
  if (!isStudent.value) {
    cols.push({ key: 'actions', label: 'Actions', align: 'right' });
  }
  return cols;
});

const filteredApplications = computed(() => {
  if (!searchQuery.value.trim()) return applications.value;
  const query = searchQuery.value.toLowerCase();
  return applications.value.filter(app => 
    app.application_number?.toLowerCase().includes(query) ||
    app.student?.user?.full_name?.toLowerCase().includes(query) ||
    app.university?.name?.toLowerCase().includes(query)
  );
});

const stats = ref([
  { label: 'Total Apps', value: 0 },
  { label: 'Pending', value: 0 },
  { label: 'Offer Letters', value: 0 },
  { label: 'Visa Process', value: 0 },
]);

const handlePerPageChange = (newPerPage) => {
  perPage.value = newPerPage;
  loadApplications(1);
};

const loadApplications = async (page = 1) => {
  const url = isStudent.value ? '/auth/student/applications' : '/auth/admin/applications';
  await fetchWithCache({
    url,
    params: { page, per_page: perPage.value },
    loadingRef: loading,
    dataRef: applications,
    paginationRef: pagination,
    toast
  });
  updateStats();
};

const updateStats = () => {
  stats.value[0].value = applications.value.length;
  stats.value[1].value = applications.value.filter(a => a.status === 'pending').length;
  stats.value[2].value = applications.value.filter(a => a.status === 'offer_letter').length;
  stats.value[3].value = applications.value.filter(a => a.status === 'visa_process').length;
};

const statusClass = (status) => {
  const map = {
    pending: 'bg-yellow-50 text-yellow-700 border-yellow-100 dark:bg-yellow-900/10 dark:text-yellow-500 dark:border-yellow-900/20',
    document_review: 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-900/10 dark:text-blue-500 dark:border-blue-900/20',
    university_submitted: 'bg-indigo-50 text-indigo-700 border-indigo-100 dark:bg-indigo-900/10 dark:text-indigo-500 dark:border-indigo-900/20',
    offer_letter: 'bg-green-50 text-green-700 border-green-100 dark:bg-green-900/10 dark:text-green-500 dark:border-green-900/20',
    visa_process: 'bg-purple-50 text-purple-700 border-purple-100 dark:bg-purple-900/10 dark:text-purple-500 dark:border-purple-900/20',
    completed: 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/10 dark:text-emerald-500 dark:border-emerald-900/20',
    rejected: 'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/10 dark:text-red-500 dark:border-red-900/20',
  };
  return map[status] || 'bg-gray-50 text-gray-700 border-gray-100';
};

const confirmDelete = async (app) => {
  const ok = await confirm.ask({
    title: 'Delete Application?',
    message: `Are you sure you want to delete application #${app.application_number}? This action cannot be undone.`
  });

  if (ok) {
    try {
      await axios.delete(`/auth/admin/applications/${app.id}`);
      clearCache('/auth/admin/applications');
      clearCache('/auth/student/applications');
      toast.success('Application deleted successfully');
      loadApplications(1);
    } catch (error) {
      toast.error('Failed to delete application');
    }
  }
};

const edit = (app) => {
  router.push(`/dashboard/applications/${app.id}/edit`);
};

onMounted(loadApplications);
</script>
