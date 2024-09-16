<script setup lang="ts">
import {useQuery} from "@vue/apollo-composable";
import {ref} from "vue";
import gql from "graphql-tag";

const search = ref('');
const videos = ref([]);
const SEARCH_VIDEO_QUERY = gql`
    query SearchVideos($search: String!) {
        searchVideos(first: 10, page: 1, query: $search) {
            data {
                url
                title,
                description,
                thumbnails {
                    thumbnail_url
                }
            }
        }
    }
`;

const searchVideos = () => {
    const {data, loading, error, onResult, onError} = useQuery(SEARCH_VIDEO_QUERY, {
        search: search.value
    })

    onResult((result) => {
        videos.value = result.data.searchVideos.data;
        console.log(videos.value);
    })
}


</script>

<template>
    <!--     searchbar with autocomplete-->
    <div class="relative">
        <input type="text" v-model="search" @input="searchVideos"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-blue-500 focus:border-blue-500"/>
        <div class="absolute top-12 left-0 w-full bg-white dark:bg-zinc-900 rounded-lg shadow-lg overflow-hidden">
            <div class="p-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Search Results</h3>
                <div class="flex flex-col gap-4">
                    <div v-for="video in videos" :key="video.url"
                         class="bg-white dark:bg-zinc-900 rounded-lg shadow-lg overflow-hidden">
                        <div class="relative pb-9/16">
                            <img :src="video.thumbnails[2].thumbnail_url" alt="Video thumbnail"
                                 class="absolute inset-0 w-full h-full object-cover"/>
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">{{ video.title }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ video.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<style scoped>

</style>
