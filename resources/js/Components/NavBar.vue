<script setup lang="ts">
import {ref, onMounted, watch} from 'vue';
import {useAuthStore} from "@/stores/authStore";
import debounce from 'lodash/debounce';
import {useLazyQuery} from "@vue/apollo-composable";
import gql from "graphql-tag";
import router from "@/router";


const user = ref(null);
const isMenuOpen = ref(false);
const searchQuery = ref('');
const searchResults = ref([]);
const showResults = ref(false);

onMounted(() => {
    user.value = useAuthStore().user;
});

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

const hideResults = () => {
    showResults.value = false;
};

const SEARCH_VIDEO_QUERY = gql`
    query SearchVideos($search: String!) {
        searchVideos(first: 10, page: 1, query: $search) {
            data {
            video_code,
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

const {load: searchVideos, result} = useLazyQuery(SEARCH_VIDEO_QUERY, {
    search: searchQuery
});

const search = async () => {
    try {
        await searchVideos();
        console.log(result.value);
        if (result.value && result.value.searchVideos.data) {
            searchResults.value = result.value.searchVideos.data;
            showResults.value = true;
            console.log(searchResults.value);
        }
    } catch (e) {
        console.error(e);
    }
};

const selectResult = (result) => {
    router.push(`/watch?v=${result.video_code}`);
};
</script>

<template>
    <nav class="bg-[#0f0f0f] shadow-lg">
        <div class="max-w-full mx-auto px-4">
            <div class="flex items-center justify-between h-14">
                <div class="flex items-center">
                    <button @click="toggleMenu" type="button"
                            class="p-2 rounded-full text-gray-400 hover:bg-gray-800 focus:outline-none">
                        <span class="sr-only">Open main menu</span>
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <a href="/" class="flex-shrink-0 ml-4">
                        <img class="h-6" src="https://www.youtube.com/s/desktop/8a85ed3f/img/favicon_144x144.png"
                             alt="YouTube Logo"/>
                    </a>
                </div>
                <div class="flex-1 flex justify-center px-2 lg:ml-6 lg:justify-center">
                    <div class="max-w-lg w-full lg:max-w-2xl">
                        <div class="relative">
                            <input
                                @input="search"
                                v-model="searchQuery"
                                @focus="showResults = true"
                                @blur="hideResultsDelayed"
                                type="text"
                                class="block w-full bg-[#121212] border border-[#303030] rounded-l-full py-2 pl-4 pr-10 text-sm placeholder-gray-400 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:placeholder-gray-500"
                                placeholder="Search"
                            />
                            <button
                                class="absolute inset-y-0 right-0 flex items-center justify-center px-4 bg-[#222222] rounded-r-full border border-[#303030] border-l-0 hover:bg-[#3f3f3f] focus:outline-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                     viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                          clip-rule="evenodd"/>
                                </svg>
                            </button>
                            <div v-if="showResults && searchResults.length > 0"
                                 class="absolute mt-1 w-full bg-[#212121] rounded-md shadow-lg max-h-60 overflow-auto z-10">
                                <ul class="py-1">
                                    <li v-for="result in searchResults" :key="result.url"
                                        @mousedown="selectResult(result)"
                                        class="px-4 py-2 hover:bg-[#3f3f3f] cursor-pointer text-white">
                                        {{ result.title }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center">
                    <button class="p-2 rounded-full text-gray-400 hover:bg-gray-800 focus:outline-none">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </button>
                    <button v-if="user"
                            class="ml-4 flex items-center justify-center h-8 w-8 rounded-full bg-red-500 text-white font-bold text-sm focus:outline-none">
                        {{ user.name.charAt(0) }}
                    </button>
                </div>
            </div>
        </div>
    </nav>
</template>
