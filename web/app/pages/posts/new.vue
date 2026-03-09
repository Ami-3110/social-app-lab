<!-- pages/posts/new.vue -->
<template>
  <main class="p-6 ui-bg ui-text min-h-screen">
    <!-- TL ヘッダー分の余白 -->
    <div class="max-w-2xl h-[3.25rem]"></div>

    <div class="max-w-2xl mx-auto space-y-6 px-5">
      <h1 class="text-2xl font-bold">✨ New Post</h1>

      <!-- Form -->
      <form
        class="space-y-4 p-6 ui-card ui-border-all rounded-xl"
        @submit.prevent="submit"
      >
        <!-- Title -->
        <div>
          <label class="block text-sm font-medium mb-1 ui-muted">
            Title
          </label>
          <input
            v-model="title"
            type="text"
            class="w-full rounded-xl px-3 py-2 ui-bg ui-text ui-border-all"
            maxlength="120"
            required
          >
        </div>

        <!-- Topic -->
        <div>
          <label class="block text-sm font-medium mb-1 ui-muted">
            Topic (optional)
          </label>
          <input
            v-model="topic"
            type="text"
            class="w-full rounded-xl px-3 py-2 ui-bg ui-text ui-border-all"
            maxlength="100"
          >
        </div>

        <!-- Body -->
        <div>
          <label class="block text-sm font-medium mb-1 ui-muted">
            Body
          </label>
          <textarea
            v-model="body"
            class="w-full rounded-xl px-3 py-2 h-40 ui-bg ui-text ui-border-all"
            required
          ></textarea>
        </div>

        <!-- Media -->
        <div class="space-y-2">
          <label class="block text-sm font-medium ui-muted">
            Media (optional)
          </label>

          <input
            type="file"
            accept="image/*"
            @change="onPickMedia"
          >

          <div v-if="previewUrl" class="pt-2">
            <img
              :src="previewUrl"
              alt="media preview"
              class="max-h-80 w-auto rounded-xl ui-border-all object-cover"
            >
          </div>

          <button
            v-if="mediaFile"
            type="button"
            class="rounded px-3 py-2 text-sm ui-border-all hover:bg-zinc-50 dark:hover:bg-zinc-700/50"
            @click="clearMedia"
          >
            Remove media
          </button>
        </div>

        <!-- Actions -->
        <div class="flex gap-2 items-center">
          <button
            :disabled="loading"
            class="px-4 py-2 rounded bg-blue-600 text-white disabled:opacity-50"
          >
            {{ loading ? 'Saving...' : 'Create' }}
          </button>

          <NuxtLink
            to="/posts"
            class="px-4 py-2 rounded ui-border-all ui-muted hover:bg-zinc-50 dark:hover:bg-zinc-700/50"
          >
            Cancel
          </NuxtLink>
        </div>

        <!-- Messages -->
        <p v-if="errorMsg" class="text-red-600 text-sm whitespace-pre-wrap">
          {{ errorMsg }}
        </p>
        <p v-if="okMsg" class="text-green-700 text-sm">
          {{ okMsg }}
        </p>
      </form>
    </div>
  </main>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'app',
  middleware: 'auth',
})

import { onBeforeUnmount } from 'vue'

const router = useRouter()
const title = ref('')
const topic = ref('')
const body = ref('')
const mediaFile = ref<File | null>(null)
const previewUrl = ref<string | null>(null)
const loading = ref(false)
const errorMsg = ref('')
const okMsg = ref('')

const { create } = usePost()

const clearPreviewUrl = () => {
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value)
    previewUrl.value = null
  }
}

const onPickMedia = (e: Event) => {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0] ?? null

  mediaFile.value = file
  clearPreviewUrl()

  if (file) {
    previewUrl.value = URL.createObjectURL(file)
  }
}

const clearMedia = () => {
  mediaFile.value = null
  clearPreviewUrl()
}

onBeforeUnmount(() => {
  clearPreviewUrl()
})

const submit = async () => {
  errorMsg.value = ''
  okMsg.value = ''

  const trimmedTitle = title.value.trim()
  const trimmedBody = body.value.trim()
  const trimmedTopic = topic.value.trim()

  loading.value = true

  try {
    await create({
      title: trimmedTitle,
      body: trimmedBody,
      topic: trimmedTopic,
      media: mediaFile.value,
    })

    okMsg.value = 'Created!'
    setTimeout(() => router.push('/posts'), 300)
  } catch (e: any) {
    if (e?.data?.errors) {
      const lines = Object.values(e.data.errors).flat().join('\n')
      errorMsg.value = lines
    } else {
      errorMsg.value = e?.data?.message ?? 'Failed'
    }
  } finally {
    loading.value = false
  }
}
</script>