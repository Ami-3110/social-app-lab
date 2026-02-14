// app/plugins/auth.client.ts
import { restoreToken } from '~/composables/useAuth'

export default defineNuxtPlugin(() => {
  restoreToken()
})
