<!-- pages/posts/new.vue -->
<template>
  <main class="p-6 ui-bg ui-text min-h-screen">
    <!-- TL ヘッダー分の余白 -->
    <div class="max-w-2xl h-[3.25rem]"></div>

    <div class="max-w-2xl mx-auto space-y-6 px-5">
      <h1 class="text-2xl font-bold">✨ New Post</h1>

      <!-- フォーム（浮かせない面） -->
      <form
        class="space-y-4 p-6 ui-card ui-border rounded-xl"
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
            class="w-full rounded-xl px-3 py-2 ui-bg ui-text ui-border"
            maxlength="120"
            required
          >
        </div>

        <!-- Topic -->
        <div>
          <label class="block text-sm font-medium mb-1 ui-muted">Topic (optional)</label>
          <input v-model="topic" type="text" class="w-full rounded-xl px-3 py-2 ui-bg ui-text ui-border" maxlength="100">
        </div>

        <!-- Body -->
        <div>
          <label class="block text-sm font-medium mb-1 ui-muted">
            Body
          </label>
          <textarea
            v-model="body"
            class="w-full rounded-xl px-3 py-2 h-40 ui-bg ui-text ui-border"
            required
          ></textarea>
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
            class="px-4 py-2 rounded ui-border ui-muted hover:bg-zinc-50 dark:hover:bg-zinc-700/50"
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

const router = useRouter()
const title = ref('')
const topic = ref('')
const body  = ref('')
const loading = ref(false)
const errorMsg = ref('')
const okMsg = ref('')

// composable呼び出し
const { create } = usePost()

const submit = async () => {
  errorMsg.value = ''
  okMsg.value = ''
  loading.value = true
  try {
    await create({
      title: title.value.trim(),
      body: body.value.trim(),
      topic: topic.value.trim(),
    })
    okMsg.value = 'Created!'
    setTimeout(() => router.push('/posts'), 300)

  } catch (e: any) {
    if (e?.data?.errors) {
      // Laravelのバリデーションエラー整形
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
