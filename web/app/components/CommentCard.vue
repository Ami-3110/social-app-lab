<!-- components/CommentCard.vue -->
<template>
  <div class="py-3">
    <!-- header row -->
    <div class="flex items-start justify-between gap-2">
      <p class="text-xs ui-muted flex items-center gap-2">
        <NuxtLink
          :to="`/users/${comment.user?.id}`"
          @click.stop
          class="inline-flex h-7 w-7 items-center justify-center rounded-full overflow-hidden ui-border-all ui-text text-[11px] font-bold"
        >
          <img
            v-if="comment.user?.avatar_url"
            :src="comment.user.avatar_url"
            class="h-full w-full object-cover"
            alt="avatar"
          />
          <span v-else>
            {{ (comment.user?.name ?? 'U').slice(0,1).toUpperCase() }}
          </span>
        </NuxtLink>

        <NuxtLink
          :to="`/users/${comment.user?.id}`"
          @click.stop
          class="hover:underline"
        >
          {{ comment.user?.name }}
        </NuxtLink>
        <span>{{ new Date(comment.created_at).toLocaleString() }}</span>
      </p>

      <!-- ✅ 三点メニュー（自分のコメントだけ） -->
      <div v-if="canManage" class="relative">
        <button class="px-2 ui-muted hover:ui-text" type="button" @click="menuOpen = !menuOpen">
          ⋯
        </button>

        <div v-if="menuOpen" class="absolute right-0 mt-1 w-32 rounded ui-border-all ui-bg shadow">
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
          class="w-full rounded ui-border-all ui-text ui-bg placeholder:ui-muted px-3 py-2 text-sm"
        />
        <div class="flex justify-end gap-2 mt-2">
          <button class="px-3 py-1 text-sm ui-border-all rounded" type="button" @click="cancelEdit">
            Cancel
          </button>
          <button
            class="px-3 py-1 text-sm ui-border-all rounded"
            type="button"
            :disabled="busy || !draft.trim()"
            @click="saveEdit"
          >
            Save
          </button>
        </div>
      </div>
      <div v-else class="px-10 whitespace-pre-wrap">
        {{ comment.body }}
      </div>
    </div>
    <div v-if="comment.media?.length" class="mt-2 space-y-2">
      <div
        v-if="comment.media?.length"
        :class="comment.media.length === 1
          ? 'mt-2'
          : 'mt-2 grid grid-cols-2 gap-2'"
      >
        <img
          v-for="media in comment.media"
          :key="media.id"
          :src="media.url"
          alt=""
          class="w-full rounded-xl ui-border-all object-cover"
          :class="comment.media.length === 1
            ? 'max-h-80'
            : 'aspect-square'"
        />
      </div>
    </div>
    <div class="mt-2 px-8">
      <ActionBar
        :is-liked="comment.is_liked ?? false"
        :likes-count="comment.likes_count ?? 0"
        :comments-count="comment.replies_count ?? 0"
        :reposts-count="comment.reposts_count ?? 0"
        :is-bookmarked="comment.is_bookmarked ?? false"
        :repost-disabled="false"
        @like="emit('like', comment)"
        @comment="emit('comment', comment)"
        @repost="emit('repost', comment)"
        @bookmark="emit('bookmark', comment)"
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
  (e: 'like', comment: Comment): void
  (e: 'comment', comment: Comment): void
  (e: 'repost', comment: Comment): void
  (e: 'bookmark', comment: Comment): void
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
  draft.value = props.comment.body ?? ''
}

const cancelEdit = () => {
  editing.value = false
  draft.value = props.comment.body ?? ''
}

const saveEdit = async () => {
  const body = draft.value.trim()
  if (!body) return

  busy.value = true
  try {
    const res = await $apiFetch<{ data: Comment }>(`/comments/${props.comment.id}`, {
      method: 'PATCH',
      body: { body },
    })
    emit('updated', res.data)
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
const { openComment } = useRepostModal()

</script>
