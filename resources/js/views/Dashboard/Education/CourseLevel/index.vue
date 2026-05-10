<template>
  <MainLayout>
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Course Levels</h1>
          <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mt-1">
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-900 dark:text-white">Course Levels</span>
          </nav>
        </div>
        <button @click="openModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-hover text-white font-medium rounded-xl transition-colors shadow-lg shadow-primary/20">
          <Plus class="w-4 h-4" /> Add Level
        </button>
      </div>

      <!-- Filters Card -->
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-4 mb-4 shadow-sm">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1 relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input v-model="searchQuery" type="text" placeholder="Search levels..." class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl pl-10 pr-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
          </div>
          <button v-if="searchQuery" @click="searchQuery = ''" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Clear</button>
        </div>
      </div>

      <!-- Levels Table -->
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-[#141A21] border-b border-gray-200 dark:border-gray-700/50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Level Name</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Slug</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700/50">
              <tr v-if="loading" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <td colspan="3" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                  <div class="flex items-center justify-center gap-2"><Loader2 class="w-5 h-5 animate-spin" /> Loading levels...</div>
                </td>
              </tr>
              <tr v-else-if="filteredLevels.length === 0" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <td colspan="3" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No levels found</td>
              </tr>
              <tr v-for="level in filteredLevels" :key="level.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                      <BookOpen class="w-5 h-5 text-primary" />
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">{{ level.name }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="text-sm text-gray-500 dark:text-gray-400">{{ level.slug }}</span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button @click="openModal(level)" class="p-2 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Edit"><Pencil class="w-4 h-4" /></button>
                    <button @click="confirmDelete(level)" class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete"><Trash2 class="w-4 h-4" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700/50 flex items-center justify-between">
          <p class="text-sm text-gray-500 dark:text-gray-400">Showing {{ filteredLevels.length }} levels</p>
        </div>
      </div>

      <!-- Add/Edit Modal -->
      <div v-if="modal.show" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl border border-gray-200 dark:border-gray-700/50">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ modal.editId ? 'Edit Level' : 'Add New Level' }}</h3>
            <button @click="modal.show = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
              <X class="w-5 h-5" />
            </button>
          </div>
          <form @submit.prevent="saveLevel">
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Level Name <span class="text-red-500">*</span></label>
              <input v-model="modal.form.name" type="text" placeholder="e.g. Undergraduate" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" required />
              <p v-if="modal.error" class="text-xs text-red-500 mt-1">{{ modal.error }}</p>
            </div>
            <div class="flex justify-end gap-3">
              <button type="button" @click="modal.show = false" class="px-6 py-2.5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl font-medium transition-colors">Cancel</button>
              <button type="submit" :disabled="modal.saving" class="px-6 py-2.5 bg-primary hover:bg-primary-hover text-white rounded-xl font-bold transition-colors flex items-center gap-2 shadow-lg shadow-primary/20 disabled:opacity-50">
                <Loader2 v-if="modal.saving" class="w-4 h-4 animate-spin" />
                {{ modal.saving ? 'Saving...' : (modal.editId ? 'Update Level' : 'Save Level') }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Delete Modal -->
      <div v-if="deleteModal.show" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl border border-gray-200 dark:border-gray-700/50">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
              <AlertTriangle class="w-6 h-6 text-red-600 dark:text-red-400" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Level</h3>
          </div>
          <p class="text-gray-600 dark:text-gray-400 mb-6">Are you sure you want to delete "<strong class="text-gray-900 dark:text-white">{{ deleteModal.level?.name }}</strong>"? This action cannot be undone.</p>
          <div class="flex justify-end gap-3">
            <button @click="deleteModal.show = false" class="px-6 py-2.5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl font-medium transition-colors">Cancel</button>
            <button @click="deleteLevel" :disabled="deleteModal.loading" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-bold transition-colors flex items-center gap-2 shadow-lg shadow-red-600/20">
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
  ChevronRight, Plus, Loader2, Pencil, Trash2, AlertTriangle, Search, BookOpen, X
} from 'lucide-vue-next';

const levels = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const modal = ref({ show: false, editId: null, form: { name: '' }, saving: false, error: '' });
const deleteModal = ref({ show: false, level: null, loading: false });

const filteredLevels = computed(() => {
  if (!searchQuery.value.trim()) return levels.value;
  const query = searchQuery.value.toLowerCase();
  return levels.value.filter(l => l.name.toLowerCase().includes(query) || l.slug.toLowerCase().includes(query));
});

const fetchLevels = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/auth/admin/course-levels');
    levels.value = response.data.data || [];
  } catch (e) {
    console.error('Failed to load levels', e);
  } finally {
    loading.value = false;
  }
};

const openModal = (level = null) => {
  modal.value.editId = level ? level.id : null;
  modal.value.form.name = level ? level.name : '';
  modal.value.error = '';
  modal.value.show = true;
};

const saveLevel = async () => {
  modal.value.saving = true;
  modal.value.error = '';
  try {
    if (modal.value.editId) {
      await axios.put(`/auth/admin/course-levels/${modal.value.editId}`, modal.value.form);
    } else {
      await axios.post('/auth/admin/course-levels', modal.value.form);
    }
    fetchLevels();
    modal.value.show = false;
  } catch (e) {
    modal.value.error = e.response?.data?.message || 'Something went wrong';
  } finally {
    modal.value.saving = false;
  }
};

const confirmDelete = (level) => {
  deleteModal.value.level = level;
  deleteModal.value.show = true;
};

const deleteLevel = async () => {
  deleteModal.value.loading = true;
  try {
    await axios.delete(`/auth/admin/course-levels/${deleteModal.value.level.id}`);
    fetchLevels();
    deleteModal.value.show = false;
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to delete');
  } finally {
    deleteModal.value.loading = false;
  }
};

onMounted(fetchLevels);
</script>
