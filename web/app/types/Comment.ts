// app/types/Comment.ts
export type Comment = {
  id: number
  post_id: number
  user_id: number
  body: string
  created_at: string
  user: { id: number; name: string }
  likes_count: number
  is_liked: boolean
}
