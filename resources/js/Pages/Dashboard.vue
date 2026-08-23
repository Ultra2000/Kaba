<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import BookCard from '@/Components/BookCard.vue';

defineProps({
    stats: Object,
    listings: Array,
});

const user = usePage().props.auth.user;
const fmt = (n) => new Intl.NumberFormat('fr-FR').format(n);
</script>

<template>
    <Head title="Tableau de bord" />
    <PublicLayout>
        <div class="max-w-[1400px] mx-auto px-4 py-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl md:text-3xl font-black text-dark">Bonjour, {{ user.name.split(' ')[0] }} 👋</h1>
                    <p class="text-gray-500 font-medium mt-1">Voici l'activité de votre compte.</p>
                </div>
                <Link href="/publier" class="inline-flex items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 text-white px-6 py-3 rounded-full font-bold shadow-floating transition-all active:scale-95">
                    <i class="fa-solid fa-plus text-xs"></i> Publier une annonce
                </Link>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <i class="fa-solid fa-book text-brand-600 mb-2"></i>
                    <p class="text-2xl font-black text-dark">{{ stats.active }}</p>
                    <p class="text-xs text-gray-500 font-semibold">Annonces actives</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <i class="fa-solid fa-sack-dollar text-brand-600 mb-2"></i>
                    <p class="text-2xl font-black text-dark">{{ stats.sales }}</p>
                    <p class="text-xs text-gray-500 font-semibold">Ventes totales</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <i class="fa-solid fa-eye text-brand-600 mb-2"></i>
                    <p class="text-2xl font-black text-dark">{{ fmt(stats.views) }}</p>
                    <p class="text-xs text-gray-500 font-semibold">Vues cumulées</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <i class="fa-solid fa-star text-yellow-400 mb-2"></i>
                    <p class="text-2xl font-black text-dark">{{ user.rating_avg ?? '—' }}</p>
                    <p class="text-xs text-gray-500 font-semibold">Note moyenne</p>
                </div>
            </div>

            <!-- Accès rapides -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
                <Link href="/publier" class="bg-brand-600 text-white rounded-2xl p-5 hover:bg-brand-700 transition-colors">
                    <i class="fa-solid fa-plus text-lg mb-2"></i><p class="font-bold">Publier</p><p class="text-xs text-brand-100">Nouvelle annonce</p>
                </Link>
                <Link href="/favoris" class="bg-white border border-gray-200 rounded-2xl p-5 hover:border-brand-300 transition-colors">
                    <i class="fa-solid fa-heart text-orange-500 text-lg mb-2"></i><p class="font-bold text-dark">{{ stats.favorites }} favoris</p><p class="text-xs text-gray-500">Livres enregistrés</p>
                </Link>
                <Link href="/favoris" class="bg-white border border-gray-200 rounded-2xl p-5 hover:border-brand-300 transition-colors">
                    <i class="fa-solid fa-user-group text-brand-600 text-lg mb-2"></i><p class="font-bold text-dark">{{ stats.following }} suivis</p><p class="text-xs text-gray-500">Vendeurs suivis</p>
                </Link>
                <Link href="/notifications" class="bg-white border border-gray-200 rounded-2xl p-5 hover:border-brand-300 transition-colors">
                    <i class="fa-solid fa-bell text-brand-600 text-lg mb-2"></i><p class="font-bold text-dark">Notifications</p><p class="text-xs text-gray-500">Voir l'activité</p>
                </Link>
            </div>

            <!-- Mes annonces -->
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-black text-dark">Mes annonces</h2>
                <Link href="/explorer" class="text-sm font-bold text-brand-600 hover:underline">Voir le catalogue</Link>
            </div>

            <div v-if="listings.length" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-x-5 gap-y-10 md:gap-x-8">
                <BookCard v-for="l in listings" :key="l.id" :listing="l" />
            </div>

            <div v-else class="bg-gray-50 border border-dashed border-gray-300 rounded-2xl py-16 text-center">
                <i class="fa-solid fa-book-open text-5xl text-gray-300 mb-4"></i>
                <p class="text-gray-600 font-bold mb-1">Vous n'avez pas encore d'annonce</p>
                <p class="text-gray-500 text-sm mb-6">Publiez votre premier livre en quelques minutes.</p>
                <Link href="/publier" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white px-6 py-3 rounded-full font-bold shadow-floating transition-all">
                    <i class="fa-solid fa-plus text-xs"></i> Publier une annonce
                </Link>
            </div>
        </div>
    </PublicLayout>
</template>
