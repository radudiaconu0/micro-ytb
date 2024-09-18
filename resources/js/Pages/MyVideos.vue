<script setup>
import {useQuery, useMutation, useLazyQuery} from '@vue/apollo-composable';
import {computed, onMounted, ref} from 'vue';
import gql from 'graphql-tag';
import GuestLayout from "@/Layouts/GuestLayout.vue";
import {useRouter} from "vue-router";
const router = useRouter();
const GET_VIDEOS = gql`
  query GetVideos($page: Int!, $perPage: Int!) {
    myVideos(page: $page, first: $perPage) {
     data {
         title
         description
         status
         created_at
         user {
            name
         }
         thumbnails {
            thumbnail_url
         }
     }
  }
  }
`;

const videos = ref([]);
const variables = {
    page: 1,
    perPage: 10
};

const {result, loading, error, load: fetchVideos} = useLazyQuery(GET_VIDEOS, {
    page: variables.page,
    perPage: variables.perPage
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString();
};

const editVideo = (code) => {
    router.push('/video/' + code + '/edit');
};

onMounted(async () => {
    await fetchVideos();
    console.log(result.value.myVideos.data);
    videos.value = result.value.myVideos.data;
});


</script>

<template>
    <GuestLayout>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold dark:text-white">Video Listing</h1>
        </div>
        <div v-if="loading" class="text-center dark:text-white">Loading...</div>
        <div v-else-if="error" class="text-center text-red-500 dark:text-red-400">Error: {{ error.message }}</div>
        <template v-else>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600">
                    <thead>
                    <tr class="bg-gray-100 dark:bg-gray-600">
                        <th class="py-2 px-4 border-b dark:border-gray-500 text-left text-xs font-semibold text-gray-600 dark:text-gray-200 uppercase tracking-wider">
                            Thumbnail
                        </th>
                        <th class="py-2 px-4 border-b dark:border-gray-500 text-left text-xs font-semibold text-gray-600 dark:text-gray-200 uppercase tracking-wider">
                            Title
                        </th>
                        <th class="py-2 px-4 border-b dark:border-gray-500 text-left text-xs font-semibold text-gray-600 dark:text-gray-200 uppercase tracking-wider">
                            Description
                        </th>
                        <th class="py-2 px-4 border-b dark:border-gray-500 text-left text-xs font-semibold text-gray-600 dark:text-gray-200 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="py-2 px-4 border-b dark:border-gray-500 text-left text-xs font-semibold text-gray-600 dark:text-gray-200 uppercase tracking-wider">
                            Created At
                        </th>
                        <th class="py-2 px-4 border-b dark:border-gray-500 text-left text-xs font-semibold text-gray-600 dark:text-gray-200 uppercase tracking-wider">
                            User
                        </th>
                        <th class="py-2 px-4 border-b dark:border-gray-500 text-left text-xs font-semibold text-gray-600 dark:text-gray-200 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="video in videos" :key="video.id" class="hover:bg-gray-100 dark:hover:bg-gray-600">
                        <td class="py-2 px-4 border-b dark:border-gray-500">
                            <img
                                v-if="video.thumbnails && video.thumbnails.length"
                                :src="video.thumbnails[0].thumbnail_url"
                                :alt="video.title"
                                class="w-24 h-auto object-cover"
                            />
                            <div v-else class="w-24 h-16 bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400 dark:text-gray-300"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </td>
                        <td class="py-2 px-4 border-b dark:border-gray-500 dark:text-white">{{ video.title }}</td>
                        <td class="py-2 px-4 border-b dark:border-gray-500 dark:text-white">{{ video.description }}</td>
                        <td class="py-2 px-4 border-b dark:border-gray-500 dark:text-white">{{ video.status }}</td>
                        <td class="py-2 px-4 border-b dark:border-gray-500 dark:text-white">
                            {{ formatDate(video.created_at) }}
                        </td>
                        <td class="py-2 px-4 border-b dark:border-gray-500 dark:text-white">{{ video.user.name }}</td>
                        <td class="py-2 px-4 border-b dark:border-gray-500">
                            <button @click="editVideo(video.video_code)"
                                    class="bg-blue-500 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-800 text-white font-bold py-1 px-2 rounded transition duration-300">
                                Watch
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex justify-between items-center text-gray-600 dark:text-gray-300">
                <div>
                    Showing {{ videos.length }} of {{ videos.length }} videos
                </div>
                <div class="space-x-2">
                    <!-- Pagination controls here -->
                    <button class="p-2 bg-gray-200 dark:bg-gray-600 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 dark:text-gray-300"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <button class="p-2 bg-gray-200 dark:bg-gray-600 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 dark:text-gray-300"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5l7 7-7 7"/>
                        </svg>

                    </button>

                </div>
            </div>
        </template>
    </GuestLayout>
</template>
