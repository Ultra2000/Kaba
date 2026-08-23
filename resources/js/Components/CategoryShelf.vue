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
            <!-- Rangée de livres (défilante) -->
            <div ref="scroller"
                 class="flex items-end gap-5 md:gap-6 overflow-x-auto pr-14 snap-x scroll-smooth [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                <Link v-for="b in books" :key="b.id" :href="`/livres/${b.id}`"
                      class="group/book shrink-0 snap-start block">
                    <img :src="cover(b)" @error="onErr($event, b)" :alt="b.title" loading="lazy"
                         class="h-44 md:h-52 w-auto object-contain rounded-[3px] bg-white
                                shadow-[0_10px_18px_-6px_rgba(0,0,0,0.4)]
                                group-hover/book:-translate-y-2 group-hover/book:shadow-[0_18px_28px_-8px_rgba(0,0,0,0.5)]
                                transition-all duration-300 origin-bottom">
                </Link>
                <div v-if="!books.length" class="text-gray-400 text-sm py-16">Bientôt des livres ici.</div>
            </div>

            <!-- Planche en bois -->
            <div class="h-3 rounded-[2px] bg-gradient-to-b from-[#d3a978] via-[#b07f4a] to-[#7c5423] shadow-[0_12px_16px_-8px_rgba(0,0,0,0.45)]"></div>
            <div class="h-4 mx-6 bg-gradient-to-b from-black/12 to-transparent rounded-b-full"></div>

            <!-- Flèche de défilement -->
            <button v-if="books.length > 3" type="button" @click="next"
                    class="absolute right-0 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white shadow-floating border border-gray-100 flex items-center justify-center text-dark hover:text-brand-600 hover:scale-105 transition-all"
                    aria-label="Suivant">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </div>
</template>
