<template>
  <MainLayout>
    <div class="max-w-4xl mx-auto pb-20">
      <div class="mb-6 flex justify-between items-end">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Create New Application</h1>
          <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
            <ChevronRight class="w-4 h-4" />
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard/applications')">Applications</span>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-900 dark:text-white">Create</span>
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

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Student Selection with Search -->
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
          <h2 class="text-lg font-semibold mb-6 flex items-center gap-2 text-primary">
            <User class="w-5 h-5" /> Student Information
          </h2>
          <div class="grid grid-cols-1 gap-6">
            <div class="space-y-2 relative">
              <label class="text-sm font-medium dark:text-gray-300">Search & Select Student <span class="text-red-500">*</span></label>
              
              <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Search class="h-4 w-4 text-gray-400" />
                </div>
                <input 
                  type="text" 
                  v-model="studentSearch" 
                  @focus="showStudentList = true"
                  class="form-input !pl-10" 
                  placeholder="Type name or email to search..."
                />
                
                <div v-if="showStudentList && filteredStudents.length > 0" class="absolute z-50 w-full mt-2 bg-white dark:bg-[#1C252E] border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl max-h-60 overflow-y-auto">
                  <div 
                    v-for="s in filteredStudents" 
                    :key="s?.id" 
                    @click="selectStudent(s)"
                    class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-[#151C24] cursor-pointer border-b last:border-0 border-gray-100 dark:border-gray-800 transition-colors"
                  >
                    <p class="text-sm font-semibold dark:text-white">{{ s?.full_name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ s?.email }}</p>
                  </div>
                </div>
                
                <div v-if="showStudentList && studentSearch && filteredStudents.length === 0" class="absolute z-50 w-full mt-2 bg-white dark:bg-[#1C252E] border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-center text-sm text-gray-500">
                  No students found.
                </div>
              </div>
              <p v-if="form.student_id" class="text-xs text-primary font-medium flex items-center gap-1">
                <Check class="w-3 h-3" /> Selected: {{ selectedStudentName }}
              </p>
            </div>

            <!-- Student Quick Info Card -->
            <div v-if="selectedStudent" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/30 rounded-xl p-4 flex items-start gap-4 animate-in fade-in slide-in-from-top-2">
              <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-800 flex items-center justify-center text-blue-600 dark:text-blue-400 shadow-sm border border-blue-200 dark:border-blue-700/50">
                <User class="w-6 h-6" />
              </div>
              <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <p class="text-[10px] text-blue-600 dark:text-blue-400 font-bold uppercase tracking-widest">Email</p>
                  <p class="text-sm font-medium dark:text-gray-200">{{ selectedStudent.email }}</p>
                </div>
                <div>
                  <p class="text-[10px] text-blue-600 dark:text-blue-400 font-bold uppercase tracking-widest">Phone</p>
                  <p class="text-sm font-medium dark:text-gray-200">{{ selectedStudent.phone || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-[10px] text-blue-600 dark:text-blue-400 font-bold uppercase tracking-widest">Nationality</p>
                  <p class="text-sm font-medium dark:text-gray-200">{{ selectedStudent.nationality || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-[10px] text-blue-600 dark:text-blue-400 font-bold uppercase tracking-widest">Passport</p>
                  <p class="text-sm font-medium dark:text-gray-200">{{ selectedStudent.passport_number || 'N/A' }}</p>
                </div>
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
                <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
              </select>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Course Level (Application Type) <span class="text-red-500">*</span></label>
              <select v-model="form.course_level_id" required class="form-input">
                <option value="">Select Level</option>
                <option v-for="level in courseLevels" :key="level?.id" :value="level?.id">{{ level?.name }}</option>
              </select>
            </div>

            <div class="space-y-2 md:col-span-2" v-if="form.status === 'rejected'">
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
            Create Application
          </button>
        </div>
      </form>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from '@/plugins/axios';
import { useToastStore } from '@/stores/toast';
import MainLayout from '@/layouts/MainLayout.vue';
import { clearCache } from '@/utils/cacheHelper';
import { ChevronRight, Loader2, User, FileText, GraduationCap, Search, Check } from 'lucide-vue-next';
import { STATUS_OPTIONS } from '@/utils/applicationStatuses';

const router = useRouter();
const toast = useToastStore();
const loading = ref(false);

// Data Lists
const students = ref([]);
const countries = ref([]);
const universities = ref([]);
const courses = ref([]);
const intakes = ref([]);
const courseLevels = ref([]);

// Search State
const studentSearch = ref('');
const showStudentList = ref(false);
const selectedStudentName = ref('');

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
  status: 'document_submitted',
  notes: '',
  rejection_reason: ''
});

const statusOptions = STATUS_OPTIONS;

// Helper to unwrap lists from API responses
const unwrapList = (res) => {
  const payload = res?.data?.data || res?.data;
  if (Array.isArray(payload)) return payload;
  if (payload?.data && Array.isArray(payload.data)) return payload.data;
  return [];
};

// Filtered Students for Search
const filteredStudents = computed(() => {
  if (!studentSearch.value) return students.value;
  const search = studentSearch.value.toLowerCase();
  return students.value.filter(s => 
    s.full_name?.toLowerCase().includes(search) || 
    s.email?.toLowerCase().includes(search)
  );
});

const selectStudent = async (student) => {
  if (!student) return;
  
  // Important: Use profile ID for the application record
  const profileId = student.profile?.id || student.id;
  form.value.student_id = profileId;
  
  studentSearch.value = student.full_name;
  selectedStudentName.value = student.full_name;
  showStudentList.value = false;
  
  loading.value = true;
  try {
    // We still fetch the detail using user ID as per controller logic
    const res = await axios.get(`/auth/admin/students/${student.id}`);
    const data = res.data.data;
    selectedStudent.value = data;

    // Reset before auto-fill
    form.value.country_id = '';
    form.value.university_id = '';
    form.value.course_id = '';
    form.value.intake_id = '';
    form.value.course_level_id = '';

    // Step-by-step Auto-fill with delays
    if (data.country_id) {
       form.value.country_id = data.country_id;
       setTimeout(async () => {
         if (data.university_id) {
           form.value.university_id = data.university_id;
           setTimeout(async () => {
             if (data.course_id) {
               form.value.course_id = data.course_id;
               setTimeout(() => {
                 if (data.course_intake_id) form.value.intake_id = data.course_intake_id;
                 if (data.course_level_id) form.value.course_level_id = data.course_level_id;
               }, 600);
             }
           }, 600);
         }
       }, 600);
    }

  } catch (error) {
    console.error('Failed to load student data', error);
  } finally {
    loading.value = false;
  }
};

// Close dropdown when clicking outside
onMounted(() => {
  const handleOutsideClick = (e) => {
    if (!e.target.closest('.relative')) {
      showStudentList.value = false;
    }
  };
  window.addEventListener('click', handleOutsideClick);
});

// Load Initial Data
onMounted(async () => {
  try {
    const [studentsRes, metaRes] = await Promise.all([
      axios.get('/auth/admin/students'),
      axios.get('/auth/admin/applications/metadata')
    ]);
    students.value = unwrapList(studentsRes);
    countries.value = metaRes.data.countries || [];
    courseLevels.value = metaRes.data.course_levels || [];
  } catch (error) {
    console.error('Failed to load initial data', error);
  }
});

// Cascading Watchers
watch(() => form.value.country_id, async (val) => {
  if (!val) {
    universities.value = [];
    return;
  }
  
  loadingUniversities.value = true;
  try {
    const res = await axios.get('/auth/admin/applications/universities', { params: { country_id: val } });
    universities.value = unwrapList(res);
  } finally {
    loadingUniversities.value = false;
  }
});

watch(() => form.value.university_id, async (val) => {
  if (!val) {
    courses.value = [];
    return;
  }
  
  loadingCourses.value = true;
  try {
    const res = await axios.get('/auth/admin/applications/courses', { params: { university_id: val } });
    courses.value = unwrapList(res);
  } finally {
    loadingCourses.value = false;
  }
});

watch(() => form.value.course_id, async (val) => {
  if (!val) {
    intakes.value = [];
    return;
  }
  
  // Find selected course to auto-fill level
  const selectedCourse = courses.value.find(c => c.id === val);
  if (selectedCourse && selectedCourse.course_level_id) {
    form.value.course_level_id = selectedCourse.course_level_id;
  }

  loadingIntakes.value = true;
  try {
    const res = await axios.get('/auth/admin/applications/intakes', { params: { course_id: val } });
    intakes.value = unwrapList(res);
  } finally {
    loadingIntakes.value = false;
  }
});

const submit = async () => {
  loading.value = true;
  try {
    await axios.post('/auth/admin/applications', form.value);
    toast.success('Application created successfully!');
    clearCache('/auth/admin/applications');
    clearCache('/auth/student/applications');
    router.push('/dashboard/applications');
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to create application';
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

.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track {
  @apply bg-transparent;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
  @apply bg-gray-200 dark:bg-gray-700 rounded-full;
}
</style>
