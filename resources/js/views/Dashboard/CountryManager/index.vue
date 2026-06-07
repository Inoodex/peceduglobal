<template>
  <MainLayout>
    <!-- Loading State -->
    <div v-if="loading && countries.length === 0" class="min-h-[75vh] flex flex-col items-center justify-center bg-transparent">
      <div class="relative w-64 h-[3px] bg-gray-200 dark:bg-gray-800/80 rounded-full overflow-hidden">
        <div class="absolute inset-0 line-shimmer-sweep"></div>
      </div>
    </div>

    <!-- Loaded State -->
    <div v-else class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="mt-6">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Countries</h1>
          <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mt-1">
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-900 dark:text-white">Countries</span>
          </nav>
        </div>
        <button @click="$router.push('/dashboard/country-manager/create')" class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-hover text-white font-medium rounded-xl transition-colors mt-6 shadow-sm">
          <Plus class="w-4 h-4" /> New country
        </button>
      </div>

      <!-- Filters Card -->
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-4 mb-4">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1 relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input v-model="searchQuery" type="text" placeholder="Search country..." class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl pl-10 pr-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
          </div>
          <button v-if="searchQuery" @click="searchQuery = ''" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors font-medium">Clear</button>
        </div>
      </div>

      <!-- Countries DataTable -->
      <DataTable 
        :columns="columns" 
        :data="filteredCountries" 
        :loading="loading"
        :pagination="pagination"
        @page-change="fetchCountries"
        @per-page-change="handlePerPageChange"
      >
        <template #cell(name)="{ item }">
          <div class="flex items-center gap-3">
            <div v-if="item.thumbnail" class="w-10 h-10 rounded-lg overflow-hidden shrink-0 border border-gray-100 dark:border-gray-800">
              <img :src="item.thumbnail" :alt="item.name" class="w-full h-full object-cover" />
            </div>
            <div v-else class="w-10 h-10 rounded-lg bg-linear-to-br from-primary/20 to-primary/10 flex items-center justify-center shrink-0">
              <Globe class="w-5 h-5 text-primary" />
            </div>
            <p class="font-medium text-gray-900 dark:text-white">{{ item.name }}</p>
          </div>
        </template>

        <template #cell(slug)="{ item }">
          <span class="text-sm text-gray-600 dark:text-gray-400 font-mono">{{ item.slug }}</span>
        </template>

        <template #cell(iso_code)="{ item }">
          <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400 uppercase">
            {{ item.iso_code }}
          </span>
        </template>

        <template #cell(phone_code)="{ item }">
          <span class="text-sm text-gray-600 dark:text-gray-400 font-mono">+{{ item.phone_code }}</span>
        </template>

        <template #cell(actions)="{ item }">
          <div class="flex items-center justify-end gap-2">
            <button @click="$router.push(`/dashboard/country-manager/edit/${item.id}`)" class="p-2 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Edit">
              <Pencil class="w-4 h-4" />
            </button>
            <button @click="confirmDelete(item)" class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete">
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
import { fetchWithCache, clearCache } from '@/utils/cacheHelper';
import { useToastStore } from '@/stores/toast';
import { useConfirmStore } from '@/stores/confirm';
import {
  ChevronRight, Plus, Search, Loader2, Globe, Pencil, Trash2,
} from 'lucide-vue-next';

export default {
  name: 'CountryList',
  components: { MainLayout, DataTable, ChevronRight, Plus, Search, Loader2, Globe, Pencil, Trash2 },
  setup() {
    const toast = useToastStore();
    const confirm = useConfirmStore();
    return { toast, confirm };
  },
  data() {
    return {
      countries: [],
      loading: false,
      searchQuery: '',
      pagination: null,
      perPage: 15,
      columns: [
        { key: 'name', label: 'Country' },
        { key: 'slug', label: 'Slug' },
        { key: 'iso_code', label: 'ISO Code' },
        { key: 'phone_code', label: 'Phone Code' },
        { key: 'actions', label: 'Actions', align: 'right' }
      ]
    };
  },
  computed: {
    filteredCountries() {
      if (!this.searchQuery.trim()) return this.countries;
      const query = this.searchQuery.toLowerCase();
      return this.countries.filter(c =>
        c.name.toLowerCase().includes(query) ||
        c.slug.toLowerCase().includes(query) ||
        c.iso_code.toLowerCase().includes(query) ||
        c.phone_code.includes(query)
      );
    },
  },
  mounted() { 
    this.fetchCountries(1); 
  },
  methods: {
    async fetchCountries(page = 1) {
      await fetchWithCache({
        url: '/auth/admin/countries',
        params: { page, per_page: this.perPage },
        component: this,
        loadingKey: 'loading',
        dataKey: 'countries',
        paginationKey: 'pagination'
      });
    },
    handlePerPageChange(newPerPage) {
      this.perPage = newPerPage;
      this.fetchCountries(1);
    },
    async confirmDelete(country) {
      const confirmed = await this.confirm.ask({
        title: 'Delete Country',
        message: `Are you sure you want to delete "<strong>${country.name}</strong>"? This action cannot be undone.`,
        confirmText: 'Delete',
        variant: 'danger',
      });
      if (!confirmed) return;

      try {
        await axios.delete(`/auth/admin/countries/${country.id}`);
        clearCache('/auth/admin/countries');
        this.countries = this.countries.filter(c => c.id !== country.id);
        this.toast.success('Country deleted successfully.');
      } catch (e) {
        console.error('Failed to delete country', e);
        this.toast.error('Failed to delete country. Please try again.');
      }
    },
  },
};
</script>
