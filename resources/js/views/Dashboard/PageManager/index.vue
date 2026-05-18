<template>
  <MainLayout>
    <!-- Loading State: Clean Centered Shimmer Progress Line (Only shown on very first load) -->
    <div v-if="loading && pages.length === 0" class="min-h-[75vh] flex flex-col items-center justify-center bg-transparent">
      <!-- Premium Progress Track Line -->
      <div class="relative w-64 h-[3px] bg-gray-200 dark:bg-gray-800/80 rounded-full overflow-hidden">
        <div class="absolute inset-0 line-shimmer-sweep"></div>
      </div>
    </div>

    <!-- Loaded State: Actual Page Content (Always visible once first data is loaded) -->
    <div v-else class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pages</h1>
          <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mt-1">
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-900 dark:text-white">Pages</span>
          </nav>
        </div>
        <button @click="$router.push('/dashboard/page-manager/create')" class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-hover text-white font-medium rounded-xl transition-colors">
          <Plus class="w-4 h-4" /> New page
        </button>
      </div>

      <!-- Filters Card -->
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input v-model="searchQuery" type="text" placeholder="Search page..." class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl pl-10 pr-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
          </div>
          <div class="relative pt-1">
            <CustomSelect 
              v-model="selectedCountry" 
              :options="countries" 
              label="Country"
              placeholder="All Countries"
              labelKey="name"
              valueKey="id"
              :clearable="true"
            />
          </div>
          <div class="flex items-center gap-2">
            <button v-if="searchQuery || selectedCountry" @click="clearFilters" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Clear</button>
          </div>
        </div>
      </div>

      <!-- Pages Table -->
      <DataTable 
        :columns="columns" 
        :data="filteredPages" 
        :loading="loading"
        :pagination="pagination"
        @page-change="fetchPages"
        @per-page-change="handlePerPageChange"
      >
        <template #cell(title)="{ item }">
          <p class="font-medium text-gray-900 dark:text-white">
            <span v-if="item.parent_id" class="text-gray-500 mr-2">└</span>{{ item.title }}
          </p>
        </template>
        
        <template #cell(country)="{ item }">
          <span v-if="item.country" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-medium">
            {{ item.country.name }}
          </span>
          <span v-else class="text-sm text-gray-400">-</span>
        </template>
        
        <template #cell(type)="{ item }">
          <span v-if="item.parent_id" class="text-xs font-semibold px-2 py-1 rounded-lg bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">Sub Page</span>
          <span v-else class="text-xs font-semibold px-2 py-1 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">Main</span>
        </template>
        
        <template #cell(status)="{ item }">
          <span v-if="item.is_active" class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 dark:text-green-400">
            <div class="w-2 h-2 rounded-full bg-green-600 dark:bg-green-400"></div> Published
          </span>
          <span v-else class="inline-flex items-center gap-1 text-xs font-semibold text-gray-500 dark:text-gray-400">
            <div class="w-2 h-2 rounded-full bg-gray-400 dark:bg-gray-500"></div> Draft
          </span>
        </template>
        
        <template #cell(actions)="{ item }">
          <div class="flex items-center justify-end gap-2">
            <button @click="$router.push(`/dashboard/page-manager/edit/${item.id}`)" class="p-2 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Edit"><Pencil class="w-4 h-4" /></button>
            <button @click="confirmDelete(item)" class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete"><Trash2 class="w-4 h-4" /></button>
          </div>
        </template>
      </DataTable>

      <!-- Delete Modal -->
      <div v-if="deleteModal.show" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center"><AlertTriangle class="w-6 h-6 text-red-600 dark:text-red-400" /></div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Page</h3>
          </div>
          <p class="text-gray-600 dark:text-gray-400 mb-6">Are you sure you want to delete "<strong class="text-gray-900 dark:text-white">{{ deleteModal.page?.title }}</strong>"? This action cannot be undone.</p>
          <div class="flex justify-end gap-3">
            <button @click="deleteModal.show = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">Cancel</button>
            <button @click="deletePage" :disabled="deleteModal.loading" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl transition-colors flex items-center gap-2">
              <Loader2 v-if="deleteModal.loading" class="w-4 h-4 animate-spin" /><Trash2 v-else class="w-4 h-4" />
              {{ deleteModal.loading ? 'Deleting...' : 'Delete' }}
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
import DataTable from '@/components/Table/DataTable.vue';
import CustomSelect from '@/components/Form/CustomSelect.vue';
import { fetchWithCache, clearCache } from '@/utils/cacheHelper';
import {
  ChevronRight, Plus, Search, Loader2, Pencil, Trash2, AlertTriangle,
} from 'lucide-vue-next';

export default {
  name: 'PageList',
  components: { MainLayout, DataTable, CustomSelect, ChevronRight, Plus, Search, Loader2, Pencil, Trash2, AlertTriangle },
  data() {
    return {
      columns: [
        { key: 'title', label: 'Title' },
        { key: 'country', label: 'Country' },
        { key: 'type', label: 'Type' },
        { key: 'status', label: 'Status', align: 'center' },
        { key: 'actions', label: 'Actions', align: 'right' }
      ],
      pages: [],
      pagination: null,
      perPage: 15,
      loading: false,
      searchQuery: '',
      selectedCountry: '',
      countries: [],
      deleteModal: { show: false, page: null, loading: false },
    };
  },

  computed: {
    filteredPages() {
      if (!this.searchQuery.trim()) return this.pages;
      const query = this.searchQuery.toLowerCase();
      return this.pages.filter(p =>
        p.title.toLowerCase().includes(query) ||
        p.slug.toLowerCase().includes(query)
      );
    },
  },
  mounted() { 
    this.fetchCountries();
    this.fetchPages();
  },
  watch: {
    selectedCountry() { this.fetchPages(1); },
  },
  methods: {
    async fetchCountries() {
      try {
        const response = await axios.get('/auth/admin/countries');
        this.countries = response.data.data?.data || response.data.data || [];
      } catch (e) {
        console.error('Failed to load countries', e);
      }
    },
    async fetchPages(page = 1) {
      await fetchWithCache({
        url: '/auth/admin/pages',
        params: {
          page,
          per_page: this.perPage,
          country_id: this.selectedCountry
        },
        component: this,
        dataKey: 'pages',
        loadingKey: 'loading',
        paginationKey: 'pagination'
      });
    },
    handlePerPageChange(newPerPage) {
      this.perPage = newPerPage;
      this.fetchPages(1);
    },
    clearFilters() {
      this.searchQuery = '';
      this.selectedCountry = '';
    },
    confirmDelete(page) {
      this.deleteModal.page = page;
      this.deleteModal.show = true;
    },
    async deletePage() {
      this.deleteModal.loading = true;
      try {
        await axios.delete(`/auth/admin/pages/${this.deleteModal.page.id}`);
        clearCache('/auth/admin/pages');
        this.pages = this.pages.filter(p => p.id !== this.deleteModal.page.id);
        this.deleteModal.show = false;
        this.deleteModal.page = null;
      } catch (e) {
        console.error('Failed to delete page', e);
      } finally {
        this.deleteModal.loading = false;
      }
    },
  },
};
</script>