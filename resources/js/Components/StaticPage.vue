<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: String,
    subtitle: String,
    icon: { type: String, default: 'fa-circle-info' },
    updated: String, // date de mise à jour (pages légales)
});
</script>

<template>
    <div class="max-w-[900px] mx-auto px-4 pb-20">
        <!-- Fil d'ariane -->
        <nav class="text-sm text-gray-500 font-medium py-5 flex items-center gap-2">
            <Link href="/" class="hover:text-brand-600">Accueil</Link>
            <i class="fa-solid fa-chevron-right text-[10px] text-gray-300"></i>
            <span class="text-dark font-semibold">{{ title }}</span>
        </nav>

        <!-- En-tête -->
        <header class="bg-brand-50 rounded-3xl p-8 md:p-12 mb-10">
            <div class="w-14 h-14 rounded-2xl bg-brand-600 text-white flex items-center justify-center text-2xl mb-5">
                <i class="fa-solid" :class="icon"></i>
            </div>
            <h1 class="text-3xl md:text-4xl font-black text-dark leading-tight mb-3">{{ title }}</h1>
            <p v-if="subtitle" class="text-lg text-gray-600 max-w-2xl">{{ subtitle }}</p>
            <p v-if="updated" class="text-sm text-gray-400 mt-4">Dernière mise à jour : {{ updated }}</p>
        </header>

        <!-- Contenu -->
        <div class="space-y-8">
            <slot />
        </div>
    </div>
</template>
