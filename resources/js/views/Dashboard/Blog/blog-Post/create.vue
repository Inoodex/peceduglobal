<template>
  <MainLayout>
    <div
      class="max-w-3xl mx-auto pb-20"
      :class="{ 'dark': layout.isDarkMode }"
    >
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Create a new post</h1>
        <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer">Dashboard</span>
          <ChevronRight class="w-4 h-4" />
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer">Blog</span>
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
              <p class="text-sm text-gray-500 dark:text-gray-400">Title, short description, image...</p>
            </div>
            <ChevronDown
              class="w-5 h-5 text-gray-400 transition-transform duration-200"
              :class="{ 'rotate-180': sections.details }"
            />
          </button>

          <div v-show="sections.details" class="p-4 pt-0 space-y-4">
            <div>
              <input
                v-model="form.title"
                type="text"
                placeholder="Post title"
                class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                required
              />
            </div>
            <div>
              <textarea
                v-model="form.excerpt"
                rows="3"
                placeholder="Description"
                class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none"
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Content Section -->
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 mb-4 overflow-hidden">
          <div class="p-4 pb-2">
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-3">Content</h3>

            <!-- Rich Text Toolbar -->
            <div class="flex items-center gap-1 p-2 bg-gray-50 dark:bg-[#141A21] rounded-xl border border-gray-200 dark:border-gray-700 mb-3">
              <select class="bg-transparent text-sm text-gray-700 dark:text-gray-300 px-2 py-1 rounded hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer">
                <option>Paragraph</option>
                <option>Heading 1</option>
                <option>Heading 2</option>
              </select>
              <div class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1"></div>
              <button type="button" class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400">
                <Bold class="w-4 h-4" />
              </button>
              <button type="button" class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400">
                <Italic class="w-4 h-4" />
              </button>
              <button type="button" class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400">
                <Underline class="w-4 h-4" />
              </button>
              <button type="button" class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400">
                <Strikethrough class="w-4 h-4" />
              </button>
              <div class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1"></div>
              <button type="button" class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400">
                <AlignLeft class="w-4 h-4" />
              </button>
              <button type="button" class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400">
                <AlignCenter class="w-4 h-4" />
              </button>
              <button type="button" class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400">
                <AlignRight class="w-4 h-4" />
              </button>
              <div class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1"></div>
              <button type="button" class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400">
                <List class="w-4 h-4" />
              </button>
              <button type="button" class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400">
                <ListOrdered class="w-4 h-4" />
              </button>
              <button type="button" class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400">
                <Link class="w-4 h-4" />
              </button>
              <button type="button" class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400">
                <ImageIcon class="w-4 h-4" />
              </button>
            </div>

            <textarea
              v-model="form.content"
              rows="8"
              placeholder="Write something awesome..."
              class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none"
              required
            ></textarea>
          </div>
        </div>

        <!-- Cover Section -->
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 mb-4 overflow-hidden">
          <div class="p-4">
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-4">Cover</h3>

            <FileUpload
              v-model="form.featured_image_url"
              v-model:alt-text="form.featured_image_alt"
              :uploading="uploadingImage"
              placeholder="Drop or select a file"
              alt-placeholder="Featured image alt text"
              @select="handleFileUpload"
              @remove="form.featured_image_url = ''; form.featured_image_alt = ''"
            />
          </div>
        </div>

        <!-- Properties Section -->
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 mb-6 overflow-hidden">
          <button
            type="button"
            @click="toggleSection('properties')"
            class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
          >
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white">Properties</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Additional functions and attributes...</p>
            </div>
            <ChevronDown
              class="w-5 h-5 text-gray-400 transition-transform duration-200"
              :class="{ 'rotate-180': sections.properties }"
            />
          </button>

          <div v-show="sections.properties" class="p-4 pt-0 space-y-4">
            <!-- Category -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
              <select
                v-model="form.blog_category_id"
                class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                required
              >
                <option value="" disabled>Select a category</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>

            <!-- Tags -->
            <!-- <div>
              <input
                v-model="form.tags"
                type="text"
                placeholder="Tags"
                class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              />
            </div> -->

            <!-- Meta Title -->
            <div>
              <input
                v-model="form.meta_title"
                type="text"
                placeholder="Meta title"
                class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              />
            </div>

            <!-- Meta Description -->
            <div>
              <textarea
                v-model="form.meta_description"
                rows="2"
                placeholder="Meta description"
                class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none"
              ></textarea>
            </div>

            <!-- Meta Keywords -->
            <div>
              <input
                v-model="form.focus_keyword"
                type="text"
                placeholder="Meta keywords"
                class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              />
            </div>

            <!-- Enable Comments Toggle -->
            <!-- <div class="flex items-center justify-between py-2">
              <span class="text-sm text-gray-700 dark:text-gray-300">Enable comments</span>
              <button
                type="button"
                @click="form.enable_comments = !form.enable_comments"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                :class="form.enable_comments ? 'bg-primary' : 'bg-gray-300 dark:bg-gray-600'"
              >
                <span
                  class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                  :class="form.enable_comments ? 'translate-x-6' : 'translate-x-1'"
                />
              </button>
            </div> -->
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <!-- Publish Toggle -->
            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="form.status = form.status === 'published' ? 'draft' : 'published'"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                :class="form.status === 'published' ? 'bg-primary' : 'bg-gray-300 dark:bg-gray-600'"
              >
                <span
                  class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                  :class="form.status === 'published' ? 'translate-x-6' : 'translate-x-1'"
                />
              </button>
              <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ form.status === 'published' ? 'Publish' : 'Draft' }}
              </span>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <button
              type="button"
              class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-[#1C252E] border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
            >
              Preview
            </button>
            <button
              type="submit"
              class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-400 transition-colors shadow-sm"
            >
              Create post
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
import { useLayoutStore } from '@/stores/layout';
import {
  ChevronRight,
  ChevronDown,
  Bold,
  Italic,
  Underline,
  Strikethrough,
  AlignLeft,
  AlignCenter,
  AlignRight,
  List,
  ListOrdered,
  Link,
  Image as ImageIcon
} from 'lucide-vue-next';
import FileUpload from '@/components/FileUpload.vue';
import { useToastStore } from '@/stores/toast';

export default {
  name: 'BlogCreate',
  components: {
    MainLayout,
    ChevronRight,
    ChevronDown,
    Bold,
    Italic,
    Underline,
    Strikethrough,
    AlignLeft,
    AlignCenter,
    AlignRight,
    List,
    ListOrdered,
    Link,
    ImageIcon,
    FileUpload
  },
  setup() {
    const layout = useLayoutStore();
    const toast = useToastStore();
    return { layout, toast };
  },
  data() {
    return {
      categories: [],
      sections: {
        details: true,
        properties: true
      },
      form: {
        title: '',
        slug: '',
        blog_category_id: '',
        excerpt: '',
        content: '',
        featured_image_url: '',
        featured_image_alt: '',
        status: 'draft',
        published_at: '',
        meta_title: '',
        meta_description: '',
        focus_keyword: '',
        tags: '',
        enable_comments: true
      },
      selectedImageFile: null,
      uploadingImage: false,
    };
  },
  mounted() {
    this.fetchCategories();
  },
  methods: {
    toggleSection(section) {
      this.sections[section] = !this.sections[section];
    },
    async handleFileUpload(file) {
      // Show preview immediately
      this.form.featured_image_url = URL.createObjectURL(file);
      this.selectedImageFile = file;
      this.uploadingImage = true;

      try {
        const formData = new FormData();
        formData.append('image', file);

        const response = await axios.post('/auth/blog-posts/upload-image', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });

        if (response.data.success) {
          // Replace blob URL with real URL from server
          this.form.featured_image_url = response.data.url;
          console.log('Image uploaded:', response.data.url);
        } else {
          this.toast.error('Failed to upload image');
        }
      } catch (e) {
        console.error('Image upload failed', e);
        this.toast.error(e.response?.data?.message || 'Failed to upload image');
        // Clear preview on error
        this.form.featured_image_url = '';
      } finally {
        this.uploadingImage = false;
      }
    },
    async fetchCategories() {
      try {
        const response = await axios.get('/auth/blog-categories');
        this.categories = response.data.data || response.data;
      } catch (e) {
        console.error('Failed to load categories', e);
        if (e.response?.status === 401) {
          this.$router.push('/login');
        }
      }
    },
    async submit() {
      try {
        const payload = { ...this.form };
        if (payload.published_at) {
          payload.published_at = new Date(payload.published_at).toISOString().slice(0, 19).replace('T', ' ');
        }
        // Generate slug from title if not provided
        if (!payload.slug && payload.title) {
          payload.slug = payload.title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
        }

        await axios.post('/auth/blog-posts', payload);
        this.toast.success('Post created successfully!');
        this.$router.push('/dashboard/blog-post');
      } catch (e) {
        console.error('Create failed', e);
        if (e.response?.status === 401) {
          this.$router.push('/login');
        } else {
          this.toast.error(e.response?.data?.message || 'Failed to create post. Check console for details.');
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
.text-primary {
  color: var(--palette-primary-main, #00AB55);
}
.focus\:ring-primary\/20:focus {
  --tw-ring-color: rgb(var(--palette-primary-main, 0 171 85) / 0.2);
}
.focus\:border-primary:focus {
  border-color: var(--palette-primary-main, #00AB55);
}
</style>
