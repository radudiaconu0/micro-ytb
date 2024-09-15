import { ApolloClient, createHttpLink, InMemoryCache } from '@apollo/client/core'
import { setContext } from '@apollo/client/link/context'
import { provideApolloClient } from '@vue/apollo-composable'
import router from "@/router"
import { useAuthStore } from "@/stores/authStore"
import { useCookies } from "@vueuse/integrations/useCookies"

const httpLink = createHttpLink({
    uri: '/graphql'// Replace with your actual GraphQL endpoint
})

const authLink = setContext(async (_, { headers }) => {
    const cookies = useCookies(['access_token'])
    let token = cookies.get('access_token')

    if (token && isTokenExpired(token)) {
        const authStore = useAuthStore()
        try {
            await authStore.refreshToken()
            token = cookies.get('access_token')
        } catch (error) {
            console.error('Error refreshing token:', error)
            await authStore.logout()
        }
    }

    return {
        headers: {
            ...headers,
            Authorization: token ? `Bearer ${token}` : "",
        }
    }
})

export const apolloClient = new ApolloClient({
    link: authLink.concat(httpLink),
    cache: new InMemoryCache()
})

export default {
    install: (app) => {
        provideApolloClient(apolloClient)

        const authStore = useAuthStore()

        app.config.globalProperties.$auth = {
            login: authStore.login,
            logout: authStore.logout,
            fetchCurrentUser: authStore.fetchCurrentUser,
            refreshToken: authStore.refreshToken,
        }

        const cookies = useCookies(['access_token'])
        if (cookies.get('access_token')) {
            authStore.fetchCurrentUser().catch(async (error) => {
                console.error('Error fetching current user:', error)
                await authStore.logout()
            })
        }
    }
}

function isTokenExpired(token: string): boolean {
    try {
        const tokenPayload = JSON.parse(atob(token.split('.')[1]))
        // Add a 5-minute buffer to refresh the token before it actually expires
        return (tokenPayload.exp * 1000) < (Date.now() + 5 * 60 * 1000)
    } catch (error) {
        console.error('Error parsing token:', error)
        return true
    }
}
