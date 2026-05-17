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
          <button v-if="searchQuery" @click="searchQuery = ''" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Clear</button>
        </div>
      </div>

      <!-- Team Members Table -->
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-[#141A21] border-b border-gray-200 dark:border-gray-700/50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Member</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Designation</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Social Links</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700/50">
              <tr v-if="loading" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                  <div class="flex items-center justify-center gap-2"><Loader2 class="w-5 h-5 animate-spin" /> Loading team members...</div>
                </td>
              </tr>
              <tr v-else-if="filteredMembers.length === 0" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No team members found</td>
              </tr>
              <tr v-for="member in filteredMembers" :key="member.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <!-- Member column (Photo + Name) -->
                <td class="px-6 py-4">
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
                </td>
                <!-- Designation -->
                <td class="px-6 py-4">
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 px-2.5 py-1 rounded-lg border border-gray-150 dark:border-gray-700">{{ member.designation }}</span>
                </td>
                <!-- Social Links -->
                <td class="px-6 py-4">
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
                </td>
                <!-- Status -->
                <td class="px-6 py-4">
                  <span :class="[
                    'px-2.5 py-1 text-[10px] font-black uppercase rounded-md border inline-block',
                    member.status ? 'bg-green-50 text-green-600 border-green-100 dark:bg-green-950/20 dark:text-green-400 dark:border-green-900/30' : 'bg-gray-50 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700'
                  ]">
                    {{ member.status ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <!-- Actions -->
                <td class="px-6 py-4">
                  <div class="flex items-center justify-end gap-2">
                    <button @click="$router.push(`/dashboard/team-members/edit/${member.id}`)" class="p-2 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Edit"><Pencil class="w-4 h-4" /></button>
                    <button @click="confirmDelete(member)" class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete"><Trash2 class="w-4 h-4" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Pagination metadata / count -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700/50 flex items-center justify-between">
          <p class="text-sm text-gray-500 dark:text-gray-400">Showing {{ filteredMembers.length }} of {{ members.length }} team members</p>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div v-if="deleteModal.show" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 transition-all backdrop-blur-xs">
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl border border-gray-200 dark:border-gray-700/50">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center"><AlertTriangle class="w-6 h-6 text-red-600 dark:text-red-400" /></div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Team Member</h3>
          </div>
          <p class="text-gray-600 dark:text-gray-400 mb-6">Are you sure you want to delete "<strong class="text-gray-900 dark:text-white">{{ deleteModal.member?.name }}</strong>"? This action cannot be undone.</p>
          <div class="flex justify-end gap-3">
            <button @click="deleteModal.show = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">Cancel</button>
            <button @click="deleteMember" :disabled="deleteModal.loading" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl transition-colors flex items-center gap-2">
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
  ChevronRight, Plus, Search, Loader2, User, Pencil, Trash2, AlertTriangle,
  Facebook, Twitter, Linkedin, Instagram
} from 'lucide-vue-next';

export default {
  name: 'TeamMembersList',
  components: {
    MainLayout, ChevronRight, Plus, Search, Loader2, User, Pencil, Trash2, AlertTriangle,
    Facebook, Twitter, Linkedin, Instagram
  },
  data() {
    return {
      members: [],
      loading: false,
      searchQuery: '',
      deleteModal: { show: false, member: null, loading: false },
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
    this.fetchMembers();
  },
  methods: {
    async fetchMembers() {
      this.loading = true;
      try {
        const response = await axios.get('/auth/admin/team-members');
        this.members = response.data.data?.data || response.data.data || [];
      } catch (e) {
        console.error('Failed to load team members', e);
      } finally {
        this.loading = false;
      }
    },
    hasAnySocialLink(member) {
      if (!member.social_links) return false;
      return Object.values(member.social_links).some(link => link !== '' && link !== null);
    },
    confirmDelete(member) {
      this.deleteModal.member = member;
      this.deleteModal.show = true;
    },
    async deleteMember() {
      this.deleteModal.loading = true;
      try {
        await axios.delete(`/auth/admin/team-members/${this.deleteModal.member.id}`);
        this.members = this.members.filter(m => m.id !== this.deleteModal.member.id);
        this.deleteModal.show = false;
        this.deleteModal.member = null;
      } catch (e) {
        console.error('Failed to delete team member', e);
      } finally {
        this.deleteModal.loading = false;
      }
    },
  },
};
</script>
