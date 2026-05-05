<template>
  <MainLayout>
    <div class="max-w-7xl mx-auto p-6">
      <h1 class="text-2xl font-bold mb-4">Edit Page</h1>
      <!-- Form fields pre‑filled with existing data -->
      <div class="space-y-4">
        <input v-model="form.title" placeholder="Title" class="w-full p-2 border rounded" />
        <input v-model="form.slug" placeholder="Slug" class="w-full p-2 border rounded" />
        <textarea v-model="form.meta_description" placeholder="Meta Description" class="w-full p-2" />
        <button @click="update" class="px-4 py-2 bg-primary text-white rounded">Update</button>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import MainLayout from '@/layouts/MainLayout.vue';
import axios from '@/plugins/axios';

const route = useRoute();
const form = ref({ title: '', slug: '', meta_title: '', meta_description: '' });

onMounted(() => {
  axios.get(`/api/admin/pages/${route.params.id}`)
    .then(res => {
      form.value = res.data.data;
    })
    .catch(err => console.error(err));
});

function update() {
  axios.put(`/api/admin/pages/${route.params.id}`, form.value)
    .then(() => alert('Page updated'))
    .catch(err => console.error(err));
}
</script>
