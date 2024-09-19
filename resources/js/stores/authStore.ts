import { ref } from 'vue'
import { defineStore } from 'pinia'
import { useQuery, useMutation } from '@vue/apollo-composable'
import gql from 'graphql-tag'
import router from "@/router"

const LOGIN_MUTATION = gql`
    mutation login($email: String!, $password: String!) {
        login(email: $email, password: $password) {
            access_token
            token_type
            expires_in
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
    const { refetch: refetchCurrentUser } = useQuery(CURRENT_USER_QUERY, null, { fetchPolicy: 'network-only' })

    const login = async (credentials) => {
        try {
            const { data } = await loginMutation(credentials)
            if (data?.login?.access_token) {
                console.log('Login successful:', data)
                localStorage.setItem('access_token', data.login.access_token)
                isAuthenticated.value = true
                await fetchCurrentUser()
            } else {
                alert('Login failed: No token received')
            }
        } catch (error) {
            console.error('Login error:', error)
            alert('Login failed: ' + error.message)
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
        }
    }


    return { user, isAuthenticated, login, logout, fetchCurrentUser }
})
