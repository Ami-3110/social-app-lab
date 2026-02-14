// composables/usePostActions.ts
export const usePostActions = (refresh: () => Promise<void>) => {
  const { $apiFetch } = useNuxtApp()

  const like = async (postId: number) => {
    await $apiFetch(`/posts/${postId}/like`, { method: 'POST' })
  }

  const unlike = async (postId: number) => {
    await $apiFetch(`/posts/${postId}/like`, { method: 'DELETE' })
  }

  const toggleLike = async (postId: number, nextLiked: boolean) => {
    if (nextLiked) await like(postId)
    else await unlike(postId)

    await refresh()
  }

  return { like, unlike, toggleLike }
}
