import { ref } from 'vue'
import { defineStore } from 'pinia'
import { useQuery, useMutation } from '@vue/apollo-composable'
import gql from 'graphql-tag'
import router from "@/router"
import { useCookies } from "@vueuse/integrations/useCookies"

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

const REFRESH_TOKEN_MUTATION = gql`
    mutation refreshToken {
        refreshToken {
            access_token
            token_type
            expires_in
        }
    }
`

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const isAuthenticated = ref(false)
    const cookies = useCookies(['access_token'])

    const { mutate: loginMutation } = useMutation(LOGIN_MUTATION)
    const { mutate: logoutMutation } = useMutation(LOGOUT_MUTATION)
    const { refetch: refetchCurrentUser } = useQuery(CURRENT_USER_QUERY, null, { fetchPolicy: 'network-only' })
    const { mutate: refreshTokenMutation } = useMutation(REFRESH_TOKEN_MUTATION)

    const login = async (credentials) => {
        try {
            const { data } = await loginMutation(credentials)
            if (data?.login?.access_token) {
                cookies.set('access_token', data.login.access_token, {
                    secure: true,
                    sameSite: 'strict',
                    expires: new Date(Date.now() + data.login.expires_in * 1000)
                })
                isAuthenticated.value = true
                await fetchCurrentUser()
            } else {
                throw new Error('Login failed: No access token received')
            }
        } catch (error) {
            console.error('Login error:', error)
            throw new Error('Login failed: ' + (error.message || 'Unknown error'))
        }
    }

    const logout = async () => {
        try {
            await logoutMutation()
        } catch (error) {
            console.error('Logout error:', error)
        } finally {
            cookies.remove('access_token')
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
            console.error('Fetch user error:', error)
            if (error.message.includes('Unauthorized')) {
                await refreshToken()
            } else {
                await logout()
            }
        }
    }

    const refreshToken = async () => {
        try {
            const { data } = await refreshTokenMutation()
            if (data?.refreshToken?.access_token) {
                cookies.set('access_token', data.refreshToken.access_token, {
                    secure: true,
                    sameSite: 'strict',
                    expires: new Date(Date.now() + data.refreshToken.expires_in * 1000)
                })
                await fetchCurrentUser()
            } else {
                throw new Error('Token refresh failed: No new token received')
            }
        } catch (error) {
            console.error('Token refresh error:', error)
            await logout()
        }
    }

    return { user, isAuthenticated, login, logout, fetchCurrentUser, refreshToken }
})
