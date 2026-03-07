// ~/composables/useUserComments.ts
import { toValue } from 'vue'
import type { MaybeRefOrGetter } from 'vue'
import type { Comment } from '~/types/Comment'
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

type CommentWithPost = Comment & { post: Post }

export const useUserComments = (
  userId: number,
  page: MaybeRefOrGetter<number> = 1
) => {
  const { $apiFetch } = useNuxtApp()

  return useAsyncData<Pagination<CommentWithPost>>(
    () => `user:${userId}:comments:${toValue(page)}`,
    () =>
      $apiFetch(`/users/${userId}/comments`, {
        params: { page: toValue(page) },
      }),
    {
      watch: [() => toValue(page)],
    }
  )
}