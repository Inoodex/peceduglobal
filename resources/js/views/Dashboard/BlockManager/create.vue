<template>
  <MainLayout>
    <div class="max-w-7xl mx-auto p-6">
      <h1 class="text-2xl font-bold mb-4">Create Block</h1>
      <form @submit.prevent="save" class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Page</label>
          <select v-model="form.page_id" class="w-full p-2 border rounded" required>
            <option value="">Select Page</option>
            <option v-for="page in pages" :key="page.id" :value="page.id">{{ page.title }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Block Type</label>
          <input v-model="form.block_type" type="text" class="w-full p-2 border rounded" required />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Section Title</label>
          <input v-model="form.section_title" type="text" class="w-full p-2 border rounded" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Section Description</label>
          <textarea v-model="form.section_description" class="w-full p-2 border rounded"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Sort Order</label>
          <input v-model="form.sort_order" type="number" class="w-full p-2 border rounded" />
        </div>
        <button type="submit" class="px-4 py-2 bg-primary text-white rounded">Save</button>
      </form>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import MainLayout from '@/layouts/MainLayout.vue';
import axios from '@/plugins/axios';

const router = useRouter();

const form = ref({
  page_id: '',
  block_type: '',
  section_title: '',
  section_description: '',
  sort_order: 0
});

const pages = ref([]);

const fetchPages = async () => {
  try {
    const response = await axios.get('/api/admin/pages');
    pages.value = response.data.data.data;
  } catch (error) {
    console.error(error);
  }
};

const save = async () => {
  try {
    await axios.post('/api/admin/blocks', form.value);
    router.push('/dashboard/block-manager');
  } catch (error) {
    console.error(error);
  }
};

onMounted(fetchPages);
</script>