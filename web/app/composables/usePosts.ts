// composables/usePosts.ts
import type { Post } from '~/types/Post'

type PostsResponse = {
  current_page: number
  data: Post[]
  last_page: number
  prev_page_url: string | null
  next_page_url: string | null
}

type TimelineTab = 'All' | 'Following' | 'For you' | 'Topic'

type UsePostsOptions = {
  tab?: () => TimelineTab
  userId?: () => number | null
}

export const usePosts = (options: UsePostsOptions = {}) => {
  const { $apiFetch } = useNuxtApp()
  const route = useRoute()

  // ================================
  // Pagination（URLクエリ）
  // ================================
  const page = computed(() => Number(route.query.page ?? 1))

  // ================================
  // Topic（URLクエリ）
  // ================================
  const topic = computed(() => {
    const t = route.query.topic
    return typeof t === 'string' && t.trim() ? t : null
  })

  watch(topic, (newVal, oldVal) => {
    if (newVal === oldVal) return
    navigateTo({
      query: { ...route.query, page: undefined },
    })
  })

  // ================================
  // Query params（ここが肝）
  // ================================
  const params = computed(() => {
    const tab = options.tab?.() ?? 'All'

    const p: Record<string, any> = {
      page: page.value,
      per_page: 10,
      tab,
    }

    const uid = options.userId?.()
    if (uid) p.user_id = uid
    
    if (tab === 'Topic') {
      if (topic.value) p.topic = topic.value
      else p.topic = '__none__' // topic 未指定時の識別用
    }

    return p
  })

  // ================================
  // ⭐ 一覧取得
  // ================================
  const { data, pending, error, refresh } = useAsyncData<PostsResponse>(
    () => `posts:${JSON.stringify(params.value)}`,
    () =>
      $apiFetch('/posts', {
        params: params.value,
      }),
    { watch: [params], server: false }
  )

  // ================================
  // ページ遷移
  // ================================
  const go = (p: number) =>
    navigateTo({
      query: {
        ...route.query,
        page: p <= 1 ? undefined : p,
      },
    })

  // ================================
  // CRUD
  // ================================
  const create = async (payload: any) => {
    const res = await $apiFetch('/posts', {
      method: 'POST',
      body: payload,
    })
    await refresh()
    return res
  }

  const update = async (id: number, payload: any) => {
    const res = await $apiFetch(`/posts/${id}`, {
      method: 'PUT',
      body: payload,
    })
    await refresh()
    return res
  }

  const deletingId = ref<number | null>(null)
  const deleteError = ref('')

  const onDelete = async (id: number) => {
    if (deletingId.value) return
    deleteError.value = ''
    if (!confirm('Are you sure you want to delete it?')) return

    try {
      deletingId.value = id
      await $apiFetch(`/posts/${id}`, { method: 'DELETE' })
      await refresh()
    } catch (err: any) {
      deleteError.value = err?.data?.message ?? 'Failed to delete the post.'
    } finally {
      deletingId.value = null
    }
  }

  // bookmark  
  const toggleBookmark = async (post: Post) => {
    const isOn = (post.is_bookmarked ?? 0) > 0

    // 楽観更新（先に見た目を変える）※失敗したら戻す
    post.is_bookmarked = isOn ? 0 : 1

    try {
        if (isOn) {
          await $apiFetch(`/posts/${post.id}/bookmark`, { method: 'DELETE' })
        } else {
          await $apiFetch(`/posts/${post.id}/bookmark`, { method: 'POST' })
        }
        await refresh()
      } catch (e) {
        // 失敗したら戻す
        post.is_bookmarked = isOn ? 1 : 0
        throw e
      }
  }
  
  // repost
  const toggleRepost = async (post: Post) => {
    const isOn = (post.is_reposted ?? 0) > 0

    // 楽観更新
    post.is_reposted = isOn ? 0 : 1
    // カウントもついでに（無ければ0扱い）
    post.reposts_count = Math.max(0, (post.reposts_count ?? 0) + (isOn ? -1 : 1))

    try {
      await $apiFetch(`/posts/${post.id}/repost`, { method: isOn ? 'DELETE' : 'POST' })
      await refresh()
    } catch (e) {
      // 失敗したら戻す
      post.is_reposted = isOn ? 1 : 0
      post.reposts_count = Math.max(0, (post.reposts_count ?? 0) + (isOn ? 1 : -1))
      throw e
    }
  }


  return {
    data,
    pending,
    error,
    page,
    topic,

    go,
    refresh,

    onDelete,

    create,
    update,

    toggleBookmark,
    toggleRepost,

    deletingId,
    deleteError,
  }
}
