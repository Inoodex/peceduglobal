<template>
    <MainLayout>
      <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Course Intakes</h1>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mt-1">
              <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
              <ChevronRight class="w-4 h-4" />
              <span class="text-gray-900 dark:text-white">Intakes</span>
            </nav>
          </div>
          <!-- UPDATED PATH: Changed intake-manager to course-intakes -->
          <button @click="$router.push('/dashboard/course-intakes/create')" class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-hover text-white font-medium rounded-xl transition-colors">
            <Plus class="w-4 h-4" /> New Intake
          </button>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-4 mb-4">
          <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1 relative">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input v-model="searchQuery" type="text" placeholder="Search by intake name or course..." class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl pl-10 pr-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
            </div>
          </div>
        </div>

        <!-- Table -->
        <DataTable 
          :columns="columns" 
          :data="filteredIntakes" 
          :loading="loading"
          :pagination="pagination"
          @page-change="fetchIntakes"
          @per-page-change="handlePerPageChange"
        >
          <!-- Intake Name -->
          <template #cell(intake_name)="{ item: intake }">
            <span class="font-medium text-gray-900 dark:text-white">{{ intake.intake_name }}</span>
          </template>
          <!-- Course & University -->
          <template #cell(course_university)="{ item: intake }">
            <div class="flex flex-col">
              <span class="text-sm text-gray-900 dark:text-white">{{ intake.course?.name }}</span>
              <span class="text-xs text-gray-500 dark:text-gray-400">{{ intake.university?.name }}</span>
            </div>
          </template>
          <!-- Deadlines -->
          <template #cell(deadlines)="{ item: intake }">
            <div class="flex flex-col text-xs text-gray-500 dark:text-gray-400">
              <span>Deadline: {{ intake.application_deadline }}</span>
              <span>Starts: {{ intake.class_start_date || 'N/A' }}</span>
            </div>
          </template>
          <!-- Status -->
          <template #cell(status)="{ item: intake }">
            <span :class="{
              'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': intake.status === 'open',
              'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': intake.status === 'upcoming',
              'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': intake.status === 'closed',
            }" class="px-2 py-0.5 rounded text-xs font-medium capitalize">
              {{ intake.status }}
            </span>
          </template>
          <!-- Actions -->
          <template #cell(actions)="{ item: intake }">
            <div class="flex items-center justify-end gap-2">
              <button @click="$router.push(`/dashboard/course-intakes/edit/${intake.id}`)" class="p-2 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors"><Pencil class="w-4 h-4" /></button>
              <button @click="confirmDelete(intake)" class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"><Trash2 class="w-4 h-4" /></button>
            </div>
          </template>
        </DataTable>
      </div>
    </MainLayout>
  </template>

  <script setup>
  import { ref, computed, onMounted } from 'vue';
  import axios from '@/plugins/axios';
  import MainLayout from '@/layouts/MainLayout.vue';
  import DataTable from '@/components/Table/DataTable.vue';
  import { useToastStore } from '@/stores/toast';
  import { useConfirmStore } from '@/stores/confirm';
  import { fetchWithCache, clearCache } from '@/utils/cacheHelper';
  import { ChevronRight, Plus, Search, Loader2, Pencil, Trash2, AlertTriangle } from 'lucide-vue-next';

  const toast = useToastStore();
  const confirm = useConfirmStore();

  const intakes = ref([]);
  const loading = ref(false);
  const searchQuery = ref('');
  const pagination = ref(null);
  const perPage = ref(15);

  const columns = [
    { key: 'intake_name', label: 'Intake Name' },
    { key: 'course_university', label: 'Course & University' },
    { key: 'deadlines', label: 'Deadlines' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: 'Actions', align: 'right' }
  ];

  const filteredIntakes = computed(() => {
    if (!searchQuery.value.trim()) return intakes.value;
    const query = searchQuery.value.toLowerCase();
    return intakes.value.filter(i =>
      i.intake_name.toLowerCase().includes(query) ||
      i.course?.name?.toLowerCase().includes(query)
    );
  });

  const handlePerPageChange = (newPerPage) => {
    perPage.value = newPerPage;
    fetchIntakes(1);
  };

  const fetchIntakes = async (page = 1) => {
    await fetchWithCache({
      url: '/auth/admin/course-intakes',
      params: { page, per_page: perPage.value },
      loadingRef: loading,
      dataRef: intakes,
      paginationRef: pagination,
      toast
    });
  };

  const confirmDelete = async (intake) => {
    const confirmed = await confirm.ask({
      title: 'Delete Intake',
      message: `Are you sure you want to delete "${intake.intake_name}"? This action cannot be undone.`,
      confirmText: 'Delete',
      variant: 'danger',
    });
    if (!confirmed) return;

    try {
      await axios.delete(`/auth/admin/course-intakes/${intake.id}`);
      clearCache('/auth/admin/course-intakes');
      toast.success('Course intake deleted successfully.');
      fetchIntakes(1);
    } catch (e) {
      toast.error(e.response?.data?.message || 'Failed to delete intake.');
    }
  };

  onMounted(() => fetchIntakes(1));
  </script>
