<script setup lang="ts">
import { onMounted, ref, watch } from "vue";
import NavBar from "@/Components/NavBar.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import gql from "graphql-tag";
import { useQuery } from "@vue/apollo-composable";
import VideoCard from "@/Components/VideoCard.vue";
import LoadingSpinner from "@/Components/LoadingSpinner.vue";

const videos = ref([]);
const page = ref(1);
const loadingMore = ref(false);
const hasMorePages = ref(true);

const VIDEO_QUERY = gql`
  query GetVideos($first: Int!, $page: Int) {
    feedVideos(first: $first, page: $page) {
      data {
        video_code,
        views,
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
        },
        created_at
      },
      paginatorInfo {
        currentPage,
        lastPage,
        total,
        perPage
      }
    }
  }
`;

const { result, loading, error, fetchMore } = useQuery(VIDEO_QUERY, {
    first: 10,
    page: page.value
});

watch(result, (newResult) => {
    if (newResult && newResult.feedVideos) {
        videos.value = newResult.feedVideos.data;
        hasMorePages.value = page.value < newResult.feedVideos.paginatorInfo.lastPage;
    }
});

const loadMoreVideos = async () => {
    if (loadingMore.value || !hasMorePages.value) return;

    loadingMore.value = true;
    page.value++;

    try {
        const { data } = await fetchMore({
            variables: {
                first: 10,
                page: page.value
            },
        });

        if (data && data.feedVideos) {
            videos.value = [...videos.value, ...data.feedVideos.data];
            hasMorePages.value = page.value < data.feedVideos.paginatorInfo.lastPage;
        }
    } catch (err) {
        console.error("Error fetching more videos:", err);
    } finally {
        loadingMore.value = false;
    }
};

const lastVideoElementRef = ref(null);

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            const lastEntry = entries[entries.length - 1];
            if (lastEntry.isIntersecting) {
                loadMoreVideos();
            }
        },
        { threshold: 0.5 }
    );

    watch(lastVideoElementRef, (newRef, oldRef) => {
        if (oldRef) {
            observer.unobserve(oldRef);
        }
        if (newRef) {
            observer.observe(newRef);
        }
    });

    return () => {
        if (lastVideoElementRef.value) {
            observer.unobserve(lastVideoElementRef.value);
        }
    };
});
</script>

<template>
    <GuestLayout>
        <div v-if="loading && videos.length === 0" class="flex justify-center items-center h-64">
            <LoadingSpinner />
        </div>
        <div v-else class="container mx-auto px-4 py-8">
            <div v-if="error">Error: {{ error.message }}</div>
            <div v-else-if="videos.length === 0">No videos available.</div>
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <template v-for="(video, index) in videos" :key="video.video_code">
                    <VideoCard
                        :video="video"
                        :ref="index === videos.length - 1 ? lastVideoElementRef : undefined"
                    />
                </template>
            </div>
            <div v-if="loadingMore" class="flex justify-center items-center mt-4">
                <LoadingSpinner />
            </div>
            <div v-if="!hasMorePages && videos.length > 0" class="text-center mt-4 dark:text-white">
                No more videos to load.
            </div>
        </div>
    </GuestLayout>
</template>
