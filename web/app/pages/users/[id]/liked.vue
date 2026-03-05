<!-- pages/users/[id]/liked.vue -->
<template>
  <div class="space-y-6">
    <div v-if="pending" class="ui-muted">Loading...</div>
    <div v-else-if="error" class="text-red-600">Failed to load liked posts.</div>

    <div v-else-if="posts.length === 0" class="py-12 text-center ui-muted">
      No liked posts yet.
    </div>

    <ul v-else class="space-y-4">
      <li v-for="post in posts" :key="post.id">
        <PostCard :post="post" :show-menu="true" />
      </li>
    </ul>
  </div>
</template>

 <script setup lang="ts">
definePageMeta({ layout: 'app', middleware: 'auth' })

import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const userId = Number(route.params.id)

const { data, pending, error } = await useUserLikedPosts(userId)

const posts = computed(() => data.value?.data ?? [])
</script>
