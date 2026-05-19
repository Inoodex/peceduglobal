<template>
  <MainLayout>
    <div class="p-6 max-w-7xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Social Links</h1>
      <router-link to="/dashboard/footer-socials/create" class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-xl font-medium transition-colors">
        <Plus class="w-4 h-4" /> Add Social Link
      </router-link>
    </div>

    <DataTable :columns="columns" :data="socials" :loading="loading">
      <!-- Icon -->
      <template #cell(icon)="{ item }">
        <div class="w-10 h-10 rounded-lg bg-gray-50 dark:bg-[#141A21] border border-gray-100 dark:border-gray-800 flex items-center justify-center text-gray-600 dark:text-gray-400 overflow-hidden">
          <div v-if="item.icon && item.icon.trim().startsWith('<svg')" v-html="item.icon" class="w-5 h-5 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full"></div>
          <component v-else-if="item.icon && lucideIcons[item.icon]" :is="lucideIcons[item.icon]" class="w-5 h-5" />
          <span v-else class="text-xs">N/A</span>
        </div>
      </template>

      <!-- URL -->
      <template #cell(url)="{ item }">
        <a :href="item.url" target="_blank" class="text-primary hover:underline text-sm">{{ item.url }}</a>
      </template>

      <!-- Status -->
      <template #cell(status)="{ item }">
        <span class="px-2.5 py-1 text-[11px] font-bold uppercase rounded-md border"
              :class="item.status ? 'bg-green-50 text-green-600 border-green-200 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800' : 'bg-red-50 text-red-600 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800'">
          {{ item.status ? 'Active' : 'Inactive' }}
        </span>
      </template>

      <!-- Actions -->
      <template #cell(actions)="{ item }">
        <div class="flex items-center justify-end gap-2">
          <router-link :to="`/dashboard/footer-socials/${item.id}/edit`" class="p-2 text-blue-600 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/40 rounded-lg transition-colors">
            <Edit class="w-4 h-4" />
          </router-link>
          <button @click="deleteSocial(item.id)" class="p-2 text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/40 rounded-lg transition-colors">
            <Trash2 class="w-4 h-4" />
          </button>
        </div>
      </template>
    </DataTable>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import MainLayout from '@/layouts/MainLayout.vue';
import axios from '@/plugins/axios';
import { useToastStore } from '@/stores/toast';
import { useConfirmStore } from '@/stores/confirm';
import DataTable from '@/components/Table/DataTable.vue';
import { Plus, Edit, Trash2 } from 'lucide-vue-next';
import * as lucideIcons from 'lucide-vue-next';

const toast = useToastStore();
const confirm = useConfirmStore();
const loading = ref(true);
const socials = ref([]);

const columns = [
  { key: 'serial_no', label: 'SL No', width: 'w-16' },
  { key: 'icon', label: 'Icon', width: 'w-20' },
  { key: 'name', label: 'Platform Name' },
  { key: 'url', label: 'URL' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: 'Actions', align: 'right' }
];

const fetchSocials = async () => {
  try {
    const res = await axios.get('/auth/admin/footer-socials');
    socials.value = res.data.data;
  } catch (err) {
    toast.error('Failed to fetch social links.');
  } finally {
    loading.value = false;
  }
};

const deleteSocial = async (id) => {
  const isConfirmed = await confirm.ask({
    title: 'Delete Social Link',
    message: 'Are you sure you want to delete this social link? This action cannot be undone.',
    confirmText: 'Delete Now',
    variant: 'danger'
  });

  if (isConfirmed) {
    try {
      await axios.delete(`/auth/admin/footer-socials/${id}`);
      toast.success('Social link deleted successfully.');
      fetchSocials();
    } catch (err) {
      toast.error('Failed to delete social link.');
    }
  }
};

onMounted(() => fetchSocials());
</script>
