<template>
  <MainLayout>
    <div class="max-w-3xl mx-auto pb-20">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Create a new category</h1>
        <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
          <ChevronRight class="w-4 h-4" />
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/blog-post')">Blog</span>
          <ChevronRight class="w-4 h-4" />
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/blog-category')">Categories</span>
          <ChevronRight class="w-4 h-4" />
          <span class="text-gray-900 dark:text-white">Create</span>
        </nav>
      </div>

      <form @submit.prevent="submit">
        <!-- Details Section -->
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 mb-4 overflow-hidden">
          <button
            type="button"
            @click="toggleSection('details')"
            class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
          >
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white">Details</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Name, slug, description...</p>
            </div>
            <ChevronDown
              class="w-5 h-5 text-gray-400 transition-transform"
              :class="{ 'rotate-180': sections.details }"
            />
          </button>

          <div v-show="sections.details" class="p-4 pt-0 border-t border-gray-200 dark:border-gray-700/50 space-y-4">
            <!-- Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                placeholder="Category name"
                class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                required
              />
            </div>

            <!-- Description -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Description
              </label>
              <textarea
                v-model="form.description"
                rows="3"
                placeholder="Short description..."
                class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none"
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Properties Section -->
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 mb-4 overflow-hidden">
          <button
            type="button"
            @click="toggleSection('properties')"
            class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
          >
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white">Properties</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Status, display order...</p>
            </div>
            <ChevronDown
              class="w-5 h-5 text-gray-400 transition-transform"
              :class="{ 'rotate-180': sections.properties }"
            />
          </button>

          <div v-show="sections.properties" class="p-4 pt-0 border-t border-gray-200 dark:border-gray-700/50 space-y-4">
            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Status
              </label>
              <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    v-model="form.status"
                    type="radio"
                    value="active"
                    class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                  />
                  <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    v-model="form.status"
                    type="radio"
                    value="inactive"
                    class="w-4 h-4 text-gray-400 border-gray-300 focus:ring-gray-400"
                  />
                  <span class="text-sm text-gray-700 dark:text-gray-300">Inactive</span>
                </label>
              </div>
            </div>

            <!-- Display Order -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Display Order
              </label>
              <input
                v-model="form.display_order"
                type="number"
                min="0"
                placeholder="0"
                class="w-full sm:w-48 bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              />
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Lower numbers appear first</p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
          <button
            type="button"
            @click="$router.push('/blog-category')"
            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors"
          >
            Cancel
          </button>
          <div class="flex items-center gap-3">
            <button
              type="submit"
              class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-400 transition-colors shadow-sm"
            >
              Create category
            </button>
          </div>
        </div>
      </form>
    </div>
  </MainLayout>
</template>

<script>
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import {
  ChevronRight,
  ChevronDown,
} from 'lucide-vue-next';
import { useToastStore } from '@/stores/toast';

export default {
  name: 'BlogCategoryCreate',
  components: {
    MainLayout,
    ChevronRight,
    ChevronDown,
  },
  setup() {
    const toast = useToastStore();
    return { toast };
  },
  data() {
    return {
      sections: {
        details: true,
        properties: true,
      },
      form: {
        name: '',
        description: '',
        status: 'active',
        display_order: '',
      },
    };
  },
  methods: {
    toggleSection(section) {
      this.sections[section] = !this.sections[section];
    },
    async submit() {
      try {
        const payload = { ...this.form };

        // Convert display_order to number or null
        if (payload.display_order === '' || payload.display_order === null) {
          delete payload.display_order;
        } else {
          payload.display_order = parseInt(payload.display_order);
        }

        await axios.post('/auth/blog-categories', payload);
        this.toast.success('Category created successfully!');
        this.$router.push('/dashboard/blog-category');
      } catch (e) {
        console.error('Create failed', e);
        if (e.response?.status === 401) {
          this.$router.push('/login');
        } else {
          this.toast.error(e.response?.data?.message || 'Failed to create category. Check console for details.');
        }
      }
    },
  },
};
</script>

<style scoped>
.bg-primary {
  background-color: var(--palette-primary-main, #00AB55);
}
.bg-primary-hover {
  background-color: var(--palette-primary-dark, #007867);
}
</style>
