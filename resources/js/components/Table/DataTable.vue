<template>
  <div class="relative">
    <!-- Premium Top-Edge Progress Bar for background loading (Filters/Search/SWR) -->
    <div v-if="loading && data.length > 0" class="absolute top-0 left-0 right-0 h-[3px] overflow-hidden z-20 bg-gray-100 dark:bg-gray-800">
      <div class="absolute inset-0 line-shimmer-sweep bg-primary"></div>
    </div>

    <!-- Very first load: No data in memory yet -> Show Centered Premium Shimmer Line -->
    <div v-if="loading && data.length === 0" class="py-32 flex items-center justify-center bg-transparent">
      <!-- Premium Progress Track Line -->
      <div class="relative w-64 h-[3px] bg-gray-200 dark:bg-gray-800/80 rounded-full overflow-hidden">
        <div class="absolute inset-0 line-shimmer-sweep"></div>
      </div>
    </div>

    <!-- Table Card Structure -->
    <div 
      v-else
      class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 overflow-hidden shadow-sm transition-all duration-300"
      :class="{ 'opacity-55 pointer-events-none select-none': loading }"
    >
      <!-- Toolbar Slot (For Search, Filters, Add Button) -->
      <div v-if="$slots.toolbar" class="p-4 border-b border-gray-200 dark:border-gray-700/50 flex flex-wrap items-center justify-between gap-4">
        <slot name="toolbar"></slot>
      </div>

      <!-- Table Container -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
          <thead class="text-xs text-gray-500 dark:text-gray-400 bg-gray-50/50 dark:bg-[#141A21]/50 border-b border-gray-200 dark:border-gray-700/50">
            <tr>
              <th v-for="col in columns" :key="col.key" scope="col" class="px-6 py-4 font-semibold whitespace-nowrap" :class="[col.align === 'right' ? 'text-right' : (col.align === 'center' ? 'text-center' : 'text-left'), col.width || '']">
                {{ col.label }}
              </th>
            </tr>
          </thead>

          <!-- Data Rows -->
          <tbody v-if="data.length > 0">
            <tr v-for="(item, index) in data" :key="index" class="border-b border-gray-200 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-[#141A21]/80 transition-colors group">
              <td v-for="col in columns" :key="col.key" class="px-6 py-4" :class="col.align === 'right' ? 'text-right' : (col.align === 'center' ? 'text-center' : 'text-left')">
                
                <!-- Dynamic Named Slots for Custom Cell Rendering -->
                <slot :name="`cell(${col.key})`" :item="item">
                  <span class="text-gray-900 dark:text-white font-medium">{{ item[col.key] }}</span>
                </slot>

              </td>
            </tr>
          </tbody>

          <!-- Empty State -->
          <tbody v-else>
            <tr>
              <td :colspan="columns.length" class="px-6 py-16 text-center">
                <div class="flex flex-col items-center justify-center">
                  <div class="w-16 h-16 mb-4 rounded-full bg-gray-100 dark:bg-gray-800/50 flex items-center justify-center">
                    <InboxIcon class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                  </div>
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No data found</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">There are no records to display at the moment.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div v-if="pagination && pagination.total > 0" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700/50 flex flex-wrap items-center justify-between gap-4 text-sm text-gray-600 dark:text-gray-400 bg-white dark:bg-[#1C252E]">
        <div class="flex items-center gap-2">
          <span>Rows per page:</span>
          <div ref="dropdownContainer" class="relative">
            <button 
              @click="toggleDropdown" 
              class="flex items-center gap-1.5 text-gray-900 dark:text-white font-medium hover:bg-gray-100 dark:hover:bg-gray-800/60 px-2 py-1 rounded-lg transition-all duration-200 text-sm focus:outline-none select-none border border-transparent hover:border-gray-200 dark:hover:border-gray-700/50"
            >
              <span>{{ currentPerPageLabel }}</span>
              <ChevronDown 
                class="w-4 h-4 text-gray-500 transition-transform duration-200" 
                :class="{ 'rotate-180 text-primary': dropdownOpen }" 
              />
            </button>

            <!-- Floating Dropdown Popover (Opens Upward) -->
            <transition name="popover-fade">
              <div 
                v-if="dropdownOpen" 
                class="absolute bottom-full left-0 mb-2 w-20 bg-white dark:bg-[#1C252E] border border-gray-200 dark:border-gray-700/60 rounded-xl shadow-xl z-50 overflow-hidden py-1.5 focus:outline-none"
              >
                <button 
                  v-for="size in pageSizes" 
                  :key="size.value" 
                  @click="selectSize(size.value)"
                  class="w-full text-left px-3 py-1.5 text-xs font-semibold transition-colors flex items-center justify-between focus:outline-none"
                  :class="[
                    pagination.per_page === size.value 
                      ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400' 
                      : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/40'
                  ]"
                >
                  <span>{{ size.label }}</span>
                  <span v-if="pagination.per_page === size.value" class="text-blue-600 dark:text-blue-400 text-[10px]">✓</span>
                </button>
              </div>
            </transition>
          </div>
        </div>
        
        <div class="flex items-center gap-6">
          <span class="font-medium text-gray-900 dark:text-white">{{ pagination.from || 0 }}-{{ pagination.to || 0 }} of {{ pagination.total }}</span>
          
          <div class="flex items-center gap-1">
            <button 
              @click="changePage(pagination.current_page - 1)" 
              :disabled="pagination.current_page === 1"
              class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-30 disabled:cursor-not-allowed transition-colors text-gray-700 dark:text-gray-300">
              <ChevronLeft class="w-5 h-5" />
            </button>
            <button 
              @click="changePage(pagination.current_page + 1)" 
              :disabled="pagination.current_page === pagination.last_page"
              class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-30 disabled:cursor-not-allowed transition-colors text-gray-700 dark:text-gray-300">
              <ChevronRight class="w-5 h-5" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ChevronLeft, ChevronRight, ChevronDown, Inbox as InboxIcon } from 'lucide-vue-next';
import Skeleton from '../UI/Skeleton.vue';

export default {
  name: 'DataTable',
  components: {
    ChevronLeft,
    ChevronRight,
    ChevronDown,
    InboxIcon,
    Skeleton
  },
  props: {
    columns: {
      type: Array,
      required: true,
      // Array of objects: { key: 'id', label: 'ID', align: 'left|center|right', width: 'w-20' }
    },
    data: {
      type: Array,
      required: true,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    },
    pagination: {
      type: Object,
      default: null
      // Expected: { current_page, last_page, per_page, total, from, to }
    }
  },
  data() {
    return {
      dropdownOpen: false,
      pageSizes: [
        { label: '15',  value: 15 },
        { label: '50',  value: 50 },
        { label: '100', value: 100 },
        // { label: 'All', value: 9999 },
      ]
    };
  },
  mounted() {
    document.addEventListener('click', this.handleOutsideClick);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleOutsideClick);
  },
  methods: {
    changePage(page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.$emit('page-change', page);
      }
    },
    changePerPage(size) {
      this.$emit('per-page-change', size);
    },
    toggleDropdown() {
      this.dropdownOpen = !this.dropdownOpen;
    },
    closeDropdown() {
      this.dropdownOpen = false;
    },
    selectSize(size) {
      this.changePerPage(size);
      this.closeDropdown();
    },
    handleOutsideClick(e) {
      if (this.$refs.dropdownContainer && !this.$refs.dropdownContainer.contains(e.target)) {
        this.closeDropdown();
      }
    }
  },
  computed: {
    currentPerPageLabel() {
      const found = this.pageSizes.find(s => s.value === this.pagination?.per_page);
      return found ? found.label : this.pagination?.per_page;
    }
  }
}
</script>

<style scoped>
.popover-fade-enter-active,
.popover-fade-leave-active {
  transition: all 0.15s ease-out;
}
.popover-fade-enter-from,
.popover-fade-leave-to {
  opacity: 0;
  transform: translateY(4px) scale(0.95);
}

.line-shimmer-sweep {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  transform: translateX(-100%);
  background: linear-gradient(
    90deg,
    rgba(255, 255, 255, 0) 0%,
    rgba(255, 255, 255, 0.2) 20%,
    rgba(255, 255, 255, 0.95) 50%,
    rgba(255, 255, 255, 0.2) 80%,
    rgba(255, 255, 255, 0) 100%
  );
  animation: line-shimmer 1.5s infinite ease-in-out;
}

@keyframes line-shimmer {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}
</style>
