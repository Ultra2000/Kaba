<script setup>
import AppHeader from '@/Components/AppHeader.vue';
import AppFooter from '@/Components/AppFooter.vue';
</script>

<template>
    <div class="min-h-screen bg-white font-sans text-dark antialiased flex flex-col">
        <AppHeader />
        <main class="flex-1">
            <slot />
        </main>
        <AppFooter />
    </div>
</template>
