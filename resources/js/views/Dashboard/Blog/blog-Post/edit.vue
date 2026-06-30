<template>
  <MainLayout>
    <div
      class="max-w-3xl mx-auto pb-20 relative"
      :class="{ 'dark': layout.isDarkMode }"
    >
      <!-- Loading Overlay -->
      <div v-if="loading" class="absolute inset-0 bg-white/80 dark:bg-gray-900/80 z-50 flex items-center justify-center">
        <div class="flex flex-col items-center gap-3">
          <div class="w-10 h-10 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
          <p class="text-gray-700 dark:text-gray-300">Loading post...</p>
        </div>
      </div>

      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Edit post</h1>
        <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
          <ChevronRight class="w-4 h-4" />
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/blog-post')">Blog</span>
          <ChevronRight class="w-4 h-4" />
          <span class="text-gray-900 dark:text-white">Edit</span>
        </nav>
      </div>

      <form @submit.prevent="submit" :class="{ 'opacity-50 pointer-events-none': loading }">
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
              class="w-5 h-5 text-gray-400 transition-transform"
              :class="{ 'rotate-180': sections.details }"
            />
          </button>

          <div v-show="sections.details" class="p-4 pt-0 space-y-4">
            <!-- Title -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Title</label>
              <input
                v-model="form.title"
                type="text"
                placeholder="Enter post title"
                class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              />
            </div>

            <!-- Description -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Description</label>
              <textarea
                v-model="form.excerpt"
                rows="3"
                placeholder="Enter a short description..."
                class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none"
              />
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ form.excerpt?.length || 0 }} characters</p>
            </div>

            <!-- Content -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Content</label>
              <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <!-- Toolbar -->
                <div class="flex items-center gap-1 p-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-[#141A21]">
                  <button type="button" class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400">
                    <Bold class="w-4 h-4" />
                  </button>
                  <button type="button" class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400">
                    <Italic class="w-4 h-4" />
                  </button>
                  <button type="button" class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400">
                    <Underline class="w-4 h-4" />
                  </button>
                  <div class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1" />
                  <button type="button" class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400">
                    <AlignLeft class="w-4 h-4" />
                  </button>
                  <button type="button" class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400">
                    <AlignCenter class="w-4 h-4" />
                  </button>
                  <button type="button" class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400">
                    <AlignRight class="w-4 h-4" />
                  </button>
                  <div class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1" />
                  <button type="button" class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400">
                    <List class="w-4 h-4" />
                  </button>
                  <button type="button" class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400">
                    <ListOrdered class="w-4 h-4" />
                  </button>
                  <div class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1" />
                  <button type="button" class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400">
                    <Link class="w-4 h-4" />
                  </button>
                  <button type="button" class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400">
                    <ImageIcon class="w-4 h-4" />
                  </button>
                </div>
                <!-- Editor -->
                <textarea
                  v-model="form.content"
                  rows="8"
                  placeholder="Write your post content here..."
                  class="w-full bg-white dark:bg-[#1C252E] px-4 py-3 text-gray-900 dark:text-white focus:outline-none resize-none"
                />
              </div>
            </div>

            <!-- Cover -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Cover</label>
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
              <p class="text-sm text-gray-500 dark:text-gray-400">Extra options...</p>
            </div>
            <ChevronDown
              class="w-5 h-5 text-gray-400 transition-transform"
              :class="{ 'rotate-180': sections.properties }"
            />
          </button>

          <div v-show="sections.properties" class="p-4 pt-0 space-y-4">
            <!-- Category -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Category</label>
              <select
                v-model="form.blog_category_id"
                class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              >
                <option value="">Select category</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>

            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Status</label>
              <select
                v-model="form.status"
                class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              >
                <option value="draft">Draft</option>
                <option value="published">Published</option>
                <option value="scheduled">Scheduled</option>
                <option value="in_review">In Review</option>
                <option value="archived">Archived</option>
              </select>
            </div>

            <!-- Publish Date -->
            <div v-if="form.status === 'scheduled' || form.status === 'published'">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                {{ form.status === 'scheduled' ? 'Schedule Date' : 'Publish Date' }}
              </label>
              <input
                v-model="form.published_at"
                type="datetime-local"
                class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              />
            </div>

            <!-- SEO -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">SEO Title</label>
              <input
                v-model="form.meta_title"
                type="text"
                placeholder="SEO meta title"
                class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">SEO Description</label>
              <textarea
                v-model="form.meta_description"
                rows="2"
                placeholder="SEO meta description"
                class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none"
              />
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500 dark:text-gray-400">Status:</span>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 capitalize">{{ form.status }}</span>
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
              Update post
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
  name: 'BlogEdit',
  components: {
    MainLayout,
    ChevronRight,
    ChevronDown,
    Bold,
    Italic,
    Underline,
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
      loading: false,
    };
  },
  mounted() {
    this.fetchCategories();
    this.fetchPost();
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
    async fetchPost() {
      this.loading = true;
      const postId = this.$route.params.id;
      try {
        const response = await axios.get(`/auth/blog-posts/${postId}`);
        const post = response.data.data;
        // Fill form with existing data
        this.form = {
          title: post.title || '',
          slug: post.slug || '',
          blog_category_id: post.blog_category_id || '',
          excerpt: post.excerpt || '',
          content: post.content || '',
          featured_image_url: post.featured_image_url || '',
          featured_image_alt: post.featured_image_alt || '',
          status: post.status || 'draft',
          published_at: post.published_at ? post.published_at.slice(0, 16) : '',
          meta_title: post.seo?.meta_title || post.meta_title || '',
          meta_description: post.seo?.meta_description || post.meta_description || '',
          focus_keyword: post.seo?.focus_keyword || post.focus_keyword || '',
          tags: post.tags || '',
          enable_comments: post.enable_comments ?? true
        };
      } catch (e) {
        console.error('Failed to load post', e);
        this.toast.error('Failed to load post data');
        this.$router.push('/blog-post');
      } finally {
        this.loading = false;
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

        const postId = this.$route.params.id;
        await axios.put(`/auth/blog-posts/${postId}`, payload);
        this.toast.success('Post updated successfully!');
        this.$router.push('/blog-post');
      } catch (e) {
        console.error('Update failed', e);
        if (e.response?.status === 401) {
          this.$router.push('/login');
        } else {
          this.toast.error(e.response?.data?.message || 'Failed to update post. Check console for details.');
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
