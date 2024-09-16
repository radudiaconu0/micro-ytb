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
<!--    youtube like flex box-->

    <div class="flex flex-wrap gap-4">
        <VideoCard v-for="video in videos" :key="video.url" :video="video"/>
    </div>

</GuestLayout>
</template>

<style scoped>

</style>
