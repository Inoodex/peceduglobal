<template>
  <MainLayout>
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Universities</h1>
          <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mt-1">
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-900 dark:text-white">Universities</span>
          </nav>
        </div>
        <button @click="$router.push('/dashboard/university-manager/create')" class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-hover text-white font-medium rounded-xl transition-colors">
          <Plus class="w-4 h-4" /> New University
        </button>
      </div>

      <!-- Filters Card -->
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-4 mb-4">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1 relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input v-model="searchQuery" type="text" placeholder="Search university by name or location..." class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl pl-10 pr-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
          </div>
          <button v-if="searchQuery" @click="searchQuery = ''; clearFiltersState('university_manager');" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors cursor-pointer">Clear</button>
        </div>
      </div>

      <!-- Universities Table -->
      <DataTable 
        :columns="columns" 
        :data="filteredUniversities" 
        :loading="loading"
        :pagination="pagination"
        @page-change="fetchUniversities"
        @per-page-change="handlePerPageChange"
      >
        <!-- University -->
        <template #cell(university)="{ item: university }">
          <div class="flex items-center gap-3">
            <div v-if="university.logo" class="w-10 h-10 rounded-lg overflow-hidden shrink-0 border border-gray-100 dark:border-gray-700">
              <img :src="university.logo" :alt="university.name" class="w-full h-full object-contain bg-white" />
            </div>
            <div v-else class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
              <School class="w-5 h-5 text-primary" />
            </div>
            <div>
              <p class="font-medium text-gray-900 dark:text-white">{{ university.name }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ university.location }}</p>
            </div>
          </div>
        </template>
        <!-- Country -->
        <template #cell(country)="{ item: university }">
          <span class="text-sm text-gray-600 dark:text-gray-400">{{ university.country?.name || 'N/A' }}</span>
        </template>
        <!-- Ranking -->
        <template #cell(ranking)="{ item: university }">
          <span class="text-sm font-medium text-gray-900 dark:text-white">#{{ university.ranking || 'N/A' }}</span>
        </template>
        <!-- Status -->
        <template #cell(status)="{ item: university }">
          <div class="flex flex-col gap-1">
            <span v-if="university.is_partner" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 w-fit">Partner</span>
            <span v-if="university.is_popular" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 w-fit">Popular</span>
          </div>
        </template>
        <!-- Actions -->
        <template #cell(actions)="{ item: university }">
          <div class="flex items-center justify-end gap-2">
            <button @click="$router.push(`/dashboard/university-manager/edit/${university.id}`)" class="p-2 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Edit"><Pencil class="w-4 h-4" /></button>
            <button @click="confirmDelete(university)" class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete"><Trash2 class="w-4 h-4" /></button>
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
import { saveFiltersState, restoreFiltersState, clearFiltersState } from '@/utils/filterHelper';
import { watch } from 'vue';
import {
  ChevronRight, Plus, Search, Loader2, Pencil, Trash2, AlertTriangle, School
} from 'lucide-vue-next';

const toast = useToastStore();
const confirm = useConfirmStore();

const universities = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const pagination = ref(null);
const perPage = ref(15);

const columns = [
  { key: 'university', label: 'University' },
  { key: 'country', label: 'Country' },
  { key: 'ranking', label: 'Ranking' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: 'Actions', align: 'right' }
];

const filteredUniversities = computed(() => {
  if (!searchQuery.value.trim()) return universities.value;
  const query = searchQuery.value.toLowerCase();
  return universities.value.filter(u =>
    u.name.toLowerCase().includes(query) ||
    (u.location && u.location.toLowerCase().includes(query)) ||
    (u.country?.name && u.country.name.toLowerCase().includes(query))
  );
});

const handlePerPageChange = (newPerPage) => {
  perPage.value = newPerPage;
  fetchUniversities(1);
};

let searchTimer = null;
watch(searchQuery, () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    saveFiltersState('university_manager', { page: 1, searchQuery: searchQuery.value });
    fetchUniversities(1);
  }, 400);
});

const fetchUniversities = async (page = 1) => {
  saveFiltersState('university_manager', {
    page,
    searchQuery: searchQuery.value
  });
  await fetchWithCache({
    url: '/auth/admin/universities',
    params: { page, per_page: perPage.value },
    loadingRef: loading,
    dataRef: universities,
    paginationRef: pagination,
    toast
  });
};

const confirmDelete = async (university) => {
  const confirmed = await confirm.ask({
    title: 'Delete University',
    message: `Are you sure you want to delete "${university.name}"? This action cannot be undone.`,
    confirmText: 'Delete',
    variant: 'danger',
  });
  if (!confirmed) return;

  try {
    await axios.delete(`/auth/admin/universities/${university.id}`);
    clearCache('/auth/admin/universities');
    toast.success('University deleted successfully.');
    fetchUniversities(1);
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to delete university.');
  }
};

onMounted(() => {
  const state = restoreFiltersState('university_manager', { searchQuery: '', page: 1 });
  searchQuery.value = state.searchQuery;
  // Use nextTick or simple setTimeout to ensure search watch does not immediately trigger fetch on mount
  setTimeout(() => {
    fetchUniversities(state.page);
  }, 50);
});
</script>
