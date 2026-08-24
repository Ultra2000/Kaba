<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    title: String,
    description: String,
    icon: { type: String, default: 'fa-book-open' },
    href: { type: String, default: '/explorer' },
    books: { type: Array, default: () => [] },
});

const scroller = ref(null);
function next() { scroller.value?.scrollBy({ left: 340, behavior: 'smooth' }); }
function prev() { scroller.value?.scrollBy({ left: -340, behavior: 'smooth' }); }

// Couverture : annonce → ISBN → placeholder coloré (une étagère a toujours des couvertures).
const COVERS = ['7c3aed', 'f59e0b', '10b981', 'ef4444', '3b82f6', '6d28d9', 'db2777', '0891b2', '65a30d', 'c2410c'];
const placeholder = (l) =>
    `https://placehold.co/300x450/${COVERS[l.id % COVERS.length]}/ffffff?text=${encodeURIComponent((l.title || '').slice(0, 20))}`;
const cover = (l) => l.cover_url
    ? l.cover_url
    : (l.isbn ? `https://covers.openlibrary.org/b/isbn/${l.isbn}-L.jpg?default=false` : placeholder(l));
function onErr(e, l) { e.target.onerror = null; e.target.src = placeholder(l); }
</script>

<template>
    <div class="flex flex-col md:flex-row gap-6 md:gap-8 items-center">
        <!-- Colonne info -->
        <div class="w-full md:w-52 shrink-0 md:text-left">
            <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center text-xl mb-3">
                <i class="fa-solid" :class="icon"></i>
            </div>
            <h3 class="text-xl font-black text-dark leading-tight">{{ title }}</h3>
            <p v-if="description" class="text-gray-500 text-sm mt-1.5 mb-4">{{ description }}</p>
            <Link :href="href"
                  class="inline-flex items-center gap-2 h-10 px-4 rounded-full border border-gray-200 text-sm font-bold text-dark hover:border-brand-300 hover:text-brand-600 transition-colors">
                Voir tout <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </Link>
        </div>

        <!-- Étagère -->
        <div class="relative flex-1 min-w-0 w-full">
            <!-- Rangée de livres (défilante, avec perspective 3D) -->
            <div ref="scroller"
                 class="flex items-end gap-6 md:gap-7 overflow-x-auto overflow-y-hidden pr-14 pt-6 snap-x scroll-smooth [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                <Link v-for="b in books" :key="b.id" :href="`/livres/${b.id}`"
                      class="group/book shrink-0 snap-start relative block origin-bottom transition-transform duration-300 ease-out hover:-translate-y-2.5">
                    <!-- Ombre de contact sur l'étagère -->
                    <div class="absolute inset-x-1 -bottom-1 h-2.5 bg-black/40 blur-md rounded-[50%]
                                transition-all duration-300 group-hover/book:inset-x-0 group-hover/book:bg-black/30 group-hover/book:blur-lg"></div>
                    <!-- Livre : couverture + tranche de pages (épaisseur) -->
                    <div class="relative rounded-[3px] overflow-hidden shadow-[0_10px_18px_-6px_rgba(0,0,0,0.45)]
                                border-r-[3px] border-r-[#e6ddca] group-hover/book:shadow-[0_20px_30px_-8px_rgba(0,0,0,0.5)] transition-shadow duration-300">
                        <img :src="cover(b)" @error="onErr($event, b)" :alt="b.title" loading="lazy"
                             class="h-44 md:h-52 w-auto object-contain bg-white block">
                        <!-- Reliure (bord gauche, léger creux) -->
                        <div class="absolute inset-y-0 left-0 w-2.5 bg-gradient-to-r from-black/35 via-black/10 to-transparent"></div>
                        <!-- Reflet lumineux -->
                        <div class="absolute inset-y-0 left-2.5 w-[3px] bg-white/25"></div>
                    </div>
                </Link>
                <div v-if="!books.length" class="text-gray-400 text-sm py-16">Bientôt des livres ici.</div>
            </div>

            <!-- Planche en bois clair (texture) + épaisseur 3D -->
            <div class="relative">
                <!-- Face supérieure -->
                <div class="h-3 bg-repeat-x rounded-t-[2px]"
                     style="background-image:url('/images/wood-shelf.svg'); background-size:auto 100%"></div>
                <!-- Épaisseur (bord avant, bois clair ombré) -->
                <div class="h-3 bg-gradient-to-b from-[#c8aa79] to-[#a1855b] shadow-[inset_0_1px_0_rgba(0,0,0,0.12)] rounded-b-[2px]"></div>
                <!-- Ombre portée au sol -->
                <div class="h-4 mx-8 bg-gradient-to-b from-black/18 to-transparent rounded-[50%] blur-[2px]"></div>
            </div>

            <!-- Flèche de défilement -->
            <button v-if="books.length > 3" type="button" @click="next"
                    class="absolute right-0 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white shadow-floating border border-gray-100 flex items-center justify-center text-dark hover:text-brand-600 hover:scale-105 transition-all"
                    aria-label="Suivant">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </div>
</template>
