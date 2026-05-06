<template>
  <MainLayout>
    <div class="max-w-7xl mx-auto p-6">
      <h1 class="text-2xl font-bold mb-4">Create Page</h1>
      <form @submit.prevent="save" class="space-y-4">
        <FormInput label="Title" v-model="form.title" type="text" required class="w-full" />
        <FormInput label="Slug" v-model="form.slug" type="text" required class="w-full" />
        <FormInput label="Meta Title" v-model="form.meta_title" type="text" class="w-full" />
        <div>
          <label class="block text-sm font-medium mb-1">Meta Description</label>
          <RichEditor v-model="form.meta_description" class="w-full" />
        </div>
        <FormInput label="Thumbnail" v-model="form.thumbnail" type="file" class="w-full" />
        <div class="flex items-center">
          <input v-model="form.is_active" type="checkbox" class="mr-2" />
          <span>Is Active</span>
        </div>
        <button type="submit" class="px-4 py-2 bg-primary text-white rounded">Save</button>
      </form>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue';
import RichEditor from '@/components/RichEditor.vue';
import { useRouter } from 'vue-router';
import MainLayout from '@/layouts/MainLayout.vue';
import FormInput from '@/components/FormInput.vue';
import axios from '@/plugins/axios';

const router = useRouter();

const form = ref({
  title: '',
  slug: '',
  meta_title: '',
  meta_description: '',
  thumbnail: null,
  is_active: true
});

const handleFile = (event) => {
  form.value.thumbnail = event.target.files[0];
        <FormInput label="Title" v-model="form.title" type="text" required class="w-full" />
        <FormInput label="Slug" v-model="form.slug" type="text" required class="w-full" />
        <FormInput label="Meta Title" v-model="form.meta_title" type="text" class="w-full" />
};

const save = async () => {
  try {
    const formData = new FormData();
    Object.keys(form.value).forEach(key => {
      if (form.value[key] !== null) formData.append(key, form.value[key]);
        <FormInput label="Thumbnail" v-model="form.thumbnail" type="file" @change="handleFile" class="w-full" />
    });
    await axios.post('/api/admin/pages', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    router.push('/dashboard/page-manager');
  } catch (error) {
    console.error(error);
  }
};
</script>
