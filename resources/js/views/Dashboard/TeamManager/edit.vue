<template>
  <MainLayout>
    <div class="max-w-3xl mx-auto pb-20">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Edit team member</h1>
        <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
          <ChevronRight class="w-4 h-4" />
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard/team-members')">Team Members</span>
          <ChevronRight class="w-4 h-4" />
          <span class="text-gray-900 dark:text-white">Edit</span>
        </nav>
      </div>

      <div v-if="loading" class="flex justify-center items-center py-20">
        <Loader2 class="w-8 h-8 animate-spin text-primary" />
      </div>

      <form v-else @submit.prevent="save">
        <!-- Details Section -->
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 mb-4 overflow-hidden shadow-sm">
          <div class="p-4 border-b border-gray-200 dark:border-gray-700/50">
            <h3 class="font-semibold text-gray-900 dark:text-white text-lg">Details</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Enter member name, designation, and bio</p>
          </div>
          <div class="p-6 space-y-4">
            <!-- Name & Designation -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Name <span class="text-red-500">*</span></label>
                <input v-model="form.name" type="text" placeholder="John Doe" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" required />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Designation <span class="text-red-500">*</span></label>
                <input v-model="form.designation" type="text" placeholder="e.g. Senior Consultant" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" required />
              </div>
            </div>

            <!-- Content (Bio) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Content (Bio)</label>
              <textarea v-model="form.content" rows="4" placeholder="Brief description of the team member..." class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"></textarea>
            </div>

            <!-- Photo Upload -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Photo</label>
              <FileUpload
                v-model="photoUrl"
                :show-alt-input="false"
                placeholder="Drop or select a photo"
                hint="PNG, JPG, WEBP, SVG, AVIF up to 2MB"
                @select="handleFileSelect"
                @remove="handleFileRemove"
              />
            </div>

            <!-- Status -->
            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-[#141A21] rounded-xl border border-gray-200 dark:border-gray-700/50">
              <input v-model="form.status" type="checkbox" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary" />
              <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active (Visible on website)</span>
            </div>
          </div>
        </div>

        <!-- Social Links Section -->
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 mb-6 overflow-hidden shadow-sm">
          <div class="p-4 border-b border-gray-200 dark:border-gray-700/50">
            <h3 class="font-semibold text-gray-900 dark:text-white text-lg">Social Links</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Add social media URLs for this member</p>
          </div>
          <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Facebook -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Facebook</label>
                <input v-model="form.social_links.facebook" type="url" placeholder="https://facebook.com/username" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
              </div>
              <!-- Twitter / X -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Twitter / X</label>
                <input v-model="form.social_links.twitter" type="url" placeholder="https://twitter.com/username" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
              </div>
              <!-- LinkedIn -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">LinkedIn</label>
                <input v-model="form.social_links.linkedin" type="url" placeholder="https://linkedin.com/in/username" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
              </div>
              <!-- Instagram -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Instagram</label>
                <input v-model="form.social_links.instagram" type="url" placeholder="https://instagram.com/username" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
          <button type="button" @click="$router.push('/dashboard/team-members')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">Cancel</button>
          <div class="flex items-center gap-3">
            <button type="submit" :disabled="saving" class="px-5 py-2.5 text-sm font-bold text-white bg-primary rounded-xl hover:bg-primary/95 transition-all shadow-md flex items-center gap-2">
              <Loader2 v-if="saving" class="w-4 h-4 animate-spin" />
              {{ saving ? 'Updating...' : 'Update Member' }}
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
import { ChevronRight, Loader2 } from 'lucide-vue-next';
import FileUpload from '@/components/FileUpload.vue';

export default {
  name: 'TeamMemberEdit',
  components: { MainLayout, ChevronRight, Loader2, FileUpload },
  data() {
    return {
      loading: true,
      saving: false,
      photoUrl: '',
      form: {
        name: '',
        designation: '',
        content: '',
        photo: null,
        status: true,
        social_links: {
          facebook: '',
          twitter: '',
          linkedin: '',
          instagram: ''
        }
      },
    };
  },
  async mounted() {
    await this.fetchMember();
  },
  methods: {
    async fetchMember() {
      const id = this.$route.params.id;
      try {
        const res = await axios.get(`/auth/admin/team-members/${id}`);
        const member = res.data.data;
        this.form.name = member.name;
        this.form.designation = member.designation;
        this.form.content = member.content || '';
        this.form.status = member.status;
        
        if (member.social_links) {
          this.form.social_links = {
            facebook: member.social_links.facebook || '',
            twitter: member.social_links.twitter || '',
            linkedin: member.social_links.linkedin || '',
            instagram: member.social_links.instagram || ''
          };
        }

        if (member.photo) {
          this.photoUrl = member.photo;
        }
      } catch (e) {
        console.error('Failed to fetch member details', e);
      } finally {
        this.loading = false;
      }
    },
    handleFileSelect(file) {
      this.form.photo = file;
      this.photoUrl = URL.createObjectURL(file);
    },
    handleFileRemove() {
      this.form.photo = null;
      this.photoUrl = '';
    },
    async save() {
      this.saving = true;
      const id = this.$route.params.id;
      try {
        const formData = new FormData();
        formData.append('name', this.form.name);
        formData.append('designation', this.form.designation);
        formData.append('content', this.form.content || '');
        formData.append('status', this.form.status ? 1 : 0);
        
        if (this.form.photo) {
          formData.append('photo', this.form.photo);
        }

        // Append social links properly
        Object.keys(this.form.social_links).forEach(key => {
          formData.append(`social_links[${key}]`, this.form.social_links[key] || '');
        });

        await axios.post(`/auth/admin/team-members/${id}`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        this.$router.push('/dashboard/team-members');
      } catch (e) {
        console.error('Update failed', e);
      } finally {
        this.saving = false;
      }
    },
  },
};
</script>
