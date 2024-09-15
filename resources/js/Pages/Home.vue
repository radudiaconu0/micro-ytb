<script setup lang="ts">
import {onMounted, ref} from "vue";
import NavBar from "@/Components/NavBar.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import gql from "graphql-tag";
import {useQuery} from "@vue/apollo-composable";
const videos = ref([]);
const VIDEO_QUERY = gql`
   query GetVideos($first: Int!, $page: Int) {
    feedVideos(first: $first, page: $page) {
            data {
                url,
                title,
                description,
                thumbnails {
                    thumbnail_url,
                    width,
                    height,
                    size
                }
           }
        }
    }
`;

const {data, loading, error, onResult, onError} = useQuery(VIDEO_QUERY, {
    first: 10,
    page: 1
})
onResult((result) => {
    videos.value = result.data?.feedVideos?.data;
    console.log(videos.value);
})
</script>

<template>
<GuestLayout>
    <div class="container mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="video in videos" :key="video.url" class="dark:bg-gray-800 bg-white rounded-lg shadow-lg">
                <img :src="video.thumbnails[0].thumbnail_url" alt="thumbnail" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h2 class="font-bold text-lg">{{video.title}}</h2>
                    <p class="mt-2 text-gray-600">{{video.description}}</p>
                </div>
            </div>
        </div>
    </div>

</GuestLayout>
</template>

<style scoped>

</style>
