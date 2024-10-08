import {createMemoryHistory, createRouter, createWebHistory, RouteRecordRaw} from 'vue-router'

import Home from "@/Pages/Home.vue";
import Login from "@/Pages/Login.vue";

const routes: Readonly<RouteRecordRaw[]> = [
    {name: 'home', path: '/', component: Home},
    {
        name: 'login',
        path: '/login', component: Login, meta: {
            auth: 'guest'
        }
    },
    {path: '/video-upload', component: () => import('@/Pages/UploadVideoForm.vue'), meta: {auth: 'auth'}},
    {name: 'register', path: '/register', component: () => import('@/Pages/Register.vue'), meta: {auth: 'guest'}},
    {name: 'search', path: '/results', component: () => import('@/Pages/SearchResults.vue')},
    {name: 'watch', path: '/watch', component: () => import('@/Pages/Video.vue')},
    {path: '/my-videos', component: () => import('@/Pages/MyVideos.vue'), meta: {auth: 'auth'}},
    {path: '/video/:code/edit', component: () => import('@/Pages/EditVideo.vue'), meta: {auth: 'auth'}}
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach((to, from, next) => {
    const user = localStorage.getItem('access_token')
    if (to.meta.auth === 'auth' && !user) {
        return next({path: '/login'})
    }
    if (to.meta.auth === 'guest' && user) {
        return next({path: '/'})
    }
    next()
});
export default router
