<template>
  <MainLayout>
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Courses</h1>
          <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mt-1">
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-900 dark:text-white">Courses</span>
          </nav>
        </div>
        <button @click="$router.push('/dashboard/course-manager/create')" class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-hover text-white font-medium rounded-xl transition-colors">
          <Plus class="w-4 h-4" /> New Course
        </button>
      </div>

      <!-- Filters Card -->
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-4 mb-4">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1 relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input v-model="searchQuery" type="text" placeholder="Search course by name, university or level..." class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl pl-10 pr-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
          </div>
          <button v-if="searchQuery" @click="searchQuery = ''" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Clear</button>
        </div>
      </div>

      <!-- Courses Table -->
      <DataTable 
        :columns="columns" 
        :data="filteredCourses" 
        :loading="loading"
        :pagination="pagination"
        @page-change="fetchCourses"
        @per-page-change="handlePerPageChange"
      >
        <!-- Course -->
        <template #cell(course)="{ item: course }">
          <div>
            <p class="font-medium text-gray-900 dark:text-white">{{ course.name }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ course.duration }}</p>
          </div>
        </template>
        <!-- University -->
        <template #cell(university)="{ item: course }">
          <div class="flex items-center gap-2">
            <span class="text-sm text-gray-600 dark:text-gray-400">{{ course.university?.name || 'N/A' }}</span>
            <span class="text-xs px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-500">{{ course.country?.name }}</span>
          </div>
        </template>
        <!-- Level & Intake -->
        <template #cell(level_intake)="{ item: course }">
          <div class="flex flex-col">
            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ course.course_level?.name || 'N/A' }}</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ course.intake || 'N/A' }}</span>
          </div>
        </template>
        <!-- Status -->
        <template #cell(status)="{ item: course }">
          <span v-if="course.is_popular" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">Popular</span>
          <span v-else class="text-xs text-gray-400">Standard</span>
        </template>
        <!-- Actions -->
        <template #cell(actions)="{ item: course }">
          <div class="flex items-center justify-end gap-2">
            <button @click="$router.push(`/dashboard/course-manager/edit/${course.id}`)" class="p-2 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Edit"><Pencil class="w-4 h-4" /></button>
            <button @click="confirmDelete(course)" class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete"><Trash2 class="w-4 h-4" /></button>
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
import {
  ChevronRight, Plus, Search, Loader2, Pencil, Trash2, AlertTriangle, BookOpen
} from 'lucide-vue-next';

const toast = useToastStore();
const confirm = useConfirmStore();

const courses = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const pagination = ref(null);
const perPage = ref(15);

const columns = [
  { key: 'course', label: 'Course' },
  { key: 'university', label: 'University' },
  { key: 'level_intake', label: 'Level & Intake' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: 'Actions', align: 'right' }
];

const filteredCourses = computed(() => {
  if (!searchQuery.value.trim()) return courses.value;
  const query = searchQuery.value.toLowerCase();
  return courses.value.filter(c =>
    c.name.toLowerCase().includes(query) ||
    (c.level && c.level.toLowerCase().includes(query)) ||
    (c.university?.name && c.university.name.toLowerCase().includes(query))
  );
});

const handlePerPageChange = (newPerPage) => {
  perPage.value = newPerPage;
  fetchCourses(1);
};

const fetchCourses = async (page = 1) => {
  await fetchWithCache({
    url: '/auth/admin/courses',
    params: { page, per_page: perPage.value },
    loadingRef: loading,
    dataRef: courses,
    paginationRef: pagination,
    toast
  });
};

const confirmDelete = async (course) => {
  const confirmed = await confirm.ask({
    title: 'Delete Course',
    message: `Are you sure you want to delete "${course.name}"? This action cannot be undone.`,
    confirmText: 'Delete',
    variant: 'danger',
  });
  if (!confirmed) return;

  try {
    await axios.delete(`/auth/admin/courses/${course.id}`);
    clearCache('/auth/admin/courses');
    toast.success('Course deleted successfully.');
    fetchCourses(1);
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to delete course.');
  }
};

onMounted(() => {
  fetchCourses(1);
});
</script>
