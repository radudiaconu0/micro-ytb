<script setup lang="ts">
import {formatDistanceToNow} from "date-fns";
import {computed} from 'vue';
import {initials, lorelei} from "@dicebear/collection";
import {createAvatar} from "@dicebear/core";

const props = defineProps({
    video: {
        type: Object,
        required: true
    }
});

const timeAgo = computed(() => formatDistanceToNow(new Date(props.video.created_at), {addSuffix: true}));

const truncateText = (text: string, length: number) => {
    return text.length > length ? text.substring(0, length) + '...' : text;
};
const generateAvatar = (name: string) => {
    const avatar = createAvatar(initials, {
        seed: name,
    });

    return avatar.toDataUri()
};
</script>

<template>
    <div
        class="video-card bg-white dark:bg-zinc-800 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
        <a :href="`/watch?v=${video.video_code}`" class="block">
            <div class="aspect-w-16 aspect-h-9">
                <img class="object-cover w-full h-full" :src="video.thumbnails[0].thumbnail_url" :alt="video.title">
            </div>
        </a>
        <div class="p-3">
            <div class="flex">
                <div class="flex-shrink-0 mr-3">
                    <img :src="generateAvatar(video.user.name)" alt="avatar" class="w-8 h-8 rounded-full">
                </div>
                <div>
                    <a :href="`/watch?v=${video.video_code}`" class="block">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-2 mb-1">
                            {{ truncateText(video.title, 60) }}
                        </h3>
                    </a>
                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ video.user.name }}</p>
                    <div class="flex items-center text-xs text-gray-600 dark:text-gray-400">
                        <span>{{ video.views || '0' }} views</span>
                        <span class="mx-1">•</span>
                        <span>{{ timeAgo }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.video-card {
    max-width: 100%;
    width: 100%;
}

.aspect-w-16 {
    position: relative;
    padding-top: 56.25%; /* 16:9 Aspect Ratio */
}

.aspect-w-16 img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
