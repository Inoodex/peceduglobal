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

      <!-- Application Progress Tracker (hidden by default, shows on row click) -->
      <transition name="progress-slide">
        <div v-if="selectedApp" class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
          <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm">
                {{ selectedApp.student?.user?.full_name?.charAt(0) || 'S' }}
              </div>
              <div>
                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ selectedApp.student?.user?.full_name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">#{{ selectedApp.application_number }} · {{ selectedApp.university?.name }}</p>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <div class="text-right">
                <span :class="['px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider border shadow-sm', statusClass(selectedApp.status)]">
                  {{ statusLabel(selectedApp.status) }}
                </span>
                <p class="text-xs font-bold text-primary mt-1">{{ progressPercent(selectedApp.status) }}%</p>
              </div>
              <button
                @click="selectedApp = null"
                class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
                title="Close"
              >
                <X class="w-4 h-4" />
              </button>
            </div>
          </div>
          <div class="p-5">
            <ApplicationStepper :status="selectedApp.status" />
          </div>
        </div>
      </transition>

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
        <!-- Student (clickable) -->
        <template #cell(student)="{ item: app }">
          <div
            class="flex items-center gap-3 cursor-pointer select-none rounded-lg px-1 py-1 -mx-1 -my-1 transition-colors"
            :class="[
              selectedApp?.id === app.id
                ? 'bg-primary/5 ring-1 ring-primary/20'
                : 'hover:bg-gray-100 dark:hover:bg-[#141A21]/80'
            ]"
            @click="toggleProgress(app)"
          >
            <div class="w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-xs font-bold text-gray-600 dark:text-gray-400 shadow-sm">
              {{ app.student?.user?.full_name?.charAt(0) || 'S' }}
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ app.student?.user?.full_name }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ app.student?.user?.email }}</p>
            </div>
            <ChevronRight
              class="w-4 h-4 text-gray-300 dark:text-gray-600 transition-transform duration-200 ml-1"
              :class="{ 'rotate-90 text-primary': selectedApp?.id === app.id }"
            />
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
          <span :class="statusClass(app.status)" class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider border shadow-sm whitespace-nowrap">
            {{ statusLabel(app.status) }}
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
import ApplicationStepper from '@/components/ApplicationStepper.vue';
import { fetchWithCache, clearCache } from '@/utils/cacheHelper';
import { Plus, Edit3, Trash2, ChevronRight, X } from 'lucide-vue-next';
import {
  TOTAL_STEPS, getStepIndex, getProgressPercent, getStatusLabel,
  getStatusBadgeClass,
} from '@/utils/applicationStatuses';

const router = useRouter();
const toast = useToastStore();
const confirm = useConfirmStore();
const authStore = useAuthStore();

const isStudent = computed(() => authStore.user?.role === 'student');

const applications = ref([]);
const loading = ref(false);
const pagination = ref(null);
const perPage = ref(15);
const searchQuery = ref('');
const selectedApp = ref(null);

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
  { label: 'In Progress', value: 0 },
  { label: 'Offer Letters', value: 0 },
  { label: 'Enrolled', value: 0 },
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
  const inWorkflow = (a) => getStepIndex(a.status) >= 0;
  stats.value[0].value = applications.value.length;
  stats.value[1].value = applications.value.filter(inWorkflow).length;
  stats.value[2].value = applications.value.filter(a => getStepIndex(a.status) >= getStepIndex('offer_letter')).length;
  stats.value[3].value = applications.value.filter(a => a.status === 'enrolled').length;
};

const toggleProgress = (app) => {
  selectedApp.value = selectedApp.value?.id === app.id ? null : app;
};

const progressPercent = (status) => getProgressPercent(status);
const statusLabel = (status) => getStatusLabel(status);
const statusClass = (status) => getStatusBadgeClass(status);

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
      if (selectedApp.value?.id === app.id) {
        selectedApp.value = null;
      }
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

<style scoped>
.progress-slide-enter-active,
.progress-slide-leave-active {
  transition: all 0.25s ease-out;
}
.progress-slide-enter-from,
.progress-slide-leave-to {
  opacity: 0;
  transform: translateY(-8px);
  max-height: 0;
  margin-top: 0;
}
.progress-slide-enter-to,
.progress-slide-leave-from {
  opacity: 1;
  transform: translateY(0);
  max-height: 500px;
}
</style>
