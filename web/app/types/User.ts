// types/User.ts
export type User = {
    id: number
    name: string
    email: string
    avator_url: string | null
    role?: string | null
}

export type PublicUser = {
    id: number
    name: string
    avatar_url?: string | null
}
