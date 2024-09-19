<script setup lang="ts">
import {formatDistanceToNow} from "date-fns";
const props = defineProps({
    video: Object
});

const timeAgo = formatDistanceToNow(new Date(props.video.created_at), {addSuffix: true});
console.log(props.video);
</script>

<template>
    <div class="max-w-sm bg-white border border-gray-200 rounded-lg shadow dark:bg-zinc-800 dark:border-gray-700">
        <a :href="`/watch?v=${video.video_code}`">
            <img class="rounded-t-lg" :src="video.thumbnails[0].thumbnail_url" alt="Video thumbnail">
        </a>
        <div class="p-5">
            <a href="#">
                <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ video.title }}</h5>
            </a>
            <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">{{ video.description }}</p>
            <div class="flex items-center justify-between">
                <div class="flex items center">
                    <p class="text-sm text-gray-700 dark:text-gray-400">{{ video.user.name }}</p>
                    <p class="text-sm text-gray-700 dark:text-gray-400">•</p>
                    <p class="text-sm text-gray-700 dark:text-gray-400">{{ timeAgo }}</p>

                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
