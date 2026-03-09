<!-- pages/index.vue -->
<template>
  <main class="p-6 space-y-4">
    <div class="max-w-2xl mx-auto space-y-4">
      <!-- ヘッダー（TL専用） -->
      <header class="space-y-3">
        <h1 class="text-2xl font-bold ui-text">Time Line</h1>
        <TlTabsHeader v-model:activeTab="activeTab" />
      </header>

      <!--Topic guard（don't forget adding "| 'Topic'" to script setup）-->
      <!--<div
        v-if="activeTab === 'Topic' && !topic"
        class="py-10 text-center ui-muted"
      >
        Add <span class="font-semibold">?topic=laravel</span> to the URL to filter by topic.
      </div>-->

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
                @togglelike="onToggleLike"
                @toggle-comment="onToggleComment"
                @bookmark-changed="onBookmarkChanged"
                @delete="onDelete"
                @open-repost="openPost"
              >
                <template #below-actions>
                  <!-- posted comment -->
                  <div
                    v-if="jp && jp.postId === post.id"
                    class="mt-3"
                    @click.stop
                  >
                    <CommentCard
                      :comment="jp.comment"
                      :me-id="myUserId"
                      variant="compact"
                      @like="onClickCommentLike"
                      @bookmark="onClickCommentBookmark"
                      @comment="() => goToPostDetail(post.id)"
                      @repost="openComment"
                    />
                  </div>
                  <!-- comment form -->
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

          <!-- ページネーション -->
          <nav class="flex items-center justify-center gap-8 mt-10" v-if="data">
            <button
              class="flex items-center gap-2 px-4 py-2 rounded-md ui-border-all ui-text ui-card ui-card-hover text-sm disabled:opacity-40 disabled:cursor-not-allowed transition"
              :disabled="!data.prev_page_url"
              @click="go(data.current_page - 1)"
            >
              <span class="text-lg">←</span>
              Prev
            </button>

            <div class="flex items-center gap-2 text-sm ui-muted">
              <span>Page</span>
              <span class="min-w-[2.2rem] text-center px-2 py-1 font-semibold rounded-md ui-border-all ui-card ui-text">
                {{ data.current_page }}
              </span>
              <span>/</span>
              <span class="ui-text">{{ data.last_page }}</span>
            </div>

            <button
              class="flex items-center gap-2 px-4 py-2 rounded-md ui-border-all ui-text ui-card ui-card-hover text-sm disabled:opacity-40 disabled:cursor-not-allowed transition"
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
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'app',
  middleware: 'auth',
})

import type { Comment } from '~/types/Comment'
import { ref, watch, nextTick, computed } from 'vue'
import { usePosts } from '~/composables/usePosts'
import type { Post } from '~/types/Post'
import { useAuthState } from '~/composables/useAuth'

const { user } = useAuthState()
const myUserId = computed(() => user.value?.id ?? null)
const jp = computed(() => justPostedComment.value) // alias

type TimelineTab = 'All' | 'Following' | 'For you' 
const activeTab = ref<TimelineTab>('All')

const posts = usePosts({
  tab: () => activeTab.value,
})
const { data, pending, topic, go, refresh, onDelete, deleteError } = posts

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
const onBookmarkChanged = async (_payload: { postId: number; isBookmarked: boolean }) => {
  await preserveScroll(async () => {
    await refresh()
  })
}

// comment
const commentBodies = ref<Record<number, string>>({})
const activeCommentPostId = ref<number | null>(null)

  //comment form open/close
const onToggleComment = (postId: number) => {
  activeCommentPostId.value = activeCommentPostId.value === postId ? null : postId
  if (activeCommentPostId.value === postId && commentBodies.value[postId] == null) {
    commentBodies.value[postId] = ''
  }
}

  // Optimistic UI for newly posted comment
const justPostedComment = ref<{ postId: number; comment: Comment } | null>(null)

const submitComment = async (postId: number) => {
  const body = (commentBodies.value[postId] ?? '').trim()
  if (!body) return

  await preserveScroll(async () => {
    const comment = await posts.submitComment(postId, body)
    justPostedComment.value = { postId, comment }
    commentBodies.value[postId] = ''
    activeCommentPostId.value = null
  })
}

// Repost / Quote
const { openPost, openComment } = useRepostModal()


// Parent comment handler
const goToPostDetail = (postId: number) => navigateTo(`/posts/${postId}`)

// comment like

const onClickCommentLike = async (comment: Comment) => {
  const curr = Number(comment.is_liked ?? 0) === 1
  const nextLiked = !curr

  // （オプション）まず見た目を即反映：jp.comment が対象なら直接更新
  if (jp.value?.comment?.id === comment.id) {
    jp.value.comment.is_liked = nextLiked
    jp.value.comment.likes_count = Number(jp.value.comment.likes_count ?? 0) + (nextLiked ? 1 : -1)
  }

  await preserveScroll(async () => {
    await $apiFetch(`/comments/${comment.id}/like`, {
      method: nextLiked ? 'POST' : 'DELETE',
    })
    await refresh()
  })
}

// comment bookmark
const onClickCommentBookmark = async (comment: Comment) => {
  const curr = Number(comment.is_bookmarked ?? 0) === 1
  const nextBookmarked = !curr

  if (jp.value?.comment?.id === comment.id) {
    jp.value.comment.is_bookmarked = nextBookmarked
  }

  await preserveScroll(async () => {
    await $apiFetch(`/comments/${comment.id}/bookmark`, {
      method: nextBookmarked ? 'POST' : 'DELETE',
    })
    await refresh()
  })
}
</script>
