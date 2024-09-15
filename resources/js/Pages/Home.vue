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
            title,
            }

        }
    }
`;

const {data, loading, error, onResult, onError} = useQuery(VIDEO_QUERY, {
    first: 10,
    page: 1
})

onMounted(() => {
    onResult((result) => {
        console.log(result);
    });
    onError((error) => {
        console.error(error);
    });
});

</script>

<template>
<GuestLayout>

</GuestLayout>
</template>

<style scoped>

</style>
