<script setup lang="ts">
const { $apiFetch } = useNuxtApp()

const data = ref<any>(null)
const errorMessage = ref('')

onMounted(async () => {
  try {
    data.value = await $apiFetch('/notifications')
    console.log('notifications:', data.value)
  } catch (error: any) {
    console.error(error)
    errorMessage.value = error?.data?.message ?? error?.message ?? 'error'
  }
})
</script>

<template>
  <main class="p-6">
    <h1 class="text-xl font-bold mb-4">test notifications</h1>

    <p v-if="errorMessage" class="mb-4 text-red-500">
      {{ errorMessage }}
    </p>

    <pre class="whitespace-pre-wrap break-all">{{ data }}</pre>
  </main>
</template>