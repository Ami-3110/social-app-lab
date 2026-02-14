<!-- app/components/AppToast.vue -->
<template>
  <transition name="fade">
    <div v-if="open" class="fixed bottom-4 right-4 border rounded px-3 py-2 bg-black/80 text-white">
      {{ message }}
    </div>
  </transition>
</template>

<script setup lang="ts">
defineProps<{ open: boolean; message: string }>()
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .2s }
.fade-enter-from, .fade-leave-to { opacity: 0 }
</style>
