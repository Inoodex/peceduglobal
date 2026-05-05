<template>
  <MainLayout>
    <div class="max-w-7xl mx-auto p-6">
      <h1 class="text-2xl font-bold mb-4">Create Page</h1>
      <!-- Form fields for title, slug, meta, etc. -->
      <div class="space-y-4">
        <input v-model="form.title" placeholder="Title" class="w-full p-2 border rounded" />
        <input v-model="form.slug" placeholder="Slug" class="w-full p-2 border rounded" />
        <textarea v-model="form.meta_description" placeholder="Meta Description" class="w-full p-2 border rounded" />
        <button @click="save" class="px-4 py-2 bg-primary text-white rounded">Save</button>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue';
import MainLayout from '@/layouts/MainLayout.vue';
import axios from '@/plugins/axios';

const form = ref({
  title: '',
  slug: '',
  meta_title: '',
  meta_description: ''
});

function save() {
  axios.post('/api/admin/pages', form.value)
    .then(() => {
      alert('Page created');
    })
    .catch(err => console.error(err));
}
</script>
