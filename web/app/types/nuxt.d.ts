// types/nuxt.d.ts
export {}

declare module '#app' {
    interface NuxtApp {
        $apiFetch: typeof $fetch
    }
}
