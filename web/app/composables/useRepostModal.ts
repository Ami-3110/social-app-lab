// ~/composables/useRepostModal.ts
import type { Post } from '~/types/Post'
import type { Comment } from '~/types/Comment'

export type RepostTarget =
  | {
      kind: 'post'
      id: number
      userName?: string | null
      title?: string | null
      body?: string | null
    }
  | {
      kind: 'comment'
      id: number
      userName?: string | null
      body?: string | null
  }
    
const _isOpen = ref(false)
const _target = ref<RepostTarget | null>(null)

export function useRepostModal() {
  const quoteBody = ref('')

  const openPost = (post: Post) => {
    _target.value = {
      kind: 'post',
      id: post.id,
      userName: post.user?.name ?? null,
      title: post.title ?? null,
      body: post.body ?? null,
    }
    quoteBody.value = ''
    _isOpen.value = true
  }

  const openComment = (comment: Comment) => {
    _target.value = {
      kind: 'comment',
      id: comment.id,
      userName: comment.user?.name ?? null,
      body: comment.body ?? null,
    }
    quoteBody.value = ''
    _isOpen.value = true
  }

  const close = () => {
    _isOpen.value = false
    _target.value = null
    quoteBody.value = ''
  }

  return {
    isOpen: _isOpen,
    target: _target,
    quoteBody,
    openPost,
    openComment,
    close
  }
}
