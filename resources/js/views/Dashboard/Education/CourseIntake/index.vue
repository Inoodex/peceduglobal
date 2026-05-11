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
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50 dark:bg-[#141A21] border-b border-gray-200 dark:border-gray-700/50">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Intake Name</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Course & University</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Deadlines</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700/50">
                <tr v-if="loading">
                  <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                    <div class="flex items-center justify-center gap-2"><Loader2 class="w-5 h-5 animate-spin" /> Loading...</div>
                  </td>
                </tr>
                <tr v-else-if="filteredIntakes.length === 0">
                  <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No intakes found</td>
                </tr>
                <tr v-for="intake in filteredIntakes" :key="intake.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                  <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ intake.intake_name }}</td>
                  <td class="px-6 py-4">
                    <div class="flex flex-col">
                      <span class="text-sm text-gray-900 dark:text-white">{{ intake.course?.name }}</span>
                      <span class="text-xs text-gray-500 dark:text-gray-400">{{ intake.university?.name }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex flex-col text-xs text-gray-500 dark:text-gray-400">
                      <span>Deadline: {{ intake.application_deadline }}</span>
                      <span>Starts: {{ intake.class_start_date || 'N/A' }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span :class="{
                      'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': intake.status === 'open',
                      'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': intake.status === 'upcoming',
                      'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': intake.status === 'closed',
                    }" class="px-2 py-0.5 rounded text-xs font-medium capitalize">
                      {{ intake.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                      <!-- UPDATED PATH: Changed intake-manager to course-intakes -->
                      <button @click="$router.push(`/dashboard/course-intakes/edit/${intake.id}`)" class="p-2 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors"><Pencil class="w-4 h-4" /></button>
                      <button @click="confirmDelete(intake)" class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"><Trash2 class="w-4 h-4" /></button>
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
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Intake</h3>
            </div>
            <p class="text-gray-600 dark:text-gray-400 mb-6">Are you sure you want to delete "<strong class="text-gray-900 dark:text-white">{{ deleteModal.intake?.intake_name }}</strong>"?</p>
            <div class="flex justify-end gap-3">
              <button @click="deleteModal.show = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">Cancel</button>
              <button @click="deleteIntake" :disabled="deleteModal.loading" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl transition-colors flex items-center gap-2">
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

  const intakes = ref([]);
  const loading = ref(false);
  const searchQuery = ref('');
  const deleteModal = ref({ show: false, intake: null, loading: false });

  const filteredIntakes = computed(() => {
    if (!searchQuery.value.trim()) return intakes.value;
    const query = searchQuery.value.toLowerCase();
    return intakes.value.filter(i =>
      i.intake_name.toLowerCase().includes(query) ||
      i.course?.name?.toLowerCase().includes(query)
    );
  });

  const fetchIntakes = async () => {
    loading.value = true;
    try {
      const response = await axios.get('/auth/admin/course-intakes');
      intakes.value = response.data.data || [];
    } catch (e) {
      console.error('Failed to load intakes', e);
    } finally {
      loading.value = false;
    }
  };

  const confirmDelete = (intake) => {
    deleteModal.value.intake = intake;
    deleteModal.value.show = true;
  };

  const deleteIntake = async () => {
    deleteModal.value.loading = true;
    try {
      await axios.delete(`/auth/admin/course-intakes/${deleteModal.value.intake.id}`);
      intakes.value = intakes.value.filter(i => i.id !== deleteModal.value.intake.id);
      deleteModal.value.show = false;
    } catch (e) {
      console.error('Delete failed', e);
    } finally {
      deleteModal.value.loading = false;
    }
  };

  onMounted(fetchIntakes);
  </script>
