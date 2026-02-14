// middleware/guest.ts
export default defineNuxtRouteMiddleware((to) => {
    const { loggedIn, token } = useAuthState()

  // すでにログインページ/登録ページにいるなら、ここでは何もしない（ループ防止）
    if (to.path === '/login' || to.path === '/register') return

  // ログイン済み(またはtokenあり)ならトップへ
    if (loggedIn.value || token.value) {
        return navigateTo('/')
    }
})
