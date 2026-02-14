// composables/useCommentLikes.ts
export const useCommentLikes = () => {
  const { $apiFetch } = useNuxtApp()

  const likeComment = (commentId: number) =>
    $apiFetch(`/comments/${commentId}/like`, { method: 'POST' })

  const unlikeComment = (commentId: number) =>
    $apiFetch(`/comments/${commentId}/like`, { method: 'DELETE' })

  return { likeComment, unlikeComment }
}
