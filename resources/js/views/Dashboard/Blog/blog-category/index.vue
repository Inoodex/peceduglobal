<template>
  <MainLayout>
    <div class="max-w-7xl mx-auto">
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

      <DataTable :columns="columns" :data="paginatedCategories" :loading="loading" :pagination="pagination" @page-change="changePage" @per-page-change="handlePerPageChange">
        <template #toolbar>
          <div class="flex flex-col sm:flex-row items-center gap-4 w-full">
            <div class="flex-1 relative w-full">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search category..."
                class="w-full h-12 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl pl-10 pr-4 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              />
            </div>
            <select
              v-model="statusFilter"
              class="w-full sm:w-36 h-12 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all cursor-pointer"
            >
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
            <button
              v-if="searchQuery || statusFilter"
              @click="clearFilters"
              class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors h-12 flex items-center justify-center shrink-0"
            >
              Clear
            </button>
          </div>
        </template>
        <template #cell(name)="{ item }">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary/10 dark:bg-blue-500/10 flex items-center justify-center shrink-0">
              <FolderOpen class="w-5 h-5 text-primary dark:text-blue-400" />
            </div>
            <div class="min-w-0 max-w-[200px] sm:max-w-[300px] md:max-w-[400px]">
              <p class="font-medium text-gray-900 dark:text-white truncate" :title="item.name">{{ item.name }}</p>
              <p v-if="item.description" class="text-sm text-gray-500 dark:text-gray-400 truncate" :title="item.description">{{ item.description }}</p>
            </div>
          </div>
        </template>
        <!-- <template #cell(slug)="{ item }">
          <span class="text-sm text-gray-600 dark:text-gray-400 font-mono">{{ item.slug }}</span>
        </template> -->
        <template #cell(status)="{ item }">
          <span
            :class="[
              'px-2.5 py-1 rounded-full text-xs font-medium',
              item.status
                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400'
            ]"
          >
            {{ item.status ? 'Active' : 'Inactive' }}
          </span>
        </template>
        <template #cell(posts)="{ item }">
          <span class="text-sm text-gray-600 dark:text-gray-400">{{ item.posts_count || 0 }}</span>
        </template>
        <template #cell(order)="{ item }">
          <span class="text-sm text-gray-600 dark:text-gray-400">{{ item.display_order || '-' }}</span>
        </template>
        <template #cell(actions)="{ item }">
          <div class="flex items-center justify-end gap-2">
            <button
              @click="editCategory(item.id)"
              class="p-2 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors"
              title="Edit"
            >
              <Pencil class="w-4 h-4" />
            </button>
            <button
              @click="confirmDelete(item)"
              class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
              title="Delete"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </template>
      </DataTable>
    </div>
  </MainLayout>
</template>

<script>
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import DataTable from '@/components/Table/DataTable.vue';
import { useToastStore } from '@/stores/toast';
import { useConfirmStore } from '@/stores/confirm';
import {
  ChevronRight,
  Plus,
  Search,
  FolderOpen,
  Pencil,
  Trash2,
} from 'lucide-vue-next';

export default {
  name: 'BlogCategoryList',
  components: {
    MainLayout,
    DataTable,
    ChevronRight,
    Plus,
    Search,
    FolderOpen,
    Pencil,
    Trash2,
  },
  setup() {
    const toast = useToastStore();
    const confirm = useConfirmStore();
    return { toast, confirm };
  },
  data() {
    return {
      categories: [],
      loading: false,
      searchQuery: '',
      statusFilter: '',
      deleteModal: {
        category: null,
      },
      columns: [
        { key: 'name', label: 'Category' },
        // { key: 'slug', label: 'Slug' },
        { key: 'status', label: 'Status' },
        { key: 'posts', label: 'Posts' },
        { key: 'order', label: 'Order' },
        { key: 'actions', label: 'Actions', align: 'right' },
      ],
      page: 1,
      perPage: 15,
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
        filtered = filtered.filter(c => Boolean(c.status) === isActive);
      }

      return filtered.sort((a, b) => (a.display_order || 0) - (b.display_order || 0));
    },
    paginatedCategories() {
      const start = (this.page - 1) * this.perPage;
      return this.filteredCategories.slice(start, start + this.perPage);
    },
    pagination() {
      const total = this.filteredCategories.length;
      const from = total === 0 ? 0 : (this.page - 1) * this.perPage + 1;
      const to = Math.min(this.page * this.perPage, total);
      return {
        current_page: this.page,
        last_page: Math.max(1, Math.ceil(total / this.perPage)),
        per_page: this.perPage,
        total,
        from,
        to,
      };
    },
  },
  watch: {
    searchQuery() { this.page = 1; },
    statusFilter() { this.page = 1; },
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
    changePage(page) {
      this.page = page;
    },
    handlePerPageChange(size) {
      this.perPage = size;
      this.page = 1;
    },
    clearFilters() {
      this.searchQuery = '';
      this.statusFilter = '';
      this.page = 1;
    },
    editCategory(id) {
      this.$router.push(`/blog-category/${id}/edit`);
    },
    async confirmDelete(category) {
      const confirmed = await this.confirm.ask({
        title: 'Delete Category',
        message: `Are you sure you want to delete "${category.name}"? This action cannot be undone.`,
        confirmText: 'Delete',
        variant: 'danger',
      });
      if (!confirmed) return;
      try {
        await axios.delete(`/auth/blog-categories/${category.id}`);
        this.categories = this.categories.filter(c => c.id !== category.id);
        this.toast.success('Category deleted successfully');
      } catch (e) {
        console.error('Failed to delete category', e);
        this.toast.error(e.response?.data?.message || 'Failed to delete category');
      }
    },
  },
};
</script>

<style scoped>
:deep(th), :deep(td) {
  padding-left: 1rem !important;  /* px-4 */
  padding-right: 1rem !important; /* px-4 */
}
</style>
