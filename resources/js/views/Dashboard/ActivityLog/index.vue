<template>
  <MainLayout>
    <div class="max-w-7xl mx-auto">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Activity Log</h1>
          <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mt-1">
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-900 dark:text-white">Activity Log</span>
          </nav>
        </div>
      </div>

      <DataTable
        :columns="columns"
        :data="logs"
        :loading="loading"
        :pagination="pagination"
        @page-change="fetchLogs"
        @per-page-change="handlePerPageChange"
      >
        <template #toolbar>
          <div class="flex flex-col sm:flex-row items-center gap-4 w-full">
            <div class="flex-1 relative w-full">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input
                v-model="filters.search"
                type="text"
                placeholder="Search user, description or IP..."
                class="w-full h-12 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl pl-10 pr-4 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              />
            </div>
            <select
              v-model="filters.event"
              class="w-full sm:w-36 h-12 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all cursor-pointer"
            >
              <option value="">All Events</option>
              <option value="created">Created</option>
              <option value="updated">Updated</option>
              <option value="deleted">Deleted</option>
              <option value="login">Login</option>
              <option value="logout">Logout</option>
              <option value="failed_login">Failed Login</option>
            </select>
            <input
              v-model="filters.from"
              type="date"
              class="w-full sm:w-40 h-12 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            />
            <input
              v-model="filters.to"
              type="date"
              class="w-full sm:w-40 h-12 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            />
            <button
              v-if="hasFilters"
              @click="clearFilters"
              class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors h-12 flex items-center justify-center shrink-0"
            >
              Clear
            </button>
          </div>
        </template>

        <template #cell(time)="{ item }">
          <div class="whitespace-nowrap">
            <p class="text-sm text-gray-900 dark:text-white">{{ formatDate(item.created_at) }}</p>
            <p class="text-xs text-gray-500">{{ formatTimeAgo(item.created_at) }}</p>
          </div>
        </template>

        <template #cell(user)="{ item }">
          <div v-if="item.user" class="flex items-center gap-2 whitespace-nowrap">
            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
              <span class="text-sm font-medium text-primary">{{ getInitials(item.user.full_name) }}</span>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ item.user.full_name }}</p>
              <p class="text-xs text-gray-500">{{ item.user.email }}</p>
            </div>
          </div>
          <span v-else class="text-sm text-gray-400 italic">—</span>
        </template>

        <template #cell(event)="{ item }">
          <span class="px-3 py-1 rounded-lg text-sm font-medium whitespace-nowrap" :class="getEventClass(item.event)">
            {{ formatEvent(item.event) }}
          </span>
        </template>

        <template #cell(description)="{ item }">
          <div class="min-w-0 max-w-xs flex items-center gap-2">
            <p class="text-sm text-gray-900 dark:text-white truncate" :title="item.description">{{ item.description }}</p>
            <button 
              v-if="item.new_values || item.old_values"
              @click="openDetails(item)"
              class="p-1 rounded-md bg-primary/10 text-primary hover:bg-primary/20 transition-colors"
              title="View Changes"
            >
              <Eye class="w-3.5 h-3.5" />
            </button>
          </div>
        </template>

        <template #cell(ip)="{ item }">
          <code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ item.ip_address || '—' }}</code>
        </template>
      </DataTable>

      <!-- Details Modal -->
      <div v-if="selectedLog" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-[#1A222B] rounded-2xl shadow-xl max-w-2xl w-full max-h-[80vh] overflow-hidden flex flex-col">
          <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Change Details</h3>
            <button @click="selectedLog = null" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500">
              <X class="w-5 h-5" />
            </button>
          </div>
          <div class="p-6 overflow-auto">
            <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-700">
              <p class="text-sm text-gray-500 dark:text-gray-400">Event Description</p>
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ selectedLog.description }}</p>
            </div>
            
            <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
              <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                  <tr>
                    <th class="px-4 py-2 font-medium">Field</th>
                    <th class="px-4 py-2 font-medium">Old Value</th>
                    <th class="px-4 py-2 font-medium">New Value</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                  <tr v-for="(val, field) in selectedLog.new_values || selectedLog.old_values" :key="field" class="text-gray-900 dark:text-white">
                    <td class="px-4 py-2 font-medium capitalize">{{ field.replace('_', ' ') }}</td>
                    <td class="px-4 py-2 text-gray-500 dark:text-gray-400">
                      {{ formatValue(selectedLog.old_values?.[field]) }}
                    </td>
                    <td class="px-4 py-2 text-primary font-medium">
                      {{ formatValue(val) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import DataTable from '@/components/Table/DataTable.vue';
import { ChevronRight, Search, Eye, X } from 'lucide-vue-next';

const logs = ref([]);
const loading = ref(false);
const pagination = ref(null);
const perPage = ref(50);
const debounceTimer = ref(null);
const refreshTimer = ref(null);
const selectedLog = ref(null);

const filters = reactive({
  search: '',
  event: '',
  from: '',
  to: '',
});

const hasFilters = computed(() => filters.search || filters.event || filters.from || filters.to);

watch(filters, () => {
  clearTimeout(debounceTimer.value);
  debounceTimer.value = setTimeout(() => fetchLogs(1), 400);
}, { deep: true });

const columns = [
  { key: 'time', label: 'Time' },
  { key: 'user', label: 'User' },
  { key: 'event', label: 'Event' },
  { key: 'description', label: 'Description' },
  { key: 'ip', label: 'IP Address' },
];

async function fetchLogs(page = 1, silent = false) {
  if (!silent || !logs.value.length) {
    loading.value = true;
  }

  try {
    const params = { page, per_page: perPage.value };
    if (filters.search) params.search = filters.search;
    if (filters.event) params.event = filters.event;
    if (filters.from) params.from = filters.from;
    if (filters.to) params.to = filters.to;

    const res = await axios.get('/auth/admin/activity-logs', { params });
    logs.value = res.data.data;
    pagination.value = res.data.meta;
  } catch (e) {
    console.error('Failed to load activity logs', e);
  } finally {
    if (!silent || !logs.value.length) {
      loading.value = false;
    }
  }
}

function startRefreshTimer() {
  stopRefreshTimer();
  refreshTimer.value = window.setInterval(() => fetchLogs(pagination.value?.current_page || 1, true), 10000);
}

function stopRefreshTimer() {
  if (refreshTimer.value) {
    window.clearInterval(refreshTimer.value);
    refreshTimer.value = null;
  }
}

function handlePerPageChange(size) {
  perPage.value = size;
  fetchLogs(1);
}

function clearFilters() {
  filters.search = '';
  filters.event = '';
  filters.from = '';
  filters.to = '';
  fetchLogs(1);
}

function openDetails(log) {
  selectedLog.value = log;
}

function formatValue(val) {
  if (val === null || val === undefined) return '—';
  if (typeof val === 'boolean') return val ? 'Yes' : 'No';
  if (typeof val === 'object') return JSON.stringify(val);
  return val;
}

onUnmounted(() => {
  stopRefreshTimer();
});

function formatDate(date) {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric', month: 'short', day: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });
}

function formatTimeAgo(date) {
  if (!date) return '';
  // Normalize: strip microseconds (6+ decimals) to 3-digit milliseconds for reliable parsing
  const normalized = date.replace(/\.(\d{3})\d+Z$/, '.$1Z');
  const d = new Date(normalized);
  if (isNaN(d.getTime())) return date;
  const diff = Math.abs(Date.now() - d.getTime());
  const mins = Math.floor(diff / 60000);
  if (mins < 1) return 'Just now';
  if (mins < 60) return `${mins}m ago`;
  const hours = Math.floor(mins / 60);
  if (hours < 24) return `${hours}h ago`;
  const days = Math.floor(hours / 24);
  return `${days}d ago`;
}

function getInitials(name) {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
}

function formatEvent(event) {
  const map = {
    created: 'Created',
    updated: 'Updated',
    deleted: 'Deleted',
    login: 'Login',
    logout: 'Logout',
    failed_login: 'Failed Login',
  };
  return map[event] || event;
}

function getEventClass(event) {
  const classes = {
    created: 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-300',
    updated: 'bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300',
    deleted: 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-300',
    login: 'bg-emerald-100 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300',
    logout: 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300',
    failed_login: 'bg-orange-100 dark:bg-orange-900/20 text-orange-700 dark:text-orange-300',
  };
  return classes[event] || 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300';
}

onMounted(() => {
  fetchLogs();
  startRefreshTimer();
});
</script>

<style scoped>
:deep(th), :deep(td) {
  padding-left: 1rem !important;
  padding-right: 1rem !important;
}
</style>
