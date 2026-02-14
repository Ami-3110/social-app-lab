<!-- app/components/TlTabsHeader.vue -->
<template>
  <header class="sticky top-0 ui-bg backdrop-blur border-b border-zinc-200 dark:border-zinc-600">
    <div class="flex items-center justify-between p-3 gap-3">
      <div class="flex items-center gap-2">
        <button
          v-for="t in tabs"
          :key="t"
          class="px-3 py-1 rounded-full text-sm ui-border ui-menu-item"
          :class="activeTab === t
            ? 'font-semibold bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 border-zinc-900 dark:border-zinc-100'
            : 'ui-muted'"
          @click="onClickTab(t)"
          type="button"
        >
          {{ t }}
        </button>
      </div>

      <NuxtLink to="/posts/new" class="h-10 w-10 flex items-center justify-center rounded-full
              bg-blue-600 text-white text-xl font-bold
              hover:bg-blue-500 transition shadow-sm hover:shadow-md"
        aria-label="New Post"
      >
        +
      </NuxtLink>
    </div>
  </header>
</template>


<script setup lang="ts">
const tabs = ['All', 'Following', 'For you', 'Topic'] as const
type Tab = typeof tabs[number]

const activeTab = defineModel<Tab>('activeTab', { required: true })
const router = useRouter()

const onClickTab = (t: Tab) => {
  if (t === 'Topic') {
    router.push('/search?mode=topic')
    return
  }
  activeTab.value = t
}
</script>
