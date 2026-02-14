<!-- app/components/AppAlert.vue -->
<template>
  <div class="border border-red-200 bg-red-50 text-red-800 px-3 py-2 rounded">
    {{ message }}
  </div>
</template>

<script setup lang="ts">
defineProps<{ message: string }>()
</script>
