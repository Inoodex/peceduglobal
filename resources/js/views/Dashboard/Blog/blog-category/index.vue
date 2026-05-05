<template>
  <MainLayout>
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Blog Categories</h1>
          <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mt-1">
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
            <ChevronRight class="w-4 h-4" />
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/blog-post')">Blog</span>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-900 dark:text-white">Categories</span>
          </nav>
        </div>
        <button
          @click="$router.push('/blog-category-create')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-hover text-white font-medium rounded-xl transition-colors"
        >
          <Plus class="w-4 h-4" />
          New category
        </button>
      </div>

      <!-- Filters Card -->
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-4 mb-4">
        <div class="flex flex-col sm:flex-row gap-4">
          <!-- Search -->
          <div class="flex-1 relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search category..."
              class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl pl-10 pr-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            />
          </div>

          <!-- Status Filter -->
          <select
            v-model="statusFilter"
            class="bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          >
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>

          <!-- Clear Filters -->
          <button
            v-if="searchQuery || statusFilter"
            @click="clearFilters"
            class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
          >
            Clear
          </button>
        </div>
      </div>

      <!-- Categories Table -->
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-[#141A21] border-b border-gray-200 dark:border-gray-700/50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Slug</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Posts</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Order</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700/50">
              <tr v-if="loading" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                  <div class="flex items-center justify-center gap-2">
                    <Loader2 class="w-5 h-5 animate-spin" />
                    Loading categories...
                  </div>
                </td>
              </tr>
              <tr v-else-if="filteredCategories.length === 0" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                  No categories found
                </td>
              </tr>
              <tr v-for="category in filteredCategories" :key="category.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <!-- Category -->
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-primary/20 to-primary/10 flex items-center justify-center">
                      <FolderOpen class="w-5 h-5 text-primary" />
                    </div>
                    <div>
                      <p class="font-medium text-gray-900 dark:text-white">{{ category.name }}</p>
                      <p v-if="category.description" class="text-sm text-gray-500 dark:text-gray-400 line-clamp-1">{{ category.description }}</p>
                    </div>
                  </div>
                </td>
                <!-- Slug -->
                <td class="px-6 py-4">
                  <span class="text-sm text-gray-600 dark:text-gray-400 font-mono">{{ category.slug }}</span>
                </td>
                <!-- Status -->
                <td class="px-6 py-4">
                  <span
                    :class="[
                      'px-2.5 py-1 rounded-full text-xs font-medium',
                      category.status
                        ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                        : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400'
                    ]"
                  >
                    {{ category.status ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <!-- Posts Count -->
                <td class="px-6 py-4">
                  <span class="text-sm text-gray-600 dark:text-gray-400">{{ category.posts_count || 0 }}</span>
                </td>
                <!-- Display Order -->
                <td class="px-6 py-4">
                  <span class="text-sm text-gray-600 dark:text-gray-400">{{ category.display_order || '-' }}</span>
                </td>
                <!-- Actions -->
                <td class="px-6 py-4">
                  <div class="flex items-center justify-end gap-2">
                    <button
                      @click="editCategory(category.id)"
                      class="p-2 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors"
                      title="Edit"
                    >
                      <Pencil class="w-4 h-4" />
                    </button>
                    <button
                      @click="confirmDelete(category)"
                      class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                      title="Delete"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700/50 flex items-center justify-between">
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Showing {{ filteredCategories.length }} of {{ categories.length }} categories
          </p>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="deleteModal.show" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
            <AlertTriangle class="w-6 h-6 text-red-600 dark:text-red-400" />
          </div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Category</h3>
        </div>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
          Are you sure you want to delete "<strong class="text-gray-900 dark:text-white">{{ deleteModal.category?.name }}</strong>"? This action cannot be undone.
        </p>
        <div class="flex justify-end gap-3">
          <button
            @click="deleteModal.show = false"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors"
          >
            Cancel
          </button>
          <button
            @click="deleteCategory"
            :disabled="deleteModal.loading"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl transition-colors flex items-center gap-2"
          >
            <Loader2 v-if="deleteModal.loading" class="w-4 h-4 animate-spin" />
            <Trash2 v-else class="w-4 h-4" />
            {{ deleteModal.loading ? 'Deleting...' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import {
  ChevronRight,
  Plus,
  Search,
  Loader2,
  FolderOpen,
  Pencil,
  Trash2,
  AlertTriangle,
} from 'lucide-vue-next';

export default {
  name: 'BlogCategoryList',
  components: {
    MainLayout,
    ChevronRight,
    Plus,
    Search,
    Loader2,
    FolderOpen,
    Pencil,
    Trash2,
    AlertTriangle,
  },
  data() {
    return {
      categories: [],
      loading: false,
      searchQuery: '',
      statusFilter: '',
      deleteModal: {
        show: false,
        category: null,
        loading: false,
      },
    };
  },
  computed: {
    filteredCategories() {
      let filtered = this.categories;

      if (this.searchQuery.trim()) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(
          c =>
            c.name.toLowerCase().includes(query) ||
            c.slug.toLowerCase().includes(query) ||
            (c.description && c.description.toLowerCase().includes(query))
        );
      }

      if (this.statusFilter) {
        const isActive = this.statusFilter === 'active';
        filtered = filtered.filter(c => c.status === isActive);
      }

      return filtered.sort((a, b) => (a.display_order || 0) - (b.display_order || 0));
    },
  },
  mounted() {
    this.fetchCategories();
  },
  methods: {
    async fetchCategories() {
      this.loading = true;
      try {
        const response = await axios.get('/auth/blog-categories');
        this.categories = response.data.data || response.data;
      } catch (e) {
        console.error('Failed to load categories', e);
        if (e.response?.status === 401) {
          this.$router.push('/login');
        }
      } finally {
        this.loading = false;
      }
    },
    clearFilters() {
      this.searchQuery = '';
      this.statusFilter = '';
    },
    editCategory(id) {
      this.$router.push(`/blog-category/${id}/edit`);
    },
    confirmDelete(category) {
      this.deleteModal.category = category;
      this.deleteModal.show = true;
    },
    async deleteCategory() {
      this.deleteModal.loading = true;
      try {
        await axios.delete(`/auth/blog-categories/${this.deleteModal.category.id}`);
        this.categories = this.categories.filter(c => c.id !== this.deleteModal.category.id);
        this.deleteModal.show = false;
        this.deleteModal.category = null;
      } catch (e) {
        console.error('Failed to delete category', e);
        alert(e.response?.data?.message || 'Failed to delete category');
      } finally {
        this.deleteModal.loading = false;
      }
    },
  },
};
</script>
