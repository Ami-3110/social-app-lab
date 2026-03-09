<!-- pages/search.vue -->
<template>
  <main class="mx-auto max-w-2xl space-y-6 p-6 ui-bg ui-text min-h-screen">
    <header class="space-y-4">
      <h1 class="text-xl font-bold">Search</h1>

      <form class="flex gap-2" @submit.prevent="onSubmit">
        <input
          v-model="inputQ"
          type="text"
          placeholder="Search posts, users, topics..."
          class="flex-1 rounded ui-border-all ui-bg px-3 py-2 text-sm"
        />
        <button
          type="submit"
          class="rounded px-4 py-2 text-sm ui-border-all hover:bg-gray-50 dark:hover:bg-gray-800"
        >
          Search
        </button>
      </form>

      <nav class="flex gap-2">
        <button
          type="button"
          class="rounded px-3 py-2 text-sm ui-border-all"
          :class="tab === 'posts' ? 'font-semibold' : 'ui-muted'"
          @click="setTab('posts')"
        >
          Posts
        </button>

        <button
          type="button"
          class="rounded px-3 py-2 text-sm ui-border-all"
          :class="tab === 'users' ? 'font-semibold' : 'ui-muted'"
          @click="setTab('users')"
        >
          Users
        </button>

        <button
          type="button"
          class="rounded px-3 py-2 text-sm ui-border-all"
          :class="tab === 'topics' ? 'font-semibold' : 'ui-muted'"
          @click="setTab('topics')"
        >
          Topics
        </button>
      </nav>
    </header>
    <!-- Post search-->
    <section v-if="tab === 'posts'" class="space-y-4">
      <div v-if="postsPending" class="ui-muted">Loading...</div>
      <div v-else-if="postsError" class="text-red-600">Failed to load search results.</div>
      <div v-else-if="!q" class="py-12 text-center ui-muted">
        Enter a keyword to search posts.
      </div>
      <div v-else-if="posts.length === 0" class="py-12 text-center ui-muted">
        No posts found.
      </div>

      <template v-else>
        <PostCard
          v-for="post in posts"
          :key="post.id"
          :post="post"
          :show-menu="true"
        />

        <nav
          v-if="postsData"
          class="mt-10 flex items-center justify-center gap-8"
        >
          <button
            :disabled="!postsData.prev_page_url"
            @click="go(postsData.current_page - 1)"
          >
            Prev
          </button>

          <span>{{ postsData.current_page }} / {{ postsData.last_page }}</span>

          <button
            :disabled="!postsData.next_page_url"
            @click="go(postsData.current_page + 1)"
          >
            Next
          </button>
        </nav>
      </template>
    </section>

    <!-- User search-->
    <section v-else-if="tab === 'users'" class="space-y-4">
      <div v-if="usersPending" class="ui-muted">Loading...</div>
      <div v-else-if="usersError" class="text-red-600">Failed to load users.</div>
      <div v-else-if="!q" class="py-12 text-center ui-muted">
        Enter a keyword to search users.
      </div>
      <div v-else-if="users.length === 0" class="py-12 text-center ui-muted">
        No users found.
      </div>

      <!-- user list -->
      <template v-else>
        <div
          v-for="user in users"
          :key="user.id"
          class="flex items-center gap-3 rounded ui-border-all p-3"
        >
          <NuxtLink :to="`/users/${user.id}`" class="shrink-0">
            <img
              v-if="user.avatar_url"
              :src="user.avatar_url"
              class="h-10 w-10 rounded-full object-cover"
              alt=""
            />
            <div
              v-else
              class="flex h-10 w-10 items-center justify-center rounded-full ui-border-all"
            >
              {{ user.name.charAt(0) }}
            </div>
          </NuxtLink>

          <div class="min-w-0 flex-1">
            <NuxtLink :to="`/users/${user.id}`" class="font-semibold hover:underline">
              {{ user.name }}
            </NuxtLink>

            <div v-if="user.bio" class="line-clamp-2 text-sm ui-muted">
              {{ user.bio }}
            </div>
          </div>

          <button
            v-if="user.id !== myUserId"
            type="button"
            class="rounded px-3 py-2 text-sm ui-border-all hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-50"
            :disabled="busyFollowIds.includes(user.id)"
            @click="toggleFollow(user)"
          >
            {{ user.is_following ? 'Following' : 'Follow' }}
          </button>
        </div>

        <nav
          v-if="usersData"
          class="mt-10 flex items-center justify-center gap-8"
        >
          <button
            :disabled="!usersData.prev_page_url"
            @click="go(usersData.current_page - 1)"
          >
            Prev
          </button>

          <span>{{ usersData.current_page }} / {{ usersData.last_page }}</span>

          <button
            :disabled="!usersData.next_page_url"
            @click="go(usersData.current_page + 1)"
          >
            Next
          </button>
        </nav>
      </template>
    </section>

    <!-- Topic search -->
      <section v-else-if="tab === 'topics'" class="space-y-4">
        <div v-if="topicsPending" class="ui-muted">Loading...</div>
        <div v-else-if="topicsError" class="text-red-600">Failed to load topics.</div>
        <div v-else-if="!q" class="py-12 text-center ui-muted">
          Enter a keyword to search topics.
        </div>
        <div v-else-if="topics.length === 0" class="py-12 text-center ui-muted">
          No topics found.
        </div>

        <template v-else>
          <NuxtLink
            v-for="item in topics"
            :key="item.topic"
            :to="{ path: '/', query: { topic: item.topic } }"
            class="block rounded ui-border-all p-4 hover:bg-gray-50 dark:hover:bg-gray-800"
          >
            <div class="font-semibold">
              #{{ item.topic }}
            </div>
          </NuxtLink>

          <nav
            v-if="topicsData"
            class="mt-10 flex items-center justify-center gap-8"
          >
            <button
              :disabled="!topicsData.prev_page_url"
              @click="go(topicsData.current_page - 1)"
            >
              Prev
            </button>

            <span>{{ topicsData.current_page }} / {{ topicsData.last_page }}</span>

            <button
              :disabled="!topicsData.next_page_url"
              @click="go(topicsData.current_page + 1)"
            >
              Next
            </button>
          </nav>
        </template>
      </section>
  </main>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'app',
  middleware: 'auth',
})

import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import type { Post } from '~/types/Post'
import type { PublicUser, User } from '~/types/User'

type SearchTab = 'posts' | 'users' | 'topics'

type TopicItem = {
  topic: string
}

type Pagination<T> = {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  prev_page_url?: string | null
  next_page_url?: string | null
}

// Nuxt app / router
const { $apiFetch } = useNuxtApp()
const route = useRoute()
const router = useRouter()

// Route query state
const q = computed(() => String(route.query.q ?? '').trim())
const tab = computed(() => String(route.query.tab ?? 'posts') as SearchTab)
const page = computed(() => Number(route.query.page ?? 1))

// Search form state
const inputQ = ref(q.value)

watch(
  () => route.query.q,
  () => {
    inputQ.value = q.value
  }
)

// Current user
const { data: me } = await useAsyncData<{ data: User }>('me', () =>
  $apiFetch('/me')
)

const myUserId = computed(() => me.value?.data?.id ?? null)

// Posts search
const {
  data: postsData,
  pending: postsPending,
  error: postsError,
} = await useAsyncData<Pagination<Post>>(
  () => `search-posts-${q.value}-${page.value}-${tab.value}`,
  () => {
    if (tab.value !== 'posts' || !q.value) {
      return Promise.resolve({
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
        prev_page_url: null,
        next_page_url: null,
      })
    }

    return $apiFetch('/posts', {
      params: {
        q: q.value,
        page: page.value,
      },
    })
  },
  {
    watch: [q, page, tab],
  }
)

const posts = computed(() => postsData.value?.data ?? [])

// Users search
const {
  data: usersData,
  pending: usersPending,
  error: usersError,
} = await useAsyncData<Pagination<PublicUser>>(
  () => `search-users-${q.value}-${page.value}-${tab.value}`,
  () => {
    if (tab.value !== 'users' || !q.value) {
      return Promise.resolve({
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
        prev_page_url: null,
        next_page_url: null,
      })
    }

    return $apiFetch('/users/search', {
      params: {
        q: q.value,
        page: page.value,
      },
    })
  },
  {
    watch: [q, page, tab],
  }
)

const users = computed(() => usersData.value?.data ?? [])

// Follow button state
const busyFollowIds = ref<number[]>([])

async function toggleFollow(user: PublicUser) {
  if (user.id === myUserId.value) return
  if (busyFollowIds.value.includes(user.id)) return

  busyFollowIds.value.push(user.id)

  try {
    if (user.is_following) {
      await $apiFetch(`/users/${user.id}/follow`, {
        method: 'DELETE',
      })
      user.is_following = 0
    } else {
      await $apiFetch(`/users/${user.id}/follow`, {
        method: 'POST',
      })
      user.is_following = 1
    }
  } finally {
    busyFollowIds.value = busyFollowIds.value.filter((id) => id !== user.id)
  }
}

// Topics search
const {
  data: topicsData,
  pending: topicsPending,
  error: topicsError,
} = await useAsyncData<Pagination<TopicItem>>(
  () => `search-topics-${q.value}-${page.value}-${tab.value}`,
  () => {
    if (tab.value !== 'topics' || !q.value) {
      return Promise.resolve({
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
        prev_page_url: null,
        next_page_url: null,
      })
    }

    return $apiFetch('/topics/search', {
      params: {
        q: q.value,
        page: page.value,
      },
    })
  },
  {
    watch: [q, page, tab],
  }
)

const topics = computed(() => topicsData.value?.data ?? [])

// Search actions
function onSubmit() {
  router.push({
    path: '/search',
    query: {
      q: inputQ.value.trim(),
      tab: 'posts',
      page: '1',
    },
  })
}

function setTab(nextTab: SearchTab) {
  router.push({
    path: '/search',
    query: {
      ...route.query,
      tab: nextTab,
      page: '1',
    },
  })
}

function go(nextPage: number) {
  const activeData =
    tab.value === 'users'
      ? usersData.value
      : tab.value === 'topics'
        ? topicsData.value
        : postsData.value

  if (!activeData) return
  if (nextPage < 1 || nextPage > activeData.last_page) return

  router.push({
    path: '/search',
    query: {
      ...route.query,
      page: String(nextPage),
    },
  })
}
</script>
