<template>
  <MainLayout>
    <div class="max-w-7xl mx-auto p-6">
      <h1 class="text-2xl font-bold mb-4">Block Manager</h1>
      <router-link to="/admin/blocks" class="text-primary hover:underline">Manage Blocks</router-link>
    </div>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/layouts/MainLayout.vue';
</script>
