<template>
  <MainLayout>
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Student Management</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">View and manage all registered students.</p>
        </div>
        <button
          class="px-4 py-2 bg-primary text-white rounded-xl text-sm font-semibold hover:shadow-lg hover:shadow-primary/30 transition-all flex items-center gap-2"
          @click="$router.push('/dashboard/students/create')"
        >
          <Plus class="w-4 h-4" /> Add Student
        </button>
      </div>

      <!-- Search & Filters -->
      <div class="bg-white dark:bg-[#1C252E] p-4 rounded-2xl border border-gray-100 dark:border-gray-800 flex flex-wrap gap-4 items-center">
        <div class="relative flex-1 min-w-[200px]">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Search by name or email..." 
            class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-[#151C24] border border-gray-100 dark:border-gray-800 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 transition-all"
          >
        </div>
      </div>

      <!-- Table Container -->
      <DataTable 
        :columns="columns" 
        :data="filteredStudents" 
        :loading="loading"
        :pagination="pagination"
        @page-change="fetchStudents"
        @per-page-change="handlePerPageChange"
      >
        <!-- Student Name -->
        <template #cell(student)="{ item: student }">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
              {{ student.full_name?.charAt(0) }}
            </div>
            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ student.full_name }}</span>
          </div>
        </template>
        <!-- Email -->
        <template #cell(email)="{ item: student }">
          <span class="text-sm text-gray-600 dark:text-gray-400">{{ student.email }}</span>
        </template>
        <!-- Role -->
        <template #cell(role)="{ item: student }">
          <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-md bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 border border-blue-100 dark:border-blue-800">
            {{ student.role }}
          </span>
        </template>
         <!-- Created By -->
        <template #cell(created_by)="{ item: student }">
          <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 text-[10px] font-bold">
              {{ student.created_by?.charAt(0) }}
            </div>
            <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">{{ student.created_by || 'Admin' }}</span>
          </div>
        </template>
        <!-- Created At -->
        <template #cell(created_at)="{ item: student }">
          <span class="text-sm text-gray-500">{{ new Date(student.created_at).toLocaleDateString() }}</span>
        </template>
        <!-- Actions -->
        <template #cell(actions)="{ item: student }">
          <div class="flex items-center justify-end gap-1">
            <button @click="$router.push(`/dashboard/students/${student.id}/edit`)" class="p-2 text-gray-500 hover:text-primary hover:bg-primary/5 rounded-lg transition-colors"><Edit3 class="w-4 h-4" /></button>
            <button @click="confirmDelete(student)" class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"><Trash2 class="w-4 h-4" /></button>
          </div>
        </template>
      </DataTable>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from '@/plugins/axios';
import { useToastStore } from '@/stores/toast';
import { useConfirmStore } from '@/stores/confirm';
import MainLayout from '@/layouts/MainLayout.vue';
import DataTable from '@/components/Table/DataTable.vue';
import { fetchWithCache, clearCache } from '@/utils/cacheHelper';
import { saveFiltersState, restoreFiltersState, clearFiltersState } from '@/utils/filterHelper';
import { Plus, Search, Edit3, Trash2, Loader2 } from 'lucide-vue-next';

const toast = useToastStore();
const confirm = useConfirmStore();
const students = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const pagination = ref(null);
const perPage = ref(15);

const columns = [
  { key: 'student', label: 'Student Name' },
  { key: 'email', label: 'Email' },
  { key: 'role', label: 'Role' },
  { key: 'created_by', label: 'Created By' },
  { key: 'created_at', label: 'Created At' },
  { key: 'actions', label: 'Actions', align: 'right' }
];

const filteredStudents = computed(() => {
  const query = searchQuery.value.toLowerCase().trim();
  if (!query) return students.value;
  return students.value.filter(s => 
    s.full_name?.toLowerCase().includes(query) || 
    s.email?.toLowerCase().includes(query)
  );
});

const handlePerPageChange = (newPerPage) => {
  perPage.value = newPerPage;
  fetchStudents(1);
};

let searchTimer = null;
watch(searchQuery, () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    saveFiltersState('student_manager', { page: 1, searchQuery: searchQuery.value });
    fetchStudents(1);
  }, 400);
});

const fetchStudents = async (page = 1) => {
  saveFiltersState('student_manager', {
    page,
    searchQuery: searchQuery.value
  });
  await fetchWithCache({
    url: '/auth/admin/students',
    params: { page, per_page: perPage.value },
    loadingRef: loading,
    dataRef: students,
    paginationRef: pagination,
    toast
  });
};

const confirmDelete = async (student) => {
  const ok = await confirm.ask({
    title: 'Delete Student?',
    message: `Are you sure you want to delete student '${student.full_name}'? All related application data will be affected.`
  });

  if (ok) {
    try {
      await axios.delete(`/auth/admin/students/${student.id}`);
      clearCache('/auth/admin/students');
      toast.success('Student deleted successfully');
      fetchStudents(1);
    } catch (e) { 
      toast.error('Error deleting student'); 
    }
  }
};

onMounted(() => {
  const state = restoreFiltersState('student_manager', { searchQuery: '', page: 1 });
  searchQuery.value = state.searchQuery;
  setTimeout(() => fetchStudents(state.page), 50);
});
</script>