<template>
  <MainLayout>
    <div class="max-w-4xl mx-auto pb-20">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ isEdit ? 'Edit Course' : 'Create a new course' }}</h1>
        <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
          <ChevronRight class="w-4 h-4" />
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard/course-manager')">Courses</span>
          <ChevronRight class="w-4 h-4" />
          <span class="text-gray-900 dark:text-white">{{ isEdit ? 'Edit' : 'Create' }}</span>
        </nav>
      </div>

      <form @submit.prevent="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Left Column: Form Fields -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info Section -->
            <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h3>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Course Name <span class="text-red-500">*</span></label>
                  <input v-model="form.name" type="text" placeholder="e.g. BSc in Computer Science" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" required />
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">University <span class="text-red-500">*</span></label>
                    <select v-model="form.university_id" @change="onUniversityChange" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" required>
                      <option value="" disabled>Select University</option>
                      <option v-for="uni in universities" :key="uni.id" :value="uni.id">{{ uni.name }}</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Course Level <span class="text-red-500">*</span></label>
                    <select v-model="form.course_level_id" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" required>
                      <option value="" disabled>Select Level</option>
                      <option v-for="level in levels" :key="level.id" :value="level.id">{{ level.name }}</option>
                    </select>
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Intake</label>
                    <input v-model="form.intake" type="text" placeholder="e.g. September, January" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Duration</label>
                    <input v-model="form.duration" type="text" placeholder="e.g. 3 Years" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" />
                  </div>
                </div>
              </div>
            </div>

            <!-- Requirements & Fees -->
            <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Requirements & Fees</h3>
              <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tuition Fee</label>
                    <div class="relative">
                      <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">$</span>
                      <input v-model="form.tuition_fee" type="number" step="0.01" placeholder="0.00" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl pl-8 pr-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" />
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">IELTS Requirement</label>
                    <input v-model="form.ielts_requirement" type="text" placeholder="e.g. 6.5 (No band less than 6.0)" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" />
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Other Requirements</label>
                  <textarea v-model="form.requirements" rows="4" placeholder="List academic or other entry requirements..." class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"></textarea>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column: Meta & Actions -->
          <div class="space-y-6">
            <!-- Status Section -->
            <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Settings</h3>
              <div class="space-y-4">
                <label class="flex items-center gap-3 cursor-pointer group">
                  <input v-model="form.is_popular" type="checkbox" class="w-5 h-5 rounded-lg border-gray-300 text-primary focus:ring-primary/20" />
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">Mark as Popular</span>
                </label>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col gap-3">
              <button type="submit" :disabled="saving" class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-primary hover:bg-primary-hover text-white font-bold rounded-xl transition-all shadow-lg shadow-primary/20 disabled:opacity-50">
                <Loader2 v-if="saving" class="w-5 h-5 animate-spin" />
                {{ isEdit ? 'Update Course' : 'Create Course' }}
              </button>
              <button type="button" @click="$router.push('/dashboard/course-manager')" class="w-full px-6 py-3 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all">
                Cancel
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import { ChevronRight, Loader2 } from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();

const isEdit = computed(() => !!route.params.id);
const universities = ref([]);
const levels = ref([]);
const saving = ref(false);

const form = ref({
  name: '',
  university_id: '',
  course_level_id: '',
  country_id: null,
  intake: '',
  duration: '',
  ielts_requirement: '',
  tuition_fee: '',
  requirements: '',
  is_popular: false
});

const fetchUniversities = async () => {
  try {
    const response = await axios.get('/auth/admin/universities');
    universities.value = response.data.data?.data || response.data.data || [];
  } catch (e) {
    console.error('Failed to fetch universities', e);
  }
};

const fetchLevels = async () => {
  try {
    const response = await axios.get('/auth/admin/course-levels');
    levels.value = response.data.data || [];
  } catch (e) {
    console.error('Failed to fetch levels', e);
  }
};

const onUniversityChange = () => {
  const selectedUni = universities.value.find(u => u.id === form.value.university_id);
  if (selectedUni) {
    form.value.country_id = selectedUni.country_id;
  }
};

const fetchCourse = async () => {
  if (!isEdit.value) return;
  try {
    const response = await axios.get(`/auth/admin/courses/${route.params.id}`);
    const data = response.data.data;
    form.value = {
      ...data,
      is_popular: !!data.is_popular
    };
  } catch (e) {
    console.error('Failed to fetch course', e);
  }
};

const save = async () => {
  saving.value = true;
  try {
    if (isEdit.value) {
      await axios.put(`/auth/admin/courses/${route.params.id}`, form.value);
    } else {
      await axios.post('/auth/admin/courses', form.value);
    }
    router.push('/dashboard/course-manager');
  } catch (e) {
    console.error('Save failed', e);
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchUniversities();
  fetchLevels();
  fetchCourse();
});
</script>
