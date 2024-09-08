import './bootstrap';
import '../css/app.css';
import 'flowbite';

import {createApp, h, DefineComponent, provide} from 'vue';
import {createInertiaApp} from '@inertiajs/vue3';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {ZiggyVue} from '../../vendor/tightenco/ziggy';
import {ApolloClient, createHttpLink, InMemoryCache} from '@apollo/client/core'
import {DefaultApolloClient} from '@vue/apollo-composable'
import {useCookies} from "@vueuse/integrations/useCookies";


// HTTP connection to the API
const httpLink = createHttpLink({
    uri: 'https://micro-ytb.test/graphql',
    headers: {
        'Authorization': ('Bearer ' + useCookies().get('access_token')) || null,
    }
})

// Cache implementation
const cache = new InMemoryCache()

// Create the apollo client
const apolloClient = new ApolloClient({
    link: httpLink,
    cache,
})

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob<DefineComponent>('./Pages/**/*.vue')),
    setup({el, App, props, plugin}) {
        createApp({
            setup() {
                provide(DefaultApolloClient, apolloClient)
            },
            render: () => h(App, props)
        })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
