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

      <!-- Loading -->
      <div v-if="pending">Loading...</div>

      <!-- Error -->
      <div v-else-if="error" class="text-red-600">
        Failed to load posts.
      </div>

      <!-- Post -->
      <div v-else-if="post" class="space-y-4">
        <header class="flex items-start justify-between">
          <div class="space-y-2">
            <h1 class="text-xl font-bold leading-snug">
              {{ post.title }}
            </h1>

            <p class="text-xs ui-muted flex items-center gap-2">
              <NuxtLink
                :to="`/users/${post.user?.id}`"
                @click.stop
                class="inline-flex h-7 w-7 items-center justify-center rounded-full overflow-hidden ui-border-all ui-text text-[11px] font-bold"
              >
                <img
                  v-if="post.user?.avatar_url"
                  :src="post.user.avatar_url"
                  class="h-full w-full object-cover"
                  alt="avatar"
                />
                <span v-else>
                  {{ (post.user?.name ?? 'U').slice(0,1).toUpperCase() }}
                </span>
              </NuxtLink>

              <NuxtLink
                :to="`/users/${post.user?.id}`"
                @click.stop
                class="hover:underline"
              >
                {{ post.user?.name }}
              </NuxtLink>

              <span>{{ new Date(post.created_at).toLocaleString() }}</span>
            </p>
          </div>

          <!-- Menu -->
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
              class="absolute right-0 mt-1 w-36 rounded-lg ui-menu ui-border-all shadow-lg text-sm overflow-hidden"
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

        <!-- Delete error -->
        <p v-if="deleteError" class="text-red-600 text-right text-sm">
          {{ deleteError }}
        </p>

        <!-- Body -->
        <div
          class="p-2 rounded-xl ui-bg ui-border-all leading-relaxed whitespace-pre-wrap"
        >
            <!-- Own body  -->
            <div v-if="post.quote_body?.trim()" class="mt-2 whitespace-pre-wrap text-sm">
              {{ post.quote_body }}
            </div>

            <div v-else-if="!post.original_post" class="mt-2 whitespace-pre-wrap text-sm">
              {{ post.body }}
            </div>

            <!-- Original post -->
            <div
              v-if="post.original_post"
              class="mt-3 rounded-xl ui-border-all ui-bg p-3"
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
              <div
                v-if="post.original_post.media?.[0]?.url"
                class="mt-3 overflow-hidden rounded-xl ui-border-all"
              >
                <img
                  :src="post.original_post.media[0].url"
                  alt="Original post media"
                  class="max-h-[28rem] w-full object-cover"
                >
              </div>
            </div>
          <!-- Media -->
          <div v-if="mediaCount === 1" class="mt-4 flex justify-center">
            <img
              :src="firstMedia?.url"
              alt="Post media"
              class="block w-full max-w-2xl max-h-[28rem] rounded-2xl object-cover"
            >
          </div>

          <div
            v-else-if="mediaCount > 1"
            class="mt-4 grid grid-cols-2 gap-3"
          >
            <img
              v-for="media in mediaList"
              :key="media.id"
              :src="media.url"
              alt="Post media"
              class="block aspect-square w-full rounded-xl object-cover"
            >
          </div>

          <!-- Action bar -->
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
            @repost="openPost(post)"
            @bookmark="onClickBookmark"
          />
        </div>
      </div>
      <!-- Comment form -->
      <form class="mt-4 flex gap-2" @submit.prevent="onSubmit">
        <input
          ref="commentInputRef"
          v-model="body"
          type="text"
          class="flex-1 rounded ui-border-all ui-bg px-3 py-2 text-sm"
          placeholder="Write a comment..."
        />
        <input
          ref="fileInputRef"
          type="file"
          multiple
          accept="image/*"
          @change="handleFileChange"
          class="text-xs"
        />
        <button
          type="submit"
          class="rounded px-3 py-2 text-sm ui-border-all hover:bg-gray-50 dark:hover:bg-gray-800"
        >
          Send
        </button>
      </form>

      <!-- Comment list -->
      <div v-if="commentTree.length" class="space-y-4">
        <CommentThread
          v-for="comment in commentTree"
          :key="comment.id"
          :comment="comment"
          :depth="0"
          :me-id="myUserId ?? null"
          :active-reply-comment-id="activeReplyCommentId"
          :reply-bodies="replyBodies"
          @like="onClickCommentLike"
          @comment="onClickCommentReply"
          @repost="openComment"
          @bookmark="onClickCommentBookmark"
          @updated="onCommentUpdated"
          @deleted="onDeleteComment"
          @reply-file-change="handleReplyFileChange"
          @close-reply="closeReply"
          @submit-reply="submitReply"
          @update-reply-body="updateReplyBody"
        />
      </div>
    </div>
  </main>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'app',
  middleware: 'auth',
})

import { computed, ref, onMounted, nextTick, watch } from 'vue'
import { useRoute } from 'vue-router'
import { usePost } from '~/composables/usePost'
import { useAuthState } from '~/composables/useAuth'
import { useComments } from '~/composables/useComments'
import type { Comment } from '~/types/Comment'

// Base / App
const route = useRoute()
const { $apiFetch } = useNuxtApp()

// Post data
const { data, pending, error, deleteError, deletePost, refresh } = usePost()
const post = computed<any>(() => (data.value as any)?.data ?? data.value) 
const mediaList = computed(() => post.value?.media ?? [])
const mediaCount = computed(() => mediaList.value.length)
const firstMedia = computed(() => mediaList.value[0] ?? null)
const postId = computed(() => Number(route.params.id))

// Auth
const {user} = useAuthState()
const myUserId = computed(() => user.value?.id)

// --------- << Comments INDEX >> ---------
// Comment part paginated response type
type Paginated<T> = {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

// Comments data
const { data: commentsRes, refresh: refreshComments } = useAsyncData<Paginated<Comment> | null>(
  () => (postId.value ? `post:${postId.value}:comments` : 'post::comments'),
  async () => {
    if (!postId.value) return null
    return await $apiFetch<Paginated<Comment>>(`/posts/${postId.value}/comments`)
  },
  { default: () => null, watch: [postId], server: false }
)

const comments = computed(() => commentsRes.value?.data ?? [])

type CommentNode = Comment & {
  children: CommentNode[]
}

const buildCommentTree = (comments: Comment[]): CommentNode[] => {
  const nodeMap = new Map<number, CommentNode>()
  const roots: CommentNode[] = []

  for (const comment of comments) {
    nodeMap.set(comment.id, {
      ...comment,
      children: [],
    })
  }

  for (const comment of comments) {
    const node = nodeMap.get(comment.id)
    if (!node) continue

    if (comment.parent_id != null && nodeMap.has(comment.parent_id)) {
      nodeMap.get(comment.parent_id)!.children.push(node)
    } else {
      roots.push(node)
    }
  }

  const sortNodes = (nodes: CommentNode[], isRoot = false) => {
    nodes.sort((a, b) => {
      const aTime = new Date(a.created_at).getTime()
      const bTime = new Date(b.created_at).getTime()

      return isRoot ? bTime - aTime : aTime - bTime
    })

    for (const node of nodes) {
      if (node.children.length) {
        sortNodes(node.children, false)
      }
    }
  }

  sortNodes(roots, true)

  return roots
}

const commentTree = computed<CommentNode[]>(() => {
  return buildCommentTree(comments.value)
})

const scrollToHashComment = async () => {
  if (!route.hash) return

  await nextTick()

  const el = document.querySelector(route.hash)
  if (el) {
    el.scrollIntoView({ behavior: 'auto', block: 'start' })
  }
}

watch(
  () => commentTree.value,
  async (nodes) => {
    if (!nodes.length) return
    await scrollToHashComment()
  },
  { immediate: true, deep: true }
)

// --------- << Post actions >> ---------
// Menu
const menuOpen = ref(false)

const toggleMenu = () => {
  menuOpen.value = !menuOpen.value
}
// Like count
const isLiked = computed(() => Number(post.value?.is_liked ?? 0) === 1)

// Bookmark count
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

// Repost / Quote
const { openPost, openComment } = useRepostModal()

// Bookmark
const onClickBookmark = async () => {
  if (!post.value) return

  const next = !isBookmarked.value
  await $apiFetch(`/posts/${post.value.id}/bookmark`, {
    method: next ? 'POST' : 'DELETE',
  })

  await refresh()
}

// --------- << Comment form (new comment) >> ---------
const body = ref('')
const commentInputRef = ref<HTMLInputElement | null>(null)
const fileInputRef = ref<HTMLInputElement | null>(null) 
const selectedFiles = ref<File[]>([])

const handleFileChange = (e: Event) => {
  const input = e.target as HTMLInputElement
  if (!input.files) return

  selectedFiles.value = Array.from(input.files)
}

const onSubmit = async () => {
  if (!post.value) return

  const text = body.value.trim()
  const formData = new FormData()

  if (text) {
    formData.append('body', text)
  }

  selectedFiles.value.forEach(file => {
    formData.append('media[]', file)
  })

  await $apiFetch(`/posts/${post.value.id}/comments`, {
    method: 'POST',
    body: formData,
  })

  body.value = ''
  selectedFiles.value = []

  if (fileInputRef.value) {
  fileInputRef.value.value = ''
  }

  await refreshComments()
  await refresh()
}

const focusCommentInput = async () => {
  await nextTick()
  commentInputRef.value?.focus()
}

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

// --------- << Comment actions >> ---------
// Refresh
const commentActions = useComments({ refresh: refreshComments })

// Comment Update
const onCommentUpdated = async () => {
  await refreshComments()
}

// Comment Delete
const onDeleteComment = async () => {
    await refreshComments()
    await refresh()
}

// Comment like
const onClickCommentLike = async (c: Comment) => {
  await commentActions.toggleLike(c)
}
// Comment bookmark
const onClickCommentBookmark = async (c: Comment) => {
  await commentActions.toggleBookmark(c)
}

// --------- << Comment reply >> ---------
// Comment reply
const activeReplyCommentId = ref<number | null>(null)
const replyBodies = ref<Record<number, string>>({})
const onClickCommentReply = (c: Comment) => openReply(c)

const replyFiles = ref<Record<number, File[]>>({})
const handleReplyFileChange = (commentId: number, e: Event) => {
  const input = e.target as HTMLInputElement
  if (!input.files) return

  replyFiles.value[commentId] = Array.from(input.files)
}

const openReply = (c: Comment) => {
  activeReplyCommentId.value = c.id
  if (replyBodies.value[c.id] == null) replyBodies.value[c.id] = ''
}

const updateReplyBody = (commentId: number, value: string) => {
  replyBodies.value[commentId] = value
}

const closeReply = () => {
  activeReplyCommentId.value = null
}

const submitReply = async (parent: Comment) => {
  if (!post.value) return

  const text = (replyBodies.value[parent.id] ?? '').trim()
  const files = replyFiles.value[parent.id] ?? []

  const formData = new FormData()

  if (text) {
    formData.append('body', text)
  }

  formData.append('parent_id', String(parent.id))

  files.forEach(file => {
    formData.append('media[]', file)
  })

  await $apiFetch(`/posts/${post.value.id}/comments`, {
    method: 'POST',
    body: formData,
  })

  replyBodies.value[parent.id] = ''
  replyFiles.value[parent.id] = []
  activeReplyCommentId.value = null

  await refreshComments()
  await refresh()
}

</script>