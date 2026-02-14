<!-- pages/profile.vue -->
<script setup lang="ts">
definePageMeta({ middleware: 'auth' })

const { user } = useAuthState()

watchEffect(() => {
  if (!user.value) return
  navigateTo(`/users/${user.value.id}`, { replace: true })
})
</script>

<template>
  <div class="p-6 ui-muted">Redirecting...</div>
</template>
