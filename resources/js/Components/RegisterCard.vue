<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useMutation } from '@vue/apollo-composable'
import { REGISTER_MUTATION } from '@/gql/mutations'
import { useCookies } from '@vueuse/integrations/useCookies'
import { router } from '@inertiajs/vue3'

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
})

const errors = ref({})
const { mutate: register, onDone, onError, loading } = useMutation(REGISTER_MUTATION)

const handleSubmit = () => {
    errors.value = {}
    register({
        name: form.name,
        email: form.email,
        password: form.password,
        password_confirmation: form.password_confirmation
    })
}

onDone((result) => {
    localStorage.setItem('access_token', result.data.register.access_token)
    router.push('/')
})

onError((error) => {
    if (error.graphQLErrors) {
        error.graphQLErrors.forEach((graphQLError) => {
            if (graphQLError.extensions && graphQLError.extensions.validation) {
                errors.value = graphQLError.extensions.validation
            } else {
                errors.value.general = [graphQLError.message]
            }
        })
    }
    if (error.networkError) {
        errors.value.general = ['Network error occurred']
    }
})
</script>

<template>
    <section class="bg-gray-50 dark:bg-gray-900">
        <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
            <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
                <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                    <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                        Create an account
                    </h1>
                    <form class="space-y-4 md:space-y-6" @submit.prevent="handleSubmit">
                        <div v-if="errors.general" class="text-red-500 text-sm">
                            <p v-for="error in errors.general" :key="error">{{ error }}</p>
                        </div>

                        <div>
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your name</label>
                            <input type="text" name="name" id="name" v-model="form.name"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                   placeholder="John Doe" required>
                            <div v-if="errors.name" class="text-red-500 text-sm">{{ errors.name[0] }}</div>
                        </div>

                        <div>
                            <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your email</label>
                            <input type="email" name="email" id="email" v-model="form.email"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                   placeholder="name@company.com" required>
                            <div v-if="errors.email" class="text-red-500 text-sm">{{ errors.email[0] }}</div>
                        </div>

                        <div>
                            <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
                            <input type="password" name="password" id="password" v-model="form.password"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                   placeholder="••••••••" required>
                            <div v-if="errors.password" class="text-red-500 text-sm">{{ errors.password[0] }}</div>
                        </div>

                        <div>
                            <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Confirm password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" v-model="form.password_confirmation"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                   placeholder="••••••••" required>
                            <div v-if="errors.password_confirmation" class="text-red-500 text-sm">{{ errors.password_confirmation[0] }}</div>
                        </div>

                        <button type="submit" :disabled="loading"
                                class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                            {{ loading ? 'Registering...' : 'Register' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</template>
