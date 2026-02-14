// web/app/plugins/init-auth.client.ts
import { initAuth } from '~/composables/useAuth'

export default defineNuxtPlugin(async () => {
  console.log('[init-auth plugin] start')

  // 起動時に1回だけ：ログイン状態の復元（Cookie→me）
  try {
    await initAuth()
  } catch (e) {
    // ここで落ちるとアプリ全体が起動しなくなるので握る
    console.warn('[init-auth] initAuth failed', e)
    
  }
})
