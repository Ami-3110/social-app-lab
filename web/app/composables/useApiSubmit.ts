// composables/useApiSubmit.ts
import { ref } from 'vue'
import { useForm } from 'vee-validate'
import { useFormApiErrors } from '~/composables/useFormApiErrors'

export function useApiSubmit(setErrorsFromForm?: (errors: Record<string, string>) => void) {
    const isSubmitting = ref(false)

    // ✅ setErrorsFromForm が渡されていればそれを使う
    let setErrors = setErrorsFromForm
    if (!setErrors) {
        const { setErrors: formSetErrors } = useForm()
        setErrors = formSetErrors
    }

    const applyApiErrors = useFormApiErrors(setErrors)

    async function submitWithErrors<T>(
        fn: () => Promise<T>,
        opts?: {
            onSuccess?: () => void
            successMessage?: string
        }
    ): Promise<{ data?: T; errorMessage?: string }> {
        isSubmitting.value = true
        try {
            const data = await fn()
            if (opts?.successMessage) {
                // ここでトーストを出してもOK（任意）
            }
            opts?.onSuccess?.()
            return { data }
        } catch (e: any) {
            const msg = applyApiErrors(e)
            return { errorMessage: msg }
        } finally {
            isSubmitting.value = false
        }
    }

    return { isSubmitting, submitWithErrors }
}
