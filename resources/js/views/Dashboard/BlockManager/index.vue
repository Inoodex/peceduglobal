<template>
  <MainLayout>
    <!-- Loading State: Clean Centered Shimmer Progress Line (Only shown on very first load) -->
    <div v-if="loading && blocks.length === 0" class="min-h-[75vh] flex flex-col items-center justify-center bg-transparent">
      <!-- Premium Progress Track Line -->
      <div class="relative w-64 h-[3px] bg-gray-200 dark:bg-gray-800/80 rounded-full overflow-hidden">
        <div class="absolute inset-0 line-shimmer-sweep"></div>
      </div>
    </div>

    <!-- Loaded State: Actual Page Content (Always visible once first data is loaded) -->
    <div v-else class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="mt-6">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Block Manager</h1>
          <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mt-1">
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-900 dark:text-white">Block Manager</span>
          </nav>
        </div>
        <button @click="$router.push('/dashboard/block-manager/create')" class="mt-6 inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-hover text-white font-medium rounded-xl transition-colors">
          <Plus class="w-4 h-4" /> New block
        </button>
      </div>

      <!-- Filters Card -->
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input v-model="searchQuery" type="text" placeholder="Search blocks..." class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl pl-10 pr-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
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
          <div class="relative pt-1">
            <CustomSelect 
              v-model="selectedPage" 
              :options="pageOptions" 
              label="Page"
              placeholder="All Pages"
              labelKey="label"
              valueKey="id"
              :clearable="true"
            />
          </div>
          <div class="flex gap-2">
            <button v-if="searchQuery || selectedCountry || selectedPage" @click="clearFilters" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Clear All</button>
          </div>
        </div>
      </div>

      <!-- Blocks Table -->
      <DataTable 
        :columns="columns" 
        :data="filteredBlocks" 
        :loading="loading"
        :pagination="pagination"
        @page-change="fetchBlocks"
        @per-page-change="handlePerPageChange"
      >
        <template #cell(block_type)="{ item }">
          <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-300 font-mono">
            {{ item.block_type }}
          </span>
        </template>

        <template #cell(section_title)="{ item }">
          <span class="text-sm text-gray-900 dark:text-white font-medium truncate max-w-[180px] block">
            {{ item.section_title || '—' }}
          </span>
        </template>

        <template #cell(page)="{ item }">
          <span v-if="item.page" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-medium">
            <span class="font-bold">{{ item.page.country?.iso_code }}</span>
            <span class="opacity-50">|</span>
            <span>{{ item.page.title }}</span>
          </span>
          <span v-else class="text-sm text-gray-400">—</span>
        </template>

        <template #cell(actions)="{ item }">
          <div class="flex items-center justify-end gap-1">
            <button @click="moveBlock(item, -1)" class="p-1.5 text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Move Up"><ArrowUp class="w-3.5 h-3.5" /></button>
            <button @click="moveBlock(item, 1)" class="p-1.5 text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Move Down"><ArrowDown class="w-3.5 h-3.5" /></button>
            <button @click="$router.push(`/dashboard/block-manager/edit/${item.id}`)" class="p-1.5 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Edit"><Pencil class="w-4 h-4" /></button>
            <button @click="confirmDelete(item)" class="p-1.5 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete"><Trash2 class="w-4 h-4" /></button>
          </div>
        </template>
      </DataTable>

      <!-- Delete Modal -->
      <div v-if="deleteModal.show" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center"><AlertTriangle class="w-6 h-6 text-red-600 dark:text-red-400" /></div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Block</h3>
          </div>
          <p class="text-gray-600 dark:text-gray-400 mb-6">Are you sure you want to delete this block? This action cannot be undone.</p>
          <div class="flex justify-end gap-3">
            <button @click="deleteModal.show = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">Cancel</button>
            <button @click="deleteBlock" :disabled="deleteModal.loading" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl transition-colors flex items-center gap-2">
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
  ChevronRight, Plus, Search, Loader2, Pencil, Trash2, AlertTriangle, ArrowUp, ArrowDown,
} from 'lucide-vue-next';

export default {
  name: 'BlockList',
  components: { MainLayout, DataTable, CustomSelect, ChevronRight, Plus, Search, Loader2, Pencil, Trash2, AlertTriangle, ArrowUp, ArrowDown },
  data() {
    return {
      columns: [
        { key: 'block_type', label: 'Block Type' },
        { key: 'section_title', label: 'Section Title' },
        { key: 'page', label: 'Page' },
        { key: 'actions', label: 'Actions', align: 'right' }
      ],
      blocks: [],
      pagination: null,
      perPage: 15,
      loading: false,
      searchQuery: '',
      selectedCountry: '',
      selectedPage: '',
      countries: [],
      pages: [],
      deleteModal: { show: false, block: null, loading: false },
    };
  },

  computed: {
    pageOptions() {
      return this.pages.map(p => ({ ...p, label: `${p.country?.iso_code ? p.country.iso_code + ' - ' : ''}${p.title}` }));
    },
    filteredBlocks() {
      if (!this.searchQuery.trim()) return this.blocks;
      const query = this.searchQuery.toLowerCase();
      return this.blocks.filter(b =>
        b.block_type.toLowerCase().includes(query) ||
        (b.section_title && b.section_title.toLowerCase().includes(query))
      );
    },
  },
  mounted() {
    this.fetchCountries();
    this.fetchPages();
    this.fetchBlocks();
  },
  watch: {
    selectedCountry() {
      this.selectedPage = '';
      this.fetchPages(this.selectedCountry);
      this.fetchBlocks(1);
    },
    selectedPage() { this.fetchBlocks(1); },
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
    async fetchPages(countryId = '') {
      try {
        const params = {};
        if (countryId) params.country_id = countryId;
        const response = await axios.get('/auth/admin/pages', { params });
        this.pages = response.data.data?.data || response.data.data || [];
      } catch (e) {
        console.error('Failed to load pages', e);
      }
    },
    clearFilters() {
      this.searchQuery = '';
      this.selectedCountry = '';
      this.selectedPage = '';
    },
    async fetchBlocks(page = 1) {
      await fetchWithCache({
        url: '/auth/admin/blocks',
        params: {
          page,
          per_page: this.perPage,
          country_id: this.selectedCountry,
          page_id: this.selectedPage
        },
        component: this,
        dataKey: 'blocks',
        loadingKey: 'loading',
        paginationKey: 'pagination'
      });
    },
    handlePerPageChange(newPerPage) {
      this.perPage = newPerPage;
      this.fetchBlocks(1);
    },
    confirmDelete(block) {
      this.deleteModal.block = block;
      this.deleteModal.show = true;
    },
    async deleteBlock() {
      this.deleteModal.loading = true;
      try {
        await axios.delete(`/auth/admin/blocks/${this.deleteModal.block.id}`);
        clearCache('/auth/admin/blocks');
        this.blocks = this.blocks.filter(b => b.id !== this.deleteModal.block.id);
        this.deleteModal.show = false;
        this.deleteModal.block = null;
      } catch (e) {
        console.error('Failed to delete block', e);
      } finally {
        this.deleteModal.loading = false;
      }
    },
    async moveBlock(block, direction) {
      const index = this.blocks.findIndex(b => b.id === block.id);
      const newIndex = index + direction;

      if (newIndex < 0 || newIndex >= this.blocks.length) return;

      // Swap elements in local array for immediate UI feedback
      const updatedBlocks = [...this.blocks];
      [updatedBlocks[index], updatedBlocks[newIndex]] = [updatedBlocks[newIndex], updatedBlocks[index]];
      this.blocks = updatedBlocks;

      try {
        const orders = this.blocks.map((b, i) => ({
          id: b.id,
          sort_order: i
        }));
        await axios.post('/auth/admin/blocks/reorder', { orders });
        clearCache('/auth/admin/blocks');
      } catch (e) {
        console.error('Failed to update block order', e);
        // Revert on failure
        this.fetchBlocks();
      }
    },
  },
};
</script>
