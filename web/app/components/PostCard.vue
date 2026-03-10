<!-- app/components/PostCard.vue -->
<template>
  <div class="px-5 py-3 rounded-xl ui-border-all ui-card ui-text ui-card-hover shadow-sm hover:shadow-md transition">
    <!-- カード全体クリックで詳細へ -->
    <div @click="navigateTo(`/posts/${post.id}`)" class="cursor-pointer">
      <!-- 上段：投稿者情報 -->
      <div class="flex items-start justify-between">
        <div class="flex items-start gap-3 flex-1">
          <NuxtLink
            :to="`/users/${post.user?.id}`"
            @click.stop
            class="h-10 w-10 rounded-full overflow-hidden flex items-center justify-center bg-gray-200 dark:bg-gray-700 text-sm font-bold"
          >
            <img
              v-if="post.user?.avatar_url"
              :src="post.user.avatar_url"
              class="h-full w-full object-cover"
              alt="avatar"
            />
            <span v-else>
              {{ post.user?.name?.slice(0,1).toUpperCase() }}
            </span>
          </NuxtLink>

          <div class="flex flex-col">
            <NuxtLink
              :to="`/users/${post.user?.id}`"
              @click.stop
              class="font-semibold ui-text hover:underline"
            >
              {{ post.user?.name ?? 'Unknown' }}
            </NuxtLink>
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
            class="absolute right-0 mt-1 w-36 ui-card ui-border-all rounded-lg shadow-lg text-sm overflow-hidden z-10"
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
        <div v-if="!post.repost_of_comment_id" class="mt-3">
          <span class="text-lg font-bold">{{ post.title }}</span>
        </div>
      </div>
      <!-- body -->
      <div class="mt-2 text-[15px] leading-relaxed ui-text">
        <!-- 1) Comment-repost -->
        <div v-if="post.repost_of_comment_id && post.repost_of_comment" class="mt-2">
          <div class="text-xs ui-muted mb-2">
            {{ post.user?.name ?? 'Unknown' }} reposted a comment
          </div>

          <NuxtLink
            :to="`/posts/${post.repost_of_comment.post_id}`"
            class="block rounded-xl ui-border-all ui-bg p-3 hover:bg-gray-50 dark:hover:bg-zinc-800 transition"
            @click.stop
          >
            <div class="ui-muted text-xs mb-1">
              {{ post.repost_of_comment.user?.name ?? 'Unknown' }}
            </div>

            <div class="whitespace-pre-wrap text-sm ui-text">
              {{ post.repost_of_comment.body }}
            </div>

            <div v-if="post.repost_of_comment.post?.title" class="mt-2 text-xs ui-muted">
              on post: {{ post.repost_of_comment.post.title }}
            </div>

            <div
              v-if="repostCommentPostFirstMedia?.url"
              class="mt-3 flex justify-center"
            >
              <img
                :src="repostCommentPostFirstMedia.url"
                alt="Reposted comment post media"
                class="block w-full max-w-sm max-h-[18rem] rounded-2xl object-cover"
              >
            </div>
          </NuxtLink>
        </div>

        <!-- 2) Quote / Repost (with post.original_post) -->
        <div v-else-if="post.original_post" class="mt-2 space-y-2">
          <!-- 自分の本文（Quoteのコメント） -->
          <div v-if="post.quote_body?.trim()" class="whitespace-pre-wrap text-sm">
            {{ post.quote_body }}
          </div>

          <!-- Original post -->
          <NuxtLink
            :to="`/posts/${post.original_post.id}`"
            class="block rounded-xl ui-border-all ui-bg p-3 hover:bg-gray-50 dark:hover:bg-zinc-800 transition"
            @click.stop
          >
            <div class="ui-muted text-xs mb-1">
              {{ post.original_post.user?.name ?? 'Unknown' }}
            </div>

            <div v-if="post.original_post.title" class="font-semibold text-sm leading-snug">
              {{ post.original_post.title }}
            </div>

            <div class="mt-1 whitespace-pre-wrap text-sm ui-text">
              {{ post.original_post.body }}
            </div>

            <div
              v-if="originalPostFirstMedia?.url"
              class="mt-3 flex justify-center"
            >
              <img
                :src="originalPostFirstMedia.url"
                alt="Original post media"
                class="block w-full max-w-sm max-h-[18rem] rounded-2xl object-cover"
              >
            </div>
                      </NuxtLink>
        </div>

        <!-- 3) Normal -->
        <div v-else class="mt-2 whitespace-pre-wrap text-sm">
          {{ post.body }}
        </div>
      </div>

      <!-- media -->
      <div
        v-if="firstMedia?.url"
        class="mt-3 flex justify-center"
      >
        <img
          :src="firstMedia.url"
          alt="Post media"
          class="block max-w-md max-h-[20rem] rounded-2xl object-cover"
        >
      </div>

      <!-- topic-->
      <div v-if="post.topic" class="mt-2">
        <button
          class="text-sm px-2 py-1 rounded-full ui-border-all ui-card ui-text ui-card-hover"
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
      @repost="() => openPost(post)"
      @bookmark="ToggleBookmark"
    />
    <!-- PostCard枠内に差し込み -->
    <div class="mt-3">
      <slot name="below-actions" />
    </div>
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

const firstMedia = computed(() => props.post.media?.[0] ?? null)

const originalPostFirstMedia = computed(() => {
  return props.post.original_post?.media?.[0] ?? null
})

const repostCommentPostFirstMedia = computed(() => {
  return props.post.repost_of_comment?.post?.media?.[0] ?? null
})

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
  (e: 'togglelike', postId: number, nextLiked: boolean): void
  (e: 'bookmark-changed', payload: { postId: number; isBookmarked: boolean }): void
  (e: 'open-repost', post: Post): void
  (e: 'toggle-comment', postId: number): void
}>()

// Like
const onClickLike = () => {
  const nextLiked = Number(props.post.is_liked ?? 0) !== 1
  emit('togglelike', props.post.id, nextLiked)
}

// Repost
const { openPost } = useRepostModal()

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

const isCommentRepost = computed(() => !!props.post.repost_of_comment_id)

</script>
