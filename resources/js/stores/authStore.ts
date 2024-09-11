import { ref } from 'vue'
import { defineStore } from 'pinia'
import { useQuery, useMutation } from '@vue/apollo-composable'
import gql from 'graphql-tag'

const LOGIN_MUTATION = gql`
    mutation login($email: String!, $password: String!) {
        login(email: $email, password: $password) {
            access_token
        }
    }
`

const LOGOUT_MUTATION = gql`
    mutation Logout {
        logout {
            message
        }
    }
`

const CURRENT_USER_QUERY = gql`
    query {
        me {
            id
            name
            email
        }
    }
`

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const isAuthenticated = ref(false)

    const { mutate: loginMutation } = useMutation(LOGIN_MUTATION)
    const { mutate: logoutMutation } = useMutation(LOGOUT_MUTATION)
    const { refetch: refetchCurrentUser, onError } = useQuery(CURRENT_USER_QUERY)
    onError((error) => {
        console.error('Fetch user error:', error)
    });

    const login = async (credentials) => {
        try {
            const { data } = await loginMutation(credentials)

            if (data.login && data.login.accessToken) {
                document.cookie = `access_token=${data.login.accessToken}; path=/; secure; samesite=strict`
                isAuthenticated.value = true
                await fetchCurrentUser()
            } else {
                throw new Error('Login failed')
            }
        } catch (error) {
            console.error('Login error:', error)
            throw error
        }
    }

    const logout = async () => {
        try {
            await logoutMutation()
            document.cookie = 'access_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT'
            user.value = null
            isAuthenticated.value = false
        } catch (error) {
            console.error('Logout error:', error)
            throw error
        }
    }

    const fetchCurrentUser = async () => {
        try {
            const { data } = await refetchCurrentUser()
            console.log(data)

            if (data.me) {
                user.value = data.me
                isAuthenticated.value = true
            } else {
                throw new Error('Failed to fetch user')
            }
        } catch (error) {
            console.error('Fetch user error:', error)
            throw error
        }
    }

    return { user, isAuthenticated, login, logout, fetchCurrentUser }
})
