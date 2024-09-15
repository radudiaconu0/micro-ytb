<script setup lang="ts">
defineProps({
    video: Object
});
</script>

<template>
    <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-lg overflow-hidden">
        <div class="relative pb-9/16">
            <img :src="video.thumbnail" alt="Video thumbnail" class="absolute inset-0 w-full h-full object-cover"/>
        </div>
        <div class="p-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">{{ video.title }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ video.description }}</p>
        </div>
    </div>
</template>

<style scoped>

</style>
