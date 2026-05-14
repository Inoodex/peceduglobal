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
      <div class="bg-white dark:bg-[#1C252E] border border-gray-100 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-gray-50/50 dark:bg-[#151C24]/50 border-b border-gray-100 dark:border-gray-800">
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Student Name</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Created At</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr v-if="loading" v-for="i in 3" :key="i" class="animate-pulse">
                <td colspan="5" class="px-6 py-4"><div class="h-10 bg-gray-100 dark:bg-gray-800 rounded-xl w-full"></div></td>
              </tr>
              <tr v-else-if="filteredStudents.length === 0">
                <td colspan="5" class="px-6 py-12 text-center text-gray-500 italic">No students found.</td>
              </tr>
              <tr v-for="student in filteredStudents" :key="student.id" class="hover:bg-gray-50/80 dark:hover:bg-[#151C24] transition-colors group">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                      {{ student.full_name?.charAt(0) }}
                    </div>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ student.full_name }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ student.email }}</td>
                <td class="px-6 py-4">
                  <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-md bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 border border-blue-100 dark:border-blue-800">
                    {{ student.role }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ new Date(student.created_at).toLocaleDateString() }}</td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button @click="$router.push(`/dashboard/students/${student.id}/edit`)" class="p-2 text-gray-500 hover:text-primary hover:bg-primary/5 rounded-lg transition-colors"><Edit3 class="w-4 h-4" /></button>
                    <button @click="confirmDelete(student)" class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" :disabled="deleteLoading">
                      <Loader2 v-if="deleteLoading" class="w-4 h-4 animate-spin" />
                      <Trash2 v-else class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from '@/plugins/axios';
import { useToastStore } from '@/stores/toast';
import { useConfirmStore } from '@/stores/confirm';
import MainLayout from '@/layouts/MainLayout.vue';
import { Plus, Search, Edit3, Trash2, Loader2 } from 'lucide-vue-next';

const toast = useToastStore();
const confirm = useConfirmStore();
const students = ref([]);
const loading = ref(false);
const deleteLoading = ref(false);
const searchQuery = ref('');

const filteredStudents = computed(() => {
  const query = searchQuery.value.toLowerCase().trim();
  if (!query) return students.value;
  return students.value.filter(s => 
    s.full_name?.toLowerCase().includes(query) || 
    s.email?.toLowerCase().includes(query)
  );
});

const fetchStudents = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/auth/admin/students'); 
    students.value = response.data.data?.data || response.data.data || [];
  } catch (e) { 
    console.error(e); 
    toast.error('Failed to load students');
  } finally { 
    loading.value = false; 
  }
};

const confirmDelete = async (student) => {
  const ok = await confirm.ask({
    title: 'Delete Student?',
    message: `Are you sure you want to delete student '${student.full_name}'? All related application data will be affected.`
  });

  if (ok) {
    deleteLoading.value = true;
    try {
      await axios.delete(`/auth/admin/students/${student.id}`);
      students.value = students.value.filter(s => s.id !== student.id);
      toast.success('Student deleted successfully');
    } catch (e) { 
      toast.error('Error deleting student'); 
    } finally { 
      deleteLoading.value = false; 
    }
  }
};

onMounted(fetchStudents);
</script>