<template>
  <MainLayout>
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Blog</h1>
          <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mt-1">
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer">Dashboard</span>
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

      <!-- Filters Card -->
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-4 mb-4">
        <div class="flex flex-col sm:flex-row gap-4">
          <!-- Search -->
          <div class="flex-1 relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search post..."
              class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl pl-10 pr-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            />
          </div>

          <!-- Status Filter -->
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

          <!-- Category Filter -->
          <select
            v-model="categoryFilter"
            class="bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          >
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
        </div>
      </div>

      <!-- Table Card -->
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-700/50">
                <th class="text-left px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300">
                  <input
                    type="checkbox"
                    :checked="selectedPosts.length === posts.length && posts.length > 0"
                    @change="toggleSelectAll"
                    class="rounded border-gray-300 dark:border-gray-600 text-primary focus:ring-primary"
                  />
                </th>
                <th class="text-left px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Post</th>
                <th class="text-left px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Author</th>
                <th class="text-left px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Category</th>
                <th class="text-left px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Status</th>
                <th class="text-left px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Published</th>
                <th class="text-center px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700/50">
              <tr
                v-for="post in filteredPosts"
                :key="post.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
              >
                <td class="px-4 py-4">
                  <input
                    type="checkbox"
                    :value="post.id"
                    v-model="selectedPosts"
                    class="rounded border-gray-300 dark:border-gray-600 text-primary focus:ring-primary"
                  />
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center overflow-hidden shrink-0">
                      <img
                        v-if="isValidImageUrl(post.featured_image_url)"
                        :src="post.featured_image_url"
                        class="w-full h-full object-cover"
                        alt=""
                        @error="$event.target.style.display='none'"
                      />
                      <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-800">
                        <ImageIcon class="w-6 h-6 text-gray-400 dark:text-gray-500" />
                      </div>
                    </div>
                    <div class="min-w-0">
                      <p class="font-medium text-gray-900 dark:text-white truncate">{{ post.title }}</p>
                      <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ post.excerpt || post.slug }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                      <span class="text-sm font-medium text-primary">
                        {{ getInitials(post.author?.name) }}
                      </span>
                    </div>
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ post.author?.name || 'Unknown' }}</span>
                  </div>
                </td>
                <td class="px-4 py-4">
                  <span class="px-3 py-1 rounded-lg text-sm font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                    {{ post.category?.name || 'Uncategorized' }}
                  </span>
                </td>
                <td class="px-4 py-4">
                  <span
                    class="px-3 py-1 rounded-lg text-sm font-medium"
                    :class="getStatusClass(post.status)"
                  >
                    {{ formatStatus(post.status) }}
                  </span>
                </td>
                <td class="px-4 py-4">
                  <span class="text-sm text-gray-700 dark:text-gray-300">
                    {{ formatDate(post.published_at || post.created_at) }}
                  </span>
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center justify-center gap-1">
                    <button
                      @click="editPost(post.id)"
                      class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-400 transition-colors"
                      title="Edit"
                    >
                      <Pencil class="w-4 h-4" />
                    </button>
                    <!-- <button
                      @click="viewPost(post.slug)"
                      class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-400 transition-colors"
                      title="View"
                    >
                      <Eye class="w-4 h-4" />
                    </button> -->
                    <button
                      @click="deletePost(post.id)"
                      class="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 dark:text-red-400 transition-colors"
                      title="Delete"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredPosts.length === 0">
                <td colspan="7" class="px-4 py-12 text-center">
                  <div class="flex flex-col items-center gap-3">
                    <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                      <FileText class="w-8 h-8 text-gray-400" />
                    </div>
                    <p class="text-gray-500 dark:text-gray-400">No posts found</p>
                    <button
                      @click="$router.push('/blog-post-create')"
                      class="text-primary hover:underline font-medium"
                    >
                      Create your first post
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-4 py-4 border-t border-gray-200 dark:border-gray-700/50">
          <span class="text-sm text-gray-700 dark:text-gray-300">
            Showing {{ posts.length }} of {{ total }} posts
          </span>
          <div class="flex items-center gap-2">
            <button
              :disabled="currentPage === 1"
              @click="changePage(currentPage - 1)"
              class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed text-gray-700 dark:text-gray-300 transition-colors"
            >
              <ChevronLeft class="w-5 h-5" />
            </button>
            <span class="text-sm text-gray-700 dark:text-gray-300 px-2">
              Page {{ currentPage }} of {{ lastPage }}
            </span>
            <button
              :disabled="currentPage === lastPage"
              @click="changePage(currentPage + 1)"
              class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed text-gray-700 dark:text-gray-300 transition-colors"
            >
              <ChevronRight class="w-5 h-5" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import { useLayoutStore } from '@/stores/layout';
import {
  ChevronRight,
  ChevronLeft,
  Search,
  Plus,
  Image as ImageIcon,
  FileText,
  Pencil,
  Eye,
  Trash2
} from 'lucide-vue-next';

export default {
  name: 'BlogList',
  components: {
    MainLayout,
    ChevronRight,
    ChevronLeft,
    Search,
    Plus,
    ImageIcon,
    FileText,
    Pencil,
    Eye,
    Trash2
  },
  setup() {
    const layout = useLayoutStore();
    return { layout };
  },
  data() {
    return {
      posts: [],
      categories: [],
      selectedPosts: [],
      searchQuery: '',
      statusFilter: '',
      categoryFilter: '',
      currentPage: 1,
      lastPage: 1,
      total: 0,
      perPage: 15
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
    async fetchPosts() {
      try {
        const response = await axios.get('/auth/blog-posts', {
          params: {
            page: this.currentPage,
            per_page: this.perPage
          }
        });
        this.posts = response.data.data || [];
        this.currentPage = response.data.meta?.current_page || 1;
        this.lastPage = response.data.meta?.last_page || 1;
        this.total = response.data.meta?.total || 0;
      } catch (e) {
        console.error('Failed to load posts', e);
        if (e.response?.status === 401) {
          this.$router.push('/login');
        }
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
    toggleSelectAll() {
      if (this.selectedPosts.length === this.posts.length) {
        this.selectedPosts = [];
      } else {
        this.selectedPosts = this.posts.map(p => p.id);
      }
    },
    isValidImageUrl(url) {
      if (!url || typeof url !== 'string') return false;
      // Reject blob URLs (temporary browser URLs)
      if (url.startsWith('blob:')) return false;
      // Must start with http://, https://, or /
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
    viewPost(slug) {
      window.open(`/blog/${slug}`, '_blank');
    },
    async deletePost(id) {
      if (!confirm('Are you sure you want to delete this post?')) return;

      try {
        await axios.delete(`/auth/blog-posts/${id}`);
        this.posts = this.posts.filter(p => p.id !== id);
        alert('Post deleted successfully');
      } catch (e) {
        console.error('Failed to delete post', e);
        alert(e.response?.data?.message || 'Failed to delete post');
      }
    },
    changePage(page) {
      this.currentPage = page;
      this.fetchPosts();
    }
  }
};
</script>

<style scoped>
.bg-primary {
  background-color: var(--palette-primary-main, #00AB55);
}
.bg-primary-hover {
  background-color: var(--palette-primary-dark, #007867);
}
.text-primary {
  color: var(--palette-primary-main, #00AB55);
}
.focus\:ring-primary\/20:focus {
  --tw-ring-color: rgb(var(--palette-primary-main, 0 171 85) / 0.2);
}
.focus\:border-primary:focus {
  border-color: var(--palette-primary-main, #00AB55);
}
</style>
