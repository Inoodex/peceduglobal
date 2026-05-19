<template>
  <MainLayout>
    <!-- Loading State -->
    <div v-if="loading && blocks.length === 0" class="min-h-[75vh] flex flex-col items-center justify-center bg-transparent">
      <div class="relative w-64 h-[3px] bg-gray-200 dark:bg-gray-800/80 rounded-full overflow-hidden">
        <div class="absolute inset-0 line-shimmer-sweep"></div>
      </div>
    </div>

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
    </div>
  </MainLayout>
</template>

<script>
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import DataTable from '@/components/Table/DataTable.vue';
import CustomSelect from '@/components/Form/CustomSelect.vue';
import { fetchWithCache, clearCache } from '@/utils/cacheHelper';
import { useToastStore } from '@/stores/toast';
import { useConfirmStore } from '@/stores/confirm';
import {
  ChevronRight, Plus, Search, Loader2, Pencil, Trash2, ArrowUp, ArrowDown,
} from 'lucide-vue-next';

export default {
  name: 'BlockList',
  components: { MainLayout, DataTable, CustomSelect, ChevronRight, Plus, Search, Loader2, Pencil, Trash2, ArrowUp, ArrowDown },
  setup() {
    const toast = useToastStore();
    const confirm = useConfirmStore();
    return { toast, confirm };
  },
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
    async confirmDelete(block) {
      const confirmed = await this.confirm.ask({
        title: 'Delete Block',
        message: `Are you sure you want to delete this block? This action cannot be undone.`,
        confirmText: 'Delete',
        variant: 'danger',
      });
      if (!confirmed) return;

      try {
        await axios.delete(`/auth/admin/blocks/${block.id}`);
        clearCache('/auth/admin/blocks');
        this.blocks = this.blocks.filter(b => b.id !== block.id);
        this.toast.success('Block deleted successfully.');
      } catch (e) {
        console.error('Failed to delete block', e);
        this.toast.error('Failed to delete block. Please try again.');
      }
    },
    async moveBlock(block, direction) {
      const index = this.blocks.findIndex(b => b.id === block.id);
      const newIndex = index + direction;

      if (newIndex < 0 || newIndex >= this.blocks.length) return;

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
        this.fetchBlocks();
      }
    },
  },
};
</script>
