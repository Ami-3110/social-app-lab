<!-- pages/users/[id]/comments.vue -->
<template>
  <div class="space-y-6">
    <div v-if="!pending && comments.length === 0" class="py-12 text-center ui-muted">
      No comments yet.
    </div>
    <div v-if="pending" class="ui-muted">Loading...</div>
    <div v-else-if="error" class="text-red-600">Failed to load comments.</div>
    <div
      v-for="comment in comments"
      :key="comment.id"
      class="space-y-1"
    >
      <PostCard :post="comment.post" />

      <div class="pl-6">
        <CommentCard :comment="comment" />
      </div>
      <div class="border-t ui-border"></div>
    </div>
  </div>
  <pre class="text-xs ui-muted ui-border-all p-3 rounded overflow-auto">
    {{ data }}
  </pre>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'app',
  middleware: 'auth',
})

import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const userId = Number(route.params.id)

const { data, pending, error } = await useUserComments(userId)
const comments = computed(() => data.value?.data ?? [])
</script>
