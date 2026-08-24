<script setup>
import { reactive } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    groups: Array, // [{ seller, items, total }]
});

const fmt = (n) => new Intl.NumberFormat('fr-FR').format(n) + ' F';
const priceLabel = (l) => l.type === 'don' ? 'Gratuit' : l.type === 'echange' ? 'Échange' : fmt(l.price);

function removeItem(listing) {
    router.delete(`/panier/${listing.id}`, { preserveScroll: true });
}

// Un formulaire de message + envoi par vendeur.
const messages = reactive({});
const sending = reactive({});
function requestAvailability(sellerId) {
    sending[sellerId] = true;
    router.post(`/demandes/vendeur/${sellerId}`, { message: messages[sellerId] ?? '' }, {
        onFinish: () => { sending[sellerId] = false; },
    });
}

const cover = (l) => l.cover_url
    ? l.cover_url
    : (l.isbn
        ? `https://covers.openlibrary.org/b/isbn/${l.isbn}-M.jpg?default=false`
        : `https://placehold.co/120x180/7c3aed/ffffff?text=${encodeURIComponent((l.title || '').slice(0, 12))}`);
function onErr(e, l) {
    e.target.onerror = null;
    e.target.src = `https://placehold.co/120x180/7c3aed/ffffff?text=${encodeURIComponent((l.title || '').slice(0, 12))}`;
}
</script>

<template>
    <Head title="Mon panier" />
    <PublicLayout>
        <div class="max-w-[900px] mx-auto px-4 py-8">
            <div class="flex items-center gap-2 text-sm text-gray-500 font-medium mb-6">
                <Link href="/" class="hover:text-brand-600">Accueil</Link>
                <i class="fa-solid fa-chevron-right text-[10px] text-gray-300"></i>
                <span class="text-dark font-semibold">Panier</span>
            </div>

            <h1 class="text-2xl md:text-3xl font-black text-dark mb-2">Mon panier</h1>
            <p class="text-gray-500 text-sm mb-8">Envoyez une demande de disponibilité à chaque vendeur — sans paiement en ligne, la remise se fait en main propre.</p>

            <!-- Vide -->
            <div v-if="!groups.length" class="text-center py-16 bg-white rounded-2xl border border-gray-100">
                <i class="fa-solid fa-basket-shopping text-5xl text-gray-200 mb-4"></i>
                <p class="text-gray-500 font-medium mb-4">Votre panier est vide.</p>
                <Link href="/explorer" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white h-11 px-6 rounded-full text-sm font-bold transition-colors">
                    <i class="fa-solid fa-compass"></i> Explorer les livres
                </Link>
            </div>

            <!-- Groupes par vendeur -->
            <div v-else class="space-y-6">
                <div v-for="g in groups" :key="g.seller.id" class="bg-white rounded-2xl border border-gray-100 shadow-soft overflow-hidden">
                    <!-- Entête vendeur -->
                    <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 bg-gray-50/60">
                        <span class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-black text-sm">
                            {{ g.seller.name.slice(0, 2).toUpperCase() }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-dark text-sm flex items-center gap-1.5">
                                {{ g.seller.name }}
                                <i v-if="g.seller.is_verified" class="fa-solid fa-circle-check text-brand-500 text-xs" title="Vendeur vérifié"></i>
                            </p>
                            <p class="text-xs text-gray-400">{{ g.seller.city }}</p>
                        </div>
                        <p v-if="g.total > 0" class="font-black text-dark">{{ fmt(g.total) }}</p>
                    </div>

                    <!-- Livres -->
                    <ul class="divide-y divide-gray-50">
                        <li v-for="l in g.items" :key="l.id" class="flex items-center gap-4 px-5 py-4">
                            <Link :href="`/livres/${l.id}`" class="shrink-0">
                                <img :src="cover(l)" @error="onErr($event, l)" :alt="l.title"
                                     class="w-12 h-[72px] object-cover rounded-[3px] shadow-[0_4px_10px_-2px_rgba(0,0,0,0.3)]">
                            </Link>
                            <div class="flex-1 min-w-0">
                                <Link :href="`/livres/${l.id}`" class="font-bold text-dark text-sm hover:text-brand-600 line-clamp-1">{{ l.title }}</Link>
                                <p class="text-xs text-gray-400 truncate">{{ l.author }}</p>
                                <p class="text-sm font-bold text-dark mt-1">{{ priceLabel(l) }}</p>
                            </div>
                            <button @click="removeItem(l)" class="w-9 h-9 rounded-full flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors" title="Retirer">
                                <i class="fa-solid fa-trash-can text-sm"></i>
                            </button>
                        </li>
                    </ul>

                    <!-- Demande -->
                    <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/40">
                        <textarea v-model="messages[g.seller.id]" rows="2" maxlength="500"
                                  class="w-full rounded-xl border border-gray-200 text-sm p-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 mb-3"
                                  placeholder="Message au vendeur (facultatif) — ex. « Bonjour, ces livres sont-ils disponibles ce week-end à Cotonou ? »"></textarea>
                        <button @click="requestAvailability(g.seller.id)" :disabled="sending[g.seller.id]"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 disabled:opacity-60 text-white h-12 px-6 rounded-full font-bold shadow-floating transition-all active:scale-95">
                            <i class="fa-solid fa-paper-plane"></i> Demander la disponibilité
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
