// types/User.ts
export type User = {
    id: number
    name: string
    email: string
    image?: string | null
    role?: string | null
}

export type PublicUser = {
    id: number
    name: string
    image?: string | null
}
