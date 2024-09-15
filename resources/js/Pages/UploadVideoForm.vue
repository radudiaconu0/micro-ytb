<script setup lang="ts">
import {ref} from 'vue'
import GuestLayout from "@/Layouts/GuestLayout.vue";
import axios from "axios";
import {useCookies} from "@vueuse/integrations/useCookies";
import Btn from "@/Components/Btn.vue";

const loading = ref(false)

const form = ref({
    title: '',
    description: '',
    video: null,
    thumbnail: null,
    frameSelector: 0,
    watermarkType: 'image',
    watermarkText: '',
    watermarkPosition: 'bottom-right',
    watermarkImage: null,
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

const handleFileUpload = (e) => {
    form.value.video = e.target.files[0]
    const reader = new FileReader()
    reader.onload = (e) => {
        videoSrc.value = e.target.result
    }
    reader.readAsDataURL(e.target.files[0])
}

const handleSubmit = async () => {
    loading.value = true
    const formData = new FormData()
    formData.append('title', form.value.title)
    formData.append('description', form.value.description)
    formData.append('video_file', form.value.video)
    if (form.value.thumbnailOption === 'custom') {
        formData.append('thumbnail_image', form.value.thumbnail)
    }
    formData.append('watermark', form.value.watermark)
    formData.append('watermark_type', form.value.watermarkType)
    formData.append('watermark_text', form.value.watermarkText)
    formData.append('watermark_image', form.value.watermarkImage)
    formData.append('watermark_position', form.value.watermarkPosition)
    let cookies = useCookies(['access_token'])
    try {
        await axios.post('/api/store-video', formData, {
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

const handleWatermarkUpload = (e) => {
    form.value.watermarkImage = e.target.files[0]
}
</script>

<template>
    <GuestLayout>
        <div class="container mx-auto p-4">
            <div class="card bg-white dark:bg-gray-800 shadow-xl rounded-lg overflow-hidden">
                <div class="card-body p-6">
                    <h2 class="card-title text-2xl font-bold mb-6 text-gray-900 dark:text-white">Video Upload</h2>
                    <div class="flex flex-col md:flex-row gap-6">
                        <!-- Left side: Form fields -->
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
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                           for="video_upload">Upload video</label>
                                    <input @change="handleFileUpload"
                                           class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                           id="video_upload" type="file" accept="video/*">
                                </div>

                                <!-- Thumbnail options -->
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Thumbnail</label>
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

                                <!-- Watermark options -->
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Watermark</label>
                                    <div class="flex items-center mb-2">
                                        <input type="radio" id="image_watermark" v-model="form.watermarkType"
                                               value="image" class="mr-2">
                                        <label for="image_watermark" class="text-sm text-gray-900 dark:text-white">Image
                                            watermark</label>
                                    </div>
                                    <div v-if="form.watermarkType === 'image'">
                                        <input @change="handleWatermarkUpload"
                                               class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                               type="file" accept="image/*">
                                    </div>
                                    <div class="flex items-center mb-2">
                                        <input type="radio" id="text_watermark" v-model="form.watermarkType"
                                               value="text" class="mr-2">
                                        <label for="text_watermark" class="text-sm text-gray-900 dark:text-white">Text
                                            watermark</label>
                                    </div>
                                    <div v-if="form.watermarkType === 'text'">
                                        <input type="text" v-model="form.watermarkText"
                                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                               placeholder="Enter watermark text">
                                    </div>
                                    <div class="mt-2">
                                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Watermark
                                            Position</label>
                                        <div class="grid grid-cols-3 gap-2">
                                            <label class="flex items-center">
                                                <input type="radio" v-model="form.watermarkPosition" value="top-left"
                                                       class="mr-2">
                                                <span class="text-sm text-gray-900 dark:text-white">Top Left</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" v-model="form.watermarkPosition" value="top-right"
                                                       class="mr-2">
                                                <span class="text-sm text-gray-900 dark:text-white">Top Right</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" v-model="form.watermarkPosition" value="bottom-left"
                                                       class="mr-2">
                                                <span class="text-sm text-gray-900 dark:text-white">Bottom Left</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" v-model="form.watermarkPosition"
                                                       value="bottom-right" class="mr-2">
                                                <span class="text-sm text-gray-900 dark:text-white">Bottom Right</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" v-model="form.watermarkPosition" value="center"
                                                       class="mr-2">
                                                <span class="text-sm text-gray-900 dark:text-white">Center</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <Btn color="blue" :loading="loading">Upload Video</Btn>
                            </form>
                        </div>

                        <!-- Right side: Video preview -->
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
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
