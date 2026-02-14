<!-- pages/users/[id]/followers.vue -->
<template>
  <main class="p-6 max-w-xl mx-auto space-y-4 ui-bg ui-text min-h-screen">
    <div class="flex items-center justify-between">
      <h1 class="text-lg font-semibold">Followers</h1>
      <NuxtLink :to="`/users/${userId}`" class="text-sm text-blue-600 hover:underline">
        Back
      </NuxtLink>
    </div>

    <div v-if="pending" class="ui-muted text-sm">Loading...</div>
    <div v-else-if="error" class="text-red-600 text-sm">Failed to load followers.</div>

    <div v-else class="space-y-3">
      <p v-if="users.length === 0" class="ui-muted text-sm">
        No followers yet.
      </p>

      <UserCard
        v-for="u in users"
        :key="u.id"
        :user="u"
        :is-me="Number(u.id) === Number(meId)"
        :busy="busyId === u.id"
        @toggle-follow="onToggleFollow"
      />
    </div>
  </main>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'app', middleware: 'auth' })

import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthState } from '~/composables/useAuth'
import { useFollow } from '~/composables/useFollow'

type UserLite = {
  id: number
  name: string
  bio?: string | null
  is_following?: boolean
  followers_count?: number
  following_count?: number
}

const { $apiFetch } = useNuxtApp()
const route = useRoute()
const { user: me } = useAuthState()
const { follow, unfollow } = useFollow()

const userId = computed(() => Number(route.params.id))
const meId = computed(() => me.value?.id)

const { data: res, pending, error } = useAsyncData<{ data: UserLite[] }>(
  () => `user:${userId.value}:followers`,
  () => $apiFetch(`/users/${userId.value}/followers`),
  { watch: [userId] }
)

const users = computed(() => res.value?.data ?? [])
const busyId = ref<number | null>(null)

const onToggleFollow = async (targetUserId: number, nextFollow: boolean) => {
  const list = res.value?.data
  if (!list) return

  const target = list.find(x => Number(x.id) === Number(targetUserId))
  if (!target) return

  const prev = !!target.is_following
  target.is_following = nextFollow

  busyId.value = targetUserId
  try {
    if (nextFollow) await follow(targetUserId)
    else await unfollow(targetUserId)
  } catch (e) {
    target.is_following = prev
    throw e
  } finally {
    busyId.value = null
  }
}
</script>

