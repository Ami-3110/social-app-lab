<!-- components/CommentCard.vue -->

<template>
  <div class="border-b dark:border-zinc-700 py-4">
    <div class="text-sm">
      <div class="font-semibold">{{ comment.user?.name ?? 'Unknown' }}</div>
      <div class="mt-1 whitespace-pre-wrap">{{ comment.body }}</div>
    </div>

    <ActionBar
      :is-liked="comment.is_liked"
      :likes-count="comment.likes_count"
      :comments-count="0"
      :reposts-count="0"
      :is-bookmarked="false"
      :repost-disabled="true"
      @like="emit('like', comment.id)"
      @comment="emit('comment', comment.id)"
      @repost="emit('repost', comment.id)"
      @bookmark="emit('bookmark', comment.id)"
    />
  </div>
</template>

<script setup lang="ts">

type Comment = {
  id: number
  body: string
  created_at?: string
  likes_count: number
  is_liked: number | boolean
  user?: { id: number; name: string }
}

const props = defineProps<{
  comment: Comment
  showMenu?: boolean
}>()

const emit = defineEmits<{
  (e: 'like', commentId: number): void
  (e: 'comment', commentId: number): void
  (e: 'repost', commentId: number): void
  (e: 'bookmark', commentId: number): void
}>()
</script>
