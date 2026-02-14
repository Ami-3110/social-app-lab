// app/composables/usePost.ts
import type { Post } from '~/types/Post'

export const usePost = (postId?: number | string) => {
    const route = useRoute()
    const { $apiFetch } = useNuxtApp()

    const id = computed(() => postId ?? route.params.id)

    // 1件取得（/posts/[id] 用）
    const { data, pending, error, refresh } = useAsyncData<Post | null>(
        () => `post:${id.value ?? ''}`,
        async () => {
        if (!id.value) return null
        return await $apiFetch<Post>(`/posts/${id.value}`)
        },
        { default: () => null, watch: [id], server: false }
    )

    // 共通 request ラッパ
    async function request<T>(
        fn: () => Promise<T>,
        opts?: { loading?: Ref<boolean>; errorMsg?: Ref<string> }
    ) {
        const loading = opts?.loading
        const errorMsg = opts?.errorMsg

        if (loading) loading.value = true
        if (errorMsg) errorMsg.value = ''

        try {
        return await fn()
        } catch (err: any) {
        console.error(err)
        if (errorMsg) {
            errorMsg.value =
            err?.data?.message ??
            'エラーが発生しました。時間をおいて再度お試しください。'
        }
        throw err
        } finally {
        if (loading) loading.value = false
        }
    }

    // 作成
    const creating = ref(false)
    const createError = ref('')

    const create = (payload: { title: string; topic?: string; body: string }) =>
        request(
          () => $apiFetch<Post>('/posts', {
            method: 'POST',
            body: payload,
          }),
        { loading: creating, errorMsg: createError }
        )

    // 更新
    const saving = ref(false)
    const saveError = ref('')

    const update = async (payload: { title: string; topic?: string; body: string }) => {
    if (!id.value) throw new Error('Post ID is missing')

    const res = await request(
      () =>
        $apiFetch<Post>(`/posts/${id.value}`, {
          method: 'PUT',
          body: payload,
      }),
        { loading: saving, errorMsg: saveError }
    )

    await refreshNuxtData(`post:${id.value}`) // ← これ
    return res
    }


    // 削除
    const deleting = ref(false)
    const deleteError = ref('')

    const deletePost = async () => {
    if (!id.value) throw new Error('Post ID is missing')

    await request(
      () =>
        $apiFetch<void>(`/posts/${id.value}`, {
          method: 'DELETE',
        }),
      { loading: deleting, errorMsg: deleteError }
    )

    await navigateTo('/posts')
}


    return {
        id,
        data,
        pending,
        error,
        refresh,

        create,
        creating,
        createError,

        update,
        saving,
        saveError,

        deletePost,
        deleting,
        deleteError,
    }
}
