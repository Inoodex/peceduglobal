<template>
  <MainLayout>
    <div class="max-w-7xl mx-auto p-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Page Manager</h1>
        <router-link to="/dashboard/page-manager/create" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover">
          Add Page
        </router-link>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="overflow-x-auto">
          <table class="w-full table-auto">
            <thead>
              <tr class="text-left border-b">
                <th class="pb-3">Title</th>
                <th class="pb-3">Slug</th>
                <th class="pb-3">Meta Title</th>
                <th class="pb-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="page in pages" :key="page.id" class="border-b">
                <td class="py-3">{{ page.title }}</td>
                <td class="py-3">{{ page.slug }}</td>
                <td class="py-3">{{ page.meta_title }}</td>
                <td class="py-3">
                  <router-link :to="`/dashboard/page-manager/edit/${page.id}`" class="text-blue-500 hover:underline mr-3">Edit</router-link>
                  <button @click="deletePage(page.id)" class="text-red-500 hover:underline">Delete</button>
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

const pages = ref([]);

const fetchPages = async () => {
  try {
    const response = await axios.get('/api/admin/pages');
    pages.value = response.data.data.data;
  } catch (error) {
    console.error(error);
  }
};

const deletePage = async (id) => {
  if (confirm('Are you sure?')) {
    try {
      await axios.delete(`/api/admin/pages/${id}`);
      fetchPages();
    } catch (error) {
      console.error(error);
    }
  }
};

onMounted(fetchPages);
</script>
