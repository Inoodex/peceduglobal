<template>
  <MainLayout>
    <div class="max-w-4xl mx-auto pb-20">
      <!-- Header Section -->
      <div class="mb-6 flex justify-between items-end">
        <div v-if="!loadingStudent">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Edit Student</h1>
          <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span
            <ChevronRight class="w-4 h-4" />
            <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard/students')">Students</span
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-900 dark:text-white">Edit</span>
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

      <!-- Loading State -->
      <div v-if="loadingStudent" class="flex flex-col items-center justify-center py-20 gap-4">
        <Loader2 class="w-10 h-10 animate-spin text-primary" />
        <p class="text-gray-500 dark:text-gray-400">Loading student data...</p>
      </div>

      <!-- Main Form -->
      <form v-else @submit.prevent="submit" class="space-y-8">
        <!-- 1. Account Section -->
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
          <h2 class="text-lg font-semibold mb-6 flex items-center gap-2 text-primary">
            <User class="w-5 h-5" /> Account
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">First Name <span class="text-red-500">*</span></label>
              <input v-model="form.first_name" type="text" required :class="[inputClass, { 'border-orange-500': !form.first_name }]" />
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Last Name <span class="text-red-500">*</span></label>
              <input v-model="form.last_name" type="text" required :class="[inputClass, { 'border-orange-500': !form.last_name }]" />
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Email <span class="text-red-500">*</span></label>
              <input v-model="form.email" type="email" required :class="[inputClass, { 'border-orange-500': !form.email }]" />
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Password</label>
              <input v-model="form.password" type="password" :class="inputClass" placeholder="Leave blank to keep current" />
            </div>
          </div>
        </div>

        <!-- 2. Personal Section -->
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
          <h2 class="text-lg font-semibold mb-6 flex items-center gap-2 text-primary">
            <FileText class="w-5 h-5" /> Personal
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Father's Name</label>
              <input v-model="form.father_name" type="text" :class="[inputClass, { 'border-orange-500': !form.father_name }]" />
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Mother's Name</label>
              <input v-model="form.mother_name" type="text" :class="[inputClass, { 'border-orange-500': !form.mother_name }]" />
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Phone <span class="text-red-500">*</span></label>
              <input v-model="form.phone" type="text" required :class="[inputClass, { 'border-orange-500': !form.phone }]" />
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Sponsor Phone</label>
              <input v-model="form.sponsor_phone" type="text" :class="[inputClass, { 'border-orange-500': !form.sponsor_phone }]" />
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Passport Number</label>
              <input v-model="form.passport_number" type="text" :class="[inputClass, { 'border-orange-500': !form.passport_number }]" />
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Passport Validity</label>
              <input v-model="form.passport_validity" type="date" :class="[inputClass, { 'border-orange-500': !form.passport_validity }]" />
            </div>
            <div class="space-y-2 md:col-span-2">
              <label class="text-sm font-medium dark:text-gray-300">Address</label>
              <textarea v-model="form.address" rows="2" :class="[inputClass, { 'border-orange-500': !form.address }]" placeholder="Street, city, country"></textarea>
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Date of Birth</label>
              <input v-model="form.date_of_birth" type="date" :class="[inputClass, { 'border-orange-500': !form.date_of_birth }]" />
            </div>
          </div>
        </div>

        <!-- 3. Study Preferences -->
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
          <h2 class="text-lg font-semibold mb-6 flex items-center gap-2 text-primary">
            <GraduationCap class="w-5 h-5" /> Study preferences
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2 md:col-span-2">
              <label class="text-sm font-medium dark:text-gray-300">Target Country <span class="text-red-500">*</span></label>
              <select v-model="form.country_id" required :class="[inputClass, { 'border-orange-500': !form.country_id }]">
                <option value="">Select Country</option>
                <option v-for="c in countries" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Preferred University</label>
              <select v-model="form.university_id" :class="[inputClass, { 'border-orange-500': !form.university_id }]">
                <option value="">Select University</option>
                <option v-for="u in universities" :key="u.id" :value="u.id">{{ u.name }}</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-gray-300">Preferred Course</label>
              <select v-model="form.course_id" :class="[inputClass, { 'border-orange-500': !form.course_id }]">
                <option value="">Select Course</option>
                <option v-for="co in courses" :key="co.id" :value="co.id">{{ co.name }}</option>
              </select>
            </div>
            <div class="space-y-2 md:col-span-2">
              <label class="text-sm font-medium dark:text-gray-300">Preferred Intake</label>
              <select v-model="form.course_intake_id" :class="[inputClass, { 'border-orange-500': !form.course_intake_id }]">
                <option value="">Select Intake</option>
                <option v-for="i in intakesForCourse" :key="i.id" :value="i.id">
                  {{ i.intake_name }} — {{ i.course?.name }} ({{ i.university?.name }})
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- 4. Documents Section -->
        <div class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-6 shadow-sm">
          <h2 class="text-lg font-semibold mb-6 flex items-center gap-2 text-primary">
            <UploadCloud class="w-5 h-5" /> Documents
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Primary Documents -->
            <div class="space-y-3">
              <label class="text-sm font-medium dark:text-gray-300">Upload Documents</label>

              <!-- Document List with View/Download/Remove -->
              <div v-if="existingDocs.length > 0" class="space-y-2 mb-3">
                <div v-for="(doc, index) in existingDocs" :key="index" class="flex items-center justify-between p-2 text-xs bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                  <span class="truncate font-medium text-gray-700 dark:text-gray-300 max-w-[150px]">{{ doc.file_name }}</span>
                  <div class="flex gap-2 shrink-0">
                    <a :href="doc.url" target="_blank" class="text-blue-600 hover:underline font-semibold">View</a>
                    <a :href="doc.url" download :download="doc.file_name" class="text-green-600 hover:underline font-semibold">Download</a>
                    <button type="button" @click="removeFile(doc.path || doc.file_path, 'documents')" class="text-red-500 hover:text-red-700 font-semibold">Remove</button>
                  </div>
                </div>
              </div>
              <div v-else class="text-xs text-orange-500 italic mb-2">
                 No documents uploaded. Please upload.
              </div>

              <input type="file" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" @change="handleFiles($event, 'documents')" :class="fileInputClass" />
            </div>

            <!-- Translation Documents -->
            <div class="space-y-3">
              <label class="text-sm font-medium dark:text-gray-300">Translation Documents (Optional)</label>

              <div v-if="existingTransDocs.length > 0" class="space-y-2 mb-3">
                <div v-for="(doc, index) in existingTransDocs" :key="index" class="flex items-center justify-between p-2 text-xs bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                  <span class="truncate font-medium text-gray-700 dark:text-gray-300 max-w-[150px]">{{ doc.file_name }}</span>
                  <div class="flex gap-2 shrink-0">
                    <a :href="doc.url" target="_blank" class="text-blue-600 hover:underline font-semibold">View</a>
                    <a :href="doc.url" download :download="doc.file_name" class="text-green-600 hover:underline font-semibold">Download</a>
                    <button type="button" @click="removeFile(doc.path || doc.file_path, 'translation_documents')" class="text-red-500 hover:text-red-700 font-semibold">Remove</button>
                  </div>
                </div>
              </div>
              <div v-else class="text-xs  text-orange-500  italic mb-2">
                No translation documents found.
              </div>

              <input type="file" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" @change="handleFiles($event, 'translation_docs')" :class="fileInputClass" />
            </div>
          </div>
        </div>

        <!-- Footer Buttons -->
        <div class="flex justify-end gap-3">
          <button type="button" @click="$router.push('/dashboard/students')" class="px-6 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400">
            Cancel
          </button>
          <button type="submit" :disabled="loading" class="px-6 py-2 rounded-lg bg-primary text-white font-semibold shadow-lg flex items-center gap-2">
            <Loader2 v-if="loading" class="w-4 h-4 animate-spin" /> Update Student
          </button>
        </div>
      </form>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import { ChevronRight, Loader2, User, FileText, GraduationCap, UploadCloud } from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();
const loading = ref(false);
const loadingStudent = ref(true);
const countries = ref([]);
const universities = ref([]);
const courses = ref([]);
const intakes = ref([]);

const inputClass = "w-full px-4 py-2 rounded-lg border dark:bg-[#141A21] dark:border-gray-700 dark:text-white outline-none focus:ring-2 focus:ring-primary/20 transition-all";
const fileInputClass = "w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20";

const form = ref({
  first_name: '', last_name: '', email: '', password: '', phone: '',
  father_name: '', mother_name: '', sponsor_phone: '', passport_number: '',
  passport_validity: '', address: '', date_of_birth: '', country_id: '',
  university_id: '', course_id: '', course_intake_id: '',
});

const existingDocs = ref([]);
const existingTransDocs = ref([]);
const files = ref({ documents: [], translation_docs: [] });

// FIX: Cast to String to ensure match with IDs from API
const intakesForCourse = computed(() => {
  const cid = form.value.course_id;
  if (!cid) return intakes.value;
  return intakes.value.filter((i) => String(i.course_id) === String(cid));
});

watch(() => form.value.course_id, () => { form.value.course_intake_id = ''; });

const handleFiles = (event, type) => {
  files.value[type] = [...event.target.files];
};

const unwrapList = (res) => {
  const payload = res.data?.data;
  if (Array.isArray(payload)) return payload;
  if (payload?.data && Array.isArray(payload.data)) return payload.data;
  return [];
};

const loadDropdownData = async () => {
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
  } catch (e) { console.error('Dropdown data loading failed', e); }
};

const fetchStudent = async () => {
  loadingStudent.value = true;
  try {
    const response = await axios.get(`/auth/admin/students/${route.params.id}`);
    const student = response.data.data;

    form.value = {
      first_name: student.first_name || '',
      last_name: student.last_name || '',
      email: student.email || '',
      password: '',
      phone: student.phone || '',
      father_name: student.father_name || '',
      mother_name: student.mother_name || '',
      sponsor_phone: student.sponsor_phone || '',
      passport_number: student.passport_number || '',
      passport_validity: student.passport_validity || '',
      address: student.address || '',
      date_of_birth: student.date_of_birth || '',
      country_id: student.country_id || '',
      university_id: student.university_id || '',
      course_id: student.course_id || '',
      course_intake_id: student.course_intake_id || '',
    };

    existingDocs.value = student.documents || [];
    existingTransDocs.value = student.translation_documents || [];

  } catch (e) {
    alert('Error fetching student details');
  } finally {
    loadingStudent.value = false;
  }
};

const removeFile = async (path, type) => {
  if (!confirm('Are you sure you want to remove this document?')) return;
  try {
    await axios.delete(`/auth/admin/students/${route.params.id}/document`, {
      data: { path, type }
    });

    if (type === 'documents') {
      existingDocs.value = existingDocs.value.filter(d => d.path !== path);
    } else {
      existingTransDocs.value = existingTransDocs.value.filter(d => d.path !== path);
    }
  } catch (e) {
    alert('Error removing document');
  }
};

const submit = async () => {
  loading.value = true;
  try {
    const fd = new FormData();
    fd.append('_method', 'PUT');

    const f = form.value;
    fd.append('first_name', f.first_name);
    fd.append('last_name', f.last_name);
    fd.append('email', f.email);
    if(f.password) fd.append('password', f.password);
    fd.append('phone', f.phone);
    fd.append('country_id', f.country_id);

    const optional = [
      'father_name', 'mother_name', 'sponsor_phone',
      'passport_number', 'passport_validity', 'address',
      'date_of_birth', 'university_id', 'course_id', 'course_intake_id',
    ];
    optional.forEach((key) => {
      if (f[key] !== '' && f[key] !== null) fd.append(key, f[key]);
    });

    files.value.documents.forEach((file) => fd.append('documents[]', file));
    files.value.translation_docs.forEach((file) => fd.append('translation_docs[]', file));

    await axios.post(`/auth/admin/students/${route.params.id}`, fd, {
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

onMounted(async () => {
  await loadDropdownData();
  await fetchStudent();
});
</script>
