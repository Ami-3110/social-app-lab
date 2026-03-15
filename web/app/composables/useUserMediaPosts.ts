// ~/composables/useUserMediaPosts.ts
import { toValue } from 'vue'
import type { MaybeRefOrGetter } from 'vue'
import type { Post } from '~/types/Post'

type Pagination<T> = {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  prev_page_url?: string | null
  next_page_url?: string | null
}

export const useUserMediaPosts = (
  userId: number,
  page: MaybeRefOrGetter<number> = 1
) => {
  const { $apiFetch } = useNuxtApp()

  return useAsyncData<Pagination<Post>>(
    () => `user:${userId}:media-posts:${toValue(page)}`,
    () =>
      $apiFetch(`/users/${userId}/media-posts`, {
        params: { page: toValue(page) },
      }),
    {
      watch: [() => toValue(page)],
    }
  )
}