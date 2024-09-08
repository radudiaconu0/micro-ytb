<script setup lang="ts">
import {onMounted} from "vue";
import NavBar from "@/Components/NavBar.vue";

onMounted(() => {
    console.log('Home page mounted');
});
</script>

<template>
    <NavBar />
</template>

<style scoped>

</style>
