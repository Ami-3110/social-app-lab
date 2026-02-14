// plugins/api-fetch.ts
export default defineNuxtPlugin(() => {
    const config = useRuntimeConfig()

    const apiFetch = $fetch.create({
        baseURL: config.public.apiBase,

        onRequest({ options }) {
        const { token } = useAuthState()

        // 常に Headers に正規化
        const headers = new Headers(options.headers)

        headers.set('Accept', 'application/json')

        if (token.value) {
            headers.set('Authorization', `Bearer ${token.value}`)
        }

        options.headers = headers
        },

        onResponseError({ response }) {
        if (response.status === 401) {
            const { user, loggedIn } = useAuthState()
            setToken(null)
            user.value = null
            loggedIn.value = false
        }
        },
    })

    return {
        provide: { apiFetch },
    }
})
