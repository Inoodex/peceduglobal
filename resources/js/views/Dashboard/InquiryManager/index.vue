<template>
  <MainLayout>
    <div class="p-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ pageTitle }}</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">Manage {{ pageDescription }}</p>
        </div>
      </div>

      <!-- Filters & Table -->
      <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700/50 flex flex-wrap gap-4">
          <select v-model="filters.status" @change="fetchInquiries" class="bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20">
            <option value="">All Status</option>
            <option value="new">New</option>
            <option value="contacted">Contacted</option>
            <option value="pending">Pending</option>
            <option value="closed">Closed</option>
          </select>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-gray-50 dark:bg-[#141A21] border-b border-gray-200 dark:border-gray-700/50">
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Student</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Contact</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700/50">
              <tr v-for="inquiry in inquiries" :key="inquiry.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                <td class="px-6 py-4">
                  <div class="font-bold text-gray-900 dark:text-white">{{ inquiry.first_name }} {{ inquiry.last_name }}</div>
                  <div class="text-xs text-gray-500 mt-1">{{ formatDate(inquiry.created_at) }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900 dark:text-white font-medium">{{ inquiry.email }}</div>
                  <div class="text-sm text-gray-500">{{ inquiry.phone }}</div>
                </td>
                <td class="px-6 py-4">
                  <span class="px-3 py-1 text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-full uppercase tracking-tight">
                    {{ inquiry.type.replace('_', ' ') }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="relative inline-block">
                    <select 
                      v-model="inquiry.status" 
                      @change="updateStatus(inquiry)" 
                      :class="[
                        'appearance-none pl-3 pr-8 py-1 text-[10px] font-bold rounded-lg border cursor-pointer transition-all focus:outline-none uppercase tracking-wider shadow-sm',
                        statusStyles(inquiry.status)
                      ]"
                    >
                      <option value="new">NEW</option>
                      <option value="contacted">CONTACTED</option>
                      <option value="pending">PENDING</option>
                      <option value="closed">CLOSED</option>
                    </select>
                    <div class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none opacity-40">
                      <ChevronDown class="w-2.5 h-2.5" />
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button @click="showInquiryDetails(inquiry)" class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="View Details">
                      <Eye class="w-5 h-5" />
                    </button>
                    <button @click="deleteInquiry(inquiry.id)" class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors" title="Delete">
                      <Trash2 class="w-5 h-5" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="inquiries.length === 0">
                <td colspan="5" class="px-6 py-12 text-center text-gray-500">No inquiries found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal for Inquiry Details -->
      <div v-if="selectedInquiry" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-[#1C252E] w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-[#141A21]">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Inquiry Details</h3>
            <button @click="selectedInquiry = null" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-full transition-colors">
              <X class="w-5 h-5" />
            </button>
          </div>
          
          <div class="p-6 max-h-[70vh] overflow-y-auto">
            <div class="grid grid-cols-2 gap-6 mb-8">
              <div>
                <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Student Name</p>
                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ selectedInquiry.first_name }} {{ selectedInquiry.last_name }}</p>
              </div>
              <div>
                <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Inquiry Type</p>
                <p class="text-sm font-bold text-primary capitalize">{{ selectedInquiry.type.replace('_', ' ') }}</p>
              </div>
              <div>
                <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Email Address</p>
                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ selectedInquiry.email }}</p>
              </div>
              <div>
                <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Phone Number</p>
                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ selectedInquiry.phone }}</p>
              </div>
            </div>

            <div class="bg-gray-50 dark:bg-[#141A21] rounded-2xl p-6 border border-gray-100 dark:border-gray-800">
              <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <FileText class="w-4 h-4 text-primary" />
                Additional Information
              </h4>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div v-for="(value, key) in selectedInquiry.additional_info" :key="key" class="p-3 bg-white dark:bg-[#1C252E] rounded-xl border border-gray-200 dark:border-gray-700/50">
                  <p class="text-[10px] uppercase font-bold text-gray-400 mb-0.5">{{ formatKey(key) }}</p>
                  <div v-if="key === 'uploaded_file' && value">
                    <a :href="value" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-primary underline flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"/></svg>
                      <span>{{ extractFilename(value) }}</span>
                    </a>
                  </div>
                  <p v-else class="text-sm font-medium text-gray-900 dark:text-gray-200">{{ formatValue(value) }}</p>
                </div>
              </div>
            </div>

            <div class="mt-6">
              <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Admin Notes</label>
              <textarea v-model="selectedInquiry.admin_notes" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-2xl p-4 text-sm focus:ring-2 focus:ring-primary/20 focus:outline-none" rows="3" placeholder="Add internal notes here..."></textarea>
            </div>
          </div>

          <div class="px-6 py-4 bg-gray-50 dark:bg-[#141A21] border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
            <button @click="selectedInquiry = null" class="px-5 py-2 text-sm font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-xl transition-colors">Close</button>
            <button @click="saveNotes" class="px-5 py-2 text-sm font-bold bg-primary text-white hover:bg-primary/90 rounded-xl transition-colors shadow-lg shadow-primary/20">Save Changes</button>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import { Trash2, Eye, FileText, X, ChevronDown } from 'lucide-vue-next';

export default {
  name: 'InquiryIndex',
  components: { MainLayout, Trash2, Eye, FileText, X, ChevronDown },
  props: {
    type: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      inquiries: [],
      loading: false,
      selectedInquiry: null,
      filters: {
        status: '',
        type: this.type || '',
      },
    };
  },
  watch: {
    type: {
      handler(newType) {
        this.filters.type = newType || '';
        this.fetchInquiries();
      },
      immediate: true
    }
  },
  computed: {
    pageTitle() {
      switch (this.filters.type) {
        case 'university_apply': return 'Student Inquiries';
        case 'air_ticket': return 'Air Ticket Bookings';
        case 'career_opportunity': return 'Career Opportunities';
        case 'consultation': return 'Consultation Requests';
        default: return 'Leads & Inquiries';
      }
    },
    pageDescription() {
      switch (this.filters.type) {
        case 'university_apply': return 'student university applications and inquiries';
        case 'air_ticket': return 'flight details and ticket booking requests';
        case 'career_opportunity': return 'career opportunities submitted by students/leads';
        case 'consultation': return 'consultation and counseling appointments';
        default: return 'all types of student inquiries and leads';
      }
    }
  },
  methods: {
    async fetchInquiries() {
      this.loading = true;
      try {
        const response = await axios.get('/auth/admin/inquiries', { params: this.filters });
        this.inquiries = response.data.data.data || [];
      } catch (error) {
        console.error('Error fetching inquiries:', error);
      } finally {
        this.loading = false;
      }
    },
    showInquiryDetails(inquiry) {
      this.selectedInquiry = { ...inquiry };
    },
    async saveNotes() {
      try {
        await axios.put(`/auth/admin/inquiries/${this.selectedInquiry.id}`, {
          admin_notes: this.selectedInquiry.admin_notes,
          status: this.selectedInquiry.status
        });
        this.selectedInquiry = null;
        this.fetchInquiries();
      } catch (error) {
        alert('Failed to save changes');
      }
    },
    async updateStatus(inquiry) {
      try {
        await axios.put(`/auth/admin/inquiries/${inquiry.id}`, { status: inquiry.status });
      } catch (error) {
        console.error('Error updating status:', error);
      }
    },
    async deleteInquiry(id) {
      if (!confirm('Are you sure you want to delete this inquiry?')) return;
      try {
        await axios.delete(`/auth/admin/inquiries/${id}`);
        this.fetchInquiries();
      } catch (error) {
        console.error('Error deleting inquiry:', error);
      }
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      });
    },
    formatKey(key) {
      return key.replace(/_/g, ' ');
    },
    statusStyles(status) {
      const map = {
        new: 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-900/10 dark:text-blue-500 dark:border-blue-900/20',
        contacted: 'bg-indigo-50 text-indigo-700 border-indigo-100 dark:bg-indigo-900/10 dark:text-indigo-500 dark:border-indigo-900/20',
        pending: 'bg-yellow-50 text-yellow-700 border-yellow-100 dark:bg-yellow-900/10 dark:text-yellow-500 dark:border-yellow-900/20',
        closed: 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/10 dark:text-emerald-500 dark:border-emerald-900/20',
      };
      return map[status] || 'bg-gray-50 text-gray-700 border-gray-100';
    }
    ,
    formatValue(value) {
      if (value === null || value === undefined || value === '') return 'N/A';
      if (typeof value === 'object') {
        try {
          return JSON.stringify(value);
        } catch (e) {
          return String(value);
        }
      }
      return String(value);
    },
    extractFilename(url) {
      if (!url) return 'file';
      try {
        const parts = url.split('/');
        return parts[parts.length - 1] || url;
      } catch (e) {
        return url;
      }
    }
  }
};
</script>
