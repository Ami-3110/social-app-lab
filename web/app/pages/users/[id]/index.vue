<!-- pages/users/[id]/index.vue -->
<template>
  <section class="pt-2">
    <div v-if="postsPending" class="space-y-4">
      <PostSkeleton />
      <PostSkeleton />
      <PostSkeleton />
    </div>

    <div v-else>
      <div
        v-if="!postsData?.data || postsData.data.length === 0"
        class="py-12 text-center text-gray-500 dark:text-gray-300 space-y-2"
      >
        <p class="text-lg font-medium">No posts yet.</p>
      </div>

      <div v-else>
        <ul class="space-y-4">
          <li v-for="post in postsData.data" :key="post.id">
            <PostCard
              :post="post"
              :show-menu="true"
              @toggle-like="onToggleLike"
              @toggle-comment="onToggleComment"
              @bookmark-changed="onBookmarkChanged"
              @delete="onDelete"
              @open-repost="openPost"
            >
              <template #below-actions>
                <!-- ✅ posted comment -->
                <div
                  v-if="justPosted?.postId === post.id"
                  class="rounded ui-border-all ui-bg px-3 py-2 text-sm"
                >
                  <div class="flex items-center gap-2 mb-1">
                    <div class="h-6 w-6 rounded-full ui-border-all flex items-center justify-center text-xs font-semibold">
                      {{ (justPosted?.comment.user.name ?? 'U').slice(0,1).toUpperCase() }}
                    </div>
                    <div class="font-medium ui-text">
                      {{ justPosted?.comment.user.name }}
                    </div>
                    <div class="text-xs ui-muted">
                      {{ justPosted?.comment.created_at }}
                    </div>
                  </div>
                  <div class="whitespace-pre-wrap ui-text">
                    {{ justPosted?.comment.body }}
                  </div>
                </div>

                <!-- ✅ comment form（枠内・ActionBar直下） -->
                <div v-if="activeCommentPostId === post.id" class="mt-3">
                  <textarea
                    v-model="commentBodies[post.id]"
                    rows="2"
                    class="w-full rounded ui-border-all ui-text ui-bg placeholder:ui-muted px-3 py-2 text-sm"
                    placeholder="Write a comment..."
                  />
                  <div class="flex justify-end mt-2">
                    <button
                      class="px-3 py-1 text-sm ui-border-all ui-text rounded"
                      @click="submitComment(post.id)"
                    >
                      Post
                    </button>
                  </div>
                </div>
              </template>
            </PostCard>
          </li>                
        </ul>
        
        <p v-if="deleteError" class="text-red-600 text-sm mt-2">{{ deleteError }}</p>

        <!-- Pagination -->
        <nav class="flex items-center justify-center gap-8 mt-10" v-if="postsData">
          <button :disabled="!postsData.prev_page_url" @click="go(postsData.current_page - 1)">Prev</button>
          <span>{{ postsData.current_page }} / {{ postsData.last_page }}</span>
          <button :disabled="!postsData.next_page_url" @click="go(postsData.current_page + 1)">Next</button>
        </nav>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'app',
  middleware: 'auth',
})

import { computed, ref, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import type { Comment } from '~/types/Comment'

const { $apiFetch } = useNuxtApp()
const route = useRoute()
const userId = computed(() => Number(route.params.id))

// User posts
const {
  data: postsData,
  pending: postsPending,
  go,
  refresh: refreshPosts,
  onDelete,
  deleteError,
} = usePosts({
  tab: () => 'All',
  userId: () => userId.value,
})

const preserveScroll = async (fn: () => Promise<void>) => {
  const y = window.scrollY
  await fn()
  await nextTick()
  window.scrollTo({ top: y })
}

// Like
const onToggleLike = async (postId: number, nextLiked: boolean) => {
  await preserveScroll(async () => {
    await $apiFetch(`/posts/${postId}/like`, {
      method: nextLiked ? 'POST' : 'DELETE',
    })
    await refreshPosts()
  })
}

// comment
const commentBodies = ref<Record<number, string>>({})
const justPosted = ref<{ postId: number; comment: Comment } | null>(null)
const activeCommentPostId = ref<number | null>(null)

const onToggleComment = (postId: number) => {
  activeCommentPostId.value = activeCommentPostId.value === postId ? null : postId
  if (activeCommentPostId.value === postId && commentBodies.value[postId] == null) {
    commentBodies.value[postId] = ''
  }
}
const submitComment = async (postId: number) => {
  const body = (commentBodies.value[postId] ?? '').trim()
  if (!body) return

  await preserveScroll(async () => {
    const res = await $apiFetch<{ data: Comment }>(`/posts/${postId}/comments`, {
      method: 'POST',
      body: { body },
    })

    justPosted.value = { postId, comment: res.data }
    commentBodies.value[postId] = ''
    activeCommentPostId.value = null
    await refreshPosts()
  })
}

// Bookmark
const onBookmarkChanged = async () => {
  await preserveScroll(async () => {
    await refreshPosts()
  })
}

// Repost / Quote
const { openPost } = useRepostModal()

</script>