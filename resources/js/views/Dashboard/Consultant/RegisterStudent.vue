<template>
  <MainLayout>
    <div class="max-w-3xl mx-auto pb-20">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Create a new category</h1>
        <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard')">Dashboard</span>
          <ChevronRight class="w-4 h-4" />
          <span class="hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer" @click="$router.push('/dashboard/students')">Students</span>
          <ChevronRight class="w-4 h-4" />
          <ChevronRight class="w-4 h-4" />
          <span class="text-gray-900 dark:text-white">Create</span>
        </nav>
      </div>

      <!-- Form container -->
      <div
        class="bg-white dark:bg-[#1C252E] rounded-2xl border border-gray-200 dark:border-gray-700/50 p-8 shadow-sm"
      >
        <form @submit.prevent="submit" class="space-y-6">

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- First name -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                First Name *
              </label>
              <input
                v-model="form.first_name"
                type="text"
                placeholder="Enter first name"
                required
                class="w-full px-4 py-2 rounded-lg border
                       dark:bg-[#141A21] dark:border-gray-700 dark:text-white
                       outline-none focus:ring-2 focus:ring-primary/20 transition-all"
              />
            </div>

            <!-- Last name -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Last Name *
              </label>
              <input
                v-model="form.last_name"
                type="text"
                placeholder="Enter last name"
                required
                class="w-full px-4 py-2 rounded-lg border
                       dark:bg-[#141A21] dark:border-gray-700 dark:text-white
                       outline-none focus:ring-2 focus:ring-primary/20 transition-all"
              />
            </div>

            <!-- Email (full width) -->
            <div class="space-y-2 md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Email Address *
              </label>
              <input
                v-model="form.email"
                type="email"
                placeholder="student@example.com"
                required
                class="w-full px-4 py-2 rounded-lg border
                       dark:bg-[#141A21] dark:border-gray-700 dark:text-white
                       outline-none focus:ring-2 focus:ring-primary/20 transition-all"
              />
            </div>

            <!-- Password -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Password *
              </label>
              <input
                v-model="form.password"
                type="password"
                placeholder="Min 6 characters"
                required
                class="w-full px-4 py-2 rounded-lg border
                       dark:bg-[#141A21] dark:border-gray-700 dark:text-white
                       outline-none focus:ring-2 focus:ring-primary/20 transition-all"
              />
            </div>

            <!-- Phone -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Phone Number *
              </label>
              <input
                v-model="form.phone"
                type="text"
                placeholder="+880..."
                required
                class="w-full px-4 py-2 rounded-lg border
                       dark:bg-[#141A21] dark:border-gray-700 dark:text-white
                       outline-none focus:ring-2 focus:ring-primary/20 transition-all"
              />
            </div>

            <!-- Country (full width) -->
            <!-- <div class="space-y-2 md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Country of Origin *
              </label>
              <select
                v-model="form.country_id"
                required
                class="w-full px-4 py-2 rounded-lg border
                       dark:bg-[#141A21] dark:border-gray-700 dark:text-white
                       outline-none focus:ring-2 focus:ring-primary/20 transition-all"
              >
                <option value="" disabled selected>Select Country</option>
                <option v-for="c in countries" :key="c.id" :value="c.id">
                  {{ c.name }}
                </option>
              </select>
            </div> -->
          </div>

          <!-- Buttons -->
          <div class="pt-4 flex justify-end gap-3">
            <button
              type="button"
              @click="$router.push('/dashboard/students')"
              class="px-6 py-2 rounded-lg border border-gray-300 dark:border-gray-600
                     text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800
                     transition-all"
            >Cancel</button>

            <button
              type="submit"
              class="px-6 py-2 rounded-lg bg-primary text-white font-semibold
                     hover:bg-primary-dark transition-all shadow-lg shadow-primary/20"
            >Register Student</button>
          </div>

        </form>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from '@/plugins/axios';
import MainLayout from '@/layouts/MainLayout.vue';
import {
  ChevronRight,
  ChevronDown,
} from 'lucide-vue-next';

const router = useRouter();
const countries = ref([]);

const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  password: '',
  phone: '',
//   country_id: '',
});

const fetchCountries = async () => {
  try {
    const { data } = await axios.get('/auth/content/countries');
    countries.value = data.data;
  } catch (e) {
    console.error('Error fetching countries:', e);
  }
};
const submit = async () => {
  try {
    const payload = {
      email: form.value.email,
      password: form.value.password,
      phone: form.value.phone,
      full_name: `${form.value.first_name} ${form.value.last_name}`.trim()
    };
    await axios.post('/auth/content/students/register', payload);
    router.push('/dashboard/students');
  } catch (e) {
    const msg = e.response?.data?.errors
      ? Object.values(e.response.data.errors).flat().join('\n')
      : e.response?.data?.message || 'Something went wrong';
    alert('Registration failed:\n' + msg);
  }
};

onMounted(fetchCountries);
</script>
