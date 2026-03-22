<!-- ~/pages/notifications.vue -->
<template>  
  <main class="mx-auto max-w-2xl space-y-6 p-6 ui-bg ui-text min-h-screen">
    <div class="mx-auto max-w-2xl p-4">
      <header class="mb-4">
        <h1 class="text-xl font-bold">Activity</h1>
      </header>

      <div v-if="pending" class="ui-muted">
        Loading...
      </div>

      <div v-else-if="error" class="space-y-3">
        <p class="text-sm text-red-500">Failed to load notifications.</p>
        <button
          class="rounded-lg px-4 py-2 ui-border-all"
          @click="refresh()"
        >
          Reload
        </button>
      </div>

      <div v-else-if="notifications.length === 0" class="ui-muted">
        No notifications yet.
      </div>

      <ul v-else class="space-y-3">
        <li
          v-for="notification in notifications"
          :key="notification.id"
          class="rounded-2xl p-4 ui-border-all ui-bg"
          :class="{ 'opacity-70': notification.read_at }"
        >
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-sm">
                {{ notificationMessage(notification) }}
              </p>
              <p class="mt-1 text-xs ui-muted">
                {{ formatDateTime(notification.created_at) }}
              </p>
            </div>

            <span
              v-if="!notification.read_at"
              class="inline-block h-2.5 w-2.5 rounded-full bg-sky-500"
            />
          </div>
        </li>
      </ul>
    </div>
  </main>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'app',
  middleware: ['auth'],
})

type Actor = {
  id: number
  name: string
  email: string
  bio: string | null
  avatar_url: string | null
}

type NotificationItem = {
  id: number
  user_id: number
  actor_id: number
  type: 'follow' | 'like' | 'comment'
  post_id: number | null
  comment_id: number | null
  read_at: string | null
  created_at: string
  updated_at: string
  actor: Actor | null
  post: unknown | null
  comment: unknown | null
}

type PaginatedResponse<T> = {
  current_page: number
  data: T[]
  first_page_url: string | null
  from: number | null
  last_page: number
  last_page_url: string | null
  links: Array<{
    url: string | null
    label: string
    active: boolean
  }>
  next_page_url: string | null
  path: string
  per_page: number
  prev_page_url: string | null
  to: number | null
  total: number
}

const { $apiFetch } = useNuxtApp()

const { data: response, pending, error, refresh } = await useAsyncData<PaginatedResponse<NotificationItem>>(
  'notifications',
  () => $apiFetch('/notifications'),
)

const notifications = computed(() => response.value?.data ?? [])

onMounted(async () => {
  await $apiFetch('/notifications/read-all', {
    method: 'PATCH',
  })

  await refresh()
  await refreshNuxtData('notifications-unread-count')
})

const notificationMessage = (notification: NotificationItem) => {
  const name = notification.actor?.name ?? 'Someone'

  switch (notification.type) {
    case 'follow':
      return `${name} followed you`
    case 'like':
      return `${name} liked your post`
    case 'comment':
      return `${name} commented on your post`
    default:
      return 'You have a new notification'
  }
}

const formatDateTime = (value: string) => {
  return new Date(value).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>