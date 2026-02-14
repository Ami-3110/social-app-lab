<!-- app/components/ActionBar.vue -->
<template>
  <div class="flex items-center justify-between ui-muted text-sm border-t dark:border-zinc-700 pt-3 mt-3">
    <!-- Like -->
    <button
      type="button"
      class="inline-flex items-center gap-1 rounded px-2 py-1 text-sm hover:bg-gray-50 dark:hover:bg-gray-800"
      @click="on('like', $event)"
    >
      <span v-if="liked">❤️</span>

      <!-- Not Liked：Light=🩶 / Dark=🖤 -->
      <span v-else>
        <span class="dark:hidden">🩶</span>
        <span class="hidden dark:inline">🖤</span>
      </span>

      <span class="tabular-nums">{{ likesCount }}</span>
    </button>

    <!-- Comment -->
    <button
      v-if="showCommentButton"
      type="button"
      class="flex items-center gap-1 hover:text-blue-600 transition"
      @click="on('comment', $event)"
    >
      💬 <span>{{ commentsCount ?? 0 }}</span>
    </button>

    <!-- Repost -->
    <button
      v-if="showRepostButton"
      type="button"
      class="flex items-center gap-1 hover:text-blue-600 transition"
      :disabled="repostDisabled"
      @click="on('repost', $event)"
    >
      🔁 <span>{{ repostsCount ?? 0 }}</span>
    </button>

    <!-- Bookmark -->
    <button
      v-if="showBookmarkButton"
      type="button"
      class="flex items-center gap-1 transition ui-muted hover:ui-text"
      :class="isBookmarked ? 'ui-text font-semibold' : ''"
      @click="on('bookmark', $event)"
    >
      🔖
      <span v-if="isBookmarked" class="text-xs ui-muted">{{ bookmarkLabel }}</span>
    </button>
  </div>
</template>

<script setup lang="ts">
type Props = {
  // Like
  isLiked?: number | boolean
  likesCount?: number

  // Comment button
  commentsCount?: number
  showCommentButton?: boolean

  // Repost button
  repostsCount?: number
  showRepostButton?: boolean
  repostDisabled?: boolean

  // Bookmark
  isBookmarked?: boolean
  showBookmarkButton?: boolean
  bookmarkLabel?: string

  // クリックを親へ伝播させたくないケース（カード全体がリンクのとき）
  stopPropagation?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  isLiked: 0,
  likesCount: 0,

  commentsCount: 0,
  showCommentButton: true,

  repostsCount: 0,
  showRepostButton: true,
  repostDisabled: true,

  isBookmarked: false,
  showBookmarkButton: true,
  bookmarkLabel: 'Saved',

  stopPropagation: true,
  
})

const emit = defineEmits(['like', 'comment', 'repost', 'bookmark'] as const)

type EventName = 'like' | 'comment' | 'repost' | 'bookmark'

const on = (name: EventName, e?: MouseEvent) => {
  if (props.stopPropagation && e) e.stopPropagation()
  emit(name)
}

const liked = computed(() => Number(props.isLiked) === 1)

</script>