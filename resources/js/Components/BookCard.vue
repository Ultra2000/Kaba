<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    listing: { type: Object, required: true },
});

const page = usePage();
const isFav = computed(() => (page.props.auth?.favorites ?? []).includes(props.listing.id));
function toggleFav() {
    if (!page.props.auth?.user) { router.get('/login'); return; }
    router.post(`/favoris/${props.listing.id}`, {}, { preserveScroll: true });
}

const COVERS = ['7c3aed','f59e0b','10b981','ef4444','3b82f6','6d28d9','db2777','0891b2','65a30d','c2410c'];
const color = computed(() => COVERS[props.listing.id % COVERS.length]);
const fallback = computed(() =>
    `https://placehold.co/400x600/${color.value}/ffffff?text=${encodeURIComponent((props.listing.title || '').slice(0, 22))}`);
const cover = computed(() => props.listing.cover_url
    ? props.listing.cover_url
    : (props.listing.isbn
        ? `https://covers.openlibrary.org/b/isbn/${props.listing.isbn}-M.jpg?default=false`
        : fallback.value));
function onError(e) { e.target.onerror = null; e.target.src = fallback.value; }

const fmt = (n) => new Intl.NumberFormat('fr-FR').format(n) + ' F';
const priceLabel = computed(() => {
    switch (props.listing.type) {
        case 'don': return 'Gratuit';
        case 'echange': return 'Échange';
        case 'recherche': return 'Recherché';
        default: return fmt(props.listing.price);
    }
});
</script>

<template>
    <Link :href="`/livres/${listing.id}`" class="group block">
        <!-- Couverture -->
        <div class="relative mb-3">
            <img :src="cover" @error="onError" :alt="listing.title" loading="lazy"
                 class="w-full aspect-[2/3] object-cover rounded-[2px] bg-gray-50
                        shadow-[0_8px_20px_-6px_rgba(0,0,0,0.35)]
                        group-hover:shadow-[0_16px_32px_-8px_rgba(0,0,0,0.45)]
                        group-hover:-translate-y-1 transition-all duration-300">

            <!-- Favori (apparaît au survol) -->
            <button type="button" @click.prevent.stop="toggleFav"
                    class="absolute top-2 right-2 w-8 h-8 rounded-full bg-white/95 shadow-sm flex items-center justify-center transition-all"
                    :class="isFav ? 'opacity-100 text-orange-500' : 'opacity-0 group-hover:opacity-100 text-gray-500 hover:text-orange-500'"
                    :title="isFav ? 'Retirer des favoris' : 'Ajouter aux favoris'">
                <i class="fa-heart text-xs" :class="isFav ? 'fa-solid' : 'fa-regular'"></i>
            </button>
        </div>

        <!-- Infos -->
        <h3 class="font-bold text-dark text-sm leading-snug line-clamp-2 group-hover:text-brand-600 transition-colors">{{ listing.title }}</h3>
        <p class="text-gray-400 text-xs mt-1 truncate">{{ listing.author }}</p>
        <p class="text-dark font-semibold text-sm mt-2">{{ priceLabel }}</p>
    </Link>
</template>
