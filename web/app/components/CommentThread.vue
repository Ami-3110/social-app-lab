<template>
  <div :id="`comment-${comment.id}`" class="scroll-mt-24">
    <div :class="depth === 0 ? 'py-4 border-b ui-border' : 'mt-3 pl-3'">
      <CommentCard
        :comment="comment"
        :me-id="meId"
        @like="$emit('like', $event)"
        @comment="$emit('comment', comment)"
        @repost="$emit('repost', $event)"
        @bookmark="$emit('bookmark', $event)"
        @updated="$emit('updated', $event)"
        @deleted="$emit('deleted', $event)"
      />

      <div v-if="activeReplyCommentId === comment.id" class="mt-2">
        <textarea
          :value="replyBodies[comment.id] ?? ''"
          rows="2"
          class="w-full rounded ui-border-all ui-bg px-3 py-2 text-sm"
          :placeholder="`Reply to ${comment.user?.name ?? 'user'}...`"
          @input="onInput"
        />
        <input
          type="file"
          multiple
          accept="image/*"
          class="text-xs"
          @change="$emit('reply-file-change', comment.id, $event)"
        />
        <div class="mt-2 flex justify-end gap-2">
          <button
            type="button"
            class="px-3 py-1 text-sm ui-border-all ui-text rounded"
            @click="$emit('close-reply')"
          >
            Cancel
          </button>
          <button
            type="button"
            class="px-3 py-1 text-sm ui-border-all ui-text rounded"
            @click="$emit('submit-reply', comment)"
          >
            Reply
          </button>
        </div>
      </div>

      <div
  v-if="comment.children.length"
  :class="
    depth === 0
      ? 'mt-3 ml-4 border-l ui-border pl-5 space-y-4'
      : 'mt-3 ml-2 border-l ui-border pl-3 space-y-4'
  "
>
        <CommentThread
          v-for="child in comment.children"
          :key="child.id"
          :comment="child"
          :depth="depth + 1"
          :me-id="meId"
          :active-reply-comment-id="activeReplyCommentId"
          :reply-bodies="replyBodies"
          @like="$emit('like', $event)"
          @comment="$emit('comment', $event)"
          @repost="$emit('repost', $event)"
          @bookmark="$emit('bookmark', $event)"
          @updated="$emit('updated', $event)"
          @deleted="$emit('deleted', $event)"
          @reply-file-change="(commentId, event) => $emit('reply-file-change', commentId, event)"
          @close-reply="$emit('close-reply')"
          @submit-reply="$emit('submit-reply', $event)"
          @update-reply-body="(commentId, value) => $emit('update-reply-body', commentId, value)"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Comment } from '~/types/Comment'

type CommentNode = Comment & {
  children: CommentNode[]
}

const props = defineProps<{
  comment: CommentNode
  depth: number
  meId: number | null
  activeReplyCommentId: number | null
  replyBodies: Record<number, string>
}>()

const emit = defineEmits<{
  (e: 'like', comment: Comment): void
  (e: 'comment', comment: Comment): void
  (e: 'repost', comment: Comment): void
  (e: 'bookmark', comment: Comment): void
  (e: 'updated', comment: Comment): void
  (e: 'deleted', commentId: number): void
  (e: 'reply-file-change', commentId: number, event: Event): void
  (e: 'close-reply'): void
  (e: 'submit-reply', comment: Comment): void
  (e: 'update-reply-body', commentId: number, value: string): void
}>()

const onInput = (event: Event) => {
  const target = event.target as HTMLTextAreaElement
  emit('update-reply-body', props.comment.id, target.value)
}
</script>