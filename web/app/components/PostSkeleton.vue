<!-- app/components/PostSkeleton.vue -->
<template>
    <div class="p-5 rounded-xl border bg-white shadow-sm animate-pulse">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-gray-200"></div>
        <div class="flex-1 space-y-2">
        <div class="h-3 w-32 bg-gray-200 rounded"></div>
        <div class="h-3 w-48 bg-gray-200 rounded"></div>
        </div>
    </div>

    <div class="mt-4 space-y-2">
        <div class="h-3 w-full bg-gray-200 rounded"></div>
        <div class="h-3 w-5/6 bg-gray-200 rounded"></div>
        <div class="h-3 w-4/6 bg-gray-200 rounded"></div>
    </div>
    </div>
</template>
