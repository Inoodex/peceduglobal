<template>
  <MainLayout>
    <div class="max-w-4xl mx-auto pb-20">
      <div class="mb-6 flex justify-between items-end">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Create Student</h1>
          <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
            <ChevronRight class="w-4 h-4" />
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard/students')">Students</span>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-900 dark:text-white">Create</span>
          </nav>
        </div>
        <button
          type="button"
          @click="$router.push('/dashboard/students')"
          class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-all"
        >
          Back to List
        </button>
      </div>

      <form @submit.prevent="submit" class="space-y-8">
        <!-- Account -->
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
          <h2 class="text-lg font-semibold mb-6 flex items-center gap-2 text-primary">
            <User class="w-5 h-5" /> Account
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">First Name <span class="text-red-500">*</span></label>
              <input v-model="form.first_name" type="text" required class="form-input" placeholder="First name" />
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Last Name <span class="text-red-500">*</span></label>
              <input v-model="form.last_name" type="text" required class="form-input" placeholder="Last name" />
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Email <span class="text-red-500">*</span></label>
              <input v-model="form.email" type="email" required autocomplete="email" class="form-input" placeholder="student@example.com" />
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Password <span class="text-red-500">*</span></label>
              <input v-model="form.password" type="password" required autocomplete="new-password" class="form-input" placeholder="Min 6 characters" />
            </div>
          </div>
        </div>

        <!-- Personal -->
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
          <h2 class="text-lg font-semibold mb-6 flex items-center gap-2 text-primary">
            <FileText class="w-5 h-5" /> Personal
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Father's Name</label>
              <input v-model="form.father_name" type="text" class="form-input" />
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Mother's Name</label>
              <input v-model="form.mother_name" type="text" class="form-input" />
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Phone <span class="text-red-500">*</span></label>
              <input v-model="form.phone" type="text" required class="form-input" placeholder="+880..." />
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Sponsor Phone</label>
              <input v-model="form.sponsor_phone" type="text" class="form-input" />
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Passport Number</label>
              <input v-model="form.passport_number" type="text" class="form-input" />
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Passport Validity</label>
              <input v-model="form.passport_validity" type="date" class="form-input" />
            </div>
            <div class="space-y-2 md:col-span-2">
              <label class="text-sm font-medium dark:text-gray-300">Address</label>
              <textarea v-model="form.address" rows="2" class="form-input" placeholder="Street, city, country"></textarea>
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Date of Birth</label>
              <input v-model="form.date_of_birth" type="date" class="form-input" />
            </div>
          </div>
        </div>

        <!-- Study preferences -->
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
          <h2 class="text-lg font-semibold mb-6 flex items-center gap-2 text-primary">
            <GraduationCap class="w-5 h-5" /> Study preferences
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2 md:col-span-2">
              <label class="text-sm font-medium dark:text-gray-300">Target Country <span class="text-red-500">*</span></label>
              <select v-model="form.country_id" required class="form-input">
                <option value="">Select Country</option>
                <option v-for="c in countries" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Preferred University</label>
              <select v-model="form.university_id" class="form-input">
                <option value="">Select University</option>
                <option v-for="u in universities" :key="u.id" :value="u.id">{{ u.name }}</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Preferred Course</label>
              <select v-model="form.course_id" class="form-input">
                <option value="">Select Course</option>
                <option v-for="co in courses" :key="co.id" :value="co.id">{{ co.name }}</option>
              </select>
            </div>
            <div class="space-y-2 md:col-span-2">
              <label class="text-sm font-medium dark:text-gray-300">Preferred Intake</label>
              <select v-model="form.course_intake_id" class="form-input">
                <option value="">Select Intake</option>
                <option v-for="i in intakesForCourse" :key="i.id" :value="i.id">
                  {{ i.intake_name }} — {{ i.course?.name }} ({{ i.university?.name }})
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Files -->
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
          <h2 class="text-lg font-semibold mb-6 flex items-center gap-2 text-primary">
            <UploadCloud class="w-5 h-5" /> Documents
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Upload Documents</label>
              <input type="file" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" @change="handleFiles($event, 'documents')" class="file-input" />
              <p class="text-xs text-gray-500 dark:text-gray-400">
                Multiple documents can be uploaded (PDF, DOC, JPG, PNG). Max 10MB per file.
              </p>
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Translation Documents (Optional)</label>
              <input type="file" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" @change="handleFiles($event, 'translation_docs')" class="file-input" />
              <p class="text-xs text-gray-500 dark:text-gray-400">
                Multiple translation documents can be uploaded (PDF, DOC, JPG, PNG). Max 10MB per file.
              </p>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-3">
          <button type="button" @click="$router.push('/dashboard/students')" class="px-6 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400">
            Cancel
          </button>
          <button type="submit" :disabled="loading" class="px-6 py-2 rounded-lg bg-primary text-white font-semibold shadow-lg flex items-center gap-2">
            <Loader2 v-if="loading" class="w-4 h-4 animate-spin" /> Create Student
          </button>
        </div>
      </form>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import { ChevronRight, Loader2, User, FileText, GraduationCap, UploadCloud } from 'lucide-vue-next';

const router = useRouter();
const loading = ref(false);
const countries = ref([]);
const universities = ref([]);
const courses = ref([]);
const intakes = ref([]);

const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  password: '',
  phone: '',
  father_name: '',
  mother_name: '',
  sponsor_phone: '',
  passport_number: '',
  passport_validity: '',
  address: '',
  date_of_birth: '',
  country_id: '',
  university_id: '',
  course_id: '',
  course_intake_id: '',
});

const files = ref({ documents: [], translation_docs: [] });

const intakesForCourse = computed(() => {
  const cid = form.value.course_id;
  if (!cid) return intakes.value;
  return intakes.value.filter((i) => String(i.course_id) === String(cid));
});

watch(
  () => form.value.course_id,
  () => {
    form.value.course_intake_id = '';
  }
);

const handleFiles = (event, type) => {
  files.value[type] = [...event.target.files];
};

const unwrapList = (res) => {
  const payload = res.data?.data;
  if (Array.isArray(payload)) return payload;
  if (payload?.data && Array.isArray(payload.data)) return payload.data;
  return [];
};

const loadData = async () => {
  try {
    const [c, u, co, ink] = await Promise.all([
      axios.get('/auth/admin/countries', { params: { per_page: 500 } }),
      axios.get('/auth/admin/universities', { params: { per_page: 500 } }),
      axios.get('/auth/admin/courses', { params: { per_page: 500 } }),
      axios.get('/auth/admin/course-intakes'),
    ]);
    countries.value = unwrapList(c);
    universities.value = unwrapList(u);
    courses.value = unwrapList(co);
    intakes.value = ink.data?.data ?? ink.data ?? [];
  } catch (e) {
    console.error('Data loading failed', e);
  }
};

const submit = async () => {
  loading.value = true;
  try {
    const fd = new FormData();
    const f = form.value;
    fd.append('first_name', f.first_name);
    fd.append('last_name', f.last_name);
    fd.append('email', f.email);
    fd.append('password', f.password);
    fd.append('phone', f.phone);
    fd.append('country_id', f.country_id);
    const optional = [
      'father_name',
      'mother_name',
      'sponsor_phone',
      'passport_number',
      'passport_validity',
      'address',
      'date_of_birth',
      'university_id',
      'course_id',
      'course_intake_id',
    ];
    optional.forEach((key) => {
      if (f[key] !== '' && f[key] !== null && f[key] !== undefined) {
        fd.append(key, f[key]);
      }
    });
    files.value.documents.forEach((file) => fd.append('documents[]', file));
    files.value.translation_docs.forEach((file) => fd.append('translation_docs[]', file));

    await axios.post('/auth/admin/students/register', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    router.push('/dashboard/students');
  } catch (e) {
    const msg = e.response?.data?.errors
      ? Object.values(e.response.data.errors).flat().join('\n')
      : e.response?.data?.message || 'Something went wrong';
    alert('Error: ' + msg);
  } finally {
    loading.value = false;
  }
};

onMounted(loadData);
</script>
