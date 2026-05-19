<template>
  <MainLayout>
    <!-- Loading State: Clean Centered Shimmer Progress Line (Only shown on very first load) -->
    <div v-if="loading && sliders.length === 0" class="min-h-[75vh] flex flex-col items-center justify-center bg-transparent">
      <!-- Premium Progress Track Line -->
      <div class="relative w-64 h-[3px] bg-gray-200 dark:bg-gray-800/80 rounded-full overflow-hidden">
        <div class="absolute inset-0 line-shimmer-sweep"></div>
      </div>
    </div>

    <!-- Loaded State: Actual Page Content (Always visible once first data is loaded) -->
    <div v-else class="p-6 max-w-7xl mx-auto space-y-6">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <Image class="w-6 h-6 text-primary" /> Hero Sliders
          </h1>
          <p class="text-sm text-gray-500 mt-1">Manage the homepage hero section sliders</p>
        </div>
        <button 
          @click="openAddModal"
          class="flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-bold rounded-xl hover:bg-primary-dark transition-all"
        >
          <Plus class="w-4 h-4" /> Add Slider
        </button>
      </div>

      <DataTable 
        :columns="columns" 
        :data="sliders" 
        :loading="loading"
        :pagination="pagination"
        @page-change="fetchSliders"
        @per-page-change="handlePerPageChange"
      >
        <template #cell(image)="{ item }">
          <div v-if="item.background_image" class="w-24 h-14 rounded-lg bg-gray-100 dark:bg-gray-800 overflow-hidden relative border border-gray-200 dark:border-gray-700/50">
            <img :src="item.background_image" class="w-full h-full object-cover" alt="Hero">
          </div>
          <div v-else class="w-24 h-14 rounded-lg bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700/50 flex items-center justify-center text-[10px] font-medium text-gray-400">
            No Image
          </div>
        </template>
        
        <template #cell(content)="{ item }">
          <div class="text-sm font-bold text-gray-900 dark:text-white">{{ item.title }}</div>
          <div class="text-xs text-gray-500 mt-0.5 truncate max-w-[200px]">{{ item.subtitle }}</div>
        </template>
        
        <template #cell(status)="{ item }">
          <span :class="[
            'px-2.5 py-1 text-[10px] font-black uppercase rounded-md border inline-flex items-center',
            item.is_active ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20' : 'bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-700'
          ]">
            {{ item.is_active ? 'Published' : 'Draft' }}
          </span>
        </template>
        
        <template #cell(actions)="{ item }">
          <div class="flex items-center justify-end gap-1">
            <button @click="openEditModal(item)" class="p-1.5 text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-all">
              <Edit class="w-4 h-4" />
            </button>
            <button @click="deleteSlider(item.id)" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/10 rounded-lg transition-all">
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </template>
      </DataTable>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
      <div class="bg-white dark:bg-[#1C252E] w-full max-w-2xl rounded-2xl shadow-xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between p-6 border-b border-gray-100 dark:border-gray-800">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white">
            {{ isEditing ? 'Edit Slider' : 'Add New Slider' }}
          </h3>
          <button @click="closeModal" class="p-2 text-gray-400 hover:bg-gray-100 dark:hover:bg-[#151C24] rounded-full transition-all">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-4">
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Title *</label>
            <input v-model="form.title" type="text" class="w-full px-4 py-2 bg-gray-50 dark:bg-[#151C24] border border-gray-200 dark:border-gray-700 rounded-xl outline-none focus:border-primary/50 text-sm" placeholder="e.g. Experience Excellence in Education">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Subtitle</label>
            <input v-model="form.subtitle" type="text" class="w-full px-4 py-2 bg-gray-50 dark:bg-[#151C24] border border-gray-200 dark:border-gray-700 rounded-xl outline-none focus:border-primary/50 text-sm" placeholder="e.g. Discover quality education...">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Button Text</label>
              <input v-model="form.button_text" type="text" class="w-full px-4 py-2 bg-gray-50 dark:bg-[#151C24] border border-gray-200 dark:border-gray-700 rounded-xl outline-none focus:border-primary/50 text-sm" placeholder="e.g. Apply Now">
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Button URL</label>
              <input v-model="form.button_url" type="text" class="w-full px-4 py-2 bg-gray-50 dark:bg-[#151C24] border border-gray-200 dark:border-gray-700 rounded-xl outline-none focus:border-primary/50 text-sm" placeholder="e.g. /apply-now">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Sort Order</label>
              <input v-model="form.sort_order" type="number" class="w-full px-4 py-2 bg-gray-50 dark:bg-[#151C24] border border-gray-200 dark:border-gray-700 rounded-xl outline-none focus:border-primary/50 text-sm">
            </div>
            <div class="flex items-center pt-6">
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="form.is_active" type="checkbox" class="w-4 h-4 rounded text-primary focus:ring-primary">
                <span class="text-sm font-medium">Active (Visible on website)</span>
              </label>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Background Image</label>
            <div class="flex items-center gap-4">
              <div v-if="imagePreview" class="w-32 h-16 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden relative group">
                <img :src="imagePreview" class="w-full h-full object-cover">
              </div>
              <input type="file" ref="fileInput" @change="onFileChange" class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer" accept="image/*">
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Floating Images (Multiple)</label>
            <div class="space-y-3">
              <input type="file" ref="floatingFileInput" @change="handleFloatingFileSelect" multiple class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer" accept="image/*">
              
              <div class="flex flex-wrap gap-3 mt-2" v-if="form.retained_floating_images.length > 0 || newFloatingPreviews.length > 0">
                <!-- Retained existing images -->
                <div v-for="(img, index) in form.retained_floating_images" :key="'retained-'+index" class="relative group w-20 h-20 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                  <img :src="img" class="w-full h-full object-cover">
                  <button type="button" @click="removeRetainedImage(index)" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <X class="w-3 h-3" />
                  </button>
                </div>
                
                <!-- New images previews -->
                <div v-for="(preview, index) in newFloatingPreviews" :key="'new-'+index" class="relative group w-20 h-20 rounded-lg border-2 border-dashed border-primary/50 overflow-hidden">
                  <img :src="preview" class="w-full h-full object-cover">
                  <button type="button" @click="removeNewFloatingImage(index)" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <X class="w-3 h-3" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="p-6 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-3 bg-gray-50/50 dark:bg-[#151C24]/50">
          <button @click="closeModal" type="button" class="px-6 py-2 rounded-xl text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-800 transition-all">
            Cancel
          </button>
          <button @click="saveSlider" :disabled="saving" class="flex items-center gap-2 px-6 py-2 rounded-xl text-sm font-bold text-white bg-primary hover:bg-primary-dark transition-all disabled:opacity-50">
            <Loader2 v-if="saving" class="w-4 h-4 animate-spin" />
            {{ isEditing ? 'Update Slider' : 'Add Slider' }}
          </button>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '@/plugins/axios';
import { useToastStore } from '@/stores/toast';
import { useConfirmStore } from '@/stores/confirm';
import { fetchWithCache, clearCache } from '@/utils/cacheHelper';
import MainLayout from '@/layouts/MainLayout.vue';
import DataTable from '@/components/Table/DataTable.vue';
import { Image, Plus, Edit, Trash2, X, Loader2 } from 'lucide-vue-next';

const columns = [ 
  { key: 'image', label: 'Image', width: 'w-32' },
  { key: 'content', label: 'Content' },
  { key: 'sort_order', label: 'Sort', align: 'center', width: 'w-24' },
  { key: 'status', label: 'Status', align: 'center', width: 'w-28' },
  { key: 'actions', label: 'Actions', align: 'right', width: 'w-24' }
];

const pagination = ref(null);
const perPage = ref(15);

const toast = useToastStore();
const confirmStore = useConfirmStore();
const sliders = ref([]);
const loading = ref(true);
const showModal = ref(false);
const isEditing = ref(false);
const saving = ref(false);
const fileInput = ref(null);
const floatingFileInput = ref(null);
const imagePreview = ref(null);
const newFloatingPreviews = ref([]);

const form = ref({
  id: null,
  title: '',
  subtitle: '',
  button_text: '',
  button_url: '',
  sort_order: 0,
  is_active: true,
  image: null,
  new_floating_images: [],
  retained_floating_images: []
});

const fetchSliders = async (page = 1) => {
  await fetchWithCache({
    url: '/auth/admin/hero-sliders',
    params: { page, per_page: perPage.value },
    loadingRef: loading,
    dataRef: sliders,
    paginationRef: pagination,
    toast
  });
};

const handlePerPageChange = (newPerPage) => {
  perPage.value = newPerPage;
  fetchSliders(1);
};

const openAddModal = () => {
  isEditing.value = false;
  form.value = {
    id: null,
    title: '',
    subtitle: '',
    button_text: '',
    button_url: '',
    sort_order: sliders.value.length,
    is_active: true,
    image: null,
    new_floating_images: [],
    retained_floating_images: []
  };
  imagePreview.value = null;
  newFloatingPreviews.value = [];
  if (fileInput.value) fileInput.value.value = '';
  if (floatingFileInput.value) floatingFileInput.value.value = '';
  showModal.value = true;
};

const openEditModal = (slider) => {
  isEditing.value = true;
  form.value = {
    id: slider.id,
    title: slider.title,
    subtitle: slider.subtitle || '',
    button_text: slider.button_text || '',
    button_url: slider.button_url || '',
    sort_order: slider.sort_order,
    is_active: slider.is_active,
    image: null,
    new_floating_images: [],
    retained_floating_images: slider.floating_images || []
  };
  imagePreview.value = slider.background_image || null;
  newFloatingPreviews.value = [];
  if (fileInput.value) fileInput.value.value = '';
  if (floatingFileInput.value) floatingFileInput.value.value = '';
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const onFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.value.image = file;
    imagePreview.value = URL.createObjectURL(file);
  }
};

const handleFloatingFileSelect = (event) => {
  const files = Array.from(event.target.files);
  if (files.length > 0) {
    const totalCurrent = form.value.new_floating_images.length + form.value.retained_floating_images.length;
    
    if (totalCurrent + files.length > 8) {
      toast.error('You can only upload a maximum of 8 floating images.');
      // Clear the file input so they have to re-select
      if (floatingFileInput.value) floatingFileInput.value.value = '';
      return;
    }

    files.forEach((file) => {
      form.value.new_floating_images.push(file);
      newFloatingPreviews.value.push(URL.createObjectURL(file));
    });
  }
};

const removeRetainedImage = (index) => {
  form.value.retained_floating_images.splice(index, 1);
};

const removeNewFloatingImage = (index) => {
  form.value.new_floating_images.splice(index, 1);
  newFloatingPreviews.value.splice(index, 1);
  // Reset input value to allow selecting same files again if needed
  if (form.value.new_floating_images.length === 0 && floatingFileInput.value) {
      floatingFileInput.value.value = '';
  }
};

const saveSlider = async () => {
  if (!form.value.title) {
    toast.error('Title is required');
    return;
  }

  const totalFloating = form.value.new_floating_images.length + form.value.retained_floating_images.length;
  if (totalFloating > 8) {
    toast.error('You can only upload a maximum of 8 floating images.');
    return;
  }

  saving.value = true;
  try {
    const formData = new FormData();
    formData.append('title', form.value.title);
    if (form.value.subtitle) formData.append('subtitle', form.value.subtitle);
    if (form.value.button_text) formData.append('button_text', form.value.button_text);
    if (form.value.button_url) formData.append('button_url', form.value.button_url);
    formData.append('sort_order', form.value.sort_order);
    formData.append('is_active', form.value.is_active ? '1' : '0');
    if (form.value.image) {
      formData.append('image', form.value.image);
    }
    
    // Append multiple floating images
    form.value.new_floating_images.forEach((file) => {
      formData.append('new_floating_images[]', file);
      // Fallback for store method (creation)
      formData.append('floating_images[]', file);
    });

    // Append retained images
    form.value.retained_floating_images.forEach((url) => {
      formData.append('retained_floating_images[]', url);
    });

    if (isEditing.value) {
      await axios.post(`/auth/admin/hero-sliders/${form.value.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      toast.success('Slider updated successfully');
    } else {
      await axios.post('/auth/admin/hero-sliders', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      toast.success('Slider created successfully');
    }

    closeModal();
    clearCache('/auth/admin/hero-sliders');
    fetchSliders();
  } catch (error) {
    if (error.response?.data?.errors) {
      const firstError = Object.values(error.response.data.errors)[0][0];
      toast.error(firstError);
    } else {
      toast.error(error.response?.data?.message || 'Failed to save slider');
    }
  } finally {
    saving.value = false;
  }
};

const deleteSlider = async (id) => {
  const confirmed = await confirmStore.ask({
    title: 'Delete Slider',
    message: 'Are you sure you want to delete this slider? This action cannot be undone.',
    confirmText: 'Delete Now',
    variant: 'danger'
  });

  if (!confirmed) return;
  
  try {
    await axios.delete(`/auth/admin/hero-sliders/${id}`);
    toast.success('Slider deleted successfully');
    clearCache('/auth/admin/hero-sliders');
    fetchSliders();
  } catch (error) {
    toast.error('Failed to delete slider');
  }
};

onMounted(() => {
  fetchSliders();
});
</script>
