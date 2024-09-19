import { ApolloClient, createHttpLink, InMemoryCache } from '@apollo/client/core'
import { setContext } from '@apollo/client/link/context'
import { provideApolloClient } from '@vue/apollo-composable'
import { useAuthStore } from "@/stores/authStore"
import {App} from "vue";


const httpLink = createHttpLink({
    uri: '/graphql'
})

const authLink = setContext(async (_, { headers }) => {
    let token = localStorage.getItem('access_token')
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
    install: (app: App) => {
        provideApolloClient(apolloClient)

        const authStore = useAuthStore()

        let auth = {
            login: authStore.login,
            logout: authStore.logout,
            fetchCurrentUser: authStore.fetchCurrentUser,
        }
        app.provide('auth', auth)

        if (localStorage.getItem('access_token')) {
            authStore.fetchCurrentUser().catch(async (error) => {
                console.error('Error fetching current user:', error)
                await authStore.logout()
            })
        }
    }
}
