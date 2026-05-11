<template>
  <MainLayout>
    <div class="p-6 space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Add Application</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">Create a new application for a student.</p>
        </div>
        <button
          class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-xl text-sm font-semibold"
          @click="$router.push('/dashboard/applications')"
        >
          Back to List
        </button>
      </div>

      <div class="bg-white dark:bg-[#1C252E] border border-gray-100 dark:border-gray-800 rounded-2xl p-6">
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Application create form scaffold is ready. Bind intake, university, and course fields with API data.
        </p>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/layouts/MainLayout.vue';
</script>
