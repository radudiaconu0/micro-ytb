<script setup>
import { ref } from 'vue'

const email = ref('')
const password = ref('')
const remember = ref(false)

const handleSubmit = () => {
    console.log('Login submitted', { email: email.value, password: password.value, remember: remember.value })
}
</script>

<template>
    <div class="w-full p-6 bg-gray-800 rounded-lg shadow-xl">
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <h2 class="text-xl font-semibold text-white mb-4">Sign in to our platform</h2>

            <div class="space-y-2">
                <label for="email" class="block text-sm font-medium text-gray-300">Your email</label>
                <input v-model="email" type="email" id="email" required
                       class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="name@company.com" />
            </div>

            <div class="space-y-2">
                <label for="password" class="block text-sm font-medium text-gray-300">Your password</label>
                <input v-model="password" type="password" id="password" required
                       class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="••••••••" />
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input v-model="remember" id="remember" type="checkbox"
                           class="w-4 h-4 border-gray-600 rounded bg-gray-700 text-blue-600 focus:ring-blue-500" />
                    <label for="remember" class="ml-2 text-sm text-gray-300">Remember me</label>
                </div>
                <a href="#" class="text-sm text-blue-500 hover:underline">Lost Password?</a>
            </div>

            <button type="submit"
                    class="w-full px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-800">
                Login to your account
            </button>

            <p class="text-sm text-gray-400">
                Not registered? <a href="#" class="text-blue-500 hover:underline">Create account</a>
            </p>
        </form>
    </div>
</template>
