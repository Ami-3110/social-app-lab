<!-- layouts/app.vue -->

<template>
  <div class="min-h-screen ui-bg">
    <div class="mx-auto max-w-5xl grid grid-cols-[240px_1fr] gap-6">
      <!-- Left Sidebar -->
      <aside class="sticky top-0 h-screen p-5 ui-card ui-border ui-text border-r">
        <div class="mb-6">
          <NuxtLink to="/" class="text-xl font-bold tracking-tight ui-text">
            tw-like
          </NuxtLink>
        </div>

        <nav class="space-y-1 text-sm">
          <NuxtLink to="/" v-slot="{ isActive }" class="block">
            <span :class="navClass(isActive)">🏠 <span class="ml-2">Home</span></span>
          </NuxtLink>

          <NuxtLink to="/search" v-slot="{ isActive }" class="block">
            <span :class="navClass(isActive)">🔍 <span class="ml-2">Search</span></span>
          </NuxtLink>

          <NuxtLink to="/posts/new" v-slot="{ isActive }" class="block">
            <span :class="navClass(isActive)">➕ <span class="ml-2">Post</span></span>
          </NuxtLink>

          <NuxtLink to="/likes" v-slot="{ isActive }" class="block">
            <span :class="navClass(isActive)">💡 <span class="ml-2">Activity</span></span>
          </NuxtLink>

          <NuxtLink to="/bookmarks" v-slot="{ isActive }" class="block">
            <span :class="navClass(isActive)">🔖 <span class="ml-2">Saved</span></span>
          </NuxtLink>

          <NuxtLink to="/profile" v-slot="{ isActive }" class="block">
            <span :class="navClass(isActive)">👤 <span class="ml-2">My page</span></span>
          </NuxtLink>

          <!-- Divider + menu -->
          <div class="border-t border-zinc-200 dark:border-zinc-600">
            <div class="relative" ref="menuRoot">
              <!-- trigger -->
              <button
                class="w-full text-left px-4 py-3 ui-menu-item flex items-center gap-2 ui-text"
                @click="menuOpen = !menuOpen"
                aria-haspopup="menu"
                :aria-expanded="menuOpen"
              >
                <span class="text-lg">⋯</span>
                <span class="text-sm ml-2">Menu</span>
              </button>

              <!-- dropdown (opens to upper-right) -->
              <div
                v-if="menuOpen"
                class="absolute bottom-full left-0 mb-2 w-56 rounded-xl ui-menu ui-border shadow-lg overflow-hidden"
                role="menu"
              >
                <button
                  class="w-full text-left px-4 py-3 ui-menu-item flex items-center gap-2 ui-text"
                  role="menuitem"
                  @click="toggleTheme(); menuOpen = false"
                >
                  <span>🌓</span>
                  <span>Theme: {{ themeLabel }}</span>
                </button>

                <div class="border-t border-zinc-200 dark:border-zinc-600"></div>

                <button
                  class="w-full text-left px-4 py-3 ui-menu-item flex items-center gap-2 text-red-600"
                  role="menuitem"
                  @click="onLogoutFromMenu"
                >
                  <span>👋</span>
                  <span>Logout</span>
                </button>
              </div>
            </div>
          </div>
        </nav>
      </aside>

      <!-- Main Column -->
      <div class="py-4">
        <main class="py-4">
          <slot />
        </main>
      </div>
    </div>
  </div>
</template>


<script setup lang="ts">
import { logout } from '~/composables/useAuth'

const navClass = (active: boolean) => {
  return [
    'flex items-center rounded-md px-3 py-2 text-base ui-text ui-menu-item',
    active
      ? 'font-semibold bg-zinc-100 dark:bg-zinc-700'
      : 'ui-muted',
  ].join(' ')
}

const onLogout = async () => {
  await logout()
}

import { onBeforeUnmount, onMounted, ref, computed } from 'vue'

const menuOpen = ref(false)
const menuRoot = ref<HTMLElement | null>(null)

// --- click outside to close
const onDocClick = (e: MouseEvent) => {
  if (!menuOpen.value) return
  const el = menuRoot.value
  if (!el) return
  if (e.target instanceof Node && el.contains(e.target)) return
  menuOpen.value = false
}

onMounted(() => document.addEventListener('click', onDocClick))
onBeforeUnmount(() => document.removeEventListener('click', onDocClick))

// --- theme (no module required)
const theme = ref<'light' | 'dark'>('light')

const applyTheme = (t: 'light' | 'dark') => {
  theme.value = t
  const root = document.documentElement
  root.classList.toggle('dark', t === 'dark')
  localStorage.setItem('theme', t)
}

onMounted(() => {
  const saved = localStorage.getItem('theme')
  if (saved === 'dark' || saved === 'light') {
    applyTheme(saved)
  } else {
    // 初回はOS設定に合わせる
    const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)')?.matches
    applyTheme(prefersDark ? 'dark' : 'light')
  }
})

const toggleTheme = () => applyTheme(theme.value === 'dark' ? 'light' : 'dark')
const themeLabel = computed(() => (theme.value === 'dark' ? 'Dark' : 'Light'))

// --- logout wrapper (optional confirm)
const onLogoutFromMenu = async () => {
  menuOpen.value = false
  await onLogout()
}

</script>
