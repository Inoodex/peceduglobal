<template>
  <div class="w-full md:w-80 lg:w-96 border-r border-gray-100 dark:border-gray-800/60 flex flex-col h-full bg-gray-50/50 dark:bg-[#161B22]/20">
    <!-- Search & Filter Header -->
    <div class="p-4 border-b border-gray-100 dark:border-gray-800/60 space-y-3">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
          <MessageSquare class="w-5 h-5 text-primary" />
          Chat Inbox
        </h3>
        <!-- Mobile back button (shown when chat is open) -->
        <button
          v-if="mobileOpen"
          @click="$emit('back')"
          class="md:hidden p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors"
        >
          <ArrowLeft class="w-4 h-4 text-gray-500" />
        </button>
      </div>

      <!-- Search -->
      <div class="relative">
        <Search class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search conversations..."
          class="w-full bg-white dark:bg-[#0D1117] border border-gray-200 dark:border-gray-800 rounded-xl pl-9 pr-4 py-2 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
        />
      </div>

      <!-- Filter pills -->
      <div class="flex gap-1">
        <button
          v-for="f in filters"
          :key="f.key"
          @click="activeFilter = f.key"
          class="px-2.5 py-1 rounded-lg text-[10px] font-semibold transition-all"
          :class="[
            activeFilter === f.key
              ? 'bg-primary/10 text-primary'
              : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'
          ]"
        >
          {{ f.label }}
          <span class="opacity-70">({{ countFor(f.key) }})</span>
        </button>
      </div>
    </div>

    <!-- Conversations Scrollable Area -->
    <div class="flex-1 overflow-y-auto p-2 space-y-1">
      <div v-if="chat.loadingConversations" class="flex flex-col items-center justify-center py-12 space-y-3">
        <Loader2 class="w-6 h-6 animate-spin text-primary" />
        <span class="text-xs text-gray-400">Loading inbox...</span>
      </div>

      <div v-else-if="filteredConversations.length === 0" class="flex flex-col items-center justify-center py-12 text-center px-4">
        <Inbox class="w-8 h-8 text-gray-400 mb-2" />
        <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">No chats found</span>
        <span class="text-xs text-gray-400 mt-1">Inbox is empty or no matches found.</span>
      </div>

      <ConversationItem
        v-else
        v-for="conv in filteredConversations"
        :key="conv.id"
        :conv="conv"
        :is-active="chat.activeConversationId === conv.id"
        @select="$emit('select', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { MessageSquare, Search, Inbox, Loader2, ArrowLeft } from 'lucide-vue-next';
import ConversationItem from './ConversationItem.vue';

const props = defineProps({
  chat: { type: Object, required: true },
  mobileOpen: { type: Boolean, default: false }, // whether a chat is open on mobile
});

defineEmits(['select', 'back']);

const searchQuery = ref('');
const activeFilter = ref('all');

const filters = [
  { key: 'all', label: 'All' },
  { key: 'open', label: 'Open' },
  { key: 'closed', label: 'Closed' },
];

const countFor = (key) => {
  if (key === 'all') return props.chat.conversations.length;
  return props.chat.conversations.filter((c) => c.status === key).length;
};

const filteredConversations = computed(() => {
  let list = props.chat.conversations;

  // Status filter
  if (activeFilter.value !== 'all') {
    list = list.filter((c) => c.status === activeFilter.value);
  }

  // Search filter
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase();
    list = list.filter((c) => c.guest?.email?.toLowerCase().includes(q));
  }

  return list;
});
</script>
