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
              <p class="text-sm leading-relaxed break-words">
                <NuxtLink
                  v-if="actorPath(notification)"
                  :to="actorPath(notification)!"
                  class="font-semibold underline underline-offset-2 hover:opacity-80"
                >
                  @{{ notification.actor?.name ?? 'someone' }}
                </NuxtLink>
                <span v-else class="font-semibold">
                  @{{ notification.actor?.name ?? 'someone' }}
                </span>

                <template v-if="notification.type === 'follow'">
                  <span class="ml-1">followed you</span>
                </template>

                <template v-else-if="notification.type === 'like'">
                  <span class="ml-1">liked your post</span>
                  <NuxtLink
                    v-if="postPath(notification)"
                    :to="postPath(notification)!"
                    class="ml-1 underline underline-offset-2 hover:opacity-80"
                  >
                    "{{ postExcerpt(notification) }}"
                  </NuxtLink>
                </template>

                <template v-else-if="notification.type === 'comment'">
                  <span class="ml-1">commented</span>
                  <NuxtLink
                    v-if="commentPath(notification)"
                    :to="commentPath(notification)!"
                    class="ml-1 underline underline-offset-2 hover:opacity-80"
                  >
                    "{{ commentExcerpt(notification) }}"
                  </NuxtLink>
                  <span class="ml-1">on your post</span>
                  <NuxtLink
                    v-if="postPath(notification)"
                    :to="postPath(notification)!"
                    class="ml-1 underline underline-offset-2 hover:opacity-80"
                  >
                    "{{ postExcerpt(notification) }}"
                  </NuxtLink>
                </template>

                <template v-else-if="notification.type === 'repost'">
                  <span class="ml-1">reposted your post</span>
                  <NuxtLink
                    v-if="postPath(notification)"
                    :to="postPath(notification)!"
                    class="ml-1 underline underline-offset-2 hover:opacity-80"
                  >
                    "{{ postExcerpt(notification) }}"
                  </NuxtLink>
                </template>

                <template v-else-if="notification.type === 'quote'">
                  <span class="ml-1">quoted your post</span>
                  <NuxtLink
                    v-if="postPath(notification)"
                    :to="postPath(notification)!"
                    class="ml-1 underline underline-offset-2 hover:opacity-80"
                  >
                    "{{ postExcerpt(notification) }}"
                  </NuxtLink>
                </template>

                <template v-else-if="notification.type === 'bookmark'">
                  <span class="ml-1">bookmarked your post</span>
                  <NuxtLink
                    v-if="postPath(notification)"
                    :to="postPath(notification)!"
                    class="ml-1 underline underline-offset-2 hover:opacity-80"
                  >
                    "{{ postExcerpt(notification) }}"
                  </NuxtLink>
                </template>

                <template v-else-if="notification.type === 'comment_like'">
                  <span class="ml-1">liked your comment</span>
                  <NuxtLink
                    v-if="commentPath(notification)"
                    :to="commentPath(notification)!"
                    class="ml-1 underline underline-offset-2 hover:opacity-80"
                  >
                    "{{ commentExcerpt(notification) }}"
                  </NuxtLink>
                </template>

                <template v-else-if="notification.type === 'comment_reply'">
                  <span class="ml-1">replied to your comment</span>
                  <NuxtLink
                    v-if="commentPath(notification)"
                    :to="commentPath(notification)!"
                    class="ml-1 underline underline-offset-2 hover:opacity-80"
                  >
                    "{{ commentExcerpt(notification) }}"
                  </NuxtLink>
                  <span class="ml-1">on</span>
                  <NuxtLink
                    v-if="postPath(notification)"
                    :to="postPath(notification)!"
                    class="ml-1 underline underline-offset-2 hover:opacity-80"
                  >
                    "{{ postExcerpt(notification) }}"
                  </NuxtLink>
                </template>

                <template v-else-if="notification.type === 'comment_repost'">
                  <span class="ml-1">reposted your comment</span>
                  <NuxtLink
                    v-if="commentPath(notification)"
                    :to="commentPath(notification)!"
                    class="ml-1 underline underline-offset-2 hover:opacity-80"
                  >
                    "{{ commentExcerpt(notification) }}"
                  </NuxtLink>
                </template>

                <template v-else-if="notification.type === 'comment_quote'">
                  <span class="ml-1">quoted your comment</span>
                  <NuxtLink
                    v-if="commentPath(notification)"
                    :to="commentPath(notification)!"
                    class="ml-1 underline underline-offset-2 hover:opacity-80"
                  >
                    "{{ commentExcerpt(notification) }}"
                  </NuxtLink>
                </template>

                <template v-else-if="notification.type === 'comment_bookmark'">
                  <span class="ml-1">bookmarked your comment</span>
                  <NuxtLink
                    v-if="commentPath(notification)"
                    :to="commentPath(notification)!"
                    class="ml-1 underline underline-offset-2 hover:opacity-80"
                  >
                    "{{ commentExcerpt(notification) }}"
                  </NuxtLink>
                </template>

                <template v-else>
                  <span class="ml-1">sent you a notification</span>
                </template>
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
  type: 'follow' | 'like' | 'comment' | 'repost' | 'quote' | 'bookmark' | 'comment_like' | 'comment_reply' | 'comment_repost' | 'comment_quote' | 'comment_bookmark'
  post_id: number | null
  comment_id: number | null
  read_at: string | null
  created_at: string
  updated_at: string
  actor: Actor | null
  post: NotificationPost | null
  comment: NotificationComment | null
}

type NotificationPost = {
  id: number
  user_id: number
  title: string | null
  body: string | null
  topic: string | null
  repost_of_post_id: number | null
  quote_body: string | null
  repost_of_comment_id: number | null
}

type NotificationComment = {
  id: number
  post_id: number
  user_id: number
  body: string | null
  parent_id: number | null
  root_id: number | null
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

const actorPath = (notification: NotificationItem) => {
  return notification.actor?.id ? `/users/${notification.actor.id}` : null
}

const postPath = (notification: NotificationItem) => {
  return notification.post_id ? `/posts/${notification.post_id}` : null
}

const commentPath = (notification: NotificationItem) => {
  if (!notification.post_id) return null
  if (!notification.comment_id) return `/posts/${notification.post_id}`
  return `/posts/${notification.post_id}#comment-${notification.comment_id}`
}

const truncateText = (value: string | null | undefined, max = 28) => {
  const text = (value ?? '').trim().replace(/\s+/g, ' ')
  if (!text) return '...'
  return text.length > max ? `${text.slice(0, max)}...` : text
}

const postExcerpt = (notification: NotificationItem) => {
  return truncateText(
    notification.post?.quote_body || notification.post?.body || notification.post?.title
  )
}

const commentExcerpt = (notification: NotificationItem) => {
  return truncateText(notification.comment?.body)
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