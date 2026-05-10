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
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-[#141A21] border-b border-gray-200 dark:border-gray-700/50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Course</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">University</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Level & Intake</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700/50">
              <tr v-if="loading" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                  <div class="flex items-center justify-center gap-2"><Loader2 class="w-5 h-5 animate-spin" /> Loading courses...</div>
                </td>
              </tr>
              <tr v-else-if="filteredCourses.length === 0" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No courses found</td>
              </tr>
              <tr v-for="course in filteredCourses" :key="course.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <td class="px-6 py-4">
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ course.name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ course.duration }}</p>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ course.university?.name || 'N/A' }}</span>
                    <span class="text-xs px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-500">{{ course.country?.name }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex flex-col">
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ course.course_level?.name || 'N/A' }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ course.intake || 'N/A' }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span v-if="course.is_popular" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">Popular</span>
                  <span v-else class="text-xs text-gray-400">Standard</span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button @click="$router.push(`/dashboard/course-manager/edit/${course.id}`)" class="p-2 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Edit"><Pencil class="w-4 h-4" /></button>
                    <button @click="confirmDelete(course)" class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete"><Trash2 class="w-4 h-4" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700/50 flex items-center justify-between">
          <p class="text-sm text-gray-500 dark:text-gray-400">Showing {{ filteredCourses.length }} courses</p>
        </div>
      </div>

      <!-- Delete Modal -->
      <div v-if="deleteModal.show" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
              <AlertTriangle class="w-6 h-6 text-red-600 dark:text-red-400" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Course</h3>
          </div>
          <p class="text-gray-600 dark:text-gray-400 mb-6">Are you sure you want to delete "<strong class="text-gray-900 dark:text-white">{{ deleteModal.course?.name }}</strong>"? This action cannot be undone.</p>
          <div class="flex justify-end gap-3">
            <button @click="deleteModal.show = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">Cancel</button>
            <button @click="deleteCourse" :disabled="deleteModal.loading" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl transition-colors flex items-center gap-2">
              <Loader2 v-if="deleteModal.loading" class="w-4 h-4 animate-spin" />
              <Trash2 v-else class="w-4 h-4" />
              {{ deleteModal.loading ? 'Deleting...' : 'Delete' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import {
  ChevronRight, Plus, Search, Loader2, Pencil, Trash2, AlertTriangle, BookOpen
} from 'lucide-vue-next';

const courses = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const deleteModal = ref({ show: false, course: null, loading: false });

const filteredCourses = computed(() => {
  if (!searchQuery.value.trim()) return courses.value;
  const query = searchQuery.value.toLowerCase();
  return courses.value.filter(c =>
    c.name.toLowerCase().includes(query) ||
    (c.level && c.level.toLowerCase().includes(query)) ||
    (c.university?.name && c.university.name.toLowerCase().includes(query))
  );
});

const fetchCourses = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/auth/admin/courses');
    courses.value = response.data.data?.data || response.data.data || [];
  } catch (e) {
    console.error('Failed to load courses', e);
  } finally {
    loading.value = false;
  }
};

const confirmDelete = (course) => {
  deleteModal.value.course = course;
  deleteModal.value.show = true;
};

const deleteCourse = async () => {
  deleteModal.value.loading = true;
  try {
    await axios.delete(`/auth/admin/courses/${deleteModal.value.course.id}`);
    courses.value = courses.value.filter(c => c.id !== deleteModal.value.course.id);
    deleteModal.value.show = false;
    deleteModal.value.course = null;
  } catch (e) {
    console.error('Failed to delete course', e);
  } finally {
    deleteModal.value.loading = false;
  }
};

onMounted(fetchCourses);
</script>
