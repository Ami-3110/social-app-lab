// nuxt.config.ts
export default defineNuxtConfig({
  ssr: false,

  compatibilityDate: '2025-07-15',

  devtools: { enabled: true },

  modules: ['@nuxtjs/tailwindcss'],

  css: ['@/assets/css/tailwind.css'],

  components: [{ path: '~/components', pathPrefix: false }],

  runtimeConfig: {
    public: {
      apiBase: '/api',
    },
  },

    // 開発時のみ proxy
  nitro: {
    devProxy: {
      '/api': {
        target: 'http://localhost:8000/api',
        changeOrigin: true,
      }
    }
  },

  routeRules: {
    '/api/**': {
      proxy: 'http://localhost:8000/api/**'
    }
  },

  // ★ TypeScript設定
  typescript: {
    tsConfig: {
      compilerOptions: {
        allowJs: true,
        checkJs: false,
      }
    }
  }
})
