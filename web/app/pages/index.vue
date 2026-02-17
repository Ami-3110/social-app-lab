<!-- pages/index.vue -->
<template>
  <main class="p-6 space-y-4">
    <div class="max-w-2xl mx-auto space-y-4">
      <!-- ヘッダー（TL専用） -->
      <header class="space-y-3">
        <h1 class="text-2xl font-bold ui-text">Time Line</h1>
        <TlTabsHeader v-model:activeTab="activeTab" />
      </header>

      <!--トピックガード（作ったら削除）-->
      <div
        v-if="activeTab === 'Topic' && !topic"
        class="py-10 text-center ui-muted"
      >
        Add <span class="font-semibold">?topic=laravel</span> to the URL to filter by topic.
      </div>

      <!-- ローディング -->
      <div v-if="pending" class="space-y-4">
        <PostSkeleton />
        <PostSkeleton />
        <PostSkeleton />
      </div>

      <div v-else>
        <!-- 投稿ゼロ -->
        <div
          v-if="!data?.data || data.data.length === 0"
          class="py-12 text-center text-gray-500 dark:text-gray-300 space-y-2"
        >
          <p class="text-lg font-medium">No posts yet.</p>
          <p class="text-sm">
            Create your first post from <NuxtLink to="/posts/new" class="text-blue-600 underline">New</NuxtLink>
            .😊
          </p>
        </div>

        <!-- 投稿がある -->
        <div v-else>
          <ul class="space-y-4">
            <li v-for="post in data.data" :key="post.id">
              <PostCard
                :post="post"
                :show-menu="true"
                @toggle-like="onToggleLike"
                @toggle-comment="onToggleComment"
                @bookmark-changed="onBookmarkChanged"
                @delete="onDelete"
                @open-repost="openRepostModal"
              >
                <template #below-actions>
                  <!-- posted comment -->
                  <CommentCard
                    v-if="justPosted?.postId === post.id"
                    :comment="justPosted.comment"
                    :me-id="myUserId"
                    variant="compact"
                    @like="onClickCommentLike"
                    @comment="onClickCommentReply"
                    @repost="onClickCommentRepost"
                    @bookmark="onClickCommentBookmark"
                    @updated="onCommentUpdated"
                    @deleted="onCommentDeleted"
                  />
                  <!-- comment form -->
                  <div v-if="activeCommentPostId === post.id" class="mt-3">
                    <textarea
                      v-model="commentBodies[post.id]"
                      rows="2"
                      class="w-full rounded ui-border ui-text ui-bg placeholder:ui-muted px-3 py-2 text-sm"
                      placeholder="Write a comment..."
                    />
                    <div class="flex justify-end mt-2">
                      <button
                        class="px-3 py-1 text-sm ui-border ui-text rounded"
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

          <!-- ページネーション -->
          <nav class="flex items-center justify-center gap-8 mt-10" v-if="data">
            <button
              class="flex items-center gap-2 px-4 py-2 rounded-md ui-border ui-text ui-card ui-card-hover text-sm disabled:opacity-40 disabled:cursor-not-allowed transition"
              :disabled="!data.prev_page_url"
              @click="go(data.current_page - 1)"
            >
              <span class="text-lg">←</span>
              Prev
            </button>

            <div class="flex items-center gap-2 text-sm ui-muted">
              <span>Page</span>
              <span class="min-w-[2.2rem] text-center px-2 py-1 font-semibold rounded-md ui-border ui-card ui-text">
                {{ data.current_page }}
              </span>
              <span>/</span>
              <span class="ui-text">{{ data.last_page }}</span>
            </div>

            <button
              class="flex items-center gap-2 px-4 py-2 rounded-md ui-border ui-text ui-card ui-card-hover text-sm disabled:opacity-40 disabled:cursor-not-allowed transition"
              :disabled="!data.next_page_url"
              @click="go(data.current_page + 1)"
            >
              Next
              <span class="text-lg">→</span>
            </button>
          </nav>
        </div>
      </div>
    </div>
  </main>

  <!-- Repost / Quote Modal -->
  <div
    v-if="repostModalOpen"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
    @click.self="closeRepostModal"
  >
    <div class="w-full max-w-lg rounded-2xl ui-bg ui-text ui-border p-4 shadow-xl">
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-semibold">
          Repost
        </h3>
        <button class="ui-muted hover:ui-text" type="button" @click="closeRepostModal">✕</button>
      </div>

      <textarea
        v-model="quoteBody"
        rows="4"
        class="w-full rounded ui-border ui-bg px-3 py-2 text-sm"
        placeholder="Add a comment (optional)..."
      />

      <!-- 元投稿プレビュー（任意だけど便利） -->
      <div v-if="repostTarget" class="mt-3 rounded-xl ui-border ui-bg p-3">
        <div class="ui-muted text-xs mb-2">
          {{ repostTarget.user?.name ?? 'Unknown' }}
        </div>
        <div class="font-semibold text-sm leading-snug">
          {{ repostTarget.title }}
        </div>
        <div class="mt-1 whitespace-pre-wrap text-sm ui-muted">
          {{ repostTarget.body }}
        </div>
      </div>

      <div class="mt-4 flex items-center justify-end gap-2">
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

</template>

<script setup lang="ts">
definePageMeta({
  layout: 'app',
  middleware: 'auth',
})

import type { Comment } from '~/types/Comment'
import { ref, watch, nextTick } from 'vue'
import { usePosts } from '~/composables/usePosts'
import type { Post } from '~/types/Post'
import { useAuthState } from '~/composables/useAuth'

const { user } = useAuthState()
const myUserId = computed(() => user.value?.id ?? null)

type TimelineTab = 'All' | 'Following' | 'For you' | 'Topic'
const activeTab = ref<TimelineTab>('All')

const {
  data,
  pending,
  topic,
  go,
  refresh,
  onDelete,
  deleteError,
} = usePosts({
  tab: () => activeTab.value,
})

const { $apiFetch } = useNuxtApp()

// like
const preserveScroll = async (fn: () => Promise<void>) => {
  const y = window.scrollY
  await fn()
  await nextTick()
  window.scrollTo({ top: y })
}

const onToggleLike = async (postId: number, nextLiked: boolean) => {
  await preserveScroll(async () => {
    await $apiFetch(`/posts/${postId}/like`, {
      method: nextLiked ? 'POST' : 'DELETE',
    })
    await refresh()
  })
}

// bookmark
const onBookmarkChanged = async () => {
  await preserveScroll(async () => {
    await refresh()
  })
}

// comment
type CommentDTO = {
  id: number
  body: string
  created_at: string
  user: { id: number; name: string }
}

const commentBodies = ref<Record<number, string>>({})
const justPosted = ref<{ postId: number; comment: CommentDTO } | null>(null)

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
    const res = await $apiFetch<{ comment: CommentDTO }>(`/posts/${postId}/comments`, {
      method: 'POST',
      body: { body },
    })

    // ✅ “どの投稿に紐づくか” を確実にする
    justPosted.value = { postId, comment: res.comment }

    // 入力クリア
    commentBodies.value[postId] = ''

    // ✅ フォームは閉じる（要望どおり）
    activeCommentPostId.value = null

    await refresh()

  })
}

//コメント内アクション
const onClickCommentLike = (commentId: number) => {
  console.log('like', commentId)
}
const onClickCommentReply = (commentId: number) => {
  console.log('reply', commentId)
}

const onClickCommentRepost = (commentId: number) => {
  console.log('repost', commentId)
}

const onClickCommentBookmark = (commentId: number) => {
  console.log('bookmark', commentId)
}

const onCommentUpdated = (c: any) => {
  if (justPosted.value && justPosted.value.comment.id === c.id) {
    justPosted.value = { ...justPosted.value, comment: c }
  }
}

const onCommentDeleted = () => {
  justPosted.value = null
  refresh()
}



// Repost modal state
const repostModalOpen = ref(false)
const repostTarget = ref<Post | null>(null)
const quoteBody = ref('')

const openRepostModal = (post: Post) => {
  repostTarget.value = post
  quoteBody.value = '' // 毎回クリア（好みで保持でもOK）
  repostModalOpen.value = true
}

const closeRepostModal = () => {
  repostModalOpen.value = false
  repostTarget.value = null
  quoteBody.value = ''
}

const submitRepost = async () => {
  const target = repostTarget.value
  if (!target) return

  const q = quoteBody.value.trim()

  await preserveScroll(async () => {
    await $apiFetch(`/posts/${target.id}/repost`, {
      method: 'POST',
      body: { quote_body: q === '' ? null : q },
    })
    await refresh()
  })

  closeRepostModal()
}

import { onBeforeUnmount, onMounted } from 'vue'

const onKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && repostModalOpen.value) closeRepostModal()
}

onMounted(() => window.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown))

</script>
