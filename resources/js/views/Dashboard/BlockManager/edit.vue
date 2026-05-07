<template>
  <MainLayout>
    <div class="max-w-5xl mx-auto pb-20 px-4">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 mt-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Block</h1>
          <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mt-2">
            <span class="hover:text-primary cursor-pointer transition-colors" @click="$router.push('/dashboard')">Dashboard</span>
            <ChevronRight class="w-4 h-4" />
            <span class="hover:text-primary cursor-pointer transition-colors" @click="$router.push('/dashboard/block-manager')">Block Manager</span>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-900 dark:text-white font-medium">Edit</span>
          </nav>
        </div>
        <div class="flex items-center gap-3">
          <button type="button" @click="$router.push('/dashboard/block-manager')" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-all">Cancel</button>
          <button @click="save" :disabled="loading" class="px-6 py-2 text-sm font-semibold text-white bg-primary hover:bg-primary-hover rounded-xl transition-all shadow-lg shadow-primary/20 flex items-center gap-2">
            <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
            {{ loading ? 'Updating...' : 'Update Block' }}
          </button>
        </div>
      </div>

      <form @submit.prevent="save" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form Section -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Basic Details -->
          <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700/50 bg-gray-50/50 dark:bg-[#141A21]/50 flex items-center gap-3">
              <div class="p-2 bg-primary/10 rounded-lg text-primary"><Layout class="w-5 h-5" /></div>
              <h3 class="font-bold text-gray-900 dark:text-white">Block Configuration</h3>
            </div>
            <div class="p-6 space-y-5">
              <div class="grid grid-cols-1 gap-5">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Target Page <span class="text-red-500">*</span></label>
                  <select v-model="form.page_id" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" required>
                    <option value="">Select Page</option>
                    <option v-for="page in pages" :key="page.id" :value="page.id">{{ page.title }}</option>
                  </select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Block Type <span class="text-red-500">*</span></label>
                    <input v-model="form.block_type" type="text" placeholder="e.g. Hero, Features, FAQ" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" required />
                  </div>
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Sort Order</label>
                    <input v-model="form.sort_order" type="number" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                  </div>
                </div>
                <div class="grid grid-cols-1 gap-5">
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Section Title</label>
                    <input v-model="form.section_title" type="text" placeholder="e.g. Our Top Universities" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                  </div>
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Section Description</label>
                    <textarea v-model="form.section_description" rows="4" placeholder="Enter section description..." class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"></textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Preview Sidebar -->
        <div class="lg:col-span-1 space-y-6">
          <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm sticky top-6">
            <h3 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <Eye class="w-5 h-5 text-primary" /> Block Preview
            </h3>
            <div class="space-y-4">
              <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-600 text-center">
                <div class="text-xs font-bold text-gray-400 uppercase mb-2">Block Type</div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">{{ form.block_type || 'Not Specified' }}</div>
              </div>
              <div class="space-y-2">
                <div class="text-xs font-bold text-primary uppercase tracking-wider">Section Title</div>
                <div class="text-md font-semibold text-gray-900 dark:text-white truncate">{{ form.section_title || 'No title provided' }}</div>
              </div>
              <div class="space-y-2">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Description</div>
                <div class="text-sm text-gray-600 dark:text-gray-400 line-clamp-3 italic">
                  {{ form.section_description || 'No description provided...' }}
                </div>
              </div>
              <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <span class="text-xs text-gray-500">Order: {{ form.sort_order }}</span>
                <span class="text-xs px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-md font-medium">
                  {{ pages.find(p => p.id == form.page_id)?.title || 'No Page' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </MainLayout>
</template>

<script>
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import { ChevronRight, Loader2, Layout, Eye } from 'lucide-vue-next';

export default {
  name: 'BlockEdit',
  components: { MainLayout, ChevronRight, Loader2, Layout, Eye },
  data() {
    return {
      loading: false,
      pages: [],
      form: {
        page_id: '',
        block_type: '',
        section_title: '',
        section_description: '',
        sort_order: 0
      },
    };
  },
  mounted() {
    this.fetchPages();
    this.fetchBlock();
  },
  methods: {
    async fetchPages() {
      try {
        const response = await axios.get('/api/admin/pages');
        this.pages = response.data.data?.data || response.data.data || [];
      } catch (error) {
        console.error('Error fetching pages:', error);
      }
    },
    async fetchBlock() {
      try {
        const response = await axios.get(`/api/admin/blocks/${this.$route.params.id}`);
        this.form = response.data.data;
      } catch (error) {
        console.error('Error fetching block:', error);
      }
    },
    async save() {
      this.loading = true;
      try {
        await axios.put(`/api/admin/blocks/${this.$route.params.id}`, this.form);
        this.$router.push('/dashboard/block-manager');
      } catch (error) {
        console.error('Error updating block:', error);
        alert('Error updating block. Please check your input.');
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>