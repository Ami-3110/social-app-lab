// types/api.ts

export type FieldErrors = Record<string, string[]>

export type NormalizedApiErrors = {
    status: number
    message: string
    fieldErrors?: FieldErrors
    raw?: any
}
