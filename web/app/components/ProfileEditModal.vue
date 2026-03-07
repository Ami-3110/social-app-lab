<!-- ~/components/ProfileEditModal.vue -->
<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
    @click.self="emit('close')"
  >
    <div class="w-full max-w-lg rounded-2xl ui-bg ui-text ui-border-all p-4 shadow-xl space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold">Edit profile</h3>
        <button class="ui-muted hover:ui-text" type="button" @click="emit('close')">✕</button>
      </div>

      <!-- avatar -->
      <div class="space-y-3">
        <div class="text-sm font-medium">Avatar</div>

        <div class="flex items-center gap-4">
          <div
            class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full ui-border-all ui-muted"
          >
            <img
              v-if="displayAvatarUrl"
              :src="displayAvatarUrl"
              class="h-full w-full object-cover"
              alt="avatar preview"
            />
            <span v-else class="text-2xl font-semibold">
              {{ avatarInitial }}
            </span>
          </div>

          <div class="space-y-2">
            <input type="file" accept="image/*" @change="onPickFile" />
            <p class="text-xs ui-muted">
              JPG, PNG, WEBP など
            </p>

            <button
              v-if="canRemoveAvatar"
              type="button"
              class="rounded px-3 py-2 text-sm ui-border-all hover:bg-gray-50 dark:hover:bg-gray-800"
              @click="onRemoveAvatar"
            >
              Remove avatar
            </button>
          </div>
        </div>
      </div>

      <!-- bio -->
      <div class="space-y-2">
        <div class="text-sm font-medium">Bio</div>
        <textarea
          v-model="bio"
          rows="5"
          class="w-full rounded ui-border-all ui-bg px-3 py-2 text-sm"
          placeholder="Write something about you..."
        />
      </div>

      <div class="flex justify-end gap-2">
        <button
          type="button"
          class="rounded px-3 py-2 text-sm ui-border-all hover:bg-gray-50 dark:hover:bg-gray-800"
          @click="emit('close')"
        >
          Cancel
        </button>

        <button
          type="button"
          class="rounded px-3 py-2 text-sm ui-border-all hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-50"
          :disabled="busy"
          @click="save"
        >
          Save
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from 'vue'

const props = defineProps<{
  initialBio: string
  initialAvatarUrl?: string | null
  initialName?: string | null
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'saved'): void
}>()

const { $apiFetch } = useNuxtApp()

const bio = ref(props.initialBio ?? '')
const file = ref<File | null>(null)
const previewUrl = ref<string | null>(null)
const removeAvatar = ref(false)
const busy = ref(false)

const displayAvatarUrl = computed(() => {
  if (removeAvatar.value) return null
  if (previewUrl.value) return previewUrl.value
  if (props.initialAvatarUrl) return props.initialAvatarUrl
  return null
})

const avatarInitial = computed(() => {
  return props.initialName?.trim()?.charAt(0) || '?'
})

const canRemoveAvatar = computed(() => {
  return !!props.initialAvatarUrl || !!previewUrl.value
})

const clearPreviewUrl = () => {
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value)
    previewUrl.value = null
  }
}

const onPickFile = (e: Event) => {
  const input = e.target as HTMLInputElement
  const f = input.files?.[0] ?? null

  file.value = f
  removeAvatar.value = false

  clearPreviewUrl()
  previewUrl.value = f ? URL.createObjectURL(f) : null
}

const onRemoveAvatar = () => {
  removeAvatar.value = true
  file.value = null
  clearPreviewUrl()
}

onBeforeUnmount(() => {
  clearPreviewUrl()
})

const save = async () => {
  if (busy.value) return
  busy.value = true

  try {
    await $apiFetch('/me/profile', {
      method: 'PATCH',
      body: { bio: bio.value },
    })

    if (removeAvatar.value) {
      await $apiFetch('/me/avatar', {
        method: 'DELETE',
      })
    } else if (file.value) {
      const fd = new FormData()
      fd.append('avatar', file.value)
      await $apiFetch('/me/avatar', { method: 'POST', body: fd })
    }

    emit('saved')
  } finally {
    busy.value = false
  }
}
</script>