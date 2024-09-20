import { ref } from 'vue'
import { defineStore } from 'pinia'
import { useQuery, useMutation } from '@vue/apollo-composable'
import gql from 'graphql-tag'
import router from "@/router"
import {LOGIN_MUTATION, LOGOUT_MUTATION} from "@/gql/mutations";
import {CURRENT_USER_QUERY} from "@/gql/queries";



export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const isAuthenticated = ref(false)

    const { mutate: loginMutation } = useMutation(LOGIN_MUTATION)
    const { mutate: logoutMutation } = useMutation(LOGOUT_MUTATION)
    const { refetch: refetchCurrentUser } = useQuery(CURRENT_USER_QUERY, null, { fetchPolicy: 'network-only' })

    const login = async (credentials) => {
        try {
            const { data } = await loginMutation(credentials)
            if (data?.login?.access_token) {
                localStorage.setItem('access_token', data.login.access_token)
                isAuthenticated.value = true
                await fetchCurrentUser()
            } else {
                throw new Error('Login failed: No token received')
            }
        } catch (error) {
            console.error('Login error:', error)
            throw error
        }
    }

    const logout = async () => {
        try {
            await logoutMutation()
        } catch (error) {
            console.error('Logout error:', error)
        } finally {
            localStorage.removeItem('access_token')
            user.value = null
            isAuthenticated.value = false
            await router.push('/login')
        }
    }

    const fetchCurrentUser = async () => {
        try {
            const { data } = await refetchCurrentUser()
            if (data?.me) {
                user.value = data.me
                isAuthenticated.value = true
            } else {
                throw new Error('Failed to fetch user data')
            }
        } catch (error) {
            console.error('Fetch current user error:', error)
            await logout()
            throw error
        }
    }

    return { user, isAuthenticated, login, logout, fetchCurrentUser }
})
