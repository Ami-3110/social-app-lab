// app/types/Post.ts
import type { PublicUser } from '~/types/User'
import type { Comment } from '~/types/Comment'

export type Post = {
  id: number
  title: string
  topic?: string
  body: string

  user: { id: number; name: string }

  repost_of_post_id?: number | null
  quote_body?: string | null
  original_post?: Post | null

  is_bookmarked?: number
  is_liked?: number
  is_reposted?: number

  likes_count?: number
  comments_count?: number
  reposts_count?: number

  repost_of_comment_id?: number | null
  repost_of_comment?: (Comment & { post?: Post | null }) | null
  
  created_at: string
  updated_at: string
}
