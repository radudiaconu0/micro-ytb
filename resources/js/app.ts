import './bootstrap';
import '../css/app.css';
import 'flowbite';
import App from "@/App.vue";
import {createPinia} from 'pinia'
import {createApp} from 'vue';
import router from "@/router";
import auth from "@/plugins/auth";


const pinia = createPinia()

const app = createApp(App)
    .use(router)
    .use(pinia)
    .use(auth)


app.mount('#app');
