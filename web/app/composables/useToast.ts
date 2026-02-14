// ~/composables/useToast.ts
import { useState } from '#app'

export function useToast() {
    // Nuxtのグローバル状態（同じキーならどこから呼んでも同じrefを共有）
    const open = useState<boolean>('toast_open', () => false)
    const message = useState<string>('toast_message', () => '')
    function show(msg: string, ms = 1500) {
        message.value = msg
        open.value = true
        // デバッグ用ログ（動作確認できたら消してOK）
        // console.log('[toast] show:', msg)
        setTimeout(() => (open.value = false), ms)
    }

    return { open, message, show }
}
