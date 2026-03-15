<!-- pages/users/[id]/media.vue -->
<template>
  <div class="space-y-6">
    <div v-if="pending" class="ui-muted">Loading...</div>
    <div v-else-if="error" class="text-red-600">Failed to load media posts.</div>

    <div v-else-if="posts.length === 0" class="py-12 text-center ui-muted">
      No media posts yet.
    </div>

    <ul v-else class="space-y-4">
      <li v-for="post in posts" :key="post.id">
        <PostCard :post="post" :show-menu="true" />
      </li>
    </ul>

    <nav
      v-if="data && posts.length > 0"
      class="mt-10 flex items-center justify-center gap-8"
    >
      <button
        :disabled="!data.prev_page_url"
        @click="go(data.current_page - 1)"
      >
        Prev
      </button>

      <span>{{ data.current_page }} / {{ data.last_page }}</span>

      <button
        :disabled="!data.next_page_url"
        @click="go(data.current_page + 1)"
      >
        Next
      </button>
    </nav>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'app', middleware: 'auth' })

import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const userId = Number(route.params.id)

const page = ref(Number(route.query.page ?? 1))

const { data, pending, error } = await useUserMediaPosts(userId, page)
const posts = computed(() => data.value?.data ?? [])

function go(nextPage: number) {
  if (!data.value) return
  if (nextPage < 1 || nextPage > data.value.last_page) return

  page.value = nextPage

  router.push({
    query: {
      ...route.query,
      page: String(nextPage),
    },
  })
}
</script>