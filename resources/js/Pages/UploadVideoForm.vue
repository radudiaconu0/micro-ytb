<script setup lang="ts">
import { ref, watch } from 'vue'
import GuestLayout from "@/Layouts/GuestLayout.vue";
import axios from "axios";
import { useCookies } from "@vueuse/integrations/useCookies";
import Btn from "@/Components/Btn.vue";

const loading = ref(false)
const errors = ref<Record<string, string[]>>({})
const successMessage = ref('')

const form = ref({
    title: '',
    description: '',
    video: null as File | null,
    thumbnail: null as File | null,
    thumbnailOption: 'frame',
    frameSelector: 0,
    watermarkType: 'image',
    watermarkText: '',
    watermarkPosition: 'bottom-right',
    watermarkImage: null as File | null,
})

const videoSrc = ref<string | null>(null)
const videoFile = ref<HTMLVideoElement | null>(null)
const currentFrame = ref<HTMLCanvasElement | null>(null)

const onFrameSelectorChange = () => {
    const video = videoFile.value
    const canvas = currentFrame.value
    if (!video || !canvas) return

    const ctx = canvas.getContext('2d')
    if (!ctx) return

    video.currentTime = (form.value.frameSelector / 100) * video.duration
    video.addEventListener('seeked', () => {
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height)
    })
    canvas.toBlob((blob) => {
        if (blob) {
            form.value.thumbnail = new File([blob], 'thumbnail.jpg', { type: 'image/jpeg' })
        }
    }, 'image/jpeg')
}

const handleFileUpload = (e: Event) => {
    const target = e.target as HTMLInputElement
    if (target.files && target.files.length > 0) {
        form.value.video = target.files[0]
        const reader = new FileReader()
        reader.onload = (e) => {
            videoSrc.value = e.target?.result as string
        }
        reader.readAsDataURL(target.files[0])
    }
}

const handleSubmit = async () => {
    loading.value = true
    errors.value = {}
    successMessage.value = ''
    const formData = new FormData()
    formData.append('title', form.value.title)
    formData.append('description', form.value.description)
    if (form.value.video) formData.append('video_file', form.value.video)
    if (form.value.thumbnailOption === 'custom' && form.value.thumbnail) {
        formData.append('thumbnail_image', form.value.thumbnail)
    }
    formData.append('watermark_type', form.value.watermarkType)
    if (form.value.watermarkType === 'image' && form.value.watermarkImage) {
        formData.append('watermark_image', form.value.watermarkImage)
    } else if (form.value.watermarkType === 'text') {
        formData.append('watermark_text', form.value.watermarkText)
    }
    formData.append('watermark_position', form.value.watermarkPosition)

    const cookies = useCookies(['access_token'])
    try {
        const response = await axios.post('/api/store-video', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'Authorization': `Bearer ${localStorage.getItem('access_token')}`
            }
        })
        successMessage.value = response.data.message || 'Video uploaded successfully! It is now being processed.'
    } catch (error: any) {
        if (error.response) {
            if (error.response.status === 422) {
                errors.value = error.response.data.errors
            } else {
                errors.value = { general: ['An error occurred while uploading the video. Please try again.'] }
            }
        } else if (error.request) {
            errors.value = { general: ['No response received from the server. Please check your connection and try again.'] }
        } else {
            errors.value = { general: ['An unexpected error occurred. Please try again.'] }
        }
        console.error('Error details:', error)
    } finally {
        loading.value = false
    }
}

const handleThumbnailUpload = (e: Event) => {
    const target = e.target as HTMLInputElement
    if (target.files && target.files.length > 0) {
        form.value.thumbnail = target.files[0]
    }
}

const handleWatermarkUpload = (e: Event) => {
    const target = e.target as HTMLInputElement
    if (target.files && target.files.length > 0) {
        form.value.watermarkImage = target.files[0]
    }
}

watch(() => form.value.video, (newVideo) => {
    if (newVideo && videoFile.value) {
        videoFile.value.src = URL.createObjectURL(newVideo)
    }
})
</script>

<template>
    <GuestLayout>
        <div class="container mx-auto p-4">
            <div class="card bg-white dark:bg-gray-800 shadow-xl rounded-lg overflow-hidden">
                <div class="card-body p-6">
                    <h2 class="card-title text-2xl font-bold mb-6 text-gray-900 dark:text-white">Video Upload</h2>

                    <!-- Success Message -->
                    <div v-if="successMessage" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        {{ successMessage }}
                    </div>

                    <!-- General Error Messages -->
                    <div v-if="errors.general" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <p v-for="error in errors.general" :key="error">{{ error }}</p>
                    </div>

                    <div class="flex flex-col md:flex-row gap-6">
                        <!-- Left side: Form fields -->
                        <div class="w-full md:w-1/2">
                            <form @submit.prevent="handleSubmit" class="space-y-4">
                                <!-- Title Input -->
                                <div>
                                    <label for="title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Video Title</label>
                                    <input type="text" id="title" v-model="form.title"
                                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                           placeholder="Enter video title" required>
                                    <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title[0] }}</p>
                                </div>

                                <!-- Description Input -->
                                <div>
                                    <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description</label>
                                    <textarea id="description" v-model="form.description" rows="4"
                                              class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                              placeholder="Write your video description here"></textarea>
                                    <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</p>
                                </div>

                                <!-- Video Upload Input -->
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="video_upload">Upload video</label>
                                    <input @change="handleFileUpload"
                                           class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                           id="video_upload" type="file" accept="video/*">
                                    <p v-if="errors.video_file" class="mt-1 text-sm text-red-600">{{ errors.video_file[0] }}</p>
                                </div>

                                <!-- Thumbnail options -->
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Thumbnail</label>
                                    <div class="flex items-center mb-2">
                                        <input type="radio" id="custom_thumbnail" v-model="form.thumbnailOption"
                                               value="custom" class="mr-2">
                                        <label for="custom_thumbnail" class="text-sm text-gray-900 dark:text-white">Upload custom thumbnail</label>
                                    </div>
                                    <div v-if="form.thumbnailOption === 'custom'">
                                        <input @change="handleThumbnailUpload"
                                               class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                               type="file" accept="image/*">
                                    </div>
                                    <div class="flex items-center mb-2">
                                        <input type="radio" id="video_frame" v-model="form.thumbnailOption"
                                               value="frame" class="mr-2">
                                        <label for="video_frame" class="text-sm text-gray-900 dark:text-white">Select frame from video</label>
                                    </div>
                                    <div v-if="form.thumbnailOption === 'frame'">
                                        <input @input="onFrameSelectorChange" type="range" v-model="form.frameSelector"
                                               min="0" max="100" step="1"
                                               class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                                        <canvas ref="currentFrame" width="320" height="180"
                                                class="mt-2 w-full h-auto rounded-lg bg-gray-100 dark:bg-gray-700"></canvas>
                                    </div>
                                    <p v-if="errors.thumbnail_image" class="mt-1 text-sm text-red-600">{{ errors.thumbnail_image[0] }}</p>
                                </div>

                                <!-- Watermark options -->
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Watermark</label>
                                    <div class="flex items-center mb-2">
                                        <input type="radio" id="image_watermark" v-model="form.watermarkType"
                                               value="image" class="mr-2">
                                        <label for="image_watermark" class="text-sm text-gray-900 dark:text-white">Image watermark</label>
                                    </div>
                                    <div v-if="form.watermarkType === 'image'">
                                        <input @change="handleWatermarkUpload"
                                               class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                               type="file" accept="image/*">
                                    </div>
                                    <div class="flex items-center mb-2">
                                        <input type="radio" id="text_watermark" v-model="form.watermarkType"
                                               value="text" class="mr-2">
                                        <label for="text_watermark" class="text-sm text-gray-900 dark:text-white">Text watermark</label>
                                    </div>
                                    <div v-if="form.watermarkType === 'text'">
                                        <input type="text" v-model="form.watermarkText"
                                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                               placeholder="Enter watermark text">
                                    </div>
                                    <div class="mt-2">
                                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Watermark Position</label>
                                        <div class="grid grid-cols-3 gap-2">
                                            <label v-for="position in ['top-left', 'top-right', 'bottom-left', 'bottom-right', 'center']" :key="position" class="flex items-center">
                                                <input type="radio" v-model="form.watermarkPosition" :value="position" class="mr-2">
                                                <span class="text-sm text-gray-900 dark:text-white">{{ position.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <p v-if="errors.watermark_image" class="mt-1 text-sm text-red-600">{{ errors.watermark_image[0] }}</p>
                                    <p v-if="errors.watermark_text" class="mt-1 text-sm text-red-600">{{ errors.watermark_text[0] }}</p>
                                </div>

                                <Btn color="blue" :loading="loading" :disabled="loading">
                                    {{ loading ? 'Uploading...' : 'Upload Video' }}
                                </Btn>
                            </form>
                        </div>

                        <div class="w-full md:w-1/2">
                            <div v-if="videoSrc" class="aspect-w-16 aspect-h-9">
                                <video :src="videoSrc" controls ref="videoFile"
                                       class="rounded-lg w-full h-full object-cover"></video>
                            </div>
                            <div v-else
                                 class="flex items-center justify-center h-64 bg-gray-100 rounded-lg dark:bg-gray-700">
                                <p class="text-gray-500 dark:text-gray-400">Video preview will appear here</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>

.aspect-w-16 {
    position: relative;
    padding-bottom: 56.25%;
}
.aspect-w-16 > * {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}
</style>
