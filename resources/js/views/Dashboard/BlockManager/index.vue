<template>
  <MainLayout>
    <div class="max-w-7xl mx-auto p-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Block Manager</h1>
        <router-link to="/dashboard/block-manager/create" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover">
          Add Block
        </router-link>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="overflow-x-auto">
          <table class="w-full table-auto">
            <thead>
              <tr class="text-left border-b">
                <th class="pb-3">Block Type</th>
                <th class="pb-3">Section Title</th>
                <th class="pb-3">Page</th>
                <th class="pb-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="block in blocks" :key="block.id" class="border-b">
                <td class="py-3">{{ block.block_type }}</td>
                <td class="py-3">{{ block.section_title }}</td>
                <td class="py-3">{{ block.page?.title || 'N/A' }}</td>
                <td class="py-3">
                  <router-link :to="`/dashboard/block-manager/edit/${block.id}`" class="text-blue-500 hover:underline mr-3">Edit</router-link>
                  <button @click="deleteBlock(block.id)" class="text-red-500 hover:underline">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import MainLayout from '@/layouts/MainLayout.vue';
import axios from '@/plugins/axios';

const blocks = ref([]);

const fetchBlocks = async () => {
  try {
    const response = await axios.get('/api/admin/blocks');
    blocks.value = response.data.data.data;
  } catch (error) {
    console.error(error);
  }
};

const deleteBlock = async (id) => {
  if (confirm('Are you sure?')) {
    try {
      await axios.delete(`/api/admin/blocks/${id}`);
      fetchBlocks();
    } catch (error) {
      console.error(error);
    }
  }
};

onMounted(fetchBlocks);
</script>
