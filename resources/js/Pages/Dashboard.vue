<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineProps({
    stats: Object,
    listings: Array,
});

const user = usePage().props.auth.user;
const fmt = (n) => new Intl.NumberFormat('fr-FR').format(n);

const STATUS = {
    active:  { label: 'En ligne', class: 'bg-green-50 text-green-700 border-green-200' },
    sold:    { label: 'Vendue', class: 'bg-gray-100 text-gray-600 border-gray-200' },
    pending: { label: 'À valider', class: 'bg-amber-50 text-amber-700 border-amber-200' },
    hidden:  { label: 'Masquée', class: 'bg-red-50 text-red-600 border-red-200' },
};

const priceLabel = (l) => {
    if (l.type === 'don') return 'Don gratuit';
    if (l.type === 'echange') return 'Échange';
    if (l.type === 'recherche') return 'Recherche';
    return fmt(l.price) + ' F';
};

const placeholder = (l) => `https://placehold.co/120x180/7c3aed/ffffff?text=${encodeURIComponent((l.title || '').slice(0, 12))}`;
const cover = (l) => l.cover_url
    || (l.isbn ? `https://covers.openlibrary.org/b/isbn/${l.isbn}-M.jpg?default=false` : placeholder(l));
function onCoverError(e, l) { e.target.onerror = null; e.target.src = placeholder(l); }

const toggleStatus = (l) => router.post(`/livres/${l.id}/statut`, {}, { preserveScroll: true });
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

            <div v-if="listings.length" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="l in listings" :key="l.id"
                     class="bg-white border border-gray-200 rounded-2xl p-4 flex gap-4 hover:border-brand-300 transition-colors">
                    <Link :href="`/livres/${l.id}`" class="shrink-0">
                        <img :src="cover(l)" @error="onCoverError($event, l)" :alt="l.title"
                             class="w-16 h-24 object-cover rounded-md shadow-sm"
                             :class="{ 'opacity-50 grayscale': l.status !== 'active' }">
                    </Link>

                    <div class="flex-1 min-w-0 flex flex-col">
                        <div class="flex items-start gap-2">
                            <Link :href="`/livres/${l.id}`" class="font-bold text-dark text-sm leading-snug line-clamp-2 hover:text-brand-600 flex-1">{{ l.title }}</Link>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md border shrink-0" :class="STATUS[l.status].class">{{ STATUS[l.status].label }}</span>
                        </div>

                        <p class="text-xs text-gray-500 mt-1">
                            {{ priceLabel(l) }}
                            <span v-if="l.quantity > 1" class="text-gray-400">· {{ l.quantity }} ex.</span>
                            <span class="text-gray-400">· {{ l.views }} vues</span>
                        </p>

                        <div class="flex flex-wrap gap-1.5 mt-auto pt-3">
                            <Link :href="`/livres/${l.id}/modifier`"
                                  class="inline-flex items-center gap-1.5 h-8 px-3 rounded-full text-xs font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
                                <i class="fa-solid fa-pen text-[10px]"></i> Modifier
                            </Link>
                            <button v-if="l.type !== 'recherche'" @click="toggleStatus(l)"
                                    class="inline-flex items-center gap-1.5 h-8 px-3 rounded-full text-xs font-bold transition-colors"
                                    :class="l.status === 'active' ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-green-50 text-green-700 hover:bg-green-100'">
                                <i class="fa-solid text-[10px]" :class="l.status === 'active' ? 'fa-box-archive' : 'fa-rotate-left'"></i>
                                {{ l.status === 'active' ? 'Marquer vendu' : 'Remettre en ligne' }}
                            </button>
                        </div>
                    </div>
                </div>
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
