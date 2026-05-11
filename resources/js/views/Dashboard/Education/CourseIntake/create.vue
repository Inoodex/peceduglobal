<template>
    <MainLayout>
      <div class="max-w-4xl mx-auto pb-20">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ isEdit ? 'Edit Intake' : 'Create New Intake' }}</h1>
          <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
            <ChevronRight class="w-4 h-4" />
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard/course-intakes')">Intakes</span>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-900 dark:text-white">{{ isEdit ? 'Edit' : 'Create' }}</span>
          </nav>
        </div>

        <form @submit.prevent="save">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Course & Intake Info -->
            <div class="lg:col-span-2 space-y-6">
              <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">General Information</h3>
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Intake Name <span class="text-red-500">*</span></label>
                    <input v-model="form.intake_name" type="text" placeholder="e.g. September 2024" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" required />
                  </div>

                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- UNIVERSITY FIRST -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">University <span class="text-red-500">*</span></label>
                      <select v-model="form.university_id" @change="onUniversityChange" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" required>
                        <option value="" disabled>Select University</option>
                        <option v-for="uni in universities" :key="uni.id" :value="uni.id">{{ uni.name }}</option>
                      </select>
                    </div>

                    <!-- COURSE SECOND (Filtered) -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Course <span class="text-red-500">*</span></label>
                      <select v-model="form.course_id" :disabled="!form.university_id" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all disabled:opacity-50" required>
                        <option value="" disabled>Select Course</option>
                        <option v-for="course in filteredCourses" :key="course.id" :value="course.id">{{ course.name }}</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Timeline Section -->
              <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Important Dates</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Application Start Date <span class="text-red-500">*</span></label>
                    <input v-model="form.application_start_date" type="date" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" required />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Application Deadline <span class="text-red-500">*</span></label>
                    <input v-model="form.application_deadline" type="date" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" required />
                  </div>
                  <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Class Start Date</label>
                    <input v-model="form.class_start_date" type="date" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" />
                  </div>
                </div>
              </div>
            </div>

            <!-- Right Column: Status & Actions -->
            <div class="space-y-6">
              <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Availability</h3>
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Intake Status</label>
                    <select v-model="form.status" class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                      <option value="upcoming">Upcoming</option>
                      <option value="open">Open</option>
                      <option value="closed">Closed</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="flex flex-col gap-3">
                <button type="submit" :disabled="saving" class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-primary hover:bg-primary-hover text-white font-bold rounded-xl transition-all shadow-lg shadow-primary/20 disabled:opacity-50">
                  <Loader2 v-if="saving" class="w-5 h-5 animate-spin" />
                  {{ isEdit ? 'Update Intake' : 'Create Intake' }}
                </button>
                <button type="button" @click="$router.push('/dashboard/course-intakes')" class="w-full px-6 py-3 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all">
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
  const courses = ref([]);
  const universities = ref([]);
  const saving = ref(false);

  const form = ref({
    intake_name: '',
    course_id: '',
    university_id: '',
    application_start_date: '',
    application_deadline: '',
    class_start_date: '',
    status: 'upcoming'
  });

  // Logic to filter courses based on selected university
  const filteredCourses = computed(() => {
    if (!form.value.university_id) return [];
    return courses.value.filter(course => course.university_id === form.value.university_id);
  });

  const fetchCourses = async () => {
    try {
      const response = await axios.get('/auth/admin/courses');
      courses.value = response.data.data?.data || response.data.data || [];
    } catch (e) { console.error(e); }
  };

  const fetchUniversities = async () => {
    try {
      const response = await axios.get('/auth/admin/universities');
      universities.value = response.data.data?.data || response.data.data || [];
    } catch (e) { console.error(e); }
  };

  const onUniversityChange = () => {
    // Reset course_id if university is changed to prevent wrong course selection
    form.value.course_id = '';
  };

  const fetchIntake = async () => {
    if (!isEdit.value) return;
    try {
      const response = await axios.get(`/auth/admin/course-intakes/${route.params.id}`);
      form.value = response.data.data;
    } catch (e) { console.error(e); }
  };

  const save = async () => {
    saving.value = true;
    try {
      if (isEdit.value) {
        await axios.put(`/auth/admin/course-intakes/${route.params.id}`, form.value);
      } else {
        await axios.post('/auth/admin/course-intakes', form.value);
      }
      router.push('/dashboard/course-intakes');
    } catch (e) {
      console.error('Save failed', e);
    } finally {
      saving.value = false;
    }
  };

  onMounted(() => {
    fetchCourses();
    fetchUniversities();
    fetchIntake();
  });
  </script>
