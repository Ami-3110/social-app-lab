<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})
await navigateTo('/', { replace: true })
</script>

<template />

