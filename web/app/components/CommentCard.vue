<!-- components/CommentCard.vue -->
<template>
  <div class="border-b dark:border-zinc-700 py-4">
    <!-- header row -->
     <div v-if="comment.parent" class="mb-2 text-xs ui-text opacity-70">
  Replying to {{ comment.parent.user.name }}: {{ comment.parent.body }}
</div>

    <div class="flex items-start justify-between gap-2">
      <div class="text-sm min-w-0">
        <div class="font-semibold truncate">
          {{ comment.user?.name ?? 'Unknown' }}
        </div>
      </div>    

      <!-- ✅ 三点メニュー（自分のコメントだけ） -->
      <div v-if="canManage" class="relative">
        <button class="px-2 ui-muted hover:ui-text" type="button" @click="menuOpen = !menuOpen">
          ⋯
        </button>

        <div v-if="menuOpen" class="absolute right-0 mt-1 w-32 rounded ui-border ui-bg shadow">
          <button
            class="block w-full text-left px-3 py-2 text-sm hover:opacity-90"
            type="button"
            @click="startEdit"
          >
            ✏️ Edit
          </button>
          <button
            class="block w-full text-left px-3 py-2 text-sm hover:opacity-90"
            type="button"
            @click="doDelete"
          >
            🗑 Delete
          </button>
        </div>
      </div>
    </div>

    <!-- body / editor -->
    <div class="mt-1 text-sm">
      <div v-if="editing">
        <textarea
          v-model="draft"
          rows="2"
          class="w-full rounded ui-border ui-text ui-bg placeholder:ui-muted px-3 py-2 text-sm"
        />
        <div class="flex justify-end gap-2 mt-2">
          <button class="px-3 py-1 text-sm ui-border rounded" type="button" @click="cancelEdit">
            Cancel
          </button>
          <button
            class="px-3 py-1 text-sm ui-border rounded"
            type="button"
            :disabled="busy || !draft.trim()"
            @click="saveEdit"
          >
            Save
          </button>
        </div>
      </div>
      <div v-else class="whitespace-pre-wrap">
        {{ comment.body }}
      </div>
    </div>

    <div class="mt-2">
      <ActionBar
        :is-liked="comment.is_liked ?? false"
        :likes-count="comment.likes_count ?? 0"
        :comments-count="comment.replies_count ?? 0"
        :reposts-count="comment.reposts_count ?? 0"
        :is-bookmarked="comment.is_bookmarked ?? false"
        :repost-disabled="false"
        @like="emit('like', comment.id)"
        @comment="emit('comment', comment.id)"
        @repost="emit('repost', comment.id)"
        @bookmark="emit('bookmark', comment.id)"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Comment } from '~/types/Comment'

const props = defineProps<{
  comment: Comment
  meId?: number | null
}>()


const emit = defineEmits<{
  (e: 'like', commentId: number): void
  (e: 'comment', commentId: number): void
  (e: 'repost', commentId: number): void
  (e: 'bookmark', commentId: number): void
  (e: 'updated', comment: Comment): void
  (e: 'deleted', commentId: number): void
}>()

const { $apiFetch } = useNuxtApp()

const canManage = computed(() => {
  if (props.meId == null) return false
  return Number(props.comment.user?.id) === Number(props.meId)
})

const menuOpen = ref(false)
const editing = ref(false)
const busy = ref(false)
const draft = ref('')

const startEdit = () => {
  menuOpen.value = false
  editing.value = true
  draft.value = props.comment.body
}

const cancelEdit = () => {
  editing.value = false
  draft.value = props.comment.body
}

const saveEdit = async () => {
  const body = draft.value.trim()
  if (!body) return

  busy.value = true
  try {
    const res = await $apiFetch<{ comment: Comment }>(`/comments/${props.comment.id}`, {
      method: 'PATCH',
      body: { body },
    })
    emit('updated', res.comment)
    editing.value = false
  } finally {
    busy.value = false
  }
}

const doDelete = async () => {
  menuOpen.value = false
  busy.value = true
  try {
    await $apiFetch(`/comments/${props.comment.id}`, { method: 'DELETE' })
    emit('deleted', props.comment.id)
  } finally {
    busy.value = false
  }
}

</script>
