<template>
  <MainLayout>
    <div class="max-w-7xl mx-auto p-6">
      <h1 class="text-2xl font-bold mb-4">Edit Page</h1>
      <form @submit.prevent="save" class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Title</label>
          <input v-model="form.title" type="text" class="w-full p-2 border rounded" required />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Slug</label>
          <input v-model="form.slug" type="text" class="w-full p-2 border rounded" required />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Meta Title</label>
          <input v-model="form.meta_title" type="text" class="w-full p-2 border rounded" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Meta Description</label>
          <RichEditor v-model="form.meta_description" class="w-full" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Thumbnail</label>
          <input type="file" @change="handleFile" class="w-full p-2 border rounded" />
        </div>
        <div>
          <label class="flex items-center">
            <input v-model="form.is_active" type="checkbox" class="mr-2" />
            Is Active
          </label>
        </div>
        <button type="submit" class="px-4 py-2 bg-primary text-white rounded">Update</button>
      </form>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import RichEditor from '@/components/RichEditor.vue';
import FormInput from '@/components/FormInput.vue';
import { useRouter, useRoute } from 'vue-router';
import MainLayout from '@/layouts/MainLayout.vue';
import axios from '@/plugins/axios';

const router = useRouter();
const route = useRoute();

const form = ref({
  title: '',
  slug: '',
  meta_title: '',
  meta_description: '',
  thumbnail: null,
  is_active: true
});
        <FormInput label="Title" v-model="form.title" type="text" required class="w-full" />
        <FormInput label="Slug" v-model="form.slug" type="text" required class="w-full" />
        <FormInput label="Meta Title" v-model="form.meta_title" type="text" class="w-full" />

const fetchPage = async () => {
  try {
    const response = await axios.get(`/api/admin/pages/${route.params.id}`);
        <FormInput label="Thumbnail" v-model="form.thumbnail" type="file" class="w-full" />
    const page = response.data.data;
    form.value = { ...page, thumbnail: null };
  } catch (error) {
    console.error(error);
  }
};

const handleFile = (event) => {
  form.value.thumbnail = event.target.files[0];
};

const save = async () => {
  try {
    const formData = new FormData();
    Object.keys(form.value).forEach(key => {
      if (form.value[key] !== null) formData.append(key, form.value[key]);
    });
    await axios.post(`/api/admin/pages/${route.params.id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      params: { _method: 'PUT' }
    });
    router.push('/dashboard/page-manager');
  } catch (error) {
    console.error(error);
  }
};

onMounted(fetchPage);
</script>
