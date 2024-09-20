<script setup lang="ts">
import {useLazyQuery, useQuery} from "@vue/apollo-composable";
import {onMounted, ref, watch} from "vue";
import {useRoute} from "vue-router";
import gql from "graphql-tag";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import LoadingSpinner from "@/Components/LoadingSpinner.vue";
import {formatDistanceToNow} from "date-fns";

const route = useRoute();
const query = ref(route.query.search_query as string);

watch(() => route.query.search_query, (newQuery) => {
    query.value = newQuery as string;
});


const videos = ref([]);
const SEARCH_VIDEO_QUERY = gql`
    query SearchVideos($search: String!) {
        searchVideos(first: 10, page: 1, query: $search) {
            data {
                video_code,
                views
                url
                title,
                description,
                thumbnails {
                    thumbnail_url
                }
                created_at
            }
        }
    }
`;
const {load: fetchData, loading, result, onResult, onError} = useLazyQuery(SEARCH_VIDEO_QUERY, {
    search: query.value
})

watch(
    () => route.query.search_query,
    async newQuery => {
        try {
            console.log(newQuery);
            query.value = newQuery as string;
            await fetchData();
            videos.value = result.value?.searchVideos.data;
        } catch (e) {
            console.error(e);
        }
    }
)

onMounted(async () => {
   try {
         await fetchData();
         videos.value = result.value?.searchVideos.data;
    } catch (e) {
         console.error(e);
   }
})

</script>

<template>
    <GuestLayout>
        <div class="flex justify-center">
            <div class="w-full max-w-2xl">
                <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-lg overflow-hidden">
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Search Results</h3>
                        <div class="flex flex-col gap-4">
                            <div v-if="loading && videos.length === 0" class="flex justify-center items-center h-64">
                                <LoadingSpinner />
                            </div>
                            <div v-for="video in videos" :key="video.url"
                                 class="bg-white dark:bg-zinc-800 rounded-lg shadow-md overflow-hidden">
                                <a :href="'/watch?v=' + video.video_code"
                                   class="flex flex-col sm:flex-row items-center hover:bg-gray-50 dark:hover:bg-zinc-700 transition duration-150 ease-in-out">
                                    <img class="object-cover w-full h-48 sm:w-32 sm:h-24 rounded-t-lg sm:rounded-none sm:rounded-l-lg" :src="video.thumbnails[2].thumbnail_url" alt="Thumbnail"/>
                                    <div class="flex flex-col justify-between p-4 leading-normal">
                                        <h5 class="mb-2 text-xl font-bold tracking-tight text-gray-900 dark:text-white">{{video.title}}</h5>
                                        <p class="text-sm text-gray-700 dark:text-gray-400">{{video.description}}</p>
                                        <div class="flex items center justify-between mt-4">
                                            <div class="flex items center text-xs text-gray-600 dark:text-gray-400">
                                                <span>{{video.views}} views</span>
                                                <span class="mx-1">•</span>
                                                <span>{{formatDistanceToNow(new Date(video.created_at), {addSuffix: true})}}</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>

</style>
