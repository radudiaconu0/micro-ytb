<script setup lang="ts">
import {onMounted} from "vue";
import NavBar from "@/Components/NavBar.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";

onMounted(() => {
    console.log('Home page mounted');
    // get access_token from cookie
    // if access_token is not available, redirect to login page


    // if access_token is available, redirect to dashboard page

    let token = document.cookie.split(';').find((c) => c.trim().startsWith('access_token='));
    if (token) {
        token = token.split('=')[1];
        console.log(token);
    }
    else {
        console.log('No token found');
    }
});
</script>

<template>
<GuestLayout>
    Landing Page
</GuestLayout>
</template>

<style scoped>

</style>
