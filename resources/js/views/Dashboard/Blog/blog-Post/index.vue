<template>
  <MainLayout>
    <div class="max-w-7xl mx-auto">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Blog</h1>
          <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mt-1">
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-900 dark:text-white">Blog</span>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-900 dark:text-white">List</span>
          </nav>
        </div>
        <button
          @click="$router.push('/blog-post-create')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-hover text-white font-medium rounded-xl transition-colors"
        >
          <Plus class="w-4 h-4" />
          New post
        </button>
      </div>

      <DataTable
        :columns="columns"
        :data="filteredPosts"
        :loading="loading"
        :pagination="pagination"
        @page-change="changePage"
        @per-page-change="handlePerPageChange"
      >
        <template #toolbar>
          <div class="flex flex-col sm:flex-row gap-4 w-full">
            <div class="flex-1 relative">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search post..."
                class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl pl-10 pr-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              />
            </div>
            <select
              v-model="statusFilter"
              class="bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            >
              <option value="">All Status</option>
              <option value="published">Published</option>
              <option value="draft">Draft</option>
              <option value="scheduled">Scheduled</option>
              <option value="in_review">In Review</option>
              <option value="archived">Archived</option>
            </select>
            <select
              v-model="categoryFilter"
              class="bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            >
              <option value="">All Categories</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
            <button
              v-if="searchQuery || statusFilter || categoryFilter"
              @click="clearFilters"
              class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
            >
              Clear
            </button>
          </div>
        </template>
        <template #cell(post)="{ item }">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center overflow-hidden shrink-0">
              <img
                v-if="isValidImageUrl(item.featured_image_url)"
                :src="item.featured_image_url"
                class="w-full h-full object-cover"
                alt=""
                @error="$event.target.style.display='none'"
              />
              <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-800">
                <ImageIcon class="w-6 h-6 text-gray-400 dark:text-gray-500" />
              </div>
            </div>
            <div class="min-w-0">
              <p class="font-medium text-gray-900 dark:text-white truncate">{{ item.title }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ item.excerpt || item.slug }}</p>
            </div>
          </div>
        </template>
        <template #cell(author)="{ item }">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
              <span class="text-sm font-medium text-primary">
                {{ getInitials(item.author?.full_name || item.author?.name) }}
              </span>
            </div>
            <span class="text-sm text-gray-700 dark:text-gray-300">{{ item.author?.full_name || item.author?.name || '—' }}</span>
          </div>
        </template>
        <template #cell(category)="{ item }">
          <span class="px-3 py-1 rounded-lg text-sm font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
            {{ item.category?.name || 'Uncategorized' }}
          </span>
        </template>
        <template #cell(status)="{ item }">
          <span
            class="px-3 py-1 rounded-lg text-sm font-medium"
            :class="getStatusClass(item.status)"
          >
            {{ formatStatus(item.status) }}
          </span>
        </template>
        <template #cell(published)="{ item }">
          <span class="text-sm text-gray-700 dark:text-gray-300">
            {{ formatDate(item.published_at || item.created_at) }}
          </span>
        </template>
        <template #cell(actions)="{ item }">
          <div class="flex items-center justify-center gap-1">
            <button
              @click="editPost(item.id)"
              class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-400 transition-colors"
              title="Edit"
            >
              <Pencil class="w-4 h-4" />
            </button>
            <button
              @click="confirmDelete(item)"
              class="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 dark:text-red-400 transition-colors"
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
import { useLayoutStore } from '@/stores/layout';
import { useToastStore } from '@/stores/toast';
import { useConfirmStore } from '@/stores/confirm';
import {
  ChevronRight,
  Search,
  Plus,
  Image as ImageIcon,
  FileText,
  Pencil,
  Trash2,
} from 'lucide-vue-next';

export default {
  name: 'BlogList',
  components: {
    MainLayout,
    DataTable,
    ChevronRight,
    Search,
    Plus,
    ImageIcon,
    FileText,
    Pencil,
    Trash2
  },
  setup() {
    const layout = useLayoutStore();
    const toast = useToastStore();
    const confirm = useConfirmStore();
    return { layout, toast, confirm };
  },
  data() {
    return {
      posts: [],
      categories: [],
      searchQuery: '',
      statusFilter: '',
      categoryFilter: '',
      loading: false,
      pagination: null,
      perPage: 15,
      deleteModal: {
        post: null,
      },
      columns: [
        { key: 'post', label: 'Post' },
        { key: 'author', label: 'Author' },
        { key: 'category', label: 'Category' },
        { key: 'status', label: 'Status' },
        { key: 'published', label: 'Published' },
        { key: 'actions', label: 'Actions', align: 'center' },
      ],
    };
  },
  computed: {
    filteredPosts() {
      let result = this.posts;

      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        result = result.filter(post =>
          post.title?.toLowerCase().includes(query) ||
          post.slug?.toLowerCase().includes(query) ||
          post.author?.name?.toLowerCase().includes(query)
        );
      }

      if (this.statusFilter) {
        result = result.filter(post => post.status === this.statusFilter);
      }

      if (this.categoryFilter) {
        result = result.filter(post => post.category?.id === this.categoryFilter);
      }

      return result;
    }
  },
  mounted() {
    this.fetchPosts();
    this.fetchCategories();
  },
  methods: {
    async fetchPosts(page = 1) {
      this.loading = true;
      try {
        const response = await axios.get('/auth/blog-posts', {
          params: { page, per_page: this.perPage }
        });
        this.posts = response.data.data || [];
        this.pagination = response.data.meta || null;
      } catch (e) {
        console.error('Failed to load posts', e);
        if (e.response?.status === 401) {
          this.$router.push('/login');
        }
      } finally {
        this.loading = false;
      }
    },
    async fetchCategories() {
      try {
        const response = await axios.get('/auth/blog-categories');
        this.categories = response.data.data || response.data || [];
      } catch (e) {
        console.error('Failed to load categories', e);
      }
    },
    clearFilters() {
      this.searchQuery = '';
      this.statusFilter = '';
      this.categoryFilter = '';
    },
    isValidImageUrl(url) {
      if (!url || typeof url !== 'string') return false;
      if (url.startsWith('blob:')) return false;
      return url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/');
    },
    getInitials(name) {
      if (!name) return '?';
      return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
    },
    formatStatus(status) {
      const statusMap = {
        'draft': 'Draft',
        'published': 'Published',
        'scheduled': 'Scheduled',
        'in_review': 'In Review',
        'archived': 'Archived'
      };
      return statusMap[status] || status;
    },
    getStatusClass(status) {
      const classes = {
        'draft': 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300',
        'published': 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-300',
        'scheduled': 'bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300',
        'in_review': 'bg-yellow-100 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300',
        'archived': 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-300'
      };
      return classes[status] || classes.draft;
    },
    formatDate(date) {
      if (!date) return 'Not published';
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    },
    editPost(id) {
      this.$router.push(`/blog-post/${id}/edit`);
    },
    async confirmDelete(post) {
      const confirmed = await this.confirm.ask({
        title: 'Delete Post',
        message: `Are you sure you want to delete "${post.title}"? This action cannot be undone.`,
        confirmText: 'Delete',
        variant: 'danger',
      });
      if (!confirmed) return;
      try {
        await axios.delete(`/auth/blog-posts/${post.id}`);
        this.posts = this.posts.filter(p => p.id !== post.id);
        this.toast.success('Post deleted successfully');
      } catch (e) {
        console.error('Failed to delete post', e);
        this.toast.error(e.response?.data?.message || 'Failed to delete post');
      }
    },

    changePage(page) {
      this.fetchPosts(page);
    },
    handlePerPageChange(size) {
      this.perPage = size;
      this.fetchPosts(1);
    }
  }
};
</script>
