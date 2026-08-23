<script setup>
import { reactive, ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import BookCard from '@/Components/BookCard.vue';
import FilterPanel from '@/Components/FilterPanel.vue';

const props = defineProps({
    listings: Object,
    categories: Array,
    cities: Array,
    conditions: Object,
    languages: Array,
    filters: Object,
});

const form = reactive({
    q: props.filters.q ?? '',
    type: props.filters.type ?? 'all',
    category: props.filters.category ?? 'all',
    city: props.filters.city ?? 'all',
    condition: props.filters.condition ?? 'all',
    language: props.filters.language ?? 'all',
    price_max: props.filters.price_max ?? 40000,
    sort: props.filters.sort ?? 'popular',
});

const TYPES = [
    { v: 'all', label: 'Tout' },
    { v: 'vente', label: 'Vente' },
    { v: 'don', label: 'Don' },
    { v: 'echange', label: 'Échange' },
    { v: 'recherche', label: 'Recherche' },
];

function apply() {
    const params = {};
    for (const [k, v] of Object.entries(form)) {
        if (v !== '' && v !== 'all' && !(k === 'price_max' && v == 40000) && !(k === 'sort' && v === 'popular')) {
            params[k] = v;
        }
    }
    router.get('/explorer', params, { preserveState: true, preserveScroll: true, replace: true });
}
function setType(v) { form.type = v; apply(); }
function reset() {
    Object.assign(form, { q: '', type: 'all', category: 'all', city: 'all', condition: 'all', language: 'all', price_max: 40000, sort: 'popular' });
    apply();
}
const fmt = (n) => Number(n) === 0 ? 'Gratuit' : new Intl.NumberFormat('fr-FR').format(n) + ' F';

const mobileFiltersOpen = ref(false);
// Nombre de filtres actifs (hors recherche et tri) — pour le badge du bouton mobile.
const activeFilters = computed(() =>
    ['type', 'category', 'city', 'condition', 'language'].filter((k) => form[k] !== 'all').length
    + (Number(form.price_max) < 40000 ? 1 : 0));
function applyMobile() { apply(); mobileFiltersOpen.value = false; }
</script>

<template>
    <Head title="Explorer les livres" />
    <PublicLayout>
        <div class="max-w-[1400px] mx-auto px-4 py-6">
            <div class="flex items-center gap-2 text-sm text-gray-500 font-medium mb-4">
                <Link href="/" class="hover:text-brand-600">Accueil</Link>
                <i class="fa-solid fa-chevron-right text-[10px] text-gray-300"></i>
                <span class="text-dark font-semibold">Explorer</span>
            </div>

            <div class="flex gap-8">
                <!-- Sidebar filtres (desktop) -->
                <aside class="hidden lg:block w-64 shrink-0">
                    <div class="sticky top-28">
                        <FilterPanel :form="form" :categories="categories" :cities="cities"
                                     :conditions="conditions" :languages="languages" :types="TYPES"
                                     @apply="apply" @set-type="setType" @reset="reset" />
                    </div>
                </aside>

                <!-- Résultats -->
                <div class="flex-1 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                        <p class="text-gray-500 text-sm font-medium"><span class="font-bold text-dark">{{ listings.total }}</span> résultat{{ listings.total > 1 ? 's' : '' }}</p>
                        <form @submit.prevent="apply" class="flex items-center gap-2 w-full sm:w-auto">
                            <div class="relative flex-1 sm:w-64">
                                <i class="fa-solid fa-magnifying-glass text-gray-400 text-sm absolute left-3 top-1/2 -translate-y-1/2"></i>
                                <input v-model="form.q" class="w-full h-10 pl-9 pr-3 bg-gray-50 border border-gray-200 rounded-full text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200" placeholder="Rechercher...">
                            </div>
                            <select v-model="form.sort" @change="apply" class="px-3 py-2 rounded-xl border border-gray-200 text-sm font-medium text-gray-700 bg-white outline-none focus:border-brand-500">
                                <option value="popular">Populaires</option>
                                <option value="price-asc">Prix ↑</option>
                                <option value="price-desc">Prix ↓</option>
                            </select>
                            <button type="button" @click="mobileFiltersOpen = true"
                                    class="lg:hidden shrink-0 relative flex items-center gap-2 px-4 h-10 rounded-full border border-gray-200 text-sm font-bold text-gray-700 bg-white hover:border-brand-300">
                                <i class="fa-solid fa-sliders"></i> Filtres
                                <span v-if="activeFilters" class="bg-brand-600 text-white text-[10px] font-bold rounded-full min-w-4 h-4 px-1 flex items-center justify-center">{{ activeFilters }}</span>
                            </button>
                        </form>
                    </div>

                    <div v-if="listings.data.length" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-x-5 gap-y-10 md:gap-x-8">
                        <BookCard v-for="l in listings.data" :key="l.id" :listing="l" />
                    </div>
                    <div v-else class="text-center py-16">
                        <i class="fa-solid fa-book-open text-5xl text-gray-200 mb-4"></i>
                        <p class="text-gray-500 font-medium">Aucun livre ne correspond à ces critères.</p>
                        <button @click="reset" class="mt-4 text-brand-600 font-bold hover:underline">Réinitialiser les filtres</button>
                    </div>

                    <!-- Pagination -->
                    <div v-if="listings.last_page > 1" class="flex flex-wrap gap-1.5 justify-center mt-10">
                        <template v-for="(link, i) in listings.links" :key="i">
                            <Link v-if="link.url" :href="link.url" preserve-scroll
                                  class="min-w-9 h-9 px-3 rounded-full flex items-center justify-center text-sm font-bold transition-colors"
                                  :class="link.active ? 'bg-brand-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:border-brand-300'"
                                  v-html="link.label" />
                            <span v-else class="min-w-9 h-9 px-3 rounded-full flex items-center justify-center text-sm text-gray-300" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tiroir filtres (mobile / tablette) -->
        <Teleport to="body">
            <transition
                enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="mobileFiltersOpen" class="lg:hidden fixed inset-0 z-[60] bg-black/40" @click="mobileFiltersOpen = false"></div>
            </transition>
            <transition
                enter-active-class="transition-transform duration-300" enter-from-class="translate-x-full" enter-to-class="translate-x-0"
                leave-active-class="transition-transform duration-300" leave-from-class="translate-x-0" leave-to-class="translate-x-full">
                <div v-if="mobileFiltersOpen" class="lg:hidden fixed top-0 right-0 z-[61] h-full w-[85%] max-w-sm bg-white shadow-2xl flex flex-col">
                    <div class="flex items-center justify-between px-5 h-16 border-b border-gray-100 shrink-0">
                        <span class="font-black text-dark text-lg">Filtres</span>
                        <button @click="mobileFiltersOpen = false" class="w-9 h-9 rounded-full flex items-center justify-center text-gray-500 hover:bg-gray-100">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto px-5 py-6">
                        <FilterPanel :form="form" :categories="categories" :cities="cities"
                                     :conditions="conditions" :languages="languages" :types="TYPES" hide-title
                                     @apply="apply" @set-type="setType" @reset="reset" />
                    </div>
                    <div class="px-5 py-4 border-t border-gray-100 shrink-0">
                        <button @click="applyMobile" class="w-full h-12 rounded-full bg-brand-600 text-white font-bold hover:bg-brand-700 transition-colors">
                            Voir {{ listings.total }} résultat{{ listings.total > 1 ? 's' : '' }}
                        </button>
                    </div>
                </div>
            </transition>
        </Teleport>
    </PublicLayout>
</template>
