<template>
    <div class="flex flex-col h-screen bg-white dark:bg-zinc-900">
        <NavBar/>

        <main class="flex-grow dark:bg-zinc-900">
            <slot/>
        </main>
    </div>


</template>

<script setup lang="ts">
import {Link} from "@inertiajs/vue3";
import {ref} from "vue";
import NavBar from "@/Components/NavBar.vue";

const menuOpen = ref(false);

</script>
