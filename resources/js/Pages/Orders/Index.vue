<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    sent: Array,     // demandes envoyées (je suis l'acheteur)
    received: Array, // demandes reçues (je suis le vendeur)
});

const tab = ref(props.received.length && !props.sent.length ? 'received' : 'sent');

const fmt = (n) => new Intl.NumberFormat('fr-FR').format(n) + ' F';
// Total des livres non refusés uniquement.
const total = (o) => o.items.filter((it) => it.status !== 'declined').reduce((s, it) => s + (it.price || 0), 0);

const ITEM_BADGE = {
    accepted: 'bg-green-50 text-green-700 border-green-200',
    declined: 'bg-red-50 text-red-500 border-red-200 line-through',
    pending:  'bg-amber-50 text-amber-600 border-amber-200',
};

function actItem(order, item, action) {
    router.post(`/demandes/${order.id}/livres/${item.id}/${action}`, {}, { preserveScroll: true });
}

const STATUS_STYLE = {
    pending:   'bg-amber-50 text-amber-700 border-amber-200',
    accepted:  'bg-green-50 text-green-700 border-green-200',
    declined:  'bg-red-50 text-red-600 border-red-200',
    completed: 'bg-brand-50 text-brand-700 border-brand-200',
    cancelled: 'bg-gray-100 text-gray-500 border-gray-200',
};

function act(order, action) {
    router.post(`/demandes/${order.id}/${action}`, {}, { preserveScroll: true });
}

const cover = (l) => l?.cover_url
    ? l.cover_url
    : (l?.isbn
        ? `https://covers.openlibrary.org/b/isbn/${l.isbn}-M.jpg?default=false`
        : `https://placehold.co/80x120/7c3aed/ffffff?text=${encodeURIComponent((l?.title || '').slice(0, 10))}`);
function onErr(e, l) {
    e.target.onerror = null;
    e.target.src = `https://placehold.co/80x120/7c3aed/ffffff?text=${encodeURIComponent((l?.title || '').slice(0, 10))}`;
}
const dateFr = (d) => new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' });
</script>

<template>
    <Head title="Mes demandes" />
    <PublicLayout>
        <div class="max-w-[900px] mx-auto px-4 py-8">
            <div class="flex items-center gap-2 text-sm text-gray-500 font-medium mb-6">
                <Link href="/" class="hover:text-brand-600">Accueil</Link>
                <i class="fa-solid fa-chevron-right text-[10px] text-gray-300"></i>
                <span class="text-dark font-semibold">Mes demandes</span>
            </div>

            <h1 class="text-2xl md:text-3xl font-black text-dark mb-6">Demandes de disponibilité</h1>

            <!-- Onglets -->
            <div class="flex gap-2 mb-8">
                <button @click="tab = 'sent'"
                        class="h-11 px-5 rounded-full text-sm font-bold border transition-colors"
                        :class="tab === 'sent' ? 'bg-brand-600 text-white border-brand-600' : 'bg-white text-gray-600 border-gray-200 hover:border-brand-300'">
                    Envoyées <span class="opacity-70">({{ sent.length }})</span>
                </button>
                <button @click="tab = 'received'"
                        class="h-11 px-5 rounded-full text-sm font-bold border transition-colors"
                        :class="tab === 'received' ? 'bg-brand-600 text-white border-brand-600' : 'bg-white text-gray-600 border-gray-200 hover:border-brand-300'">
                    Reçues <span class="opacity-70">({{ received.length }})</span>
                </button>
            </div>

            <!-- Liste -->
            <div v-for="mode in [tab]" :key="mode">
                <div v-if="(mode === 'sent' ? sent : received).length === 0" class="text-center py-16 bg-white rounded-2xl border border-gray-100">
                    <i class="fa-solid fa-inbox text-5xl text-gray-200 mb-4"></i>
                    <p class="text-gray-500 font-medium">{{ mode === 'sent' ? "Vous n'avez envoyé aucune demande." : "Aucune demande reçue pour le moment." }}</p>
                </div>

                <div v-else class="space-y-5">
                    <div v-for="o in (mode === 'sent' ? sent : received)" :key="o.id"
                         class="bg-white rounded-2xl border border-gray-100 shadow-soft overflow-hidden">
                        <!-- Entête -->
                        <div class="flex flex-wrap items-center gap-3 px-5 py-4 border-b border-gray-100">
                            <span class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-black text-sm">
                                {{ (mode === 'sent' ? o.seller?.name : o.buyer?.name)?.slice(0, 2).toUpperCase() }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-dark text-sm">
                                    {{ mode === 'sent' ? 'À ' + (o.seller?.name ?? '—') : 'De ' + (o.buyer?.name ?? '—') }}
                                </p>
                                <p class="text-xs text-gray-400">{{ dateFr(o.created_at) }} · {{ o.items.length }} livre{{ o.items.length > 1 ? 's' : '' }}</p>
                            </div>
                            <span class="text-xs font-bold px-3 py-1.5 rounded-full border" :class="STATUS_STYLE[o.status]">{{ o.status_label }}</span>
                        </div>

                        <!-- Livres -->
                        <ul class="divide-y divide-gray-50">
                            <li v-for="it in o.items" :key="it.id" class="flex items-center gap-4 px-5 py-3">
                                <img :src="cover(it.listing)" @error="onErr($event, it.listing)" :alt="it.listing?.title"
                                     class="w-10 h-[60px] object-cover rounded-[3px] shadow-sm shrink-0"
                                     :class="{ 'opacity-40 grayscale': it.status === 'declined' }">
                                <div class="flex-1 min-w-0">
                                    <Link v-if="it.listing" :href="`/livres/${it.listing.id}`" class="font-bold text-dark text-sm hover:text-brand-600 line-clamp-1"
                                          :class="{ 'text-gray-400 line-through': it.status === 'declined' }">{{ it.listing.title }}</Link>
                                    <span v-else class="text-sm text-gray-400 italic">Annonce supprimée</span>
                                    <p class="text-xs text-gray-400 truncate">{{ it.listing?.author }}</p>
                                </div>

                                <!-- Statut du livre (dès qu'il y a eu une réponse) -->
                                <span v-if="it.status !== 'pending' || o.status !== 'pending'"
                                      class="text-[11px] font-bold px-2.5 py-1 rounded-full border shrink-0" :class="ITEM_BADGE[it.status]">
                                    {{ it.status_label }}
                                </span>

                                <p class="text-sm font-bold text-dark shrink-0">{{ it.price > 0 ? fmt(it.price) : (it.listing?.type === 'echange' ? 'Échange' : 'Gratuit') }}</p>

                                <!-- Réponse livre par livre (vendeur, tant que le livre est en attente) -->
                                <div v-if="mode === 'received' && o.status === 'pending' && it.status === 'pending'" class="flex gap-1.5 shrink-0">
                                    <button @click="actItem(o, it, 'accepter')" title="Disponible"
                                            class="w-9 h-9 rounded-full bg-green-50 border border-green-200 text-green-600 hover:bg-green-600 hover:text-white transition-colors flex items-center justify-center">
                                        <i class="fa-solid fa-check text-sm"></i>
                                    </button>
                                    <button @click="actItem(o, it, 'refuser')" title="Indisponible"
                                            class="w-9 h-9 rounded-full bg-red-50 border border-red-200 text-red-500 hover:bg-red-500 hover:text-white transition-colors flex items-center justify-center">
                                        <i class="fa-solid fa-xmark text-sm"></i>
                                    </button>
                                </div>
                            </li>
                        </ul>

                        <!-- Message + total + actions -->
                        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/40">
                            <p v-if="o.message" class="text-sm text-gray-600 italic mb-3">« {{ o.message }} »</p>
                            <div class="flex flex-wrap items-center gap-3">
                                <p v-if="total(o) > 0" class="font-black text-dark mr-auto">Total : {{ fmt(total(o)) }}</p>
                                <p v-else class="mr-auto"></p>

                                <!-- Discuter (dès que le vendeur a répondu favorablement) -->
                                <button v-if="['accepted', 'completed'].includes(o.status)" @click="act(o, 'discuter')"
                                        class="inline-flex items-center gap-2 bg-white border-2 border-brand-200 text-brand-700 hover:bg-brand-50 h-10 px-5 rounded-full text-sm font-bold transition-colors">
                                    <i class="fa-regular fa-comment"></i> {{ mode === 'sent' ? 'Discuter avec le vendeur' : "Discuter avec l'acheteur" }}
                                </button>

                                <!-- Actions vendeur -->
                                <template v-if="mode === 'received'">
                                    <button v-if="o.status === 'pending'" @click="act(o, 'accepter')"
                                            class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white h-10 px-5 rounded-full text-sm font-bold transition-colors">
                                        <i class="fa-solid fa-check-double"></i> {{ o.items.length > 1 ? 'Tout accepter' : 'Accepter' }}
                                    </button>
                                    <button v-if="o.status === 'pending'" @click="act(o, 'refuser')"
                                            class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-600 hover:border-red-300 hover:text-red-600 h-10 px-5 rounded-full text-sm font-bold transition-colors">
                                        <i class="fa-solid fa-xmark"></i> {{ o.items.length > 1 ? 'Tout refuser' : 'Refuser' }}
                                    </button>
                                    <button v-if="o.status === 'accepted'" @click="act(o, 'remise')"
                                            class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white h-10 px-5 rounded-full text-sm font-bold transition-colors">
                                        <i class="fa-solid fa-handshake"></i> Confirmer la remise
                                    </button>
                                </template>

                                <!-- Actions acheteur -->
                                <template v-if="mode === 'sent'">
                                    <button v-if="['pending', 'accepted'].includes(o.status)" @click="act(o, 'annuler')"
                                            class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-600 hover:border-red-300 hover:text-red-600 h-10 px-5 rounded-full text-sm font-bold transition-colors">
                                        <i class="fa-solid fa-xmark"></i> Annuler
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
