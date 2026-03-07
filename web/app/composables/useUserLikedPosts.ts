// ~/composables/useUserLikedPosts.ts
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

export const useUserLikedPosts = (
  userId: number,
  page: MaybeRefOrGetter<number> = 1
) => {
  const { $apiFetch } = useNuxtApp()

  return useAsyncData<Pagination<Post>>(
    () => `user:${userId}:liked-posts:${toValue(page)}`,
    () =>
      $apiFetch(`/users/${userId}/liked-posts`, {
        params: { page: toValue(page) },
      }),
    {
      watch: [() => toValue(page)],
    }
  )
}