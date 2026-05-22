<template>
  <MainLayout>
    <!-- Loading State -->
    <div v-if="loading && contacts.length === 0" class="min-h-[75vh] flex flex-col items-center justify-center bg-transparent">
      <div class="relative w-64 h-[3px] bg-gray-200 dark:bg-gray-800/80 rounded-full overflow-hidden">
        <div class="absolute inset-0 line-shimmer-sweep"></div>
      </div>
    </div>

    <div v-else class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Contact Messages</h1>
          <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mt-1">
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-900 dark:text-white">Contacts</span>
          </nav>
        </div>
        <!-- Note: Removed "New Page" button as contacts are submitted by users, not admins -->
      </div>

      <!-- Filters Card -->
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input v-model="searchQuery" type="text" placeholder="Search by name or email..." class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl pl-10 pr-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
          </div>
          <div class="flex items-center gap-2">
            <button v-if="searchQuery" @click="clearFilters" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Clear</button>
          </div>
        </div>
      </div>

      <!-- Contacts Table -->
      <DataTable 
        :columns="columns" 
        :data="filteredContacts" 
        :loading="loading"
        :pagination="pagination"
        @page-change="fetchContacts"
        @per-page-change="handlePerPageChange"
      >
        <!-- Name Column -->
        <template #cell(name)="{ item }">
          <p class="font-medium text-gray-900 dark:text-white">{{ item.name }}</p>
        </template>
        
        <!-- Email Column -->
        <template #cell(email)="{ item }">
          <span class="text-sm text-gray-600 dark:text-gray-400">{{ item.email }}</span>
        </template>
        
        <!-- Subject Column -->
        <template #cell(subject)="{ item }">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ item.subject }}</span>
        </template>
        
        <!-- Message Column (Truncated) -->
        <template #cell(message)="{ item }">
          <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-1">
            {{ item.message }}
          </p>
        </template>

        <!-- Created At Column -->
        <template #cell(created_at)="{ item }">
          <span class="text-xs text-gray-500 dark:text-gray-400">
            {{ new Date(item.created_at).toLocaleDateString() }}
          </span>
        </template>
        
        <!-- Actions Column -->
        <template #cell(actions)="{ item }">
          <div class="flex items-center justify-end gap-2">
            <button @click="openViewModal(item)" class="p-2 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="View"><Eye class="w-4 h-4" /></button>
            <button @click="confirmDelete(item)" class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete"><Trash2 class="w-4 h-4" /></button>
          </div>
        </template>
      </DataTable>
    </div>

    <!-- View Contact Message Modal -->
    <div v-if="showViewModal && selectedContact" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
      <div class="bg-white dark:bg-[#1C252E] w-full max-w-2xl rounded-2xl shadow-xl overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-100 dark:border-gray-800">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white">
            Contact Message Details
          </h3>
          <button @click="closeViewModal" class="p-2 text-gray-400 hover:bg-gray-100 dark:hover:bg-[#151C24] rounded-full transition-all">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto space-y-6">
          <!-- Contact Info Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase mb-1">Sender Name</span>
              <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ selectedContact.name }}</p>
            </div>
            <div>
              <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase mb-1">Email Address</span>
              <p class="text-sm font-semibold text-gray-900 dark:text-white select-all">{{ selectedContact.email }}</p>
            </div>
            <div>
              <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase mb-1">Phone Number</span>
              <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ selectedContact.phone || 'N/A' }}</p>
            </div>
            <div>
              <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase mb-1">Received Date</span>
              <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ new Date(selectedContact.created_at).toLocaleString() }}</p>
            </div>
          </div>

          <hr class="border-gray-100 dark:border-gray-800" />

          <!-- Subject -->
          <div>
            <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase mb-1">Subject</span>
            <p class="text-base font-semibold text-gray-900 dark:text-white">{{ selectedContact.subject }}</p>
          </div>

          <!-- Message Content -->
          <div>
            <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase mb-1">Message</span>
            <div class="mt-2 p-4 bg-gray-50 dark:bg-[#141A21] rounded-xl border border-gray-150 dark:border-gray-800 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed select-text">
              {{ selectedContact.message }}
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-6 border-t border-gray-100 dark:border-gray-800 flex justify-end bg-gray-50/50 dark:bg-[#151C24]/50">
          <button @click="closeViewModal" type="button" class="px-6 py-2 rounded-xl text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-800 transition-all">
            Close
          </button>
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
import { useToastStore } from '@/stores/toast';
import { useConfirmStore } from '@/stores/confirm';
import {
  ChevronRight, Search, Trash2, Eye, X,
} from 'lucide-vue-next';

export default {
  name: 'ContactList',
  components: { MainLayout, DataTable, ChevronRight, Search, Trash2, Eye, X },
  setup() {
    const toast = useToastStore();
    const confirm = useConfirmStore();
    return { toast, confirm };
  },
  data() {
    return {
      columns: [
        { key: 'name', label: 'Name' },
        { key: 'email', label: 'Email' },
        { key: 'subject', label: 'Subject' },
        { key: 'message', label: 'Message' },
        { key: 'created_at', label: 'Date', align: 'center' },
        { key: 'actions', label: 'Actions', align: 'right' }
      ],
      contacts: [],
      pagination: null,
      perPage: 15,
      loading: false,
      searchQuery: '',
      showViewModal: false,
      selectedContact: null,
    };
  },

  computed: {
    filteredContacts() {
      if (!this.searchQuery.trim()) return this.contacts;
      const query = this.searchQuery.toLowerCase();
      return this.contacts.filter(c =>
        c.name.toLowerCase().includes(query) ||
        c.email.toLowerCase().includes(query) ||
        c.subject.toLowerCase().includes(query)
      );
    },
  },
  mounted() { 
    this.fetchContacts();
  },
  methods: {
    async fetchContacts(page = 1) {
      await fetchWithCache({
        url: '/auth/admin/contacts', 
        params: {
          page,
          per_page: this.perPage,
        },
        component: this,
        dataKey: 'contacts',
        loadingKey: 'loading',
        paginationKey: 'pagination'
      });
    },
    handlePerPageChange(newPerPage) {
      this.perPage = newPerPage;
      this.fetchContacts(1);
    },
    clearFilters() {
      this.searchQuery = '';
    },
    openViewModal(contact) {
      this.selectedContact = contact;
      this.showViewModal = true;
    },
    closeViewModal() {
      this.showViewModal = false;
      this.selectedContact = null;
    },
    async confirmDelete(contact) {
      const confirmed = await this.confirm.ask({
        title: 'Delete Message',
        message: `Are you sure you want to delete the message from ${contact.name}?`,
        confirmText: 'Delete',
        variant: 'danger',
      });
      if (!confirmed) return;

      try {
        await axios.delete(`/auth/admin/contacts/${contact.id}`);
        clearCache('/auth/admin/contacts');
        this.contacts = this.contacts.filter(c => c.id !== contact.id);
        this.toast.success('Message deleted successfully.');
      } catch (e) {
        console.error('Failed to delete contact', e);
        this.toast.error('Failed to delete message.');
      }
    },
  },
};
</script>
