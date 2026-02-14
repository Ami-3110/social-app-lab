// app/composables/useAuth.ts
import type { User } from '~/types/User'

const TOKEN_KEY = 'auth_token'

export const useAuthState = () => {
    const loggedIn = useState('auth_loggedIn', () => false)
    const user = useState<User | null>('auth_user', () => null)
    const token = useState<string | null>('auth_token', () => null)
    return { loggedIn, user, token }
}

export const setToken = (t: string | null) => {
    const { token } = useAuthState()
    token.value = t

    if (!import.meta.client) return
    if (t) localStorage.setItem(TOKEN_KEY, t)
    else localStorage.removeItem(TOKEN_KEY)
    }

    export const restoreToken = () => {
    if (!import.meta.client) return
    const t = localStorage.getItem(TOKEN_KEY)
    if (t) setToken(t)
}

export const initAuth = async () => {
    if (!import.meta.client) return

    const { $apiFetch } = useNuxtApp()
    const { loggedIn, user, token } = useAuthState()

    if (!token.value) {
        user.value = null
        loggedIn.value = false
        return
    }

    try {
        const me = await $apiFetch<User>('/me')
        user.value = me
        loggedIn.value = true
    } catch {
        setToken(null)
        user.value = null
        loggedIn.value = false
    }
}

export const loginSuccess = async (newToken: string) => {
    const { $apiFetch } = useNuxtApp()
    const { loggedIn, user } = useAuthState()

    setToken(newToken)

    const me = await $apiFetch<User>('/me')
    user.value = me
    loggedIn.value = true

    const route = useRoute()
    const next = (route.query.next as string) || '/'
    await navigateTo(next, { replace: true })
}

export const logout = async () => {
    const { $apiFetch } = useNuxtApp()
    await $apiFetch('/logout', { method: 'POST' })

    const { loggedIn, user } = useAuthState()
    setToken(null)
    user.value = null
    loggedIn.value = false

    await navigateTo('/login')
}
