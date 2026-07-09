<template>
  <MainLayout>
    <div class="space-y-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Activity Log</h1>
          <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mt-1">
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-900 dark:text-white">Activity Log</span>
          </nav>
        </div>

        <div class="bg-gray-100 dark:bg-gray-800 p-1 rounded-xl flex gap-1 self-start">
          <button
            @click="activeTab = 'logs'"
            class="px-4 py-2 rounded-lg text-sm font-bold transition-all"
            :class="activeTab === 'logs' ? 'bg-white dark:bg-gray-700 text-primary shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
          >
            Activity Log
          </button>
          <button
            @click="activeTab = 'history'"
            class="px-4 py-2 rounded-lg text-sm font-bold transition-all"
            :class="activeTab === 'history' ? 'bg-white dark:bg-gray-700 text-primary shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
          >
            Session History
          </button>
        </div>
      </div>

      <div v-if="activeTab === 'logs'">
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
          </div>

      <div v-else class="space-y-6">
        <div>
          <h2 class="text-xl font-bold text-gray-900 dark:text-white">Session History</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Latest user sessions. Use filters to search by user, email, IP, or date range.</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="bg-paper-light dark:bg-paper-dark rounded-2xl border border-gray-100 dark:border-gray-800 p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">Total Sessions</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ sessionStats.total_sessions || 0 }}</p>
          </div>
          <div class="bg-paper-light dark:bg-paper-dark rounded-2xl border border-gray-100 dark:border-gray-800 p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">Total Online Hours</p>
            <p class="text-2xl font-bold text-primary mt-1">{{ sessionStats.total_hours_display || '0 hrs' }}</p>
          </div>
          <div class="bg-paper-light dark:bg-paper-dark rounded-2xl border border-gray-100 dark:border-gray-800 p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">Active Sessions</p>
            <p class="text-2xl font-bold text-emerald-500 mt-1">{{ sessionStats.active_sessions || 0 }}</p>
          </div>
        </div>

        <div class="bg-paper-light dark:bg-paper-dark rounded-3xl border border-gray-100 dark:border-gray-800 shadow-card dark:shadow-card-dark overflow-hidden transition-all duration-300">
          <!-- Session Filter Toolbar -->
          <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row items-center gap-4">
            <div class="flex-1 relative w-full">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input
                v-model="sessionFilters.search"
                type="text"
                placeholder="Search user, email or IP..."
                class="w-full h-12 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl pl-10 pr-4 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              />
            </div>
            <select
              v-model="sessionFilters.user_id"
              class="w-full sm:w-56 h-12 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all cursor-pointer"
            >
              <option value="">All Users</option>
              <option v-for="u in adminConsultantUsers" :key="u.id" :value="u.id">{{ u.full_name }} ({{ u.role }})</option>
            </select>

            <input
              v-model="sessionFilters.from"
              type="date"
              class="w-full sm:w-40 h-12 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            />
            <input
              v-model="sessionFilters.to"
              type="date"
              class="w-full sm:w-40 h-12 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            />
            <button
              v-if="hasSessionFilters"
              @click="clearSessionFilters"
              class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors h-12 flex items-center justify-center shrink-0"
            >
              Clear
            </button>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-gray-50 dark:bg-[#141A21]/50 border-b border-gray-100 dark:border-gray-800 text-gray-500 dark:text-gray-400 text-xs font-semibold uppercase tracking-wider">
                <tr>
                  <th class="px-6 py-4">User</th>
                  <th class="px-6 py-4">Login At</th>
                  <th class="px-6 py-4">Logout At</th>
                  <th class="px-6 py-4">Duration</th>
                  <th class="px-6 py-4">IP Address</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                <tr v-for="session in sessions" :key="session.id" class="text-sm text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-[#141A21]/80 transition-colors group">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-semibold">{{ getInitials(session.user?.full_name || session.user?.email || 'NA') }}</div>
                      <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ session.user?.full_name || 'Unknown User' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ session.user?.email || '—' }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">{{ formatDate(session.login_at) }}</td>
                  <td class="px-6 py-4">{{ session.logout_at ? formatDate(session.logout_at) : 'Still Active' }}</td>
                  <td class="px-6 py-4 font-medium text-primary">{{ formatSessionDuration(session) }}</td>
                  <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400">{{ session.ip_address || '—' }}</td>
                </tr>
                <tr v-if="!sessionLoading && sessions.length === 0">
                  <td colspan="5" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400 italic">No session history found.</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-if="sessionLoading" class="py-8 text-center text-gray-500">Loading session history...</div>

          <!-- Session Pagination Footer -->
          <div v-if="sessionPagination && sessionPagination.total > 0" class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 flex flex-wrap items-center justify-between gap-4 text-sm text-gray-600 dark:text-gray-400">
            <div class="flex items-center gap-2">
              <span>Rows per page:</span>
              <div data-session-dropdown class="relative">
                <button
                  @click="sessionDropdownOpen = !sessionDropdownOpen"
                  class="flex items-center gap-1.5 text-gray-900 dark:text-white font-medium hover:bg-gray-100 dark:hover:bg-gray-800/60 px-2 py-1 rounded-lg transition-all duration-200 text-sm focus:outline-none select-none border border-transparent hover:border-gray-200 dark:hover:border-gray-700/50"
                >
                  <span>{{ sessionPerPage }}</span>
                  <ChevronDown
                    class="w-4 h-4 text-gray-500 transition-transform duration-200"
                    :class="{ 'rotate-180 text-primary': sessionDropdownOpen }"
                  />
                </button>
                <transition name="popover-fade">
                  <div
                    v-if="sessionDropdownOpen"
                    class="absolute bottom-full left-0 mb-2 w-20 bg-white dark:bg-[#1C252E] border border-gray-200 dark:border-gray-700/60 rounded-xl shadow-xl z-50 overflow-hidden py-1.5 focus:outline-none"
                  >
                    <button
                      v-for="size in [15, 50, 100]"
                      :key="size"
                      @click="sessionPerPage = size; sessionDropdownOpen = false; fetchSessions(1)"
                      class="w-full text-left px-3 py-1.5 text-xs font-semibold transition-colors flex items-center justify-between focus:outline-none"
                      :class="[
                        sessionPerPage === size
                          ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400'
                          : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/40'
                      ]"
                    >
                      <span>{{ size }}</span>
                      <span v-if="sessionPerPage === size" class="text-blue-600 dark:text-blue-400 text-[10px]">✓</span>
                    </button>
                  </div>
                </transition>
              </div>
            </div>

            <div class="flex items-center gap-6">
              <span class="font-medium text-gray-900 dark:text-white">{{ sessionPagination.from || 0 }}-{{ sessionPagination.to || 0 }} of {{ sessionPagination.total }}</span>
              <div class="flex items-center gap-1">
                <button
                  @click="fetchSessions(sessionPage - 1)"
                  :disabled="sessionPage === 1"
                  class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-30 disabled:cursor-not-allowed transition-colors text-gray-700 dark:text-gray-300"
                >
                  <ChevronLeft class="w-5 h-5" />
                </button>
                <button
                  @click="fetchSessions(sessionPage + 1)"
                  :disabled="sessionPage === (sessionPagination?.last_page || 1)"
                  class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-30 disabled:cursor-not-allowed transition-colors text-gray-700 dark:text-gray-300"
                >
                  <ChevronRight class="w-5 h-5" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Details Modal -->
      <div v-if="selectedLog" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-paper-light dark:bg-paper-dark rounded-3xl shadow-card dark:shadow-card-dark max-w-2xl w-full max-h-[80vh] overflow-hidden flex flex-col border border-gray-100 dark:border-gray-800">
          <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Change Details</h3>
            <button @click="selectedLog = null" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500">
              <X class="w-5 h-5" />
            </button>
          </div>
          <div class="p-6 overflow-auto">
            <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-800">
              <p class="text-sm text-gray-500 dark:text-gray-400">Event Description</p>
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ selectedLog.description }}</p>
            </div>
            
            <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
              <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-[#141A21]/50 text-gray-500 dark:text-gray-400">
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
import { useRoute } from 'vue-router';
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import DataTable from '@/components/Table/DataTable.vue';
import { ChevronRight, Search, Eye, X, ChevronLeft, ChevronDown } from 'lucide-vue-next';

const route = useRoute();
const logs = ref([]);
const loading = ref(false);
const pagination = ref(null);
const perPage = ref(50);
const debounceTimer = ref(null);
const refreshTimer = ref(null);
const selectedLog = ref(null);
const activeTab = ref(route.query.tab === 'history' ? 'history' : 'logs');
const sessions = ref([]);
const sessionLoading = ref(false);
const sessionPage = ref(1);
const sessionPerPage = ref(15);
const sessionPagination = ref(null);
const sessionUserId = ref(route.query.user_id ? String(route.query.user_id) : null);
const sessionSearchTimer = ref(null);
const sessionStats = ref({});
const adminConsultantUsers = ref([]);
const sessionFilters = reactive({
  search: '',
  user_id: '',
  role: '',
  from: '',
  to: '',
});
const hasSessionFilters = computed(() => sessionFilters.search || sessionFilters.user_id || sessionFilters.role || sessionFilters.from || sessionFilters.to);
const sessionDropdownOpen = ref(false);
const durationTick = ref(0);
let durationTimer = null;

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

watch(sessionFilters, () => {
  clearTimeout(sessionSearchTimer.value);
  sessionSearchTimer.value = setTimeout(() => {
    if (activeTab.value === 'history') {
      fetchSessions(1);
    }
  }, 400);
}, { deep: true });

watch(activeTab, (tab) => {
  if (tab === 'history') {
    fetchSessions();
    startDurationTimer();
  } else {
    stopDurationTimer();
  }
});

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

async function fetchSessions(page = 1) {
  sessionLoading.value = true;
  sessionPage.value = page;
  try {
    const userId = sessionFilters.user_id || sessionUserId.value;
const params = {
      per_page: sessionPerPage.value,
      page,
      search: sessionFilters.search,
    };

    if (sessionFilters.role) params.role = sessionFilters.role;
    if (userId) params.user_id = userId;
    if (sessionFilters.from) params.from = sessionFilters.from;
    if (sessionFilters.to) params.to = sessionFilters.to;

    const res = await axios.get('/auth/admin/user-sessions', { params });
    sessions.value = res.data.data;
    sessionPagination.value = res.data.meta;
    sessionStats.value = res.data.stats || {};
  } catch (e) {
    console.error('Failed to load session history', e);
  } finally {
    sessionLoading.value = false;
  }
}

function clearSessionFilters() {
  sessionFilters.search = '';
  sessionFilters.user_id = '';
  sessionFilters.role = '';
  sessionFilters.from = '';
  sessionFilters.to = '';
  fetchSessions(1);
}

function handleClickOutside(e) {
  const el = document.querySelector('[data-session-dropdown]');
  if (el && !el.contains(e.target)) {
    sessionDropdownOpen.value = false;
  }
}

function startDurationTimer() {
  stopDurationTimer();
  durationTimer = window.setInterval(() => { durationTick.value++; }, 1000);
}

function stopDurationTimer() {
  if (durationTimer) {
    window.clearInterval(durationTimer);
    durationTimer = null;
  }
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

function formatSessionDuration(session) {
  durationTick.value; // reactive dependency for live-updating active session durations

  if (session.duration_human) {
    return session.duration_human;
  }

  const start = session.login_at ? new Date(session.login_at) : null;
  const end = session.logout_at ? new Date(session.logout_at) : new Date();
  if (!start || isNaN(start.getTime())) return '—';

  let diff = Math.max(0, Math.floor((end.getTime() - start.getTime()) / 1000));
  if (diff < 60) {
    return `${diff}s`;
  }

  const minutes = Math.floor(diff / 60);
  if (minutes < 60) {
    return `${minutes}m`;
  }

  const hours = Math.floor(minutes / 60);
  const remainingMinutes = minutes % 60;
  return `${hours}h:${remainingMinutes}m`;
}

onUnmounted(() => {
  stopRefreshTimer();
  stopDurationTimer();
  document.removeEventListener('click', handleClickOutside);
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

async function fetchAdminConsultantUsers() {
  try {
    const res = await axios.get('/auth/admin/users');
    const data = res.data.data || res.data || [];
    adminConsultantUsers.value = data.filter(u => u.role !== 'student');
  } catch (e) {
    console.error('Failed to load users for session filter', e);
  }
}

onMounted(() => {
  fetchLogs();
  fetchAdminConsultantUsers();
  startRefreshTimer();
  document.addEventListener('click', handleClickOutside);
  if (activeTab.value === 'history') {
    fetchSessions();
    startDurationTimer();
  }
});
</script>

<style scoped>
:deep(th), :deep(td) {
  padding-left: 1rem !important;
  padding-right: 1rem !important;
}
.popover-fade-enter-active,
.popover-fade-leave-active {
  transition: all 0.15s ease-out;
}
.popover-fade-enter-from,
.popover-fade-leave-to {
  opacity: 0;
  transform: translateY(4px) scale(0.95);
}
</style>
