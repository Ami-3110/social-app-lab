// types/User.ts
export type User = {
    id: number
    name: string
    email: string
    bio: string | null
    avator_url: string | null
    role?: string | null
    is_following?: number | boolean
}

export type PublicUser = {
    id: number
    name: string
    bio: string | null
    avatar_url?: string | null
    is_following?: number | boolean
}
