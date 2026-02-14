<!-- pages/posts/[id]/index.vue -->
<template>
  <main class="p-6 space-y-4 ui-bg ui-text min-h-screen">
    <div class="max-w-2xl mx-auto mb-2">
      <NuxtLink
        to="/posts"
        class="inline-block px-3 py-1 text-sm text-blue-600 hover:underline"
      >
        ← Back to timeline
      </NuxtLink>

      <!-- ローディング -->
      <div v-if="pending">Loading...</div>

      <!-- エラー -->
      <div v-else-if="error" class="text-red-600">
        Failed to load posts.
      </div>

      <!-- 本文 -->
      <div v-else-if="post" class="space-y-4">

        <!-- タイトル & メニュー -->
        <header class="flex items-start justify-between">
          <!-- 左 -->
          <div class="space-y-2">
            <h1 class="text-xl font-bold leading-snug">
              {{ post.title }}
            </h1>

            <p class="text-xs ui-muted flex items-center gap-2">
              <span
                class="inline-flex h-7 w-7 items-center justify-center rounded-full ui-border ui-text text-[11px] font-bold"
              >
                {{ (post.user?.name ?? 'U').slice(0,1).toUpperCase() }}
              </span>
              <span>{{ post.user?.name }}</span>
              <span>{{ new Date(post.created_at).toLocaleString() }}</span>
            </p>
          </div>

          <!-- 右：3点メニュー -->
          <div class="relative">
            <button
              @click="toggleMenu"
              class="px-2 text-2xl ui-muted"
              aria-haspopup="menu"
              :aria-expanded="menuOpen"
            >
              ⋯
            </button>

            <div
              v-if="menuOpen"
              class="absolute right-0 mt-1 w-36 rounded-lg ui-menu ui-border shadow-lg text-sm overflow-hidden"
            >
              <NuxtLink
                :to="`/posts/${post.id}/edit`"
                class="block px-4 py-2 ui-menu-item ui-text"
              >
                ✏️ Edit
              </NuxtLink>

              <button
                @click="() => { menuOpen = false; deletePost() }"
                class="w-full text-left px-4 py-2 ui-menu-item text-red-600"
              >
                🗑 Delete
              </button>
            </div>
          </div>
        </header>

        <!-- Delete エラー -->
        <p v-if="deleteError" class="text-red-600 text-right text-sm">
          {{ deleteError }}
        </p>

        <!-- 本文（浮かせない） -->
        <div
          class="p-2 rounded-xl ui-bg ui-border leading-relaxed whitespace-pre-wrap"
        >
            <!-- Itself  -->
            <div v-if="post.quote_body?.trim()" class="mt-2 whitespace-pre-wrap text-m">
              {{ post.quote_body }}
            </div>

            <div v-else-if="!post.original_post" class="mt-2 whitespace-pre-wrap text-sm">
              {{ post.body }}
            </div>

            <!-- Original post -->
            <div
              v-if="post.original_post"
              class="mt-3 rounded-xl ui-border ui-bg p-3"
            >
              <div class="ui-muted text-xs mb-2">
                {{ post.original_post.user?.name ?? 'Unknown' }}
              </div>

              <div class="font-semibold text-sm leading-snug">
                {{ post.original_post.title }}
              </div>

              <div class="mt-1 whitespace-pre-wrap text-sm ui-muted">
                {{ post.original_post.body }}
              </div>
          </div>

          <!-- Action Bar -->
          <ActionBar
            :is-liked="post.is_liked"
            :likes-count="post.likes_count ?? 0"
            :comments-count="post.comments_count ?? 0"
            :reposts-count="post.reposts_count ?? 0"
            :is-bookmarked="isBookmarked"
            :show-repost-button="true"
            :repost-disabled="false"
            @like="onClickLike"
            @comment="focusCommentInput"
            @repost="openRepostModal"
            @bookmark="onClickBookmark"
          />
        </div>

          <!-- Comment form -->
          <form class="mt-4 flex gap-2" @submit.prevent="onSubmit">
            <input
              ref="commentInputRef"
              v-model="body"
              type="text"
              class="flex-1 rounded ui-border ui-bg px-3 py-2 text-sm"
              placeholder="Write a comment..."
            />
            <button
              type="submit"
              class="rounded px-3 py-2 text-sm ui-border hover:bg-gray-50 dark:hover:bg-gray-800"
            >
              Send
            </button>
          </form>

          <!-- Comment card -->
          <h2 class="mt-6 text-sm font-semibold ui-text">Comments</h2>
          <CommentCard
            v-for="c in comments"
            :key="c.id"
            :comment="c"
            @like="onClickCommentLike"
          />
        </div>
      </div>
      <!-- Repost Modal -->
      <div
        v-if="repostModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        @click.self="closeRepostModal"
      >
        <div class="w-full max-w-lg rounded-2xl ui-bg ui-text ui-border p-4 shadow-xl">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold">Repost</h3>
            <button class="ui-muted hover:ui-text" type="button" @click="closeRepostModal">✕</button>
          </div>

          <textarea
            v-model="quoteBody"
            rows="4"
            class="w-full rounded ui-border ui-bg px-3 py-2 text-sm"
            placeholder="Add a comment (optional)..."
          />
          <div v-if="post" class="mt-3 rounded-xl ui-border ui-bg p-3">
            <div class="ui-muted text-xs mb-2">
              {{ post.user?.name ?? 'Unknown' }}
            </div>
            <div class="font-semibold text-sm leading-snug">
              {{ post.title }}
            </div>
            <div class="mt-1 whitespace-pre-wrap text-sm ui-muted">
              {{ post.body }}
            </div>
          </div>

          <div class="mt-3 flex items-center justify-end gap-2">
            <button
              type="button"
              class="rounded px-3 py-2 text-sm ui-border hover:bg-gray-50 dark:hover:bg-gray-800"
              @click="closeRepostModal"
            >
              Cancel
            </button>

            <button
              type="button"
              class="rounded px-3 py-2 text-sm ui-border hover:bg-gray-50 dark:hover:bg-gray-800"
              @click="submitRepost"
            >
              {{ quoteBody.trim() ? 'Quote' : 'Repost' }}
            </button>
          </div>
        </div>
      </div>
  </main>
</template>


<script setup lang="ts">
definePageMeta({
  layout: 'app',
  middleware: 'auth',
})

import { computed, ref, onBeforeUnmount, onMounted, nextTick, watch } from 'vue'
import { usePost } from '~/composables/usePost'
import type { Comment } from '~/types/Comment'
import { useRoute } from 'vue-router'
import { useAuthState } from '~/composables/useAuth'

// NuxtApp
const { $apiFetch } = useNuxtApp() // ← これが必要（ここでlike/bookmark叩くため）

const route = useRoute()

// Post data and actions
const { data, pending, error, deleting, deleteError, deletePost, refresh } = usePost()

// ✅ showが { data: post } の形でも、旧形式（post直）でも両対応
const post = computed<any>(() => (data.value as any)?.data ?? data.value)

// ✅ コメント取得は route.params.id を使う（postが取れる前でも動く）
const postId = computed(() => Number(route.params.id))


// Menu
const menuOpen = ref(false)
const toggleMenu = () => {
  menuOpen.value = !menuOpen.value
}

// Like/Bookmark
const isLiked = computed(() => Number(post.value?.is_liked ?? 0) === 1)
const isBookmarked = computed(() => Number(post.value?.is_bookmarked ?? 0) === 1)

// Likes
const onClickLike = async () => {
  if (!post.value) return

  const nextLiked = !isLiked.value
  await $apiFetch(`/posts/${post.value.id}/like`, {
    method: nextLiked ? 'POST' : 'DELETE',
  })

  await refresh()
}

// Repost modal
const repostModalOpen = ref(false)
const quoteBody = ref('')

const openRepostModal = () => {
  quoteBody.value = '' // 毎回クリア（好みで保持でもOK）
  repostModalOpen.value = true
}

const closeRepostModal = () => {
  repostModalOpen.value = false
}

// Repost
const isReposted = computed(() => Number(post.value?.is_reposted ?? 0) === 1)

const submitRepost = async () => {
  if (!post.value) return

  const q = quoteBody.value.trim()

  await $apiFetch(`/posts/${post.value.id}/repost`, {
    method: 'POST',
    body: { quote_body: q === '' ? null : q },
  })

  repostModalOpen.value = false
  quoteBody.value = ''
  await refresh()
}

const onKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && repostModalOpen.value) closeRepostModal()
}

onMounted(() => window.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown))

// Bookmark
const onClickBookmark = async () => {
  if (!post.value) return

  const next = !isBookmarked.value
  await $apiFetch(`/posts/${post.value.id}/bookmark`, {
    method: next ? 'POST' : 'DELETE',
  })

  await refresh()
}

// Comment part paginated response type
type Paginated<T> = {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

// Comments data
const body = ref('')
const { data: commentsRes, refresh: refreshComments } = useAsyncData<Paginated<Comment> | null>(
  () => (postId.value ? `post:${postId.value}:comments` : 'post::comments'),
  async () => {
    if (!postId.value) return null
    return await $apiFetch<Paginated<Comment>>(`/posts/${postId.value}/comments`)
  },
  { default: () => null, watch: [postId], server: false }
)

const comments = computed(() => commentsRes.value?.data ?? [])

// Comment Submit
const onSubmit = async () => {
  if (!post.value) return
  const text = body.value.trim()
  if (!text) return

  await $apiFetch(`/posts/${post.value.id}/comments`, {
    method: 'POST',
    body: { body: text },
  })

  body.value = ''
  await refreshComments()
  await refresh()
}

const commentInputRef = ref<HTMLInputElement | null>(null)

// Comment Delete
const {user} = useAuthState()
const myUserId = computed(() => user.value?.id)

const isMyComment = (c: Comment) => {
  return Number(c.user_id) === Number(myUserId.value)
}

const deletingCommentId = ref<number | null>(null)

const onDeleteComment = async (commentId: number) => {
  if (!confirm('Delete this comment?')) return

  deletingCommentId.value = commentId
  try {
    await $apiFetch(`/comments/${commentId}`, { method: 'DELETE' })
    await refreshComments()
    await refresh()
  } finally {
    deletingCommentId.value = null
  }
}

// Comments list
const onClickCommentLike = async (commentId: number) => {
  const c = comments.value.find(x => Number(x.id) === Number(commentId))
  const liked = Number(c?.is_liked ?? 0) === 1

  await $apiFetch(`/comments/${commentId}/like`, {
    method: liked ? 'DELETE' : 'POST',
  })

  await refreshComments()
}

// Focus comment input if ?focus=comment
onMounted(async () => {
  if (route.query.focus === 'comment') {
    await nextTick()
    commentInputRef.value?.focus()
  }
})
watch(post, async (p) => {
  if (!p) return
  if (route.query.focus !== 'comment') return

  await nextTick()
  commentInputRef.value?.focus()
}, { immediate: true })

const focusCommentInput = async () => {
  await nextTick()
  commentInputRef.value?.focus()
}

</script>