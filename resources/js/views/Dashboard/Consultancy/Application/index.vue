<template>
  <MainLayout>
    <div class="p-6 space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Application List</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">Track all student applications here.</p>
        </div>
        <button
          class="px-4 py-2 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary-dark transition"
          @click="$router.push('/dashboard/applications/create')"
        >
          Add Application
        </button>
      </div>

      <div class="bg-white dark:bg-[#1C252E] border border-gray-100 dark:border-gray-800 rounded-2xl p-6">
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Application list UI is ready. Connect it with your application listing API.
        </p>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/layouts/MainLayout.vue';
</script>
