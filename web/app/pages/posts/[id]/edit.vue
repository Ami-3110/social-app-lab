<!-- pages/posts/[id]/edit.vue -->
<template>
  <main class="p-6 ui-bg ui-text min-h-screen">
    <!-- TL ヘッダー分の余白 -->
    <div class="max-w-2xl h-[3.25rem]"></div>

    <div class="max-w-2xl mx-auto space-y-6 px-5">
      <h1 class="text-2xl font-bold">✏️ Edit Post</h1>

      <div v-if="pending">Loading...</div>

      <div v-else-if="error" class="text-red-600">
        Failed to load posts.
      </div>

      <!-- フォーム（浮かせない） -->
      <form
        v-else
        @submit.prevent="onSubmit"
        class="space-y-4 p-6 ui-card ui-border rounded-xl"
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
          />
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
            rows="6"
            class="w-full rounded-xl px-3 py-2 ui-bg ui-text ui-border"
          ></textarea>
        </div>

        <!-- Error -->
        <p v-if="saveError" class="text-sm text-red-600">
          {{ saveError }}
        </p>

        <!-- Actions -->
        <div class="flex items-center gap-3">
          <button
            type="submit"
            class="px-4 py-2 rounded bg-blue-600 text-white disabled:opacity-50"
            :disabled="saving"
          >
            {{ saving ? 'Saving...' : 'Save' }}
          </button>

          <NuxtLink
            to="/posts"
            class="px-4 py-2 rounded ui-border ui-muted hover:bg-zinc-50 dark:hover:bg-zinc-700/50"
          >
            Cancel
          </NuxtLink>
        </div>
      </form>
    </div>
  </main>
</template>


<script setup lang="ts">
definePageMeta({
  layout: 'app',
  middleware: 'auth',
})
import { ref, watch } from 'vue'
import { navigateTo } from '#app'
import { useRouter } from '#imports'
import { usePost } from '~/composables/usePost'

const { data, pending, error, update, saving, saveError } = usePost()

const title = ref('')
const topic = ref('')
const body = ref('')

// 投稿取得後の同期
watch(data, (val) => {
    if (val) {
      title.value = val.title
      topic.value = val.topic ?? ''
      body.value = val.body
    }
}, { immediate: true })

// 保存処理
const onSubmit = async () => {
    await update({
      title: title.value,
      topic: topic.value,
      body: body.value,
    })

    // 保存後に詳細ページへ戻す（重要）
    if (!data.value) return
    await navigateTo(`/posts/${data.value.id}`)
}
</script>
