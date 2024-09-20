<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import GuestLayout from "@/Layouts/GuestLayout.vue";
import axios from "axios";
import Btn from "@/Components/Btn.vue";
import {useRoute, useRouter} from "vue-router";

const route = useRoute()
const router = useRouter()
const loading = ref(false)
const videoCode = route.params.code
const video = ref(null)
const videoFile = ref<HTMLVideoElement | null>(null)

const form = ref({
    title: '',
    video_code: '',
    description: '',
    thumbnail: null as File | null,
    frameSelector: 0,
    thumbnailOption: 'current',
})

const currentFrame = ref<HTMLCanvasElement | null>(null)

const onFrameSelectorChange = () => {
    if (!videoFile.value || !currentFrame.value) return;

    const video = videoFile.value
    const canvas = currentFrame.value
    const ctx = canvas.getContext('2d')

    video.currentTime = (form.value.frameSelector / 100) * video.duration
    video.addEventListener('seeked', () => {
        ctx?.drawImage(video, 0, 0, canvas.width, canvas.height)
        try {
            const dataUrl = canvas.toDataURL('image/jpeg')
            fetch(dataUrl)
                .then(res => res.blob())
                .then(blob => {
                    form.value.thumbnail = new File([blob], 'thumbnail.jpg', { type: 'image/jpeg' })
                    console.log('Frame selected and converted to File:', form.value.thumbnail)
                })
        } catch (error) {
            console.error('Error creating thumbnail:', error)
        }
    }, { once: true })
}

const handleSubmit = async () => {
    loading.value = true
    const formData = new FormData()
    formData.append('title', form.value.title)
    formData.append('description', form.value.description)
    formData.append('video_code', videoCode as string)
    formData.append('video_code', form.value.video_code)

    console.log('Thumbnail option:', form.value.thumbnailOption)
    console.log('Thumbnail file:', form.value.thumbnail)

    if (form.value.thumbnailOption !== 'current' && form.value.thumbnail) {
        formData.append('thumbnail_image', form.value.thumbnail, 'thumbnail.jpg')
        console.log('Appending thumbnail to form data')
    }

    for (let [key, value] of formData.entries()) {
        console.log(key, value)
    }

    try {
        const response = await axios.post('/api/videos/update', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'Authorization': `Bearer ${localStorage.getItem('access_token')}`
            }
        })
        console.log('Video updated successfully:', response.data)
    } catch (error) {
        console.error('Error updating video:', error)
        if (axios.isAxiosError(error) && error.response) {
            console.error('Response data:', error.response.data)
            console.error('Response status:', error.response.status)
            console.error('Response headers:', error.response.headers)
        }
        await router.push('/my-videos')
    } finally {
        loading.value = false
    }
}

const handleThumbnailUpload = (e: Event) => {
    const target = e.target as HTMLInputElement
    if (target.files) {
        form.value.thumbnail = target.files[0]
    }
}

const fetchVideoDetails = async () => {
    try {
        const response = await axios.get('/api/videos/' + videoCode, {
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('access_token')}`
            }
        })
        video.value = response.data.data
        form.value.title = video.value.title
        form.value.description = video.value.description
    } catch (error) {
        console.error('Error fetching video details:', error)
    }
}

watch(() => form.value.thumbnailOption, (newValue) => {
    if (newValue === 'frame') {
        form.value.frameSelector = 0
        onFrameSelectorChange()
    } else {
        form.value.thumbnail = null
    }
})

onMounted(async () => {
    form.value.video_code = videoCode as string
    await fetchVideoDetails()
    if (videoFile.value) {
        videoFile.value.crossOrigin = 'anonymous'
        videoFile.value.addEventListener('loadedmetadata', () => {
            if (currentFrame.value) {
                currentFrame.value.width = videoFile.value!.videoWidth
                currentFrame.value.height = videoFile.value!.videoHeight
            }
        })
    }
})
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
                            <div v-if="video" class="aspect-w-16 aspect-h-9">
                                <video :src="video.url" :poster="video.thumbnails[2].thumbnail_url"
                                       controls class="rounded-lg w-full h-full object-cover"
                                       ref="videoFile"></video>
                            </div>
                            <div v-else
                                 class="flex items-center justify-center h-full bg-gray-100 rounded-lg dark:bg-gray-700">
                                <p class="text-gray-500 dark:text-gray-400">Video preview will appear here</p>
                            </div>
                            <div class="card bg-white dark:bg-gray-800 shadow-md rounded-lg mt-4">
                                <div class="card-body p-4">
                                    <h3 class="card-title text-lg font-bold mb-2 text-gray-900 dark:text-white">Video
                                        Metadata</h3>
                                    <div v-if="video" class="space-y-2">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Duration: {{ video.metadata.duration }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Resolution: {{ video.metadata.width }}x{{ video.metadata.height }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Frame Rate: {{ video.metadata.frame_rate }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Bitrate: {{ video.metadata.bit_rate }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Video Codec: {{ video.metadata.codec_name }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Audio Codec: {{ video.metadata.audio_codec }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Audio Channels: {{ video.metadata.audio_channels }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Audio Sample Rate: {{ video.metadata.audio_sample_rate }}</p>
                                    </div>
                                </div>
                            </div>

                        </div>
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
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Thumbnail</label>
                                    <div class="flex items-center mb-2">
                                        <input type="radio" id="current_thumbnail" v-model="form.thumbnailOption"
                                               value="current" class="mr-2">
                                        <label for="current_thumbnail" class="text-sm text-gray-900 dark:text-white">Keep
                                            current thumbnail</label>
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
                                        <input @input="onFrameSelectorChange" type="range" v-model="form.frameSelector"
                                               min="0" max="100" step="1" class="w-full text-sm text-gray-900 dark:text-white">
                                        <canvas ref="currentFrame"
                                                class="w-full h-auto rounded-lg bg-gray-100 dark:bg-gray-700"></canvas>
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
