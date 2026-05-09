<template>
  <MainLayout>
    <div class="max-w-7xl mx-auto">
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input v-model="searchQuery" type="text" placeholder="Search elements..." class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl pl-10 pr-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
          </div>
          <div class="relative">
            <select v-model="selectedCountry" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
              <option disabled value="">Select country</option>
              <option value="">All Countries</option>
              <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.iso_code }} - {{ country.name }}</option>
            </select>
          </div>
          <div class="relative">
            <select v-model="selectedBlock" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
              <option disabled value="">Select block</option>
              <option value="">All Blocks</option>
              <option v-for="block in blocks" :key="block.id" :value="block.id">{{ block.page?.country?.iso_code }} - {{ block.page?.title }} | {{ block.block_type }} - {{ block.section_title }}</option>
            </select>
          </div>
          <div class="flex gap-2">
            <button v-if="searchQuery || selectedCountry || selectedBlock" @click="clearFilters" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Clear All</button>
          </div>
        </div>
      </div>

      <!-- Elements Table -->
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-[#141A21] border-b border-gray-200 dark:border-gray-700/50">
              <tr class="text-left">
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Title</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Block/Page</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Image</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700/50">
              <tr v-if="loading" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                  <div class="flex items-center justify-center gap-2"><Loader2 class="w-5 h-5 animate-spin" /> Loading elements...</div>
                </td>
              </tr>
              <tr v-else-if="filteredElements.length === 0" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No elements found</td>
              </tr>
              <tr v-for="element in filteredElements" :key="element.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <td class="px-6 py-4">
                  <span class="text-sm font-medium text-gray-900 dark:text-white">{{ element.element_title || 'No Title' }}</span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex flex-col gap-1">
                    <div v-if="element.page_block?.page" class="flex items-center gap-1 text-[10px] font-bold uppercase tracking-tight text-primary dark:text-primary-400 mb-1">
                      <span class="bg-primary/10 px-1 rounded">{{ element.page_block.page.country?.iso_code }}</span>
                      <span>{{ element.page_block.page.title }}</span>
                    </div>
                    <span v-if="element.page_block" class="text-xs font-medium text-gray-600 dark:text-gray-400">
                      Block: {{ element.page_block?.block_type }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div v-if="element.image_path" class="w-10 h-10 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                    <img :src="element.image_path" class="w-full h-full object-cover" />
                  </div>
                  <span v-else class="text-xs text-gray-400">No Image</span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button @click="$router.push('/dashboard/element-manager/edit/' + element.id)" class="p-2 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Edit"><Pencil class="w-4 h-4" /></button>
                    <button @click="confirmDelete(element)" class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete"><Trash2 class="w-4 h-4" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700/50 flex items-center justify-between">
          <p class="text-sm text-gray-500 dark:text-gray-400">Showing {{ filteredElements.length }} of {{ elements.length }} elements</p>
        </div>
      </div>

      <!-- Delete Modal -->
      <div v-if="deleteModal.show" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center"><AlertTriangle class="w-6 h-6 text-red-600 dark:text-red-400" /></div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Element</h3>
          </div>
          <p class="text-gray-600 dark:text-gray-400 mb-6">Are you sure you want to delete this element? This action cannot be undone.</p>
          <div class="flex justify-end gap-3">
            <button @click="deleteModal.show = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">Cancel</button>
            <button @click="deleteElement" :disabled="deleteModal.loading" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl transition-colors flex items-center gap-2">
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
import {
  ChevronRight, Plus, Search, Loader2, Pencil, Trash2, AlertTriangle,
} from 'lucide-vue-next';

export default {
  name: 'ElementList',
  components: { MainLayout, ChevronRight, Plus, Search, Loader2, Pencil, Trash2, AlertTriangle },
  data() {
    return {
      elements: [],
      loading: false,
      searchQuery: '',
      selectedCountry: '',
      selectedBlock: '',
      countries: [],
      blocks: [],
      deleteModal: { show: false, element: null, loading: false },
    };
  },
  computed: {
    filteredElements() {
      if (!this.searchQuery.trim()) return this.elements;
      const query = this.searchQuery.toLowerCase();
      return this.elements.filter(e =>
        (e.element_title && e.element_title.toLowerCase().includes(query)) ||
        (e.element_body && e.element_body.toLowerCase().includes(query))
      );
    },
  },
  mounted() {
    this.fetchCountries();
    this.fetchBlocksList();
    this.fetchElements();
  },
  watch: {
    selectedCountry() {
      this.selectedBlock = '';
      this.fetchBlocksList(this.selectedCountry);
      this.fetchElements();
    },
    selectedBlock() { this.fetchElements(); },
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
    async fetchBlocksList(countryId = '') {
      try {
        const params = {};
        if (countryId) params.country_id = countryId;
        const response = await axios.get('/auth/admin/blocks', { params });
        this.blocks = response.data.data?.data || response.data.data || [];
      } catch (e) {
        console.error('Failed to load blocks', e);
      }
    },
    clearFilters() {
      this.searchQuery = '';
      this.selectedCountry = '';
      this.selectedBlock = '';
    },
    async fetchElements() {
      this.loading = true;
      try {
        const params = {};
        if (this.selectedCountry) params.country_id = this.selectedCountry;
        if (this.selectedBlock) params.block_id = this.selectedBlock;
        const response = await axios.get('/auth/admin/elements', { params });
        this.elements = response.data.data?.data || response.data.data || [];
      } catch (e) {
        console.error('Failed to load elements', e);
      } finally {
        this.loading = false;
      }
    },
    confirmDelete(element) {
      this.deleteModal.element = element;
      this.deleteModal.show = true;
    },
    async deleteElement() {
      this.deleteModal.loading = true;
      try {
        await axios.delete(`/auth/admin/elements/${this.deleteModal.element.id}`);
        this.elements = this.elements.filter(e => e.id !== this.deleteModal.element.id);
        this.deleteModal.show = false;
        this.deleteModal.element = null;
      } catch (e) {
        console.error('Failed to delete element', e);
      } finally {
        this.deleteModal.loading = false;
      }
    },
  },
};
</script>