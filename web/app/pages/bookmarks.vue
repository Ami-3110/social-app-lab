<template>
  <main class="p-6 space-y-4">
    <div class="max-w-2xl mx-auto space-y-4">
      <header>
        <h1 class="text-2xl font-bold ui-text">Saved</h1>
      </header>

      <!-- ローディング -->
      <div v-if="pending" class="space-y-4">
        <PostSkeleton />
        <PostSkeleton />
      </div>

      <div v-else>
        <!-- 空 -->
        <div
          v-if="!data?.data || data.data.length === 0"
          class="py-12 text-center ui-muted"
        >
          No saved posts yet.
        </div>

        <!-- 一覧 -->
        <ul class="space-y-4">
          <PostCard
            v-for="p in posts"
            :key="p.id"
            :post="p"
            @bookmark-changed="onBookmarkChanged"
          />
        </ul>

        <!-- ページネーション -->
          <nav class="flex items-center justify-center gap-8 mt-10" v-if="data">
            <button
              class="flex items-center gap-2 px-4 py-2 rounded-md ui-border ui-text ui-card ui-card-hover text-sm disabled:opacity-40 disabled:cursor-not-allowed transition"
              :disabled="!data.prev_page_url"
              @click="go(data.current_page - 1)"
            >
              <span class="text-lg">←</span>
              Prev
            </button>

            <div class="flex items-center gap-2 text-sm ui-muted">
              <span>Page</span>
              <span class="min-w-[2.2rem] text-center px-2 py-1 font-semibold rounded-md ui-border ui-card ui-text">
                {{ data.current_page }}
              </span>
              <span>/</span>
              <span class="ui-text">{{ data.last_page }}</span>
            </div>

            <button
              class="flex items-center gap-2 px-4 py-2 rounded-md ui-border ui-text ui-card ui-card-hover text-sm disabled:opacity-40 disabled:cursor-not-allowed transition"
              :disabled="!data.next_page_url"
              @click="go(data.current_page + 1)"
            >
              Next
              <span class="text-lg">→</span>
            </button>
          </nav>
      </div>
    </div>
  </main>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'app',
  middleware: 'auth',
})

import { navigateTo } from '#app'
import { useBookmarks } from '~/composables/useBookmarks'

const { data, pending, error, go, refresh } = useBookmarks()

const posts = computed(() => data.value?.data ?? [])

const onBookmarkChanged = async ({ postId, isBookmarked }: { postId: number; isBookmarked: boolean }) => {
  if (isBookmarked) return
  await refresh()
}

</script>
