// composables/usePostsApi.ts

export const usePostsApi = () => {
    const { $apiFetch } = useNuxtApp()

    return {
        getPosts: () => $apiFetch('/posts'),
        getPost: (id: number | string) => $apiFetch(`/posts/${id}`),
        createPost: (payload: { body: string }) =>
          $apiFetch('/posts', {
            method: 'POST',
            body: payload,
          }),
        updatePost: (id: number | string, payload: { body: string }) =>
        $apiFetch(`/posts/${id}`, {
          method: 'PUT',
          body: payload,
        }),

        deletePost: (id: number | string) =>
          $apiFetch(`/posts/${id}`, {
            method: 'DELETE',
            }),
    }
}

