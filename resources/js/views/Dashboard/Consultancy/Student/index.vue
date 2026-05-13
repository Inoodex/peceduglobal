<template>
  <MainLayout>
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Students Management</h1>
          <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mt-1">
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span
            ><ChevronRight class="w-4 h-4" />
            <span class="text-gray-900 dark:text-white">Students</span>
          </nav>
        </div>
        <button @click="$router.push('/dashboard/students/create')" class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-hover text-white font-medium rounded-xl transition-colors">
          <Plus class="w-4 h-4" /> New Student
        </button>
      </div>

      <!-- Filters Card -->
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-4 mb-4">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1 relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input v-model="searchQuery" type="text" placeholder="Search by name, email or phone..." class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl pl-10 pr-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
          </div>
          <button v-if="searchQuery" @click="searchQuery = ''" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Clear</button>
        </div>
      </div>

      <!-- Students Table -->
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-[#141A21] border-b border-gray-200 dark:border-gray-700/50">
              <tr class="text-left">
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Student</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Contact Info</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700/50">
              <tr v-if="loading" class="text-center">
                <td colspan="4" class="px-6 py-12 text-gray-500 dark:text-gray-400">
                  <div class="flex items-center justify-center gap-2"><Loader2 class="w-5 h-5 animate-spin" /> Loading students...</div>
                </td>
              </tr>
              <tr v-else-if="filteredStudents.length === 0" class="text-center">
                <td colspan="4" class="px-6 py-12 text-gray-500 dark:text-gray-400">No students found</td>
              </tr>
              <tr v-for="student in filteredStudents" :key="student.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                      <span class="text-primary font-bold text-xs">{{ student.full_name?.charAt(0) || 'S' }}</span>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900 dark:text-white">{{ student.full_name }}</p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">ID: #{{ student.id }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-600 dark:text-gray-400">
                    <p>{{ student.email }}</p>
                    <p class="text-xs">{{ student.phone }}</p>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                    {{ student.role || 'Student' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button @click="$router.push(`/dashboard/students/edit/${student.id}`)" class="p-2 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors"><Pencil class="w-4 h-4" /></button>
                    <button @click="confirmDelete(student)" class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"><Trash2 class="w-4 h-4" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Delete Modal -->
      <div v-if="deleteModal.show" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
              <AlertTriangle class="w-6 h-6 text-red-600 dark:text-red-400" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Student</h3>
          </div>
          <p class="text-gray-600 dark:text-gray-400 mb-6">Are you sure you want to delete <strong class="text-gray-900 dark:text-white">{{ deleteModal.student?.full_name }}</strong>?</p>
          <div class="flex justify-end gap-3">
            <button @click="deleteModal.show = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">Cancel</button>
            <button @click="deleteStudent" :disabled="deleteModal.loading" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl transition-colors flex items-center gap-2">
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
import { ChevronRight, Plus, Search, Loader2, Pencil, Trash2, AlertTriangle } from 'lucide-vue-next';

const students = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const deleteModal = ref({ show: false, student: null, loading: false });

const filteredStudents = computed(() => {
  const query = searchQuery.value.toLowerCase().trim();
  if (!query) return students.value;
  return students.value.filter(s => 
    (s.full_name && s.full_name.toLowerCase().includes(query)) || 
    (s.email && s.email.toLowerCase().includes(query)) || 
    (s.phone && s.phone.includes(query))
  );
});

const fetchStudents = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/auth/admin/students'); 
    students.value = response.data.data?.data || response.data.data || [];
  } catch (e) { console.error(e); } finally { loading.value = false; }
};

const confirmDelete = (student) => {
  deleteModal.value.student = student;
  deleteModal.value.show = true;
};

const deleteStudent = async () => {
  deleteModal.value.loading = true;
  try {
    await axios.delete(`/auth/admin/students/${deleteModal.value.student.id}`);
    students.value = students.value.filter(s => s.id !== deleteModal.value.student.id);
    deleteModal.value.show = false;
  } catch (e) { alert('Error deleting student'); } finally { deleteModal.value.loading = false; }
};

onMounted(fetchStudents);
</script>