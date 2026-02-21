// app/types/Comment.ts
import type { PublicUser } from '~/types/User'

export type Comment = {
  id: number
  post_id: number
  user: PublicUser
  body: string

  parent_id?: number | null
  root_id?: number | null
  parent?: {
    id: number
    body: string
    user: PublicUser
  } | null

  likes_count?: number
  replies_count?: number
  reposts_count?: number
  bookmarks_count?: number

  is_liked?: boolean
  is_bookmarked?: boolean
  is_reposted?: boolean
}
