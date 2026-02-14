// middleware/auth.ts
import type { User } from '~/types/User'
import { useAuthState } from '~/composables/useAuth'

export default defineNuxtRouteMiddleware(async () => {
    const { user, loggedIn, token } = useAuthState()
    const config = useRuntimeConfig()
    const API = config.public.apiBase
    const route = useRoute()

    if (loggedIn.value) return

    if (!token.value) {
        user.value = null
        loggedIn.value = false
        if (route.path !== '/login') {
        return navigateTo(`/login?next=${encodeURIComponent(route.fullPath)}`)
        }
        return
    }

    try {
        const me = await $fetch<User>(`${API}/me`, {
        headers: { Authorization: `Bearer ${token.value}` },
        })
        user.value = me
        loggedIn.value = true
    } catch {
        token.value = null
        user.value = null
        loggedIn.value = false
        if (route.path !== '/login') {
        return navigateTo(`/login?next=${encodeURIComponent(route.fullPath)}`)
        }
        return
    }
})
