<template>
  <MainLayout>
    <div class="p-6 max-w-5xl mx-auto pb-24">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Academic Profile & Preferences</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Manage your credentials, study preferences, and upload academic documents.</p>
      </div>

      <!-- Loading State -->
      <div v-if="loadingProfile" class="flex flex-col items-center justify-center py-20 bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50">
        <Loader2 class="w-10 h-10 text-primary animate-spin mb-4" />
        <p class="text-gray-500 dark:text-gray-400">Loading academic details...</p>
      </div>

      <!-- Main Layout -->
      <div v-else class="grid grid-cols-1 gap-6">

        <!-- 1. Premium Completion Status Tracker -->
        <div class="bg-white dark:bg-[#1C252E] p-8 rounded-2xl border border-gray-200 dark:border-gray-700/50 shadow-sm">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-1.5">
              <h2 class="text-lg font-bold text-gray-900 dark:text-white">Application Readiness Tracker</h2>
              <p class="text-xs text-gray-500 dark:text-gray-400">Complete all required sections to submit your university profile for evaluation.</p>
            </div>
            <div class="flex items-center gap-4 shrink-0">
              <div class="text-right">
                <span class="text-3xl font-black text-primary">{{ completionStats.percentage }}%</span>
                <span class="text-xs text-gray-400 block font-bold uppercase tracking-wider">Completed</span>
              </div>
              <div class="w-32 bg-gray-100 dark:bg-gray-800 rounded-full h-3 overflow-hidden">
                <div class="bg-primary h-full transition-all duration-500" :style="{ width: `${completionStats.percentage}%` }"></div>
              </div>
            </div>
          </div>

          <!-- Checklist -->
          <div v-if="completionStats.pending.length > 0" class="mt-6 pt-5 border-t border-gray-100 dark:border-gray-800">
            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3">Still missing from your profile:</h4>
            <div class="flex flex-wrap gap-2">
              <span v-for="item in completionStats.pending" :key="item.label"
                class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400 text-xs font-semibold rounded-lg border border-amber-200/50 dark:border-amber-900/30">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                {{ item.label }}
              </span>
            </div>
          </div>
          <div v-else class="mt-5 p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200/50 dark:border-emerald-900/30 rounded-xl text-xs text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-2">
            ✓ Excellent! Your profile is 100% complete. You can now apply to any courses seamlessly.
          </div>
        </div>

        <!-- 2. Form Container -->
        <form @submit.prevent="saveAcademicProfile">
          <div class="bg-white dark:bg-[#1C252E] p-8 rounded-2xl border border-gray-200 dark:border-gray-700/50 shadow-sm space-y-8">
            
            <!-- Account Information -->
            <div>
              <h3 class="text-lg font-bold mb-1 flex items-center gap-2 text-gray-900 dark:text-white">
                <User :size="20" class="text-primary" /> Account Details
              </h3>
              <p class="text-xs text-gray-400 mb-6">Verify and update your basic account information.</p>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- First Name -->
                <div class="space-y-1.5">
                  <label class="text-xs font-bold text-gray-500 uppercase">First Name <span class="text-red-500">*</span></label>
                  <input type="text" v-model="profileForm.first_name" required placeholder="First name"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                  <p v-if="profileErrors.first_name" class="text-xs text-red-500">{{ profileErrors.first_name[0] }}</p>
                </div>

                <!-- Last Name -->
                <div class="space-y-1.5">
                  <label class="text-xs font-bold text-gray-500 uppercase">Last Name <span class="text-red-500">*</span></label>
                  <input type="text" v-model="profileForm.last_name" required placeholder="Last name"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                  <p v-if="profileErrors.last_name" class="text-xs text-red-500">{{ profileErrors.last_name[0] }}</p>
                </div>

                <!-- Email -->
                <div class="space-y-1.5 sm:col-span-2">
                  <label class="text-xs font-bold text-gray-500 uppercase">Email Address <span class="text-red-500">*</span></label>
                  <input type="email" v-model="profileForm.email" required placeholder="Email address"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                  <p v-if="profileErrors.email" class="text-xs text-red-500">{{ profileErrors.email[0] }}</p>
                </div>
              </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-100 dark:border-gray-800 my-6"></div>

            <!-- Personal & Guardian Info -->
            <div>
              <h3 class="text-lg font-bold mb-1 flex items-center gap-2 text-gray-900 dark:text-white">
                <FileText :size="20" class="text-primary" /> Personal & Guardian Details
              </h3>
              <p class="text-xs text-gray-400 mb-6">Enter your parents' details and emergency contact records.</p>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Father's Name -->
                <div class="space-y-1.5">
                  <label class="text-xs font-bold text-gray-500 uppercase">Father's Name</label>
                  <input type="text" v-model="profileForm.father_name" placeholder="Father's full name"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                </div>

                <!-- Mother's Name -->
                <div class="space-y-1.5">
                  <label class="text-xs font-bold text-gray-500 uppercase">Mother's Name</label>
                  <input type="text" v-model="profileForm.mother_name" placeholder="Mother's full name"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                </div>

                <!-- Phone -->
                <div class="space-y-1.5">
                  <label class="text-xs font-bold text-gray-500 uppercase">Phone Number <span class="text-red-500">*</span></label>
                  <input type="text" v-model="profileForm.phone" required placeholder="Contact phone number"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                  <p v-if="profileErrors.phone" class="text-xs text-red-500">{{ profileErrors.phone[0] }}</p>
                </div>

                <!-- Sponsor Phone -->
                <div class="space-y-1.5">
                  <label class="text-xs font-bold text-gray-500 uppercase">Sponsor Phone</label>
                  <input type="text" v-model="profileForm.sponsor_phone" placeholder="Emergency sponsor number"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                </div>

                <!-- Date of Birth -->
                <div class="space-y-1.5">
                  <label class="text-xs font-bold text-gray-500 uppercase">Date of Birth</label>
                  <input type="date" v-model="profileForm.date_of_birth"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                </div>

                <!-- Address -->
                <div class="space-y-1.5 sm:col-span-2">
                  <label class="text-xs font-bold text-gray-500 uppercase">Address</label>
                  <textarea v-model="profileForm.address" rows="2" placeholder="Street, City, Postal Code, Country"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"></textarea>
                </div>
              </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-100 dark:border-gray-800 my-6"></div>

            <!-- Academic & Passport details -->
            <div>
              <h3 class="text-lg font-bold mb-1 flex items-center gap-2 text-gray-900 dark:text-white">
                <GraduationCap :size="20" class="text-primary" /> Academic Score & Passport
              </h3>
              <p class="text-xs text-gray-400 mb-6">Enter your academic grades and passport credentials.</p>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Last Education Level (Dropdown) -->
                <div class="space-y-1.5">
                  <label class="text-xs font-bold text-gray-500 uppercase">Last Education Level</label>
                  <select v-model="profileForm.last_education_level"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all cursor-pointer">
                    <option value="" disabled>Select education level</option>
                    <option v-for="opt in educationLevelOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                  </select>
                  <p v-if="profileErrors.last_education_level" class="text-xs text-red-500">{{ profileErrors.last_education_level[0] }}</p>
                </div>

                <!-- CGPA -->
                <div class="space-y-1.5">
                  <label class="text-xs font-bold text-gray-500 uppercase">CGPA Score</label>
                  <input type="number" step="0.01" v-model="profileForm.cgpa" :placeholder="cgpaPlaceholder"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                  <p v-if="profileForm.last_education_level && cgpaScaleText" class="text-[11px] text-primary font-medium mt-0.5">Scale: {{ cgpaScaleText }}</p>
                  <p v-if="profileErrors.cgpa" class="text-xs text-red-500">{{ profileErrors.cgpa[0] }}</p>
                </div>

                <!-- IELTS -->
                <div class="space-y-1.5">
                  <label class="text-xs font-bold text-gray-500 uppercase">IELTS Score</label>
                  <input type="number" step="0.5" v-model="profileForm.ielts_score" placeholder="e.g. 7.0"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                  <p v-if="profileErrors.ielts_score" class="text-xs text-red-500">{{ profileErrors.ielts_score[0] }}</p>
                </div>

                <!-- Passport Number -->
                <div class="space-y-1.5">
                  <label class="text-xs font-bold text-gray-500 uppercase">Passport Number</label>
                  <input type="text" v-model="profileForm.passport_number" placeholder="Passport number"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                </div>

                <!-- Passport Expiry -->
                <div class="space-y-1.5">
                  <label class="text-xs font-bold text-gray-500 uppercase">Passport Expiry Date</label>
                  <input type="date" v-model="profileForm.passport_validity"
                    class="w-full bg-gray-50 dark:bg-[#141A21] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                </div>
              </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-100 dark:border-gray-800 my-6"></div>

            <!-- Study Preferences Section -->
            <div>
              <h3 class="text-lg font-bold mb-1 flex items-center gap-2 text-gray-900 dark:text-white">
                <Settings :size="20" class="text-primary" /> Study Preferences
              </h3>
              <p class="text-xs text-gray-400 mb-6">Select your preferred destination, universities, courses, and intakes.</p>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Course Level -->
                <div class="space-y-1.5">
                  <CustomSelect
                    v-model="profileForm.course_level_id"
                    :options="courseLevels"
                    label="Course Level"
                    placeholder="Select course level"
                    label-key="name"
                    value-key="id"
                  />
                </div>

                <!-- Target Country -->
                <div class="space-y-1.5">
                  <CustomSelect
                    v-model="profileForm.country_id"
                    :options="countries"
                    label="Preferred Country"
                    placeholder="Search and select country"
                    label-key="name"
                    value-key="id"
                  />
                </div>

                <!-- Target University (Filtered) -->
                <div class="space-y-1.5">
                  <CustomSelect
                    v-model="profileForm.university_id"
                    :options="filteredUniversities"
                    label="Preferred University"
                    placeholder="Search and select university"
                    label-key="name"
                    value-key="id"
                    :disabled="!profileForm.country_id"
                  />
                  <p v-if="!profileForm.country_id" class="text-[11px] text-gray-400">Please select preferred country first</p>
                </div>

                <!-- Target Course (Filtered) -->
                <div class="space-y-1.5">
                  <CustomSelect
                    v-model="profileForm.course_id"
                    :options="filteredCourses"
                    label="Preferred Course"
                    placeholder="Search and select course"
                    label-key="name"
                    value-key="id"
                    :disabled="!profileForm.university_id"
                  />
                  <p v-if="!profileForm.university_id" class="text-[11px] text-gray-400">Please select preferred university first</p>
                </div>

                <!-- Target Intake (Filtered) -->
                <div class="space-y-1.5">
                  <CustomSelect
                    v-model="profileForm.course_intake_id"
                    :options="filteredIntakes"
                    label="Preferred Intake"
                    placeholder="Select intake"
                    label-key="intake_name"
                    value-key="id"
                    :disabled="!profileForm.course_id"
                  />
                  <p v-if="!profileForm.course_id" class="text-[11px] text-gray-400">Please select preferred course first</p>
                </div>
              </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-100 dark:border-gray-800 my-6"></div>

            <!-- Documents Uploader Section matching the requested styling -->
            <div>
              <h3 class="text-lg font-semibold mb-6 flex items-center gap-2 text-primary">
                <UploadCloud class="w-5 h-5" /> Documents
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Academic Documents -->
                <div class="space-y-3">
                  <label class="text-sm font-medium dark:text-gray-300">Upload Documents</label>

                  <!-- Document List with View/Download/Remove -->
                  <div v-if="existingDocs.length > 0" class="space-y-2 mb-3">
                    <div v-for="(doc, index) in existingDocs" :key="index" class="flex items-center justify-between p-2 text-xs bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                      <span class="truncate font-medium text-gray-700 dark:text-gray-300 max-w-[150px]" :title="doc.file_name">{{ doc.file_name }}</span>
                      <div class="flex gap-2 shrink-0">
                        <a :href="doc.url" target="_blank" class="text-blue-600 hover:underline font-semibold cursor-pointer">View</a>
                        <a :href="doc.url" download :download="doc.file_name" class="text-green-600 hover:underline font-semibold cursor-pointer">Download</a>
                        <button type="button" @click="deleteExistingDoc('documents', doc.path)" class="text-red-500 hover:text-red-700 font-semibold cursor-pointer">Remove</button>
                      </div>
                    </div>
                  </div>
                  <div v-else class="text-xs text-orange-500 italic mb-2 font-medium">
                     No documents uploaded. Please upload.
                  </div>

                  <input type="file" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" @change="e => academicFiles = Array.from(e.target.files)" :class="fileInputClass" />
                </div>

                <!-- Official University Documents -->
                <div class="space-y-3">
                  <label class="text-sm font-medium dark:text-gray-300">Official University Documents</label>

                  <div v-if="existingTranslationDocs.length > 0" class="space-y-2 mb-3">
                    <div v-for="(doc, index) in existingTranslationDocs" :key="index" class="flex items-center justify-between p-2 text-xs bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                      <span class="truncate font-medium text-gray-700 dark:text-gray-300 max-w-[150px]" :title="doc.file_name">{{ doc.file_name }}</span>
                      <div class="flex gap-2 shrink-0">
                        <a :href="doc.url" target="_blank" class="text-blue-600 hover:underline font-semibold cursor-pointer">View</a>
                        <a :href="doc.url" download :download="doc.file_name" class="text-green-600 hover:underline font-semibold cursor-pointer">Download</a>
                        <button v-if="viewingStudent" type="button" @click="deleteExistingDoc('translation_documents', doc.path)" class="text-red-500 hover:text-red-700 font-semibold cursor-pointer">Remove</button>
                      </div>
                    </div>
                  </div>
                  <div v-else class="text-xs text-orange-500 italic mb-2 font-medium">
                    No official university documents found.
                  </div>

                  <input v-if="viewingStudent" type="file" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" @change="e => translationFiles = Array.from(e.target.files)" :class="fileInputClass" />
                </div>
              </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end pt-4">
              <button type="submit" :disabled="savingProfile"
                class="px-8 py-3 bg-primary text-white rounded-xl font-bold shadow-lg shadow-primary/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2 text-sm cursor-pointer">
                <Loader2 v-if="savingProfile" class="w-4 h-4 animate-spin" />
                {{ savingProfile ? 'Saving Details...' : 'Save Academic Profile' }}
              </button>
            </div>

          </div>
        </form>

      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import MainLayout from '@/layouts/MainLayout.vue';
import CustomSelect from '@/components/Form/CustomSelect.vue';
import axios from '@/plugins/axios';
import { GraduationCap, FileText, Trash2, Download, UploadCloud, Settings, Loader2, User } from 'lucide-vue-next';

const auth  = useAuthStore();
const route = useRoute();

const studentId = computed(() => route.query.student_id || null);
const isAdminOrConsultant = computed(() => auth.user?.role === 'admin' || auth.user?.role === 'consultant');
const viewingStudent = computed(() => studentId.value && isAdminOrConsultant.value);

const profileApi = (path = '') => {
  const base = viewingStudent.value ? `/auth/admin/students/${studentId.value}` : '/auth/profile';
  return base + path;
};

// ── Date Format Helper ──────────────────────────────────────────
// Converts '2007-01-21T00:00:00.000000Z' → '2007-01-21' for <input type="date">
const formatDate = (val) => {
  if (!val) return '';
  return val.toString().substring(0, 10);
};

// ── Shared: Loading State ───────────────────────────────────────
const loadingProfile = ref(true);

// ── Dropdown options lists ──────────────────────────────────────
const countries     = ref([]);
const courseLevels  = ref([]);
const universities = ref([]);
const courses      = ref([]);
const intakes      = ref([]);

// ── Temporary Selected File lists for uploading ──────────────────
const academicFiles    = ref([]);
const translationFiles = ref([]);
const existingDocs     = ref([]);
const existingTranslationDocs = ref([]);

// ── Classes matching edit.vue ───────────────────────────────────
const fileInputClass = "w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer";

// ── Education Level Options ──────────────────────────────────
const educationLevelOptions = [
  { value: 'ssc',           label: 'SSC' },
  { value: 'hsc',           label: 'HSC' },
  { value: 'diploma',       label: 'Diploma' },
  { value: 'bachelor',      label: 'Bachelor' },
  { value: 'masters',       label: 'Masters' },
  { value: 'postgraduate', label: 'Postgraduate' },
];

// ── CGPA Scale hint based on selected education level ─────────
const cgpaScaleText = computed(() => {
  const level = profileForm.value.last_education_level;
  if (!level) return '';
  const map = {
    ssc:     'out of 5.00',
    hsc:     'out of 5.00',
    diploma: 'out of 4.00',
    bachelor:'out of 4.00',
    masters: 'out of 4.00',
    postgraduate:'out of 4.00',
  };
  return map[level] || '';
});

const cgpaPlaceholder = computed(() => {
  const level = profileForm.value.last_education_level;
  if (!level) return 'Select education level first';
  return (level === 'ssc' || level === 'hsc') ? 'e.g. 4.50' : 'e.g. 3.85';
});

// ── Profile Form ────────────────────────────────────────────────
const savingProfile  = ref(false);
const profileErrors  = ref({});

const profileForm = ref({
  first_name:        '',
  last_name:         '',
  email:             '',
  phone:             '',

  father_name:       '',
  mother_name:       '',
  sponsor_phone:     '',
  passport_number:   '',
  passport_validity: '',
  address:           '',
  date_of_birth:     '',
  cgpa:              '',
  last_education_level: '',
  ielts_score:       '',
  country_id:        '',
  course_level_id:   '',
  university_id:     '',
  course_id:         '',
  course_intake_id:  '',
});

// ── Profile Completion Tracker ──────────────────────────
const completionStats = computed(() => {
  const steps = [
    { label: "First Name", filled: !!profileForm.value.first_name },
    { label: "Last Name", filled: !!profileForm.value.last_name },
    { label: "Email", filled: !!profileForm.value.email },
    { label: "Phone", filled: !!profileForm.value.phone },
    { label: "Father's Name", filled: !!profileForm.value.father_name },
    { label: "Mother's Name", filled: !!profileForm.value.mother_name },
    { label: "Passport Number", filled: !!profileForm.value.passport_number },
    { label: "Passport Expiry Date", filled: !!profileForm.value.passport_validity },
    { label: "Last Education Level", filled: !!profileForm.value.last_education_level },
    { label: "CGPA Score", filled: !!profileForm.value.cgpa },
    { label: "IELTS Score", filled: !!profileForm.value.ielts_score },
    { label: "Preferred Destination Country", filled: !!profileForm.value.country_id },
    { label: "Course Level", filled: !!profileForm.value.course_level_id },
    { label: "Preferred University", filled: !!profileForm.value.university_id },
    { label: "Preferred Course", filled: !!profileForm.value.course_id },
    { label: "Preferred Intake", filled: !!profileForm.value.course_intake_id },
    { label: "Academic Documents", filled: existingDocs.value.length > 0 },
  ];

  const total = steps.length;
  const completed = steps.filter(s => s.filled).length;
  const percentage = Math.round((completed / total) * 100);
  const pending = steps.filter(s => !s.filled);

  return { percentage, pending };
});

// ── Computed Cascading Filter Lists ──────────────────────────────
const filteredUniversities = computed(() => {
  if (!profileForm.value.country_id) return [];
  return universities.value.filter(uni => uni.country_id === profileForm.value.country_id);
});

const filteredCourses = computed(() => {
  if (!profileForm.value.university_id) return [];
  return courses.value.filter(course => course.university_id === profileForm.value.university_id);
});

const filteredIntakes = computed(() => {
  if (!profileForm.value.course_id) return [];
  return intakes.value.filter(intake => intake.course_id === profileForm.value.course_id);
});

// ── Cascading Filter Clears (Watchers) ──────────────────────────
watch(() => profileForm.value.country_id, (newVal, oldVal) => {
  if (oldVal === '' || oldVal === null || oldVal === undefined) return;
  const currentUni = universities.value.find(u => u.id === profileForm.value.university_id);
  if (!currentUni || currentUni.country_id !== newVal) {
    profileForm.value.university_id = '';
    profileForm.value.course_id = '';
    profileForm.value.course_intake_id = '';
  }
});

watch(() => profileForm.value.university_id, (newVal, oldVal) => {
  if (oldVal === '' || oldVal === null || oldVal === undefined) return;
  const currentCourse = courses.value.find(c => c.id === profileForm.value.course_id);
  if (!currentCourse || currentCourse.university_id !== newVal) {
    profileForm.value.course_id = '';
    profileForm.value.course_intake_id = '';
  }
});

watch(() => profileForm.value.course_id, (newVal, oldVal) => {
  if (oldVal === '' || oldVal === null || oldVal === undefined) return;
  const currentIntake = intakes.value.find(i => i.id === profileForm.value.course_intake_id);
  if (!currentIntake || currentIntake.course_id !== newVal) {
    profileForm.value.course_intake_id = '';
  }
});

// ── Helper to cleanly unwrap paginate API resources ─────────────
const unwrapList = (res) => {
  return res.data?.data?.data || res.data?.data || res.data || [];
};

// ── Load Dropdowns List ──────────────────────────────────────────
const loadDropdownData = async () => {
  try {
    const [c, cl, u, co, ink] = await Promise.all([
      axios.get('/auth/dropdowns/countries', { params: { per_page: 500 } }),
      axios.get('/auth/dropdowns/course-levels', { params: { per_page: 500 } }),
      axios.get('/auth/dropdowns/universities', { params: { per_page: 500 } }),
      axios.get('/auth/dropdowns/courses', { params: { per_page: 500 } }),
      axios.get('/auth/dropdowns/course-intakes'),
    ]);
    countries.value     = unwrapList(c);
    courseLevels.value  = unwrapList(cl);
    universities.value  = unwrapList(u);
    courses.value       = unwrapList(co);
    intakes.value       = unwrapList(ink);
  } catch (e) {
    console.error('Failed to load study preference dropdown options', e);
  }
};

// ── Fetch Profile ───────────────────────────────────────────────
const fetchProfile = async () => {
  loadingProfile.value = true;
  try {
    const res     = await axios.get(profileApi());
    const profile = res.data.data;

    // Prefill general info to satisfy backend validator
    profileForm.value.first_name        = profile.first_name        || '';
    profileForm.value.last_name         = profile.last_name         || '';
    profileForm.value.email             = profile.email             || '';
    profileForm.value.phone             = profile.phone             || '';

    profileForm.value.cgpa              = profile.cgpa              || '';
    profileForm.value.last_education_level = profile.last_education_level || '';
    profileForm.value.ielts_score       = profile.ielts_score       || '';
    profileForm.value.father_name       = profile.father_name       || '';
    profileForm.value.mother_name       = profile.mother_name       || '';
    profileForm.value.sponsor_phone     = profile.sponsor_phone     || '';
    profileForm.value.passport_number   = profile.passport_number             || '';
    profileForm.value.passport_validity = formatDate(profile.passport_validity);
    profileForm.value.date_of_birth     = formatDate(profile.date_of_birth);
    profileForm.value.address           = profile.address                      || '';
    profileForm.value.country_id        = profile.country_id        || '';
    profileForm.value.course_level_id   = profile.course_level_id   || '';
    profileForm.value.university_id     = profile.university_id     || '';
    profileForm.value.course_id         = profile.course_id         || '';
    profileForm.value.course_intake_id  = profile.course_intake_id  || '';

    // Uploaded Documents
    existingDocs.value            = profile.documents            || [];
    existingTranslationDocs.value = profile.translation_documents || [];
  } catch (err) {
    console.error('Failed to load academic profile details', err);
  } finally {
    loadingProfile.value = false;
  }
};

// ── Delete Document Handler ─────────────────────────────────────
const deleteExistingDoc = async (type, path) => {
  if (!confirm('Are you sure you want to remove this document permanently?')) return;
  try {
    const url = viewingStudent.value
      ? `/auth/admin/students/${studentId.value}/document`
      : '/auth/profile/document';
    await axios.delete(url, { data: { type, path } });
    await fetchProfile();
  } catch (e) {
    console.error('Failed to remove document', e);
  }
};

// ── Save Academic Profile ───────────────────────────────────────
const saveAcademicProfile = async () => {
  savingProfile.value = true;
  profileErrors.value = {};

  try {
    const fd = new FormData();
    fd.append('_method',      'PUT');
    
    // Required fields to satisfy backend validation
    fd.append('first_name',        profileForm.value.first_name        || '');
    fd.append('last_name',         profileForm.value.last_name         || '');
    fd.append('email',             profileForm.value.email             || '');
    fd.append('phone',             profileForm.value.phone             || '');

    fd.append('father_name',       profileForm.value.father_name       || '');
    fd.append('mother_name',       profileForm.value.mother_name       || '');
    fd.append('sponsor_phone',     profileForm.value.sponsor_phone     || '');
    fd.append('passport_number',   profileForm.value.passport_number   || '');
    fd.append('passport_validity', profileForm.value.passport_validity || '');
    fd.append('date_of_birth',     profileForm.value.date_of_birth     || '');
    fd.append('address',           profileForm.value.address           || '');
    fd.append('cgpa',              profileForm.value.cgpa              || '');
    fd.append('last_education_level', profileForm.value.last_education_level || '');
    fd.append('ielts_score',       profileForm.value.ielts_score       || '');
    fd.append('country_id',        profileForm.value.country_id        || '');
    fd.append('course_level_id',   profileForm.value.course_level_id   || '');
    fd.append('university_id',     profileForm.value.university_id     || '');
    fd.append('course_id',         profileForm.value.course_id         || '');
    fd.append('course_intake_id',  profileForm.value.course_intake_id  || '');

    // Append newly selected files for upload
    academicFiles.value.forEach(file => {
      fd.append('documents[]', file);
    });
    translationFiles.value.forEach(file => {
      fd.append('translation_docs[]', file);
    });

    await axios.post(profileApi(), fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    // Clear temp files list
    academicFiles.value    = [];
    translationFiles.value = [];

    await fetchProfile();
  } catch (err) {
    if (err.response?.status === 422) {
      profileErrors.value = err.response.data.errors || {};
    } else {
      console.error('Academic profile update failed:', err.response?.data?.message);
    }
  } finally {
    savingProfile.value = false;
  }
};

onMounted(async () => {
  await fetchProfile();
  await loadDropdownData();
});
</script>
