<template>
  <MainLayout>
    <div class="max-w-4xl mx-auto pb-20">
      <div class="mb-6 flex justify-between items-end">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Edit Application</h1>
          <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
            <ChevronRight class="w-4 h-4" />
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard/applications')">Applications</span>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-900 dark:text-white">Edit #{{ form.application_number }}</span>
          </nav>
        </div>
        <button
          type="button"
          @click="$router.push('/dashboard/applications')"
          class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-all"
        >
          Back to List
        </button>
      </div>

      <div v-if="loadingData" class="flex flex-col items-center justify-center py-20 bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-100 dark:border-gray-800">
        <Loader2 class="w-10 h-10 animate-spin text-primary mb-4" />
        <p class="text-gray-500">Loading application data...</p>
      </div>

      <form v-else @submit.prevent="submit" class="space-y-6">
        <!-- Student Information (Read-only in Edit) -->
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
          <h2 class="text-lg font-semibold mb-6 flex items-center gap-2 text-primary">
            <User class="w-5 h-5" /> Student Information
          </h2>
          <div v-if="selectedStudent" class="bg-gray-50 dark:bg-[#151C24] border border-gray-100 dark:border-gray-800 rounded-xl p-4 flex items-start gap-4">
            <div class="w-12 h-12 rounded-full bg-white dark:bg-gray-800 flex items-center justify-center text-primary shadow-sm border border-gray-200 dark:border-gray-700">
              <User class="w-6 h-6" />
            </div>
            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Name</p>
                <p class="text-sm font-semibold dark:text-white">{{ selectedStudent.full_name }}</p>
              </div>
              <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Email</p>
                <p class="text-sm font-medium dark:text-gray-200">{{ selectedStudent.email }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Academic Selection -->
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
          <h2 class="text-lg font-semibold mb-6 flex items-center gap-2 text-primary">
            <GraduationCap class="w-5 h-5" /> Academic Choices
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Select Country <span class="text-red-500">*</span></label>
              <select v-model="form.country_id" required class="form-input">
                <option value="">Select Country</option>
                <option v-for="c in countries" :key="c?.id" :value="c?.id">{{ c?.name }}</option>
              </select>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Select University <span class="text-red-500">*</span></label>
              <select v-model="form.university_id" required class="form-input" :disabled="!form.country_id || loadingUniversities">
                <option value="">{{ loadingUniversities ? 'Loading Universities...' : 'Select University' }}</option>
                <option v-for="u in universities" :key="u?.id" :value="u?.id">{{ u?.name }}</option>
              </select>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Select Course <span class="text-red-500">*</span></label>
              <select v-model="form.course_id" required class="form-input" :disabled="!form.university_id || loadingCourses">
                <option value="">{{ loadingCourses ? 'Loading Courses...' : 'Select Course' }}</option>
                <option v-for="co in courses" :key="co?.id" :value="co?.id">{{ co?.name }}</option>
              </select>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Select Intake</label>
              <select v-model="form.intake_id" class="form-input" :disabled="!form.course_id || loadingIntakes">
                <option value="">{{ loadingIntakes ? 'Loading Intakes...' : 'Select Intake' }}</option>
                <option v-for="i in intakes" :key="i?.id" :value="i?.id">{{ i?.intake_name }}</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Application Status & Notes -->
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
          <h2 class="text-lg font-semibold mb-6 flex items-center gap-2 text-primary">
            <FileText class="w-5 h-5" /> Processing Details
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Application Status <span class="text-red-500">*</span></label>
              <select v-model="form.status" required class="form-input">
                <option value="pending">Pending</option>
                <option value="document_review">Document Review</option>
                <option value="university_submitted">University Submitted</option>
                <option value="offer_letter">Offer Letter</option>
                <option value="visa_process">Visa Process</option>
                <option value="completed">Completed</option>
                <option value="rejected">Rejected</option>
              </select>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Course Level <span class="text-red-500">*</span></label>
              <select v-model="form.course_level_id" required class="form-input">
                <option value="">Select Level</option>
                <option v-for="level in courseLevels" :key="level?.id" :value="level?.id">{{ level?.name }}</option>
              </select>
            </div>

            <div v-if="form.status === 'rejected'" class="space-y-2 md:col-span-2">
              <label class="text-sm font-medium text-red-500">Rejection Reason</label>
              <textarea v-model="form.rejection_reason" rows="2" class="form-input border-red-200" placeholder="Explain why the application was rejected..."></textarea>
            </div>

            <div class="space-y-2 md:col-span-2">
              <label class="text-sm font-medium dark:text-gray-300">Internal Notes</label>
              <textarea v-model="form.notes" rows="3" class="form-input" placeholder="Add any specific notes or remarks for this application..."></textarea>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-3">
          <button 
            type="button" 
            @click="$router.push('/dashboard/applications')" 
            class="px-6 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition-all"
          >
            Cancel
          </button>
          <button 
            type="submit" 
            :disabled="loading" 
            class="px-8 py-2 rounded-lg bg-primary text-white font-semibold shadow-lg hover:bg-primary-dark transition-all flex items-center gap-2"
          >
            <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
            Update Application
          </button>
        </div>
      </form>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from '@/plugins/axios';
import { useToastStore } from '@/stores/toast';
import MainLayout from '@/layouts/MainLayout.vue';
import { ChevronRight, Loader2, User, FileText, GraduationCap } from 'lucide-vue-next';

const router = useRouter();
const route = useRoute();
const toast = useToastStore();
const loading = ref(false);
const loadingData = ref(true);

// Data Lists
const countries = ref([]);
const universities = ref([]);
const courses = ref([]);
const intakes = ref([]);
const courseLevels = ref([]);

// Loading States
const loadingUniversities = ref(false);
const loadingCourses = ref(false);
const loadingIntakes = ref(false);
const selectedStudent = ref(null);

const form = ref({
  student_id: '',
  country_id: '',
  university_id: '',
  course_id: '',
  course_level_id: '',
  intake_id: '',
  status: 'pending',
  notes: '',
  rejection_reason: '',
  application_number: ''
});

// Helper to unwrap lists from API responses
const unwrapList = (res) => {
  const payload = res?.data?.data || res?.data;
  if (Array.isArray(payload)) return payload;
  if (payload?.data && Array.isArray(payload.data)) return payload.data;
  return [];
};

// Data fetching logic is now handled in onMounted for parallel execution

const fetchUniversities = async (val) => {
  loadingUniversities.value = true;
  try {
    const res = await axios.get('/auth/admin/universities', { params: { country_id: val, per_page: 500 } });
    universities.value = unwrapList(res);
  } finally {
    loadingUniversities.value = false;
  }
};

const fetchCourses = async (val) => {
  loadingCourses.value = true;
  try {
    const res = await axios.get('/auth/admin/courses', { params: { university_id: val, per_page: 500 } });
    courses.value = unwrapList(res);
  } finally {
    loadingCourses.value = false;
  }
};

const fetchIntakes = async (val) => {
  loadingIntakes.value = true;
  try {
    const res = await axios.get('/auth/admin/course-intakes', { params: { course_id: val } });
    intakes.value = unwrapList(res);
  } finally {
    loadingIntakes.value = false;
  }
};

// Cascading Watchers (only for manual changes after initial load)
watch(() => form.value.country_id, (val, old) => {
  if (old && val !== old) {
    form.value.university_id = '';
    form.value.course_id = '';
    form.value.intake_id = '';
    fetchUniversities(val);
  }
});

watch(() => form.value.university_id, (val, old) => {
  if (old && val !== old) {
    form.value.course_id = '';
    form.value.intake_id = '';
    fetchCourses(val);
  }
});

watch(() => form.value.course_id, (val, old) => {
  if (old && val !== old) {
    form.value.intake_id = '';
    fetchIntakes(val);
    
    const selectedCourse = courses.value.find(c => c.id === val);
    if (selectedCourse?.course_level_id) {
      form.value.course_level_id = selectedCourse.course_level_id;
    }
  }
});

onMounted(async () => {
  loadingData.value = true;
  try {
    // 1. Fetch metadata and application info in parallel (Combined metadata for speed)
    const [metaRes, appRes] = await Promise.all([
      axios.get('/auth/admin/applications/metadata'),
      axios.get(`/auth/admin/applications/${route.params.id}`)
    ]);

    countries.value = metaRes.data.countries || [];
    courseLevels.value = metaRes.data.course_levels || [];
    
    const app = appRes.data.data;
    form.value = {
      student_id: app.student_id,
      country_id: app.country_id,
      university_id: app.university_id,
      course_id: app.course_id,
      course_level_id: app.course_level_id,
      intake_id: app.intake_id,
      status: app.status,
      notes: app.notes || '',
      rejection_reason: app.rejection_reason || '',
      application_number: app.application_number
    };

    if (app.student) {
      selectedStudent.value = {
        full_name: app.student.user?.full_name || 'N/A',
        email: app.student.user?.email || 'N/A'
      };
    }

    // 2. Fetch dependent lists (universities, courses, intakes) in parallel since we already have IDs
    const requests = [];
    if (app.country_id) requests.push(fetchUniversities(app.country_id));
    if (app.university_id) requests.push(fetchCourses(app.university_id));
    if (app.course_id) requests.push(fetchIntakes(app.course_id));
    
    await Promise.all(requests);

  } catch (error) {
    console.error('Failed to load application', error);
    toast.error('Failed to load application data');
    router.push('/dashboard/applications');
  } finally {
    loadingData.value = false;
  }
});

const submit = async () => {
  loading.value = true;
  try {
    await axios.put(`/auth/admin/applications/${route.params.id}`, form.value);
    toast.success('Application updated successfully!');
    router.push('/dashboard/applications');
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to update application';
    toast.error(message);
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
@reference "@/../css/app.css";

.form-input {
  @apply w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none transition-all;
  @apply dark:bg-[#151C24] dark:border-gray-700 dark:text-white;
  @apply focus:ring-2 focus:ring-primary/20 focus:border-primary;
}
</style>
