<script setup lang="ts">
import {ref} from 'vue';
import {useRouter} from 'vue-router';
import {useAuthStore} from '@/stores/authStore';

const router = useRouter();
const authStore = useAuthStore();

const email = ref('');
const password = ref('');
const error = ref('');
const loading = ref(false);

const handleSubmit = async () => {
    error.value = '';
    loading.value = true;

    try {
        await authStore.login({
            email: email.value,
            password: password.value
        });
        await router.push('/');
    } catch (e) {
        if (e instanceof Error) {
            error.value = e.message;
        } else {
            error.value = 'An unexpected error occurred. Please try again.';
        }
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <section class="bg-gray-50 dark:bg-gray-900">
        <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
            <!-- ... (keep the existing header and logo) ... -->
            <div
                class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
                <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                    <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                        Sign in to your account
                    </h1>
                    <form class="space-y-4 md:space-y-6" @submit.prevent="handleSubmit">
                        <div v-if="error"
                             class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
                             role="alert">
                            <span class="block sm:inline">{{ error }}</span>
                        </div>
                        <div>
                            <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your
                                email</label>
                            <input type="email" name="email" id="email" v-model="email"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                   placeholder="name@company.com" required>
                        </div>
                        <div>
                            <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
                            <input type="password" name="password" id="password" v-model="password"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                   placeholder="••••••••" required>
                        </div>
                        <button type="submit" :disabled="loading"
                                class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                            {{ loading ? 'Signing in...' : 'Sign in' }}
                        </button>
                        <p class="text-sm font-light text-gray-500 dark:text-gray-400">
                            Don't have an account yet? <a href="#"
                                                          class="font-medium text-primary-600 hover:underline dark:text-primary-500">Sign
                            up</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>
</template>
