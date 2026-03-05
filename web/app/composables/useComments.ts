// ~/composables/useComments.ts
import type { Comment } from '~/types/Comment'

export const useComments = (opts: { refresh?: () => Promise<void> } = {}) => {
  const { $apiFetch } = useNuxtApp()
  const refresh = opts.refresh ?? (async () => {})

  const toggleLike = async (c: Comment) => {
    const isOn = !!c.is_liked
    c.is_liked = !isOn
    c.likes_count = Math.max(0, (c.likes_count ?? 0) + (isOn ? -1 : 1))

    try {
      await $apiFetch(`/comments/${c.id}/like`, { method: isOn ? 'DELETE' : 'POST' })
      await refresh()
    } catch (e) {
      c.is_liked = isOn
      c.likes_count = Math.max(0, (c.likes_count ?? 0) + (isOn ? 1 : -1))
      throw e
    }
  }

  const toggleBookmark = async (c: Comment) => {
    const isOn = !!c.is_bookmarked
    c.is_bookmarked = !isOn
    c.bookmarks_count = Math.max(0, (c.bookmarks_count ?? 0) + (isOn ? -1 : 1))

    try {
      await $apiFetch(`/comments/${c.id}/bookmark`, { method: isOn ? 'DELETE' : 'POST' })
      await refresh()
    } catch (e) {
      c.is_bookmarked = isOn
      c.bookmarks_count = Math.max(0, (c.bookmarks_count ?? 0) + (isOn ? 1 : -1))
      throw e
    }
  }

  const toggleRepost = async (c: Comment) => {
    const isOn = !!c.is_reposted
    c.is_reposted = !isOn
    c.reposts_count = Math.max(0, (c.reposts_count ?? 0) + (isOn ? -1 : 1))

    try {
      await $apiFetch(`/comments/${c.id}/repost`, { method: isOn ? 'DELETE' : 'POST' })
      await refresh()
    } catch (e) {
      c.is_reposted = isOn
      c.reposts_count = Math.max(0, (c.reposts_count ?? 0) + (isOn ? 1 : -1))
      throw e
    }
  }

  const quote = async (comment: Comment, body: string) => {
    await $apiFetch(`/comments/${comment.id}/repost`, {
      method: 'POST',
      body: { quote_body: body || null }
    })
    await refresh()
  }

  return { toggleLike, toggleBookmark, toggleRepost, quote }
}