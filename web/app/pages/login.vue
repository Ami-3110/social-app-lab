<!-- pages/login.vue -->
<template>
  <main class="p-6 max-w-md mx-auto space-y-4">
    <h1 class="text-xl font-bold">Login</h1>

    <AppAlert v-if="globalError" :message="globalError" />

    <Form @submit="onSubmit" v-slot="{ isSubmitting }" class="space-y-3">
      <div>
        <label class="block text-sm">E-mail</label>
        <Field name="email" rules="required" as="input" class="border rounded px-2 py-1 w-full" />
        <p v-if="errors.email" class="text-red-600 text-sm">{{ errors.email }}</p>
      </div>

      <div>
        <label class="block text-sm">Password</label>
        <Field name="password" rules="required" as="input" type="password" class="border rounded px-2 py-1 w-full" />
        <p v-if="errors.password" class="text-red-600 text-sm">{{ errors.password }}</p>
      </div>

      <button type="submit" :disabled="isSubmitting" class="border rounded px-3 py-1">
        {{ isSubmitting ? 'Submitting…' : 'Login' }}
      </button>
      <div></div><div></div>
      <NuxtLink to="/register" class="border rounded px-3 py-1">New here? <span class="font-medium">Sign up</span> ⇢</NuxtLink>
    </Form>
  </main>

  <ClientOnly>
    <Teleport to="body">
      <div
        v-if="toast.open"
        style="position:fixed;right:16px;bottom:16px;z-index:99999;background:rgba(0,0,0,.85);color:#fff;padding:8px 12px;border-radius:8px"
      >
        {{ toast.message }}
      </div>
    </Teleport>
  </ClientOnly>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'guest' })

import { ref } from 'vue'
import { Form, Field, useForm } from 'vee-validate'
import { useToast } from '~/composables/useToast'
import { loginSuccess } from '~/composables/useAuth'

const toast = useToast()
const globalError = ref('')

const { errors } = useForm()

const config = useRuntimeConfig()
const API = config.public.apiBase

async function onSubmit(values: Record<string, any>) {
  globalError.value = ''

  try {

    // ★ 2) Login（tokenを受け取る）
    const res = await $fetch<{ token: string }>(`${API}/login`, {
      method: 'POST',
      body: values,
      headers: { Accept: 'application/json' },
    })

    // ★ 3) ログイン状態反映 + 遷移
    await loginSuccess(res.token)
    toast.show('Logged in!', 1000)

  } catch (e: any) {
    console.error('[login fail]', e)
    globalError.value = e?.message ?? 'Login failed'
    toast.show('Login failed', 1500)
  }
}
</script>

