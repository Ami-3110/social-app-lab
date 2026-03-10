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

        <!-- Post -->
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

        <!-- body -->
        <div
          class="p-2 rounded-xl ui-bg ui-border-all leading-relaxed whitespace-pre-wrap"
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
          <!-- media -->
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
            @repost="openPost(post)"
            @bookmark="onClickBookmark"
          />
        </div>
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
          <button
            type="submit"
            class="rounded px-3 py-2 text-sm ui-border-all hover:bg-gray-50 dark:hover:bg-gray-800"
          >
            Send
          </button>
        </form>

        <!-- Comment card -->
        <div v-for="group in threaded" :key="group.parent.id" class="relative py-4 border-b ui-border">

          <!-- Parent comment -->
          <CommentCard
            :comment="group.parent"
            :me-id="myUserId ?? null"
            @like="onClickCommentLike"
            @comment="onClickCommentReply"
            @repost="openComment"
            @bookmark="onClickCommentBookmark"
            @updated="onCommentUpdated"
            @deleted="onDeleteComment"
          />
          <!-- Parent reply form -->
          <div v-if="activeReplyCommentId === group.parent.id" class="mt-2">
            <textarea
              v-model="replyBodies[group.parent.id]"
              rows="2"
              class="w-full rounded ui-border-all ui-bg px-3 py-2 text-sm"
              :placeholder="`Reply to ${group.parent.user?.name ?? 'user'}...`"
            />
            <div class="flex justify-end gap-2 mt-2">
              <button type="button" class="px-3 py-1 text-sm ui-border-all ui-text rounded" @click="closeReply">
                Cancel
              </button>
              <button type="button" class="px-3 py-1 text-sm ui-border-all ui-text rounded" @click="submitReply(group.parent)">
                Reply
              </button>
            </div>
          </div>

          <!-- Children area -->
          <div
            v-if="group.children.length"
            class="mt-3 pl-10">
            <div v-for="child in group.children" :key="child.id" class="mt-4">
              <div class="ml-4 border-t ui-border">   
                  <CommentCard
                    :comment="child"
                    :me-id="myUserId ?? null"
                    @like="onClickCommentLike"
                    @comment="() => goToThread(child)"
                    @repost="openComment"
                    @bookmark="onClickCommentBookmark"
                    @updated="onCommentUpdated"
                    @deleted="onDeleteComment"
                  />
              </div> 
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
import { useComments } from '~/composables/useComments'

const route = useRoute()
// NuxtApp
const { $apiFetch } = useNuxtApp() // ← これが必要（ここでlike/bookmark叩くため）

// Post data and actions
const { data, pending, error, deleting, deleteError, deletePost, refresh } = usePost()

// ✅ showが { data: post } の形でも、旧形式（post直）でも両対応
const post = computed<any>(() => (data.value as any)?.data ?? data.value)
  
const mediaList = computed(() => post.value?.media ?? [])
const mediaCount = computed(() => mediaList.value.length)
const firstMedia = computed(() => mediaList.value[0] ?? null)

// ✅ コメント取得は route.params.id を使う（postが取れる前でも動く）
const postId = computed(() => Number(route.params.id))
// Auth
const {user} = useAuthState()
const myUserId = computed(() => user.value?.id)

const isMyComment = (c: Comment) => {
  if (!myUserId.value) return false
  return c.user.id === myUserId.value
}

// --------- << Action to Post >> ---------
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

const threaded = computed(() => {
  const list = comments.value

  const parents = list.filter(c => !c.parent_id)

  return parents.map(parent => ({
    parent,
    children: list.filter(c => c.parent_id === parent.id)
  }))
})

const goToThread = (c: Comment) => {
  navigateTo(`/comments/${c.root_id}`) // 例。あとで作るページ用
}

// --------- << Comment FORM >> ---------
const body = ref('')


const commentInputRef = ref<HTMLTextAreaElement | null>(null)

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


// --------- << Action to Comment >> ---------
// Comment Update （未実装のやつ）
const onCommentUpdated = async () => {
  await refreshComments()
}

// Comment Delete
const onDeleteComment = async (commentId: number) => {
    await refreshComments()
    await refresh()
}

// Comment action
const commentActions = useComments({ refresh: refreshComments })

// Comment like
const onClickCommentLike = async (c: Comment) => {
  await commentActions.toggleLike(c)
}

// Comment reply
const activeReplyCommentId = ref<number | null>(null)
const replyBodies = ref<Record<number, string>>({}) // commentId -> text
const onClickCommentReply = (c: Comment) => {
  openReply(c)
}

const openReply = (c: Comment) => {
  activeReplyCommentId.value = c.id
  if (replyBodies.value[c.id] == null) replyBodies.value[c.id] = ''
}

const closeReply = () => {
  activeReplyCommentId.value = null
}

const submitReply = async (parent: Comment) => {
  if (!post.value) return
  const text = (replyBodies.value[parent.id] ?? '').trim()
  if (!text) return

  await $apiFetch(`/posts/${post.value.id}/comments`, {
    method: 'POST',
    body: { body: text, parent_id: parent.id },
  })

  replyBodies.value[parent.id] = ''
  activeReplyCommentId.value = null
  await refreshComments()
  await refresh()
}

// Comment bookmark
const onClickCommentBookmark = async (c: Comment) => {
  await commentActions.toggleBookmark(c)
}

</script>