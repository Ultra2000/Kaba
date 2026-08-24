<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    listing: { type: Object, required: true },
    accentIndex: { type: Number, default: 0 },
});

// Cascade de repli du visuel :
// couverture uploadée → couverture ISBN → photo de catégorie → dégradé d'accent.
const candidates = (() => {
    const l = props.listing;
    const arr = [];
    if (l.cover_url) arr.push(l.cover_url);
    if (l.isbn) arr.push(`https://covers.openlibrary.org/b/isbn/${l.isbn}-L.jpg`);
    if (l.category?.slug) arr.push(`/images/categories/${l.category.slug}.jpg`);
    return arr;
})();
const stage = ref(0);
const cover = () => candidates[stage.value] ?? null;
const onError = () => { stage.value += 1; };

// Palette d'accents (badge coloré + accents texte sur fond blanc).
const ACCENTS = [
    { grad: 'from-brand-500 to-brand-700', badge: 'bg-brand-600', accent: 'text-brand-600', icon: 'fa-crown' },
    { grad: 'from-orange-500 to-orange-700', badge: 'bg-orange-500', accent: 'text-orange-600', icon: 'fa-fire' },
    { grad: 'from-sky-500 to-sky-700', badge: 'bg-sky-600', accent: 'text-sky-600', icon: 'fa-star' },
];
const a = ACCENTS[props.accentIndex % ACCENTS.length];

const priceLabel = (() => {
    const l = props.listing;
    if (l.type === 'don') return 'Gratuit';
    if (l.type === 'echange') return 'Échange';
    if (l.type === 'recherche') return 'Recherché';
    return new Intl.NumberFormat('fr-FR').format(l.price) + ' F';
})();
</script>

<template>
    <div class="group w-[calc(50%-0.625rem)] sm:w-[240px] rounded-2xl overflow-hidden bg-white border border-gray-100 shadow-floating flex flex-col">
        <!-- Couverture entière (fond flou de la même image pour combler proprement) -->
        <div class="relative aspect-[3/4] overflow-hidden">
            <template v-if="cover()">
                <img :src="cover()" aria-hidden="true"
                     class="absolute inset-0 w-full h-full object-cover blur-2xl scale-125 opacity-50">
                <img :src="cover()" :alt="listing.title" loading="lazy" @error="onError"
                     class="relative z-10 w-full h-full object-contain p-4 drop-shadow-[0_10px_20px_rgba(0,0,0,0.35)] group-hover:scale-105 transition-transform duration-300">
            </template>
            <div v-else class="absolute inset-0 bg-gradient-to-br" :class="a.grad"></div>

            <!-- Badge d'accent -->
            <div class="absolute top-3 left-3 z-20 w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm shadow-lg" :class="a.badge">
                <i class="fa-solid" :class="a.icon"></i>
            </div>
        </div>

        <!-- Panneau texte -->
        <div class="p-4 flex flex-col flex-1">
            <h3 class="font-black text-dark text-base leading-tight line-clamp-1">{{ listing.title }}</h3>
            <p class="text-gray-400 text-xs mt-0.5 truncate">{{ listing.author }}</p>

            <div class="flex items-center gap-1.5 text-gray-600 text-xs font-medium mt-2">
                <i class="fa-solid fa-circle-check" :class="a.accent"></i>{{ listing.condition_label ?? 'Bon état' }}
                <span class="text-gray-300">·</span><span class="font-bold text-dark">{{ priceLabel }}</span>
            </div>

            <Link :href="`/livres/${listing.id}`"
                  class="mt-3 inline-flex items-center gap-2 font-bold text-sm hover:gap-3 transition-all" :class="a.accent">
                Voir le livre <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </Link>
        </div>
    </div>
</template>
