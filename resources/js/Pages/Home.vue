<script setup>
import { reactive } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import BookCard from '@/Components/BookCard.vue';

defineProps({
    featured: Array,
    categories: Array,
    topRomans: { type: Array, default: () => [] },
});

// Image d'une catégorie : override en base, sinon convention /images/categories/{slug}.jpg
const catImage = (c) => c.image ? c.image : `/images/categories/${c.slug}.jpg`;
// Slugs dont l'image a échoué → on retombe sur l'icône.
const failedImages = reactive(new Set());
const onImageError = (slug) => failedImages.add(slug);

// Couverture d'un livre pour le fond des cards « Meilleurs romans ».
const bookCover = (l) => l.cover_url
    || (l.isbn ? `https://covers.openlibrary.org/b/isbn/${l.isbn}-L.jpg` : null);

// Palette d'accents (3 cards, style immersif façon référence).
const romanAccents = [
    { grad: 'from-brand-700 to-brand-900', badge: 'bg-brand-600', check: 'text-brand-300', link: 'text-brand-200 border-brand-300', icon: 'fa-crown' },
    { grad: 'from-orange-700 to-orange-900', badge: 'bg-orange-500', check: 'text-orange-300', link: 'text-orange-200 border-orange-300', icon: 'fa-fire' },
    { grad: 'from-sky-700 to-sky-900', badge: 'bg-sky-600', check: 'text-sky-300', link: 'text-sky-200 border-sky-300', icon: 'fa-star' },
];

const romanPrice = (l) => {
    if (l.type === 'don') return 'Don gratuit';
    if (l.type === 'echange') return 'Ouvert à l\'échange';
    if (l.type === 'recherche') return 'Recherché';
    return new Intl.NumberFormat('fr-FR').format(l.price) + ' FCFA';
};
const failedCovers = reactive(new Set());
const onCoverError = (id) => failedCovers.add(id);
</script>

<template>
    <Head title="Achat, vente, don et échange de livres d'occasion" />
    <PublicLayout>
        <!-- HERO -->
        <section class="max-w-[1400px] mx-auto px-4 py-8">
            <div class="relative bg-brand-100 rounded-3xl overflow-hidden min-h-[300px] md:h-[400px] flex items-center p-8 md:p-16">
                <div class="relative z-10 max-w-xl">
                    <h1 class="text-4xl md:text-6xl font-black text-dark leading-tight mb-4 tracking-tight">
                        Donnez une seconde vie
                        <span class="bg-brand-500 text-white px-2 py-1 rounded-lg italic -rotate-2 inline-block mt-2">à vos livres !</span>
                    </h1>
                    <p class="text-lg md:text-xl text-dark font-medium mb-8">Vendez, achetez, donnez ou échangez vos ouvrages partout au Bénin.</p>
                    <div class="flex flex-wrap gap-4">
                        <Link href="/explorer" class="bg-dark text-white px-6 py-3 rounded-full font-bold hover:bg-gray-800 transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-magnifying-glass"></i> Explorer les livres
                        </Link>
                        <Link href="/publier" class="bg-brand-600 hover:bg-brand-700 text-white px-6 py-3 rounded-full font-bold shadow-floating transition-all active:scale-95 flex items-center gap-2">
                            <i class="fa-solid fa-plus"></i> Vendre / Donner
                        </Link>
                    </div>
                </div>

                <!-- Illustration téléphone -->
                <div class="absolute right-0 bottom-0 top-0 w-1/2 hidden lg:flex justify-end items-end pr-16 pb-8">
                    <div class="w-64 h-[450px] bg-white rounded-[2.5rem] shadow-2xl border-[12px] border-dark relative rotate-6 translate-y-12 flex flex-col overflow-hidden">
                        <div class="absolute top-0 w-full h-6 flex justify-center z-20">
                            <div class="w-1/3 h-full bg-dark rounded-b-xl"></div>
                        </div>
                        <div class="bg-gray-50 flex-1 p-4 pt-8">
                            <div class="font-bold text-sm mb-4">Mes annonces</div>
                            <div class="space-y-3">
                                <div class="bg-white p-3 rounded-xl shadow-sm flex gap-3 items-center">
                                    <div class="w-10 h-14 bg-gray-200 rounded"></div>
                                    <div class="flex-1">
                                        <div class="h-3 bg-gray-200 rounded w-3/4 mb-2"></div>
                                        <div class="h-2 bg-gray-200 rounded w-1/2"></div>
                                    </div>
                                    <div class="text-brand-600 font-bold text-sm">3000 F</div>
                                </div>
                                <div class="bg-white p-3 rounded-xl shadow-sm flex gap-3 items-center opacity-70">
                                    <div class="w-10 h-14 bg-gray-200 rounded"></div>
                                    <div class="flex-1">
                                        <div class="h-3 bg-gray-200 rounded w-full mb-2"></div>
                                        <div class="h-2 bg-gray-200 rounded w-2/3"></div>
                                    </div>
                                    <div class="text-orange-500 font-bold uppercase text-[10px]">Don</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="absolute right-64 bottom-24 w-32 h-32 text-brand-600 opacity-20">
                        <i class="fa-solid fa-book-open text-9xl -rotate-12"></i>
                    </div>
                </div>
            </div>

            <!-- Points de pagination (décoratif) -->
            <div class="flex justify-center gap-2 mt-6">
                <button class="w-2.5 h-2.5 rounded-full bg-dark"></button>
                <button class="w-2.5 h-2.5 rounded-full bg-gray-200 hover:bg-gray-300"></button>
                <button class="w-2.5 h-2.5 rounded-full bg-gray-200 hover:bg-gray-300"></button>
                <button class="w-2.5 h-2.5 rounded-full bg-gray-200 hover:bg-gray-300"></button>
            </div>
        </section>

        <!-- Recommandés -->
        <section class="max-w-[1400px] mx-auto px-4 mt-10">
            <div class="flex items-end justify-between border-b border-gray-200 pb-4 mb-8">
                <h2 class="text-2xl md:text-3xl font-black text-dark">Les best-sellers</h2>
                <Link href="/explorer" class="text-sm font-bold text-gray-500 hover:text-brand-600 shrink-0">Plus de livres →</Link>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-x-5 gap-y-10 md:gap-x-8">
                <BookCard v-for="l in featured" :key="l.id" :listing="l" />
            </div>
        </section>

        <!-- Catégories (une seule ligne défilante) -->
        <section class="max-w-[1400px] mx-auto px-4 mt-16">
            <div class="flex items-end justify-between mb-6">
                <h2 class="text-2xl md:text-3xl font-black text-dark">Explorer par catégorie</h2>
                <Link href="/explorer" class="text-sm font-bold text-gray-500 hover:text-brand-600 shrink-0">Voir tout →</Link>
            </div>
            <div class="flex gap-3 md:gap-4 overflow-x-auto pb-3 -mx-4 px-4 snap-x scroll-smooth [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                <Link v-for="c in categories" :key="c.slug" :href="`/explorer?category=${c.slug}`"
                      class="shrink-0 snap-start w-24 md:w-28 flex flex-col items-center gap-2.5 group">
                    <div class="w-full aspect-square bg-white rounded-2xl shadow-soft border border-gray-100 overflow-hidden flex items-center justify-center group-hover:border-brand-300 group-hover:shadow-lg transition-all group-hover:-translate-y-1">
                        <img v-if="!failedImages.has(c.slug)" :src="catImage(c)" :alt="c.name" loading="lazy"
                             @error="onImageError(c.slug)"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <i v-else class="fa-solid text-2xl md:text-3xl text-gray-300 group-hover:text-brand-500 transition-colors" :class="c.icon"></i>
                    </div>
                    <span class="font-semibold text-dark text-xs md:text-sm group-hover:text-brand-600 transition-colors text-center leading-tight">{{ c.name }}</span>
                </Link>
            </div>
        </section>

        <!-- Meilleurs romans (style immersif) -->
        <section v-if="topRomans.length" class="max-w-[1400px] mx-auto px-4 mt-20">
            <div class="flex items-end justify-between mb-8">
                <div>
                    <p class="text-brand-600 font-bold text-sm uppercase tracking-wider mb-1">À ne pas manquer</p>
                    <h2 class="text-2xl md:text-3xl font-black text-dark">Les meilleurs romans</h2>
                </div>
                <Link href="/explorer?category=roman" class="text-sm font-bold text-gray-500 hover:text-brand-600 shrink-0">Tous les romans →</Link>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                <div v-for="(r, i) in topRomans" :key="r.id"
                     class="group relative rounded-3xl overflow-hidden min-h-[440px] flex flex-col shadow-floating">
                    <!-- Fond : couverture assombrie, sinon dégradé d'accent -->
                    <img v-if="bookCover(r) && !failedCovers.has(r.id)" :src="bookCover(r)" :alt="r.title" loading="lazy"
                         @error="onCoverError(r.id)"
                         class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div v-else class="absolute inset-0 bg-gradient-to-br" :class="romanAccents[i % 3].grad"></div>
                    <!-- Voile sombre pour la lisibilité -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/60 to-black/25"></div>

                    <!-- Contenu -->
                    <div class="relative z-10 flex flex-col h-full p-7">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white text-xl shadow-lg" :class="romanAccents[i % 3].badge">
                            <i class="fa-solid" :class="romanAccents[i % 3].icon"></i>
                        </div>

                        <div class="mt-auto">
                            <h3 class="text-2xl font-black text-white leading-tight mb-2">{{ r.title }}</h3>
                            <p class="text-white/75 text-sm font-medium mb-5">{{ r.author }} — disponible à {{ r.city }}.</p>

                            <ul class="space-y-2.5 mb-6">
                                <li class="flex items-center gap-2.5 text-white text-sm font-medium">
                                    <i class="fa-solid fa-circle-check" :class="romanAccents[i % 3].check"></i>
                                    {{ r.condition_label ?? 'Bon état' }}
                                </li>
                                <li class="flex items-center gap-2.5 text-white text-sm font-medium">
                                    <i class="fa-solid fa-circle-check" :class="romanAccents[i % 3].check"></i>
                                    {{ romanPrice(r) }}
                                </li>
                            </ul>

                            <Link :href="`/livres/${r.id}`"
                                  class="inline-flex items-center gap-2 font-bold text-sm pb-1 border-b-2 transition-colors" :class="romanAccents[i % 3].link">
                                Voir le livre <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Comment ça marche -->
        <section class="max-w-[1100px] mx-auto px-4 py-20 text-center">
            <h2 class="text-3xl md:text-4xl font-black text-dark mb-12">Comment ça marche ?</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="w-16 h-16 mx-auto bg-white border-4 border-brand-100 rounded-full flex items-center justify-center text-2xl font-black text-brand-500 mb-4">1</div>
                    <h3 class="text-lg font-bold text-dark mb-2">Publiez</h3>
                    <p class="text-gray-500 text-sm">Photos, état, prix — ou choisissez de donner / échanger.</p>
                </div>
                <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="w-16 h-16 mx-auto bg-white border-4 border-brand-100 rounded-full flex items-center justify-center text-2xl font-black text-brand-500 mb-4">2</div>
                    <h3 class="text-lg font-bold text-dark mb-2">Trouvez preneur</h3>
                    <p class="text-gray-500 text-sm">Visible par des milliers de lecteurs au Bénin.</p>
                </div>
                <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="w-16 h-16 mx-auto bg-white border-4 border-brand-100 rounded-full flex items-center justify-center text-2xl font-black text-brand-500 mb-4">3</div>
                    <h3 class="text-lg font-bold text-dark mb-2">Remettez</h3>
                    <p class="text-gray-500 text-sm">En main propre, paiement cash ou Mobile Money.</p>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
