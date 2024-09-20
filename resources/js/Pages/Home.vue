<script setup lang="ts">
import {onMounted, ref} from "vue";
import NavBar from "@/Components/NavBar.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import gql from "graphql-tag";
import {useQuery} from "@vue/apollo-composable";
import VideoCard from "@/Components/VideoCard.vue";
const videos = ref([]);
const VIDEO_QUERY = gql`
   query GetVideos($first: Int!, $page: Int) {
    feedVideos(first: $first, page: $page) {
            data {
            video_code,
            views
                url,
                title,
                description,
                user {
                    name
                },
                thumbnails {
                    thumbnail_url,
                    width,
                    height,
                    size
                }
                created_at
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
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <VideoCard v-for="video in videos" :key="video.video_code" :video="video" />
            </div>
        </div>
    </GuestLayout>
</template>
<style scoped>

</style>
