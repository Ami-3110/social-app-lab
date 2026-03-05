// ~/composables/useUserLikedPosts.ts
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

export const useUserLikedPosts = (userId: number) => {
  const { $apiFetch } = useNuxtApp()

  return useAsyncData<Pagination<Post>>(
    () => `user:${userId}:liked-posts`,
    () => $apiFetch(`/users/${userId}/liked-posts`)
  )
}