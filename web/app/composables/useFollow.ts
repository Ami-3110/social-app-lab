// composables/useFollow.ts

export const useFollow = () => {
  const { $apiFetch } = useNuxtApp()

  const follow = (userId: number) =>
    $apiFetch(`/users/${userId}/follow`, { method: 'POST' })

  const unfollow = (userId: number) =>
    $apiFetch(`/users/${userId}/follow`, { method: 'DELETE' })

  return { follow, unfollow }
}
