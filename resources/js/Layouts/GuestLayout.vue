<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 px-6 py-10 font-sans text-dark antialiased">
        <Link href="/" class="flex items-center gap-2 mb-8">
            <img src="/images/logo-trans.png" alt="KABA" class="h-10 w-auto">
            <span class="font-black text-2xl tracking-tighter text-dark">KABA</span>
        </Link>

        <div class="w-full sm:max-w-md bg-white border border-gray-200 rounded-3xl shadow-soft p-6 sm:p-8">
            <slot />
        </div>

        <Link href="/" class="mt-6 text-sm font-bold text-gray-500 hover:text-brand-600">
            <i class="fa-solid fa-arrow-left text-xs"></i> Retour à l'accueil
        </Link>
    </div>
</template>
