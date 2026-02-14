<!-- components/UserCard.vue -->
<template>
  <div class="p-4 rounded-xl ui-border ui-bg flex items-center justify-between gap-4">
    <NuxtLink :to="`/users/${user.id}`" class="flex items-center gap-3 min-w-0">
      <div
        class="h-10 w-10 rounded-full ui-border flex items-center justify-center font-bold shrink-0"
        aria-hidden="true"
      >
        {{ (user.name ?? 'U').slice(0, 1).toUpperCase() }}
      </div>

      <div class="min-w-0">
        <p class="font-semibold truncate">{{ user.name }}</p>
        <p v-if="user.bio?.trim()" class="text-xs ui-muted truncate">
          {{ user.bio }}
        </p>
      </div>
    </NuxtLink>

    <button
      v-if="!isMe"
      type="button"
      class="px-3 py-1 text-sm rounded-md ui-border ui-bg hover:opacity-90 shrink-0"
      :disabled="busy"
      @click="onToggleFollow"
    >
      {{ user.is_following ? 'Unfollow' : 'Follow' }}
    </button>
  </div>
</template>

<script setup lang="ts">
type UserLite = {
  id: number
  name: string
  bio?: string | null
  is_following?: boolean
}

const props = defineProps<{
  user: UserLite
  isMe: boolean
  busy?: boolean
}>()

const emit = defineEmits<{
  (e: 'toggle-follow', userId: number, nextFollow: boolean): void
}>()

const onToggleFollow = () => {
  const nextFollow = !Boolean(props.user.is_following)
  emit('toggle-follow', props.user.id, nextFollow)
}
</script>
