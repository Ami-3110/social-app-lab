<!-- app/components/PostCard.vue -->
<template>
  <div class="p-5 rounded-xl ui-border ui-card ui-text ui-card-hover shadow-sm hover:shadow-md transition">
    <!-- カード全体クリックで詳細へ -->
    <div @click="navigateTo(`/posts/${post.id}`)" class="cursor-pointer">
      <!-- 上段：投稿者情報 -->
      <div class="flex items-start justify-between">
        <div class="flex items-start gap-3 flex-1">
          <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-sm font-bold">
            {{ post.user?.name?.slice(0,1).toUpperCase() }}
          </div>

          <div class="flex flex-col">
            <span class="font-semibold ui-text">{{ post.user?.name ?? 'Unknown' }}</span>
            <span class="text-xs ui-muted">
              {{ new Date(post.created_at).toLocaleString() }}
            </span>
          </div>
        </div>
      
        <!-- 右：3点メニュー -->
        <div v-if="showMenu" class="relative">
          <button
            @click.stop="toggleMenu"
            class="ui-text text-xl px-2"
          >
            ⋯
          </button>

          <div
            v-if="isMenuOpen"
            class="absolute right-0 mt-1 w-36 ui-card ui-border rounded-lg shadow-lg text-sm overflow-hidden z-10"
          >
            <NuxtLink
              :to="`/posts/${post.id}/edit`"
              class="block px-4 py-2 ui-card-hover"
              @click.stop
            >
              ✏️ Edit
            </NuxtLink>

            <button
              class="w-full text-left px-4 py-2 ui-card ui-card-hover text-red-600"
              @click.stop="onDelete"
            >
              🗑 Delete
            </button>
          </div>
        </div>
      </div>

      <!-- title -->
      <div class="mt-3">
        <span class="text-lg font-bold">{{ post.title }}</span>
      </div>

      <!-- body -->
      <p class="mt-2 whitespace-pre-wrap text-[15px] leading-relaxed ui-text">
        <!-- Itself  -->
        <div v-if="post.quote_body?.trim()" class="mt-2 whitespace-pre-wrap text-sm">
          {{ post.quote_body }}
        </div>

        <div v-else-if="!post.original_post" class="mt-2 whitespace-pre-wrap text-sm">
          {{ post.body }}
        </div>

        <!-- Original post -->
        <NuxtLink
          v-if="post.original_post"
          :to="`/posts/${post.original_post.id}`"
          class="mt-3 block rounded-xl ui-border ui-bg p-3 hover:bg-gray-50 dark:hover:bg-zinc-800 transition"
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
        </NuxtLink>
      </p>

      <!-- topic-->
      <div v-if="post.topic" class="mt-2">
        <button
          class="text-sm px-2 py-1 rounded-full ui-border ui-card ui-text ui-card-hover"
          @click.stop="navigateTo({ path: '/', query: { topic: post.topic } })"
        >
          #{{ post.topic }}
        </button>
      </div>
    </div>
      <ActionBar
        :is-liked="post.is_liked"
        :likes-count="post.likes_count ?? 0"
        :comments-count="post.comments_count ?? 0"
        :reposts-count="post.reposts_count ?? 0"
        :is-bookmarked="isBookmarked"
        :show-repost-button="true"
        :repost-disabled="false"
        @like="onClickLike"
        @comment="emit('toggle-comment', post.id)"
        @repost="emit('open-repost', post)"
        @bookmark="ToggleBookmark"
      />
  </div>
</template>

<script setup lang="ts">
import type { Post } from '~/types/Post'
import { navigateTo } from '#app'

// Props
const props = defineProps<{
  post: Post
  showMenu?: boolean
}>()
// Computed
const showMenu = computed(() => props.showMenu === true)
// Menu 開閉
const isMenuOpen = ref(false)
const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}

const onDelete = () => {
  emit('delete', props.post.id)
}
// Emit
const emit = defineEmits<{
  (e: 'delete', postId: number): void
  (e: 'toggle-like', postId: number, nextLiked: boolean): void
  (e: 'bookmark-changed', payload: { postId: number; isBookmarked: boolean }): void
  (e: 'open-repost', post: Post): void
  (e: 'toggle-comment', postId: number): void
}>()

// Like
const onClickLike = () => {
  const nextLiked = Number(props.post.is_liked ?? 0) !== 1
  emit('toggle-like', props.post.id, nextLiked)
}

// Repost
const { toggleRepost } = usePosts()

const onClickRepost = async () => {
  await toggleRepost(props.post)
}

// Bookmark
const { toggleBookmark } = usePosts()

const isBookmarked = computed(() => (props.post.is_bookmarked ?? 0) > 0)

const ToggleBookmark = async () => {
  await toggleBookmark(props.post)
  emit('bookmark-changed', {
    postId: props.post.id,
    isBookmarked: (props.post.is_bookmarked ?? 0) > 0,
  })
}
</script>
