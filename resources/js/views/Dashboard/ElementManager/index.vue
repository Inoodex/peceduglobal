<template>
  <MainLayout>
    <!-- Loading State: Clean Centered Shimmer Progress Line (Only shown on very first load) -->
    <div v-if="loading && elements.length === 0" class="min-h-[75vh] flex flex-col items-center justify-center bg-transparent">
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
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Element Manager</h1>
          <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mt-1">
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-900 dark:text-white">Element Manager</span>
          </nav>
        </div>
        <button @click="$router.push('/dashboard/element-manager/create')" class="mt-6 inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-hover text-white font-medium rounded-xl transition-colors">
          <Plus class="w-4 h-4" /> New element
        </button>
      </div>

      <!-- Filters Card -->
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-4 mb-4">
        <div class="flex flex-col gap-3">
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
            <div class="relative">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input v-model="searchQuery" type="text" placeholder="Search elements..." class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl pl-10 pr-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
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
                :disabled="!selectedCountry"
              />
            </div>
            <div class="relative pt-1">
              <CustomSelect 
                v-model="selectedBlock" 
                :options="blockOptions" 
                label="Block"
                placeholder="All Blocks"
                labelKey="label"
                valueKey="id"
                :clearable="true"
                :disabled="!selectedPage"
              />
            </div>
          </div>
          <div v-if="searchQuery || selectedCountry || selectedPage || selectedBlock" class="flex justify-end">
            <button @click="clearFilters" class="text-sm font-semibold text-red-500 hover:text-red-600 dark:hover:text-red-400 transition-colors cursor-pointer">
              Clear All Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Elements Table -->
      <DataTable 
        :columns="columns" 
        :data="elements" 
        :loading="loading"
        :pagination="pagination"
        @page-change="fetchElements"
        @per-page-change="handlePerPageChange"
      >
        <template #cell(title)="{ item }">
          <div :title="item.element_title || 'No Title'" class="font-medium text-gray-900 dark:text-white text-sm truncate max-w-[240px]">{{ item.element_title || 'No Title' }}</div>
        </template>

        <template #cell(block_page)="{ item }">
          <div class="flex flex-col gap-1 max-w-[260px]">
            <div v-if="item.page_block?.page" :title="item.page_block.page.title" class="flex items-center gap-1">
              <span class="text-[10px] font-bold uppercase bg-primary/10 text-primary px-1.5 py-0.5 rounded shrink-0">{{ item.page_block.page.country?.iso_code }}</span>
              <span class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate">{{ item.page_block.page.title }}</span>
            </div>
            <span v-if="item.page_block" :title="item.page_block?.block_type + (item.page_block?.section_title ? ' - ' + item.page_block.section_title : '')" class="text-xs text-gray-500 dark:text-gray-400 font-mono truncate">
              {{ item.page_block?.block_type }}
            </span>
          </div>
        </template>

        <template #cell(image)="{ item }">
          <div v-if="item.image_path" class="w-10 h-10 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700/50">
            <img :src="item.image_path" class="w-full h-full object-cover" />
          </div>
          <span v-else class="text-xs text-gray-400">—</span>
        </template>

        <template #cell(actions)="{ item }">
          <div class="flex items-center justify-end gap-1">
            <button @click="$router.push('/dashboard/element-manager/edit/' + item.id)" class="p-1.5 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Edit"><Pencil class="w-4 h-4" /></button>
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
  ChevronRight, Plus, Search, Loader2, Pencil, Trash2,
} from 'lucide-vue-next';

export default {
  name: 'ElementList',
  components: { MainLayout, DataTable, CustomSelect, ChevronRight, Plus, Search, Loader2, Pencil, Trash2 },
  setup() {
    const toast = useToastStore();
    const confirm = useConfirmStore();
    return { toast, confirm };
  },
  data() {
    return {
      columns: [
        { key: 'title', label: 'Title' },
        { key: 'block_page', label: 'Block / Page' },
        { key: 'image', label: 'Image', width: 'w-20' },
        { key: 'actions', label: 'Actions', align: 'right' }
      ],
      elements: [],
      pagination: null,
      perPage: 15,
      loading: false,
      searchQuery: '',
      selectedCountry: '',
      selectedPage: '',
      selectedBlock: '',
      countries: [],
      pages: [],
      blocks: [],
    };
  },

  computed: {
    pageOptions() {
      return this.pages.map(p => ({
        ...p,
        label: `${p.country?.iso_code ? p.country.iso_code + ' - ' : ''}${p.title}`
      }));
    },
    blockOptions() {
      return this.blocks.map(b => ({
        ...b,
        label: `${b.page?.country?.iso_code ? b.page.country.iso_code + ' | ' : ''}${b.block_type}${b.section_title ? ' - ' + b.section_title : ''}`
      }));
    },
  },
  mounted() {
    this.fetchCountries();
    this.fetchPagesList();
    this.fetchBlocksList();
    this.fetchElements();
  },
  watch: {
    selectedCountry() {
      this.selectedPage = '';
      this.selectedBlock = '';
      this.fetchPagesList(this.selectedCountry);
      this.fetchBlocksList(this.selectedCountry);
      this.fetchElements(1);
    },
    selectedPage() {
      this.selectedBlock = '';
      this.fetchBlocksList(this.selectedCountry, this.selectedPage);
      this.fetchElements(1);
    },
    selectedBlock() { this.fetchElements(1); },
    searchQuery() {
      clearTimeout(this._searchTimer);
      this._searchTimer = setTimeout(() => this.fetchElements(1), 400);
    },
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
    async fetchPagesList(countryId = '') {
      try {
        const params = { all: true };
        if (countryId) params.country_id = countryId;
        const response = await axios.get('/auth/admin/pages', { params });
        this.pages = response.data.data?.data || response.data.data || [];
      } catch (e) {
        console.error('Failed to load pages', e);
      }
    },
    async fetchBlocksList(countryId = '', pageId = '') {
      try {
        const params = { all: true };
        if (countryId) params.country_id = countryId;
        if (pageId) params.page_id = pageId;
        const response = await axios.get('/auth/admin/blocks', { params });
        this.blocks = response.data.data?.data || response.data.data || [];
      } catch (e) {
        console.error('Failed to load blocks', e);
      }
    },
    clearFilters() {
      this.searchQuery = '';
      this.selectedCountry = '';
      this.selectedPage = '';
      this.selectedBlock = '';
    },
    async fetchElements(page = 1) {
      await fetchWithCache({
        url: '/auth/admin/elements',
        params: {
          page,
          per_page: this.perPage,
          country_id: this.selectedCountry,
          page_id: this.selectedPage || undefined,
          block_id: this.selectedBlock,
          search: this.searchQuery || undefined
        },
        component: this,
        dataKey: 'elements',
        loadingKey: 'loading',
        paginationKey: 'pagination'
      });
    },
    handlePerPageChange(newPerPage) {
      this.perPage = newPerPage;
      this.fetchElements(1);
    },
    async confirmDelete(element) {
      const confirmed = await this.confirm.ask({
        title: 'Delete Element',
        message: 'Are you sure you want to delete this element? This action cannot be undone.',
        confirmText: 'Delete',
        variant: 'danger',
      });
      if (!confirmed) return;

      try {
        await axios.delete(`/auth/admin/elements/${element.id}`);
        clearCache('/auth/admin/elements');
        this.elements = this.elements.filter(e => e.id !== element.id);
        this.toast.success('Element deleted successfully.');
      } catch (e) {
        console.error('Failed to delete element', e);
        this.toast.error('Failed to delete element. Please try again.');
      }
    },
  },
};
</script>