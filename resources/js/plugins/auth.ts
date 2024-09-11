import {ApolloClient, createHttpLink, InMemoryCache} from '@apollo/client/core'
import {setContext} from '@apollo/client/link/context'
import {provideApolloClient} from '@vue/apollo-composable'
import {useAuthStore} from "@/stores/authStore";

const httpLink = createHttpLink({
    uri: 'https://micro-ytb.test/graphql',
})

const authLink = setContext((_, {headers}) => {
    const token = document.cookie.replace(/(?:(?:^|.*;\s*)access_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
    console.log("token here: ", token)
    return {
        headers: {
            ...headers,
            Authorization: token ? `Bearer ${token}` : "",
        }
    }
})

const apolloClient = new ApolloClient({
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
        }
        if (document.cookie.includes('access_token')) {
            authStore.fetchCurrentUser().then(() => {
                authStore.isAuthenticated = true
            })
        }
    }
}
