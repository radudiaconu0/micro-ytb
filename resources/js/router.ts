import {createMemoryHistory, createRouter, createWebHistory} from 'vue-router'

import Home from "@/Pages/Home.vue";
import Login from "@/Pages/Login.vue";
import {useCookies} from "@vueuse/integrations/useCookies";

const routes = [
    {path: '/', component: Home},
    {
        path: '/login', component: Login, meta: {
            auth: 'guest'
        }
    },
    {path: '/video-upload', component: () => import('@/Pages/UploadVideoForm.vue'), meta: {auth: 'auth'}},
    {path: '/register', component: () => import('@/Pages/Register.vue'), meta: {auth: 'guest'}},
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach((to, from, next) => {
    const {get} = useCookies()
    const user = get('access_token')
    if (to.meta.auth === 'auth' && !user) {
        return next({path: '/login'})
    }
    if (to.meta.auth === 'guest' && user) {
        return next({path: '/'})
    }
    next()
});
export default router
