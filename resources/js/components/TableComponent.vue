<template>
  <div class="overflow-x-auto">
    <table class="min-w-full border-collapse" :class="tableClass">
      <thead>
        <tr>
          <th v-for="(col, index) in columns" :key="index" class="border px-4 py-2 bg-gray-100">
            {{ col.label }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, rowIndex) in data" :key="rowIndex" class="border-t">
          <td v-for="(col, colIndex) in columns" :key="colIndex" class="border px-4 py-2">
            {{ row[col.field] }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  columns: {
    type: Array,
    required: true,
    // Example: [{ label: 'Name', field: 'name' }]
  },
  data: {
    type: Array,
    required: true,
    // Example: [{ name: 'John' }]
  },
  tableClass: {
    type: String,
    default: ''
  }
});
</script>

<style scoped>
/* Add any custom table styles here */
</style>
