<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import BookCard from '@/Components/BookCard.vue';

const props = defineProps({
    listing: Object,
    similar: Array,
});

const page = usePage();

/* Favori */
const isFav = computed(() => (page.props.auth?.favorites ?? []).includes(props.listing.id));
function toggleFav() {
    if (!page.props.auth?.user) { router.get('/login'); return; }
    router.post(`/favoris/${props.listing.id}`, {}, { preserveScroll: true });
}

const isOwner = computed(() => page.props.auth?.user?.id === props.listing.user.id);
function contact() {
    if (!page.props.auth?.user) { router.get('/login'); return; }
    router.post(`/messagerie/demarrer/${props.listing.id}`);
}

/* Panier */
const inCart = computed(() => (page.props.auth?.cart ?? []).includes(props.listing.id));
function addToCart() {
    if (!page.props.auth?.user) { router.get('/login'); return; }
    router.post(`/panier/${props.listing.id}`, {}, { preserveScroll: true });
}

/* Signalement */
const REASONS = {
    faux_livre: 'Faux livre / contrefaçon',
    arnaque: "Suspicion d'arnaque",
    prix_abusif: 'Prix abusif',
    offensant: 'Contenu offensant',
};
const reportOpen = ref(false);
const reportDone = ref(false);
const reportForm = useForm({ reason: 'faux_livre', details: '' });
function openReport() {
    if (!page.props.auth?.user) { router.get('/login'); return; }
    reportDone.value = false; reportOpen.value = true;
}
function submitReport() {
    reportForm.post(`/livres/${props.listing.id}/signaler`, {
        preserveScroll: true,
        onSuccess: () => { reportDone.value = true; reportForm.reset(); },
    });
}

const COVERS = ['7c3aed','f59e0b','10b981','ef4444','3b82f6','6d28d9','db2777','0891b2','65a30d','c2410c'];
const color = computed(() => COVERS[props.listing.id % COVERS.length]);
const fallback = computed(() => `https://placehold.co/400x600/${color.value}/ffffff?text=${encodeURIComponent(props.listing.title.slice(0,22))}`);
const photos = computed(() => props.listing.photos ?? []);
const photoUrl = (p) => p.url ?? `/storage/${p.path}`;

// Galerie : la photo affichée parmi celles envoyées par le vendeur.
const activePhoto = ref(0);
const cover = computed(() => photos.value.length
    ? photoUrl(photos.value[Math.min(activePhoto.value, photos.value.length - 1)])
    : (props.listing.isbn ? `https://covers.openlibrary.org/b/isbn/${props.listing.isbn}-L.jpg?default=false` : fallback.value));
function onError(e) { e.target.onerror = null; e.target.src = fallback.value; }

const fmt = (n) => new Intl.NumberFormat('fr-FR').format(n) + ' F';
const u = computed(() => props.listing.user);
const initials = computed(() => (u.value?.name || '').split(/\s+/).map(w => w[0]).slice(0,2).join('').toUpperCase());
</script>

<template>
    <Head :title="listing.title" />
    <PublicLayout>
        <div class="max-w-[1400px] mx-auto px-4 pb-16">
            <nav class="text-sm text-gray-500 font-medium py-5 flex items-center gap-2 flex-wrap">
                <Link href="/" class="hover:text-brand-600">Accueil</Link>
                <i class="fa-solid fa-chevron-right text-[10px] text-gray-300"></i>
                <Link :href="`/explorer?category=${listing.category.slug}`" class="hover:text-brand-600">{{ listing.category.name }}</Link>
                <i class="fa-solid fa-chevron-right text-[10px] text-gray-300"></i>
                <span class="text-dark font-semibold">{{ listing.title }}</span>
            </nav>

            <div class="grid lg:grid-cols-2 gap-8 lg:gap-12">
                <!-- Image + galerie -->
                <div>
                <div class="bg-gray-50 rounded-2xl border border-gray-100 p-6 flex items-center justify-center relative">
                    <span v-if="listing.status === 'sold'" class="absolute top-4 left-4 bg-dark text-white text-xs font-black px-3 py-1.5 rounded-md shadow-sm uppercase tracking-wider">{{ listing.type === 'don' ? 'Déjà donné' : 'Vendu' }}</span>
                    <span v-else-if="listing.type === 'vente'" class="absolute top-4 left-4 bg-brand-600 text-white text-xs font-black px-3 py-1.5 rounded-md shadow-sm uppercase tracking-wider">Occasion</span>
                    <span v-else-if="listing.type === 'don'" class="absolute top-4 left-4 bg-orange-500 text-white text-xs font-black px-3 py-1.5 rounded-md shadow-sm uppercase tracking-wider">Don solidaire</span>
                    <span v-else-if="listing.type === 'echange'" class="absolute top-4 left-4 bg-blue-500 text-white text-xs font-black px-3 py-1.5 rounded-md shadow-sm uppercase tracking-wider">À échanger</span>
                    <span v-else class="absolute top-4 left-4 bg-green-600 text-white text-xs font-black px-3 py-1.5 rounded-md shadow-sm uppercase tracking-wider">Recherché</span>
                    <img :src="cover" @error="onError" :alt="listing.title" class="max-h-[460px] rounded-lg shadow-lg">

                    <!-- Compteur de photos -->
                    <span v-if="photos.length > 1" class="absolute bottom-4 right-4 bg-black/60 text-white text-xs font-bold px-2.5 py-1 rounded-full">
                        {{ activePhoto + 1 }} / {{ photos.length }}
                    </span>
                </div>

                <!-- Miniatures : montrent l'état réel du livre -->
                <div v-if="photos.length > 1" class="mt-3">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                        <i class="fa-solid fa-images"></i> Photos de l'état du livre
                    </p>
                    <div class="flex gap-2 overflow-x-auto pb-1 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                        <button v-for="(p, i) in photos" :key="p.id ?? i" type="button" @click="activePhoto = i"
                                class="shrink-0 w-16 h-20 rounded-lg overflow-hidden border-2 transition-all"
                                :class="i === activePhoto ? 'border-brand-600 ring-2 ring-brand-200' : 'border-gray-200 hover:border-brand-300 opacity-80 hover:opacity-100'"
                                :aria-label="`Photo ${i + 1}`">
                            <img :src="photoUrl(p)" :alt="`${listing.title} — photo ${i + 1}`" loading="lazy" class="w-full h-full object-cover">
                        </button>
                    </div>
                </div>
                </div>

                <!-- Infos -->
                <div>
                    <Link :href="`/explorer?category=${listing.category.slug}`" class="text-sm font-bold text-brand-600 uppercase tracking-wider">{{ listing.category.name }}</Link>
                    <h1 class="text-3xl md:text-4xl font-black text-dark leading-tight mt-1 mb-2">{{ listing.title }}</h1>
                    <p class="text-gray-500 font-medium mb-4">par <span class="text-dark font-bold">{{ listing.author || 'Auteur inconnu' }}</span></p>

                    <div v-if="listing.rating" class="flex items-center gap-2 mb-6">
                        <span class="text-yellow-400"><i class="fa-solid fa-star text-sm"></i></span>
                        <span class="text-sm font-bold text-dark">{{ listing.rating }}</span>
                        <span class="text-sm text-gray-400">· {{ listing.views }} vues</span>
                    </div>

                    <!-- Prix -->
                    <div class="mb-6 flex items-baseline gap-3 flex-wrap">
                        <template v-if="listing.type === 'vente'">
                            <span class="text-4xl font-black text-brand-600">{{ fmt(listing.price) }}</span>
                            <span v-if="listing.old_price" class="text-lg text-gray-400 line-through font-bold">{{ fmt(listing.old_price) }}</span>
                            <span v-if="listing.old_price" class="bg-brand-100 text-brand-700 px-3 py-1 rounded-lg text-sm font-bold"><i class="fa-solid fa-piggy-bank"></i> -{{ Math.round((1 - listing.price / listing.old_price) * 100) }}%</span>
                        </template>
                        <span v-else-if="listing.type === 'don'" class="text-3xl font-black text-orange-500 uppercase">Gratuit</span>
                        <span v-else-if="listing.type === 'echange'" class="text-xl font-bold text-blue-600">Échange · cherche : {{ listing.wants || 'à discuter' }}</span>
                        <span v-else class="text-xl font-bold text-green-600">Recherché · budget {{ listing.budget ? fmt(listing.budget) : 'à discuter' }}</span>
                    </div>

                    <!-- État -->
                    <div class="flex flex-wrap gap-3 mb-6">
                        <span class="bg-gray-50 border border-gray-100 rounded-xl px-4 py-2 text-sm"><span class="text-gray-400">État</span> <span class="font-bold text-dark">{{ listing.condition_label }}</span></span>
                        <span class="bg-gray-50 border border-gray-100 rounded-xl px-4 py-2 text-sm"><span class="text-gray-400">Langue</span> <span class="font-bold text-dark">{{ listing.language }}</span></span>
                        <span class="bg-gray-50 border border-gray-100 rounded-xl px-4 py-2 text-sm"><i class="fa-solid fa-location-dot text-brand-600"></i> <span class="font-bold text-dark">{{ listing.city }}</span></span>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-3 mb-6">
                        <!-- Le vendeur gère son annonce depuis sa fiche -->
                        <Link v-if="isOwner" :href="`/livres/${listing.id}/modifier`"
                              class="flex-1 bg-dark hover:bg-gray-800 text-white px-6 py-4 rounded-full font-bold shadow-floating transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-pen"></i> Modifier mon annonce
                        </Link>
                        <div v-else-if="listing.status === 'sold'" class="flex-1 bg-gray-100 text-gray-500 px-6 py-4 rounded-full font-bold flex items-center justify-center gap-2">
                            <i class="fa-solid fa-circle-check"></i> {{ listing.type === 'don' ? 'Ce livre a déjà été donné' : 'Ce livre a été vendu' }}
                        </div>
                        <Link v-else-if="!isOwner && listing.type !== 'recherche' && inCart" href="/panier"
                              class="flex-1 bg-green-600 hover:bg-green-700 text-white px-6 py-4 rounded-full font-bold shadow-floating transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-circle-check"></i> Dans le panier — voir
                        </Link>
                        <button v-else-if="!isOwner && listing.type !== 'recherche' && listing.status === 'active'" @click="addToCart"
                                class="flex-1 bg-brand-600 hover:bg-brand-700 text-white px-6 py-4 rounded-full font-bold shadow-floating transition-all active:scale-95 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-basket-shopping"></i> Ajouter au panier
                        </button>
                        <button v-if="!isOwner" @click="contact"
                                class="bg-white text-dark border-2 border-gray-200 px-6 py-4 rounded-full font-bold hover:border-dark transition-colors flex items-center justify-center gap-2">
                            <i class="fa-regular fa-comment text-brand-600"></i> Contacter le vendeur
                        </button>
                        <button @click="toggleFav" class="w-14 h-14 shrink-0 bg-white border rounded-full flex items-center justify-center transition-colors self-center"
                                :class="isFav ? 'text-orange-500 border-orange-200' : 'text-gray-400 border-gray-200 hover:text-orange-500 hover:border-orange-200'">
                            <i class="fa-heart text-lg" :class="isFav ? 'fa-solid' : 'fa-regular'"></i>
                        </button>
                    </div>

                    <!-- Vendeur -->
                    <div class="flex items-center gap-4 bg-white border border-gray-200 rounded-2xl p-4 hover:border-brand-300 transition-colors">
                        <Link :href="`/vendeurs/${u.id}`" class="flex items-center gap-4 flex-1 min-w-0">
                            <div class="w-14 h-14 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xl font-black shrink-0">{{ initials }}</div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-dark truncate">{{ u.name }}</span>
                                    <span v-if="u.is_verified" class="text-blue-500" title="Vérifié"><i class="fa-solid fa-circle-check text-sm"></i></span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-gray-500 font-medium">
                                    <span v-if="u.rating_avg > 0"><i class="fa-solid fa-star text-yellow-400"></i> {{ u.rating_avg }}</span>
                                    <span v-if="u.sales_count">· {{ u.sales_count }} ventes</span>
                                    <span v-if="u.city">· {{ u.city }}</span>
                                </div>
                            </div>
                        </Link>
                        <i class="fa-solid fa-chevron-right text-gray-300"></i>
                    </div>
                    <button @click="openReport" class="mt-3 w-full text-center text-gray-400 hover:text-red-500 text-xs font-bold transition-colors"><i class="fa-solid fa-flag"></i> Signaler cette annonce</button>
                </div>
            </div>

            <!-- Description -->
            <div class="mt-12 max-w-3xl">
                <h2 class="text-2xl font-black text-dark mb-3">Description</h2>
                <p class="text-gray-600 leading-relaxed">{{ listing.description }}</p>
            </div>

            <!-- Similaires -->
            <div v-if="similar.length" class="mt-16">
                <h2 class="text-2xl font-black text-dark mb-6">Vous aimerez aussi</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-x-5 gap-y-10 md:gap-x-8">
                    <BookCard v-for="l in similar" :key="l.id" :listing="l" />
                </div>
            </div>
        </div>

        <!-- Modale de signalement -->
        <div v-if="reportOpen" class="fixed inset-0 z-[100] flex items-end md:items-center justify-center">
            <div class="absolute inset-0 bg-dark/50 backdrop-blur-sm" @click="reportOpen = false"></div>
            <div class="relative bg-white w-full md:max-w-md md:rounded-3xl rounded-t-3xl shadow-2xl">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-dark"><i class="fa-solid fa-flag text-red-500 mr-1"></i> Signaler cette annonce</h3>
                    <button @click="reportOpen = false" class="w-9 h-9 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-500"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div v-if="!reportDone" class="p-6 space-y-4">
                    <p class="text-sm text-gray-500">Aidez-nous à garder KABA sûr. Quel est le problème ?</p>
                    <label v-for="(label, key) in REASONS" :key="key" class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-colors"
                           :class="reportForm.reason === key ? 'border-red-400 bg-red-50' : 'border-gray-200'">
                        <input type="radio" :value="key" v-model="reportForm.reason" class="accent-red-500">
                        <span class="text-sm font-medium">{{ label }}</span>
                    </label>
                    <textarea v-model="reportForm.details" rows="3" placeholder="Détails (facultatif)..." class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 resize-none"></textarea>
                    <button @click="submitReport" :disabled="reportForm.processing" class="w-full bg-red-500 hover:bg-red-600 text-white py-3.5 rounded-full font-bold transition-colors disabled:opacity-60">Envoyer le signalement</button>
                </div>
                <div v-else class="p-8 text-center">
                    <div class="w-14 h-14 bg-green-100 text-green-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-3"><i class="fa-solid fa-check"></i></div>
                    <h4 class="font-black text-dark mb-1">Merci !</h4>
                    <p class="text-gray-500 text-sm mb-4">Notre équipe va examiner cette annonce.</p>
                    <button @click="reportOpen = false" class="bg-dark text-white px-6 py-2.5 rounded-full font-bold text-sm hover:bg-gray-800">Fermer</button>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
