<template>
  <MainLayout>
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Team Members</h1>
          <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mt-1">
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-900 dark:text-white">Team Members</span>
          </nav>
        </div>
        <button @click="$router.push('/dashboard/team-members/create')" class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-hover text-white font-medium rounded-xl transition-colors">
          <Plus class="w-4 h-4" /> New Member
        </button>
      </div>

      <!-- Filters Card -->
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-4 mb-4">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1 relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input v-model="searchQuery" type="text" placeholder="Search team member by name or designation..." class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl pl-10 pr-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
          </div>
          <button v-if="searchQuery" @click="searchQuery = ''; clearFiltersState('team_members');" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors cursor-pointer">Clear</button>
        </div>
      </div>

      <!-- Team Members Table -->
      <DataTable 
        :columns="columns" 
        :data="filteredMembers" 
        :loading="loading"
        :pagination="pagination"
        @page-change="fetchMembers"
        @per-page-change="handlePerPageChange"
      >
        <!-- Member column (Photo + Name) -->
        <template #cell(member)="{ item: member }">
          <div class="flex items-center gap-3">
            <div v-if="member.photo" class="w-10 h-10 rounded-full overflow-hidden shrink-0 border border-gray-100 dark:border-gray-700">
              <img :src="member.photo" :alt="member.name" class="w-full h-full object-cover" />
            </div>
            <div v-else class="w-10 h-10 rounded-full bg-linear-to-br from-primary/20 to-primary/10 flex items-center justify-center shrink-0 border border-gray-100 dark:border-gray-700">
              <User class="w-5 h-5 text-primary" />
            </div>
            <div>
              <p class="font-semibold text-gray-900 dark:text-white">{{ member.name }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[200px]" v-if="member.content">{{ member.content }}</p>
            </div>
          </div>
        </template>
        
        <!-- Designation -->
        <template #cell(designation)="{ item: member }">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 px-2.5 py-1 rounded-lg border border-gray-150 dark:border-gray-700">{{ member.designation }}</span>
        </template>
        
        <!-- Social Links -->
        <template #cell(social_links)="{ item: member }">
          <div class="flex items-center gap-2">
            <a v-if="member.social_links && member.social_links.facebook" :href="member.social_links.facebook" target="_blank" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all" title="Facebook">
              <Facebook class="w-4 h-4" />
            </a>
            <a v-if="member.social_links && member.social_links.twitter" :href="member.social_links.twitter" target="_blank" class="p-1.5 text-gray-400 hover:text-sky-500 hover:bg-sky-50 dark:hover:bg-sky-900/20 rounded-lg transition-all" title="Twitter">
              <Twitter class="w-4 h-4" />
            </a>
            <a v-if="member.social_links && member.social_links.linkedin" :href="member.social_links.linkedin" target="_blank" class="p-1.5 text-gray-400 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all" title="LinkedIn">
              <Linkedin class="w-4 h-4" />
            </a>
            <a v-if="member.social_links && member.social_links.instagram" :href="member.social_links.instagram" target="_blank" class="p-1.5 text-gray-400 hover:text-pink-600 hover:bg-pink-50 dark:hover:bg-pink-900/20 rounded-lg transition-all" title="Instagram">
              <Instagram class="w-4 h-4" />
            </a>
            <span v-if="!hasAnySocialLink(member)" class="text-xs text-gray-400 italic">None</span>
          </div>
        </template>
        
        <!-- Status -->
        <template #cell(status)="{ item: member }">
          <span :class="[
            'px-2.5 py-1 text-[10px] font-black uppercase rounded-md border inline-block',
            member.status ? 'bg-green-50 text-green-600 border-green-100 dark:bg-green-950/20 dark:text-green-400 dark:border-green-900/30' : 'bg-gray-50 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700'
          ]">
            {{ member.status ? 'Active' : 'Inactive' }}
          </span>
        </template>
        
        <!-- Actions -->
        <template #cell(actions)="{ item: member }">
          <div class="flex items-center justify-end gap-2">
            <button @click="$router.push(`/dashboard/team-members/edit/${member.id}`)" class="p-2 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Edit"><Pencil class="w-4 h-4" /></button>
            <button @click="confirmDelete(member)" class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete"><Trash2 class="w-4 h-4" /></button>
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
import { useToastStore } from '@/stores/toast';
import { useConfirmStore } from '@/stores/confirm';
import { fetchWithCache, clearCache } from '@/utils/cacheHelper';
import { saveFiltersState, restoreFiltersState, clearFiltersState } from '@/utils/filterHelper';
import {
  ChevronRight, Plus, Search, Loader2, User, Pencil, Trash2,
  Facebook, Twitter, Linkedin, Instagram
} from 'lucide-vue-next';

export default {
  name: 'TeamMembersList',
  components: {
    MainLayout, DataTable, ChevronRight, Plus, Search, Loader2, User, Pencil, Trash2,
    Facebook, Twitter, Linkedin, Instagram
  },
  setup() {
    const toast = useToastStore();
    const confirm = useConfirmStore();
    return { toast, confirm };
  },
  data() {
    return {
      members: [],
      loading: false,
      searchQuery: '',
      pagination: null,
      perPage: 15,
      columns: [
        { key: 'member', label: 'Member' },
        { key: 'designation', label: 'Designation' },
        { key: 'social_links', label: 'Social Links' },
        { key: 'status', label: 'Status' },
        { key: 'actions', label: 'Actions', align: 'right' }
      ]
    };
  },
  computed: {
    filteredMembers() {
      if (!this.searchQuery.trim()) return this.members;
      const query = this.searchQuery.toLowerCase();
      return this.members.filter(m =>
        m.name.toLowerCase().includes(query) ||
        m.designation.toLowerCase().includes(query)
      );
    },
  },
  mounted() {
    const state = restoreFiltersState('team_members', { searchQuery: '', page: 1 });
    this.searchQuery = state.searchQuery;
    this.fetchMembers(state.page);
  },
  watch: {
    searchQuery() {
      clearTimeout(this._searchTimer);
      this._searchTimer = setTimeout(() => {
        saveFiltersState('team_members', { page: 1, searchQuery: this.searchQuery });
        this.fetchMembers(1);
      }, 400);
    }
  },
  methods: {
    async fetchMembers(page = 1) {
      saveFiltersState('team_members', {
        page,
        searchQuery: this.searchQuery
      });
      await fetchWithCache({
        url: '/auth/admin/team-members',
        params: {
          page,
          per_page: this.perPage
        },
        component: this,
        dataKey: 'members',
        loadingKey: 'loading',
        paginationKey: 'pagination'
      });
    },
    handlePerPageChange(newPerPage) {
      this.perPage = newPerPage;
      this.fetchMembers(1);
    },
    hasAnySocialLink(member) {
      if (!member.social_links) return false;
      return Object.values(member.social_links).some(link => link !== '' && link !== null);
    },
    async confirmDelete(member) {
      const confirmed = await this.confirm.ask({
        title: 'Delete Team Member',
        message: `Are you sure you want to delete "${member.name}"? This action cannot be undone.`,
        confirmText: 'Delete',
        variant: 'danger',
      });
      if (!confirmed) return;

      try {
        await axios.delete(`/auth/admin/team-members/${member.id}`);
        clearCache('/auth/admin/team-members');
        this.members = this.members.filter(m => m.id !== member.id);
        this.toast.success('Team member deleted successfully.');
      } catch (e) {
        console.error('Failed to delete team member', e);
        this.toast.error('Failed to delete team member. Please try again.');
      }
    },
  },
};
</script>
