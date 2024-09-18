<script setup lang="ts">
import { ref } from 'vue'
import GuestLayout from "@/Layouts/GuestLayout.vue";
import axios from "axios";
import { useCookies } from "@vueuse/integrations/useCookies";
import Btn from "@/Components/Btn.vue";
import gql from "graphql-tag";
import {useRoute} from "vue-router";

const route = useRoute()
const loading = ref(false)
const videoCode = route.params.code

const form = ref({
    title: '',
    description: '',
    thumbnail: null,
    frameSelector: 0,
    thumbnailOption: 'current',
})

const videoSrc = ref(null)
const videoFile = ref<HTMLVideoElement>(null)
const currentFrame = ref<HTMLCanvasElement>(null)

const onFrameSelectorChange = () => {
    const video = videoFile.value
    const canvas = currentFrame.value
    const ctx = canvas.getContext('2d')
    video.currentTime = (form.value.frameSelector / 100) * video.duration
    video.addEventListener('seeked', () => {
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height)
    })
    form.value.thumbnail = canvas.toDataURL('image/jpeg')
}

const handleSubmit = async () => {
    loading.value = true
    const formData = new FormData()
    formData.append('title', form.value.title)
    formData.append('description', form.value.description)
    if (form.value.thumbnailOption === 'custom') {
        formData.append('thumbnail_image', form.value.thumbnail)
    }
    let cookies = useCookies(['access_token'])
    try {
        await axios.post('/api/update-video', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'Authorization': `Bearer ${cookies.get('access_token')}`
            }
        })
    } catch (error) {
        console.log(error)
    }
    finally {
        loading.value = false
    }
}

const handleThumbnailUpload = (e) => {
    form.value.thumbnail = e.target.files[0]
}

const fetchVideoDetails = async () => {
    try {
        await axios.get('/api/video/' + videoCode,
            {
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${useCookies().get('access_token')}`
                }
            }
        ).then(response => {
            form.value.title = response.data.title
            form.value.description = response.data.description
            videoSrc.value = response.data.video_url
        })
    }
}

// Call fetchVideoDetails when the component is mounted
fetchVideoDetails()
</script>

<template>
    <GuestLayout>
        <div class="container mx-auto p-4">
            <div class="card bg-white dark:bg-gray-800 shadow-xl rounded-lg overflow-hidden">
                <div class="card-body p-6">
                    <h2 class="card-title text-2xl font-bold mb-6 text-gray-900 dark:text-white">Edit Video</h2>
                    <div class="flex flex-col md:flex-row gap-6">
                        <!-- Left side: Video preview -->
                        <div class="w-full md:w-1/2">
                            <div v-if="videoSrc" class="aspect-w-16 aspect-h-9">
                                <video :src="videoSrc" controls class="rounded-lg w-full h-full object-cover"
                                       ref="videoFile"></video>
                            </div>
                            <div v-else
                                 class="flex items-center justify-center h-full bg-gray-100 rounded-lg dark:bg-gray-700">
                                <p class="text-gray-500 dark:text-gray-400">Video preview will appear here</p>
                            </div>
                        </div>

                        <!-- Right side: Form fields -->
                        <div class="w-full md:w-1/2">
                            <form @submit.prevent="handleSubmit" class="space-y-4">
                                <div>
                                    <label for="title"
                                           class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Video
                                        Title</label>
                                    <input type="text" id="title" v-model="form.title"
                                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                           placeholder="Enter video title" required>
                                </div>
                                <div>
                                    <label for="description"
                                           class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description</label>
                                    <textarea id="description" v-model="form.description" rows="4"
                                              class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                              placeholder="Write your video description here"></textarea>
                                </div>

                                <!-- Thumbnail options -->
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Thumbnail</label>
                                    <div class="flex items-center mb-2">
                                        <input type="radio" id="current_thumbnail" v-model="form.thumbnailOption"
                                               value="current" class="mr-2">
                                        <label for="current_thumbnail" class="text-sm text-gray-900 dark:text-white">Keep current thumbnail</label>
                                    </div>
                                    <div class="flex items-center mb-2">
                                        <input type="radio" id="custom_thumbnail" v-model="form.thumbnailOption"
                                               value="custom" class="mr-2">
                                        <label for="custom_thumbnail" class="text-sm text-gray-900 dark:text-white">Upload
                                            custom thumbnail</label>
                                    </div>
                                    <div v-if="form.thumbnailOption === 'custom'">
                                        <input @change="handleThumbnailUpload"
                                               class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                               type="file" accept="image/*">
                                    </div>
                                    <div class="flex items-center mb-2">
                                        <input type="radio" id="video_frame" v-model="form.thumbnailOption"
                                               value="frame" class="mr-2">
                                        <label for="video_frame" class="text-sm text-gray-900 dark:text-white">Select
                                            frame from video</label>
                                    </div>
                                    <div v-if="form.thumbnailOption === 'frame'">
                                        <input @change="onFrameSelectorChange" type="range" v-model="form.frameSelector"
                                               min="0" :max="videoFile.value?.duration"
                                               class="w-full text-sm text-gray-900 dark:text-white">
                                        <canvas ref="currentFrame"
                                                class="w-full h-full rounded-lg bg-gray-100 dark:bg-gray-700"></canvas>
                                    </div>
                                </div>

                                <Btn color="blue" :loading="loading">Update Video</Btn>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
