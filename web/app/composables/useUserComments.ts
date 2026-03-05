// ~/composables/useUserComments.ts
import type { Comment } from '~/types/Comment'
import type { Post } from '~/types/Post'

type Pagination<T> = {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

type CommentWithPost = Comment & { post: Post }

export const useUserComments = (userId: number) => {
  const { $apiFetch } = useNuxtApp()

  return useAsyncData<Pagination<CommentWithPost>>(
    () => `user:${userId}:comments`,
    () => $apiFetch(`/users/${userId}/comments`)
  )
}