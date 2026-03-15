// app/types/Comment.ts
import type { PublicUser } from '~/types/User'
import type { CommentMedia } from '~/types/CommentMedia'

export type Comment = {
  id: number
  post_id: number
  user: PublicUser
  body: string | null

  created_at: string
  updated_at: string

  parent_id?: number | null
  root_id?: number | null
  parent?: {
    id: number
    body: string | null
    user: PublicUser
  } | null

  media?: CommentMedia[]

  likes_count?: number
  replies_count?: number
  reposts_count?: number
  bookmarks_count?: number

  is_liked?: boolean
  is_bookmarked?: boolean
  is_reposted?: boolean
}


