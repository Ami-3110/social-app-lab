import type { Post } from '~/types/Post'

type PostsResponse = {
  current_page: number
  data: Post[]
  last_page: number
  prev_page_url: string | null
  next_page_url: string | null
}

export const useBookmarks = () => {
  const { $apiFetch } = useNuxtApp()
  const route = useRoute()

  const page = computed(() => Number(route.query.page ?? 1))

  const { data, pending, error, refresh } = useAsyncData<PostsResponse>(
    () => `bookmarks:${page.value}`,
    () =>
      $apiFetch('/bookmarks', {
        params: { page: page.value, per_page: 10 },
      }),
    { watch: [page], server: false }
  )

  const go = (p: number) =>
    navigateTo({ query: { ...route.query, page: p } })

  return {
    data,
    pending,
    error,
    page,
    go,
    refresh,
  }
}
