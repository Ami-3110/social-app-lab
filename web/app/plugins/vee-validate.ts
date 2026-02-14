// plugins/vee-validate.ts
import { defineNuxtPlugin } from 'nuxt/app'
import { defineRule, configure } from 'vee-validate'
import { required, email, min } from '@vee-validate/rules'

export default defineNuxtPlugin(() => {
  // ルール登録
    defineRule('required', required)
    defineRule('email', email)
    defineRule('min', min)

  // バリデーションの発火タイミング
    configure({
        validateOnBlur: true,
        validateOnChange: true,
        validateOnInput: false,
        validateOnModelUpdate: true,
    })
})
