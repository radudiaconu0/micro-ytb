<script setup>
import {useLazyQuery} from '@vue/apollo-composable';
import {onMounted, ref, computed} from 'vue';
import gql from 'graphql-tag';
import GuestLayout from "@/Layouts/GuestLayout.vue";
import {useRouter} from "vue-router";
import {GET_VIDEOS} from "@/gql/queries.ts";

const router = useRouter();

const videos = ref([]);
const paginatorInfo = ref(null);
const currentPage = ref(1);
const perPage = ref(10);

const {result, loading, error, load: fetchVideos} = useLazyQuery(GET_VIDEOS, () => ({
    page: currentPage.value,
    perPage: perPage.value
}));

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString();
};

const editVideo = (code) => {
    router.push('/video/' + code + '/edit');
};


const fetchPage = async (page) => {
    currentPage.value = page;
    await fetchVideos();
    if (result.value && result.value.myVideos) {
        videos.value = result.value.myVideos.data;
        paginatorInfo.value = result.value.myVideos.paginatorInfo;
    }
};

onMounted(async () => {
    await fetchPage(1);
});

const statusColor = computed(() => (status) => {
    switch (status.toLowerCase()) {
        case 'published':
            return 'bg-green-100 text-green-800';
        case 'draft':
            return 'bg-yellow-100 text-yellow-800';
        case 'private':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
});

const showingFrom = computed(() => {
    if (!paginatorInfo.value) return 0;
    return (paginatorInfo.value.currentPage - 1) * paginatorInfo.value.perPage + 1;
});

const showingTo = computed(() => {
    if (!paginatorInfo.value) return 0;
    return Math.min(paginatorInfo.value.currentPage * paginatorInfo.value.perPage, paginatorInfo.value.total);
});

const totalVideos = computed(() => paginatorInfo.value ? paginatorInfo.value.total : 0);

</script>

<template>
    <GuestLayout>

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold dark:text-white">Video Listing</h1>
        </div>
        <div v-if="loading" class="text-center dark:text-white">
            <div
                class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-gray-900 dark:border-white"></div>
            <p class="mt-2">Loading...</p>
        </div>
        <div v-else-if="error" class="text-center text-red-500 dark:text-red-400">Error: {{ error.message }}</div>
        <template v-else-if="videos.length">
            <div class="overflow-x-auto shadow-md sm:rounded-lg">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-zinc-800 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Thumbnail</th>
                        <th scope="col" class="px-6 py-3">Title</th>
                        <th scope="col" class="px-6 py-3">Description</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3">Created At</th>
                        <th scope="col" class="px-6 py-3">User</th>
                        <th scope="col" class="px-6 py-3">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="video in videos" :key="video.video_code"
                        class="bg-white border-b dark:bg-zinc-900 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-zinc-700">
                        <td class="px-6 py-4">
                            <img
                                v-if="video.thumbnails && video.thumbnails.length"
                                :src="video.thumbnails[0].thumbnail_url"
                                :alt="video.title"
                                class="w-20 h-auto object-cover rounded"
                            />
                            <div v-else
                                 class="w-20 h-12 bg-gray-200 dark:bg-zinc-700 flex items-center justify-center rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400 dark:text-gray-300"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                            {{ video.title }}
                        </td>
                        <td class="px-6 py-4">
                            {{
                                video.description.length > 50 ? video.description.substring(0, 50) + '...' : video.description
                            }}
                        </td>
                        <td class="px-6 py-4">
                <span
                    :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', statusColor(video.status)]">
                  {{ video.status }}
                </span>
                        </td>
                        <td class="px-6 py-4">{{ formatDate(video.created_at) }}</td>
                        <td class="px-6 py-4">{{ video.user.name }}</td>
                        <td class="px-6 py-4">
                            <button @click="editVideo(video.video_code)"
                                    class="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                Edit
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="paginatorInfo" class="mt-4 flex justify-between items-center">
                <div class="text-sm text-gray-700 dark:text-gray-400">
                    Showing {{ showingFrom }} to {{ showingTo }} of {{ totalVideos }} videos
                </div>
                <div class="flex space-x-2">
                    <button
                        @click="fetchPage(paginatorInfo.currentPage - 1)"
                        :disabled="paginatorInfo.currentPage === 1"
                        class="px-3 py-1 bg-gray-200 text-gray-800 rounded-md disabled:opacity-50 dark:bg-zinc-800 dark:text-white"
                    >
                        Previous
                    </button>
                    <button
                        @click="fetchPage(paginatorInfo.currentPage + 1)"
                        :disabled="paginatorInfo.currentPage === paginatorInfo.lastPage"
                        class="px-3 py-1 bg-gray-200 text-gray-800 rounded-md disabled:opacity-50 dark:bg-zinc-800 dark:text-white"
                    >
                        Next
                    </button>
                </div>
            </div>
        </template>
        <div v-else class="text-center dark:text-white">No videos found.</div>
    </GuestLayout>
</template>
