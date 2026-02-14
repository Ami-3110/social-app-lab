// ~/composables/useFormApiErrors.ts
/**
 * API から返ってくるバリデーションエラーを
 * vee-validate の setErrors に流し込むヘルパー
 */
export function useFormApiErrors(
    setErrors: (errors: Record<string, string>) => void
) {
    return function applyApiErrors(e: any): string {
        // API エラーの形式に合わせて正規化する
        const data = e?.data || e

        // Laravel バリデーションエラー（422）
        if (data?.status === 422 && data?.errors) {
            const formErrors: Record<string, string> = {}
            for (const key in data.errors) {
                formErrors[key] = data.errors[key][0]
            }
            setErrors(formErrors)
            return data.message || '入力内容を確認してください。'
        }

        // メッセージ系
        if (data?.message) {
            return data.message
        }

        return 'エラーが発生しました。'
    }
}
