<!-- pages/users/[id].vue -->
<template>
  <main class="p-6 max-w-xl mx-auto space-y-6 ui-bg ui-text min-h-screen">
    <!-- Loading / Error -->
    <div v-if="pending" class="ui-muted">Loading...</div>
    <div v-else-if="error" class="text-red-600">Failed to load user.</div>

    <section v-else-if="profile" class="space-y-4">
      <!-- Header -->
      <header class="flex items-center gap-4">
        <div class="h-12 w-12 rounded-full ui-border-all flex items-center justify-center text-lg font-bold">
          {{ (profile.name ?? 'U').slice(0,1).toUpperCase() }}
        </div>

        <div class="flex-1 min-w-0">
          <h1 class="text-2xl font-bold truncate">{{ profile.name }}</h1>
          <p class="text-sm ui-muted truncate">{{ profile.email ?? '' }}</p>
        </div>
      </header>

      <!-- Bio（今は仮。後でAPIにbio追加） -->
      <section class="rounded-xl ui-border-all ui-bg p-4 text-sm whitespace-pre-wrap">
        <p v-if="profile.bio?.trim()">{{ profile.bio }}</p>
        <p v-else class="ui-muted">Add a bio to your profile.</p>
      </section>

      <!-- Buttons row -->
      <section class="flex gap-2">
        <!-- own -->
        <button
          v-if="isMe"
          type="button"
          class="flex-1 rounded-full px-4 py-2 text-sm ui-border-all ui-bg hover:opacity-90"
          @click="onEditProfile"
        >
          Edit profile
        </button>

        <!-- Following -->
        <button
          v-else
          type="button"
          class="flex-1 rounded-full px-4 py-2 text-sm ui-border-all ui-bg hover:opacity-90"
          :disabled="busy"
          @click="toggleFollow"
        >
          {{ profile.is_following ? 'Unfollow' : 'Follow' }}
        </button>

        <!-- Follower -->
        <button
          type="button"
          class="flex-1 rounded-full px-4 py-2 text-sm ui-border-all ui-bg hover:opacity-90"
          @click="onOpenFollowing"
        >
          {{ profile.following_count ?? 0 }} Following
        </button>

        <button
          type="button"
          class="flex-1 rounded-full px-4 py-2 text-sm ui-border-all ui-bg hover:opacity-90"
          @click="onOpenFollowers"
        >
          {{ profile.followers_count ?? 0 }} Followers
        </button>

      </section>

      <!-- Tabs（中身は後で） -->
      <nav class="grid grid-cols-4 gap-2 pt-2">
        <NuxtLink
          :to="`/users/${userId}`"
          class="rounded-md px-3 py-2 text-sm ui-border-all text-center"
          :class="isActive('post') ? activeClass : inactiveClass"
        >
          Post
        </NuxtLink>

        <NuxtLink
          :to="`/users/${userId}/comments`"
          class="rounded-md px-3 py-2 text-sm ui-border-all text-center"
          :class="isActive('comments') ? activeClass : inactiveClass"
        >
          Comment
        </NuxtLink>

        <NuxtLink
          :to="`/users/${userId}/media`"
          class="rounded-md px-3 py-2 text-sm ui-border-all text-center"
          :class="isActive('media') ? activeClass : inactiveClass"
        >
          Media
        </NuxtLink>

        <NuxtLink
          :to="`/users/${userId}/liked`"
          class="rounded-md px-3 py-2 text-sm ui-border-all text-center"
          :class="isActive('liked') ? activeClass : inactiveClass"
        >
          Liked
        </NuxtLink>
      </nav>

      <!-- 👇 子ページの差し込み口 -->
      <NuxtPage />


    </section>
  </main>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'app',
  middleware: 'auth',
})

import { computed, ref, nextTick, onMounted, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthState } from '~/composables/useAuth'
import { useFollow } from '~/composables/useFollow'
import type { Post } from '~/types/Post'
import type { Comment } from '~/types/Comment'

type Profile = {
  id: number
  name: string
  email?: string
  bio?: string
  is_following?: boolean
  following_count?: number
  followers_count?: number
}

const { $apiFetch } = useNuxtApp()
const route = useRoute()
const { user: me } = useAuthState()
const { follow, unfollow } = useFollow()

const userId = computed(() => Number(route.params.id))

const { data: profileRes, pending, error, refresh } = useAsyncData<{ data: Profile }>(
  () => `user:${userId.value}`,
  () => $apiFetch(`/users/${userId.value}`),
  { watch: [userId] }
)

const profile = computed(() => profileRes.value?.data)
const isMe = computed(() => Number(me.value?.id) === Number(profile.value?.id))

const busy = ref(false)

// follow count
const toggleFollow = async () => {
  if (!profileRes.value?.data) return
  if (isMe.value) return
  if (busy.value) return
  busy.value = true

  const p = profileRes.value.data
  const prev = !!p.is_following

  // optimistic update
  p.is_following = !prev
  p.followers_count = Number(p.followers_count ?? 0) + (prev ? -1 : 1)

  try {
    if (prev) await unfollow(p.id)
    else await follow(p.id)

  } catch (e) {
    
    // rollback
    p.is_following = prev
    p.followers_count = Number(p.followers_count ?? 0) + (prev ? 1 : -1)
    throw e
  } finally {
    busy.value = false
  }
}

// buttons
const onEditProfile = () => navigateTo('/profile/edit') // ⭐️後で作る
const onOpenFollowing = () => navigateTo(`/users/${userId.value}/following`)
const onOpenFollowers = () => navigateTo(`/users/${userId.value}/followers`)

// tabs
const activeClass =
  'font-semibold bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 border-zinc-900 dark:border-zinc-100'
const inactiveClass = 'ui-muted hover:opacity-90'

const isActive = (tab: 'post' | 'comments' | 'media' | 'liked') => {
  const p = route.path
  if (tab === 'post') return !/\/(comments|media|liked)$/.test(p)
  return p.endsWith(`/${tab}`)
}

</script>
