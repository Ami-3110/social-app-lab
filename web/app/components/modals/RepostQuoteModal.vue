<!--components/RepostQuoteModal.vue-->
<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
    @click.self="close"
  >
    <div class="w-full max-w-lg rounded-2xl ui-bg ui-text ui-border-all p-4 shadow-xl">
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-semibold">Repost</h3>
        <button class="ui-muted hover:ui-text" type="button" @click="close">✕</button>
      </div>

      <textarea
        v-model="quoteBody"
        rows="4"
        class="w-full rounded ui-border-all ui-bg px-3 py-2 text-sm"
        placeholder="Add a comment (optional)..."
      />

      <div v-if="target" class="mt-3 rounded-xl ui-border-all ui-bg p-3">
        <div class="ui-muted text-xs mb-2">
          {{ target.userName ?? 'Unknown' }}
        </div>
        <!-- post のときだけ title を出す -->
        <div v-if="target.kind === 'post'" class="font-semibold text-sm leading-snug">
          {{ target.title }}
        </div>
        <div class="mt-1 whitespace-pre-wrap text-sm ui-muted">
          {{ target.body }}
        </div>
      </div>

      <div class="mt-4 flex items-center justify-end gap-2">
        <button
          type="button"
          class="rounded px-3 py-2 text-sm ui-border-all hover:bg-gray-50 dark:hover:bg-gray-800"
          @click="close"
        >
          Cancel
        </button>

        <button
          type="button"
          class="rounded px-3 py-2 text-sm ui-border-all hover:bg-gray-50 dark:hover:bg-gray-800"
          :disabled="busy || !target"
          @click="submit"
        >
          {{ quoteBody.trim() ? 'Quote' : 'Repost' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const { $apiFetch } = useNuxtApp()
const { isOpen, target, quoteBody, close } = useRepostModal()

const busy = ref(false)

const submit = async () => {
  const t = target.value
  if (!t || busy.value) return
  busy.value = true
  try {
    const q = quoteBody.value.trim()
    const payload = { quote_body: q === '' ? null : q }

    if (t.kind === 'post') {
      await $apiFetch(`/posts/${t.id}/repost`, { method: 'POST', body: payload })
    } else {
      await $apiFetch(`/comments/${t.id}/repost`, { method: 'POST', body: payload })
    }

    refreshNuxtData()
    close()
  } finally {
    busy.value = false
  }
}

const onKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && isOpen.value) close()
}

onMounted(() => window.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown))
</script>