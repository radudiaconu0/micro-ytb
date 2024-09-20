<script setup>
import {ref, watch, onMounted, computed} from "vue";
import {useRoute} from "vue-router";
import {gql} from "graphql-tag";
import {useLazyQuery, useMutation} from "@vue/apollo-composable";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import CommentSection from "@/Components/CommentSection.vue";
import {formatDistanceToNow} from "date-fns";
import {useCookies} from "@vueuse/integrations/useCookies";
import {FETCH_VIDEO} from "@/gql/queries.ts";

const route = useRoute();
const video = ref(null);
const loading = ref(false);
const error = ref(null);
const videoCode = computed(() => route.query.v);

const INCREASE_VIEW_COUNT = gql`
    mutation IncreaseViewCount($video_code: String!) {
        incrementViewCount(video_code: $video_code) {
            success,
            message,
            views_count
        }
    }
`;

const cookies = useCookies(['viewed_videos']);
const { mutate: incrementViewCount, onDone } = useMutation(INCREASE_VIEW_COUNT, {
    video_code: videoCode.value
});



const {load: fetchVideoQuery, result} = useLazyQuery(FETCH_VIDEO, {
    video_code: videoCode.value
});

const fetchVideo = async () => {
    loading.value = true;
    error.value = null;
    try {
        await fetchVideoQuery();
        if (result.value?.fetchVideo) {
            video.value = result.value.fetchVideo;
        } else {
            error.value = "Video not found.";
        }
    } catch (e) {
        console.error("Error fetching video:", e);
        error.value = "An error occurred while fetching the video.";
    } finally {
        loading.value = false;
    }
};

watch(
    () => route.query.v,
    (newV) => {
        if (newV) {
            fetchVideo();
        }
    },
    {immediate: true}
)

const viewCount = ref(0);

onMounted(async () => {
    const viewedVideos = cookies.get('viewed_videos') || {};
    const lastViewedTime = viewedVideos[videoCode.value] || 0;
    const currentTime = Date.now();

    if (currentTime - lastViewedTime > 60 * 10) {
        try {
            await incrementViewCount({ video_code: videoCode.value });
        } catch (error) {
            console.error('Failed to increment view count:', error);
        }

        viewedVideos[videoCode.value] = currentTime;
        cookies.set('viewed_videos', viewedVideos, {
            expires: new Date(Date.now() + 365 * 24 * 60 * 60 * 1000)
        });
    }
});

onDone((result) => {
    if (result.data?.incrementViewCount?.success) {
        viewCount.value = result.data.incrementViewCount.views_count;
    }
});


</script>

<template>
    <GuestLayout>
        <div v-if="loading" class="flex justify-center items-center min-h-screen">
            <div class="flex flex-col items-center">
                <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-blue-500 mb-4"></div>
                <p class="text-gray-700">Loading video...</p>
            </div>
        </div>

        <div v-else-if="error" class="flex justify-center items-center min-h-screen">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative max-w-md text-center">
                <strong class="font-bold">Error:</strong>
                <span class="block sm:inline">{{ error }}</span>
            </div>
        </div>
        <div v-else-if="video" class="max-w-4xl mx-auto p-4">
            <div class="aspect-w-16 aspect-h-9 mb-4">
                <video
                    controls
                    class="w-full h-full object-cover rounded-lg"
                    :src="video.url"
                    :poster="video.thumbnails[0]?.thumbnail_url"
                >
                    Your browser does not support the video tag.
                </video>
            </div>
            <div class="bg-white shadow-md rounded-lg overflow-hidden dark:bg-zinc-900">
                <div class="p-4">
                    <h1 class="text-2xl font-bold mb-2 dark:text-white">{{ video.title }}</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ video.user.name }} • {{ video.views }} views • {{ formatDistanceToNow(new Date(video.created_at)) }} ago
                    </p>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-gray-700 whitespace-pre-line dark:text-gray-300"
                    >{{ video.description }}</p>
                </div>
            </div>
            <CommentSection :video-code="videoCode"/>
        </div>

        <div v-else class="flex justify-center items-center min-h-screen">
            <div class="text-gray-700">No video to display.</div>
        </div>
    </GuestLayout>
</template>
