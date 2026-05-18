<template>
  <MainLayout>
    <!-- Loading State: Clean Centered Shimmer Progress Line (Only shown on very first load) -->
    <div v-if="loading && countries.length === 0" class="min-h-[75vh] flex flex-col items-center justify-center bg-transparent">
      <!-- Premium Progress Track Line -->
      <div class="relative w-64 h-[3px] bg-gray-200 dark:bg-gray-800/80 rounded-full overflow-hidden">
        <div class="absolute inset-0 line-shimmer-sweep"></div>
      </div>
    </div>

    <!-- Loaded State: Actual Page Content -->
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

      <!-- Delete Modal -->
      <div v-if="deleteModal.show" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 animate-fade-in">
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl border border-gray-100 dark:border-gray-800">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
              <AlertTriangle class="w-6 h-6 text-red-600 dark:text-red-400" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Country</h3>
          </div>
          <p class="text-gray-600 dark:text-gray-400 mb-6">Are you sure you want to delete "<strong class="text-gray-900 dark:text-white">{{ deleteModal.country?.name }}</strong>"? This action cannot be undone.</p>
          <div class="flex justify-end gap-3">
            <button @click="deleteModal.show = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors font-medium">Cancel</button>
            <button @click="deleteCountry" :disabled="deleteModal.loading" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl transition-colors flex items-center gap-2 font-medium">
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

<script>
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import DataTable from '@/components/Table/DataTable.vue';
import { fetchWithCache, clearCache } from '@/utils/cacheHelper';
import {
  ChevronRight, Plus, Search, Loader2, Globe, Pencil, Trash2, AlertTriangle,
} from 'lucide-vue-next';

export default {
  name: 'CountryList',
  components: { MainLayout, DataTable, ChevronRight, Plus, Search, Loader2, Globe, Pencil, Trash2, AlertTriangle },
  data() {
    return {
      countries: [],
      loading: false,
      searchQuery: '',
      deleteModal: { show: false, country: null, loading: false },
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
    this.fetchCountries(); 
  },
  methods: {
    async fetchCountries() {
      await fetchWithCache({
        url: '/auth/admin/countries',
        params: {
          per_page: 500 // Large limit to enable local instant searching
        },
        component: this,
        loadingKey: 'loading',
        dataKey: 'countries'
      });
    },
    confirmDelete(country) {
      this.deleteModal.country = country;
      this.deleteModal.show = true;
    },
    async deleteCountry() {
      this.deleteModal.loading = true;
      try {
        await axios.delete(`/auth/admin/countries/${this.deleteModal.country.id}`);
        clearCache('/auth/admin/countries');
        this.countries = this.countries.filter(c => c.id !== this.deleteModal.country.id);
        this.deleteModal.show = false;
        this.deleteModal.country = null;
      } catch (e) {
        console.error('Failed to delete country', e);
      } finally {
        this.deleteModal.loading = false;
      }
    },
  },
};
</script>
