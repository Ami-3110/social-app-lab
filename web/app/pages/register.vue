<!-- pages/register.vue -->
<template>
  <main class="p-6 max-w-md mx-auto space-y-4">
    <h1 class="text-xl font-bold">Registeration</h1>

    <!-- 上部の汎用エラー -->
    <p v-if="err" class="text-red-600 text-sm">{{ err }}</p>

    <form class="space-y-3" @submit.prevent="submit">
      <label class="block">
        <span class="text-sm">Name</span>
        <input v-model="form.name" class="border rounded p-2 w-full" autocomplete="name" />
        <p v-if="fieldErrors.name" class="text-red-600 text-sm">{{ fieldErrors.name[0] }}</p>
      </label>

      <label class="block">
        <span class="text-sm">E-mail</span>
        <input v-model="form.email" type="email" class="border rounded p-2 w-full" autocomplete="email" />
        <p v-if="fieldErrors.email" class="text-red-600 text-sm">{{ fieldErrors.email[0] }}</p>
      </label>

      <label class="block">
        <span class="text-sm">Password</span>
        <input v-model="form.password" type="password" class="border rounded p-2 w-full" autocomplete="new-password" />
        <p v-if="fieldErrors.password" class="text-red-600 text-sm">{{ fieldErrors.password[0] }}</p>
      </label>

      <label class="block">
        <span class="text-sm">Password Confirmation</span>
        <input v-model="form.password_confirmation" type="password" class="border rounded p-2 w-full" autocomplete="new-password" />
      </label>

      <button :disabled="submitting" class="border rounded px-3 py-1">
        {{ submitting ? 'Submitting…' : 'Register' }}
      </button>
      <div></div><div></div>
      <NuxtLink to="/login" class="border rounded px-3 py-1">Have an account? <span class="font-medium">Log in</span> ⇢</NuxtLink>
    </form>
  </main>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'guest' })

import { useAuthState, loginSuccess } from '~/composables/useAuth'

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const submitting = ref(false)
const err = ref<string | null>(null)
const fieldErrors = ref<Record<string, string[]>>({})

/** グローバル状態 */
const { user, loggedIn } = useAuthState()

type RegisterResponse = {
  message: string
  token: string
  token_type: string
  user: {
    id: number
    name: string
    email: string
  }
}

// 登録 → 自動ログイン → /index へ
async function submit() {
  err.value = null
  fieldErrors.value = {}
  submitting.value = true

  const config = useRuntimeConfig()
  const API = config.public.apiBase // 例: http://localhost:8000/api

  try {
    // 1) Register
    const res = await $fetch<RegisterResponse>(`${API}/register`, {
      method: 'POST',
      body: form,
      headers: {
        Accept: 'application/json',
      },
    })

    // 2) tokenでログイン確定（保存 + /me + 遷移）
    await loginSuccess(res.token)

  } catch (e: any) {
    console.log('register error:', e)

    const status =
      e?.statusCode ??
      e?.response?.status ??
      e?.data?.statusCode ??
      e?.status ??
      null

    const data = e?.data ?? e
    const errors = data?.errors ?? data?.data?.errors ?? null
    const message =
      data?.message ??
      data?.data?.message ??
      e?.message ??
      '登録に失敗しました'

    if (status === 422 && errors) {
      fieldErrors.value = errors as Record<string, string[]>
      return
    }

    err.value = message
  } finally {
    submitting.value = false
  }
}
</script>