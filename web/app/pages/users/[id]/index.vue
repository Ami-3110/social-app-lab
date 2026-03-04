<!-- pages/users/[id]/index.vue -->
<template>
  <main class="p-6 max-w-xl mx-auto space-y-6 ui-bg ui-text min-h-screen">
    <!-- Loading / Error -->
    <div v-if="pending" class="ui-muted">Loading...</div>
    <div v-else-if="error" class="text-red-600">Failed to load user.</div>

    <section v-else-if="profile" class="space-y-4">
      <!-- Header -->
      <header class="flex items-center gap-4">
        <div class="h-12 w-12 rounded-full ui-border-all flex items-center justify-center text-lg font-bold">
          {{ (profile.name ?? 'U').slice(0,1).toUpperCase() }}
        </div>

        <div class="flex-1 min-w-0">
          <h1 class="text-2xl font-bold truncate">{{ profile.name }}</h1>
          <p class="text-sm ui-muted truncate">{{ profile.email ?? '' }}</p>
        </div>
      </header>

      <!-- Bio（今は仮。後でAPIにbio追加） -->
      <section class="rounded-xl ui-border-all ui-bg p-4 text-sm whitespace-pre-wrap">
        <p v-if="profile.bio?.trim()">{{ profile.bio }}</p>
        <p v-else class="ui-muted">Add a bio to your profile.</p>
      </section>

      <!-- Buttons row -->
      <section class="flex gap-2">
        <!-- own -->
        <button
          v-if="isMe"
          type="button"
          class="flex-1 rounded-full px-4 py-2 text-sm ui-border-all ui-bg hover:opacity-90"
          @click="onEditProfile"
        >
          Edit profile
        </button>

        <!-- others -->
        <button
          v-else
          type="button"
          class="flex-1 rounded-full px-4 py-2 text-sm ui-border-all ui-bg hover:opacity-90"
          :disabled="busy"
          @click="toggleFollow"
        >
          {{ profile.is_following ? 'Unfollow' : 'Follow' }}
        </button>

        <button
          type="button"
          class="flex-1 rounded-full px-4 py-2 text-sm ui-border-all ui-bg hover:opacity-90"
          @click="onOpenFollowing"
        >
          {{ profile.following_count ?? 0 }} Following
        </button>

        <button
          type="button"
          class="flex-1 rounded-full px-4 py-2 text-sm ui-border-all ui-bg hover:opacity-90"
          @click="onOpenFollowers"
        >
          {{ profile.followers_count ?? 0 }} Followers
        </button>

      </section>

      <!-- Tabs（中身は後で） -->
      <nav class="grid grid-cols-4 gap-2 pt-2">
        <button
          v-for="t in tabs"
          :key="t"
          type="button"
          class="rounded-md px-3 py-2 text-sm ui-border-all"
          :class="activeTab === t
            ? 'font-semibold bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 border-zinc-900 dark:border-zinc-100'
            : 'ui-muted hover:opacity-90'"
          @click="activeTab = t"
        >
          {{ t }}
        </button>
      </nav>

      <section class="pt-2">
        <div v-if="activeTab === 'Post'">
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
                      <!-- ✅ posted comment（枠線＋アイコン＋名前） -->
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
        </div>
      </section>
    </section>
  </main>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'app',
  middleware: 'auth',
})

import { computed, ref, nextTick, onMounted, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthState } from '~/composables/useAuth'
import { useFollow } from '~/composables/useFollow'
import type { Post } from '~/types/Post'
import type { Comment } from '~/types/Comment'

type Profile = {
  id: number
  name: string
  email?: string
  bio?: string
  is_following?: boolean
  following_count?: number
  followers_count?: number
}

const { $apiFetch } = useNuxtApp()
const route = useRoute()
const { user: me } = useAuthState()
const { follow, unfollow } = useFollow()

const userId = computed(() => Number(route.params.id))

const { data: profileRes, pending, error, refresh } = useAsyncData<{ data: Profile }>(
  () => `user:${userId.value}`,
  () => $apiFetch(`/users/${userId.value}`),
  { watch: [userId] }
)

const profile = computed(() => profileRes.value?.data)
const isMe = computed(() => Number(me.value?.id) === Number(profile.value?.id))

const busy = ref(false)

// follow count
const toggleFollow = async () => {
  if (!profileRes.value?.data) return
  if (isMe.value) return
  if (busy.value) return
  busy.value = true

  const p = profileRes.value.data
  const prev = !!p.is_following

  // optimistic update
  p.is_following = !prev
  p.followers_count = Number(p.followers_count ?? 0) + (prev ? -1 : 1)

  try {
    if (prev) await unfollow(p.id)
    else await follow(p.id)

  } catch (e) {
    
    // rollback
    p.is_following = prev
    p.followers_count = Number(p.followers_count ?? 0) + (prev ? 1 : -1)
    throw e
  } finally {
    busy.value = false
  }
}

// buttons
const onEditProfile = () => navigateTo('/profile/edit') // ⭐️後で作る
const onOpenFollowing = () => navigateTo(`/users/${userId.value}/following`)
const onOpenFollowers = () => navigateTo(`/users/${userId.value}/followers`)

// tabs
const tabs = ['Post', 'Comment', 'Media', 'Liked'] as const
type ProfileTab = typeof tabs[number]
const activeTab = ref<ProfileTab>('Post')

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

    // ✅ “どの投稿に紐づくか” を確実にする
    justPosted.value = { postId, comment: res.data }

    // 入力クリア
    commentBodies.value[postId] = ''

    // ✅ フォームは閉じる（要望どおり）
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
