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
      <div class="space-y-2">
        <div class="text-sm font-medium">Avatar</div>
        <input type="file" accept="image/*" @change="onPickFile" />
        <div v-if="previewUrl" class="mt-2">
          <img :src="previewUrl" class="h-20 w-20 rounded-full object-cover ui-border-all" alt="preview" />
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
          class="rounded px-3 py-2 text-sm ui-border-all hover:bg-gray-50 dark:hover:bg-gray-800"
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
import { ref, onBeforeUnmount } from 'vue'

const props = defineProps<{
  initialBio: string
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'saved'): void
}>()

const { $apiFetch } = useNuxtApp()

const bio = ref(props.initialBio ?? '')
const file = ref<File | null>(null)
const previewUrl = ref<string | null>(null)
const busy = ref(false)

const onPickFile = (e: Event) => {
  const input = e.target as HTMLInputElement
  const f = input.files?.[0] ?? null
  file.value = f

  // preview
  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
  previewUrl.value = f ? URL.createObjectURL(f) : null
}

onBeforeUnmount(() => {
  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
})

const save = async () => {
  if (busy.value) return
  busy.value = true
  try {
    await $apiFetch('/me/profile', { method: 'PATCH', body: { bio: bio.value } })

    if (file.value) {
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
