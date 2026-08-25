<script setup>
import { reactive } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import BookCard from '@/Components/BookCard.vue';
import CategoryShelf from '@/Components/CategoryShelf.vue';
import FeatureBookCard from '@/Components/FeatureBookCard.vue';

defineProps({
    featured: Array,
    categories: Array,
    topRomans: { type: Array, default: () => [] },
    shelves: { type: Array, default: () => [] },
});

// Image d'une catégorie : override en base, sinon convention /images/categories/{slug}.jpg
const catImage = (c) => c.image ? c.image : `/images/categories/${c.slug}.jpg`;
// Slugs dont l'image a échoué → on retombe sur l'icône.
const failedImages = reactive(new Set());
const onImageError = (slug) => failedImages.add(slug);

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

                <!-- Illustration : étagère KABA -->
                <div class="absolute right-0 bottom-0 top-0 w-[55%] hidden lg:flex justify-end items-center pr-6 xl:pr-10">
                    <img src="/images/hero-image.webp" alt="Une pile de livres d'occasion sur une étagère"
                         width="1240" height="821" fetchpriority="high" decoding="async"
                         class="w-full max-w-[620px] h-auto object-contain drop-shadow-2xl">
                </div>
            </div>

        </section>

        <!-- Recommandés -->
        <section class="max-w-[1400px] mx-auto px-4 mt-10">
            <div class="flex items-end justify-between border-b border-gray-200 pb-4 mb-8">
                <h2 class="text-2xl md:text-3xl font-black text-dark">Les best-sellers</h2>
                <Link href="/explorer" class="text-sm font-bold text-gray-500 hover:text-brand-600 shrink-0">Plus de livres →</Link>
            </div>
            <div class="flex flex-wrap gap-5">
                <FeatureBookCard v-for="(l, i) in featured" :key="l.id" :listing="l" :accent-index="i" />
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

            <div class="flex flex-wrap gap-5">
                <FeatureBookCard v-for="(r, i) in topRomans" :key="r.id" :listing="r" :accent-index="i" />
            </div>
        </section>

        <!-- Disposition en étagères par catégorie -->
        <section v-if="shelves.length" class="max-w-[1400px] mx-auto px-4 mt-24">
            <div class="text-center mb-12">
                <p class="text-brand-600 font-bold text-sm uppercase tracking-wider mb-1">Notre bibliothèque</p>
                <h2 class="text-2xl md:text-3xl font-black text-dark">Parcourez par catégorie</h2>
            </div>
            <div class="space-y-6">
                <CategoryShelf v-for="s in shelves" :key="s.slug"
                               :title="s.title"
                               :description="s.description"
                               :icon="s.icon"
                               :href="`/explorer?category=${s.slug}`"
                               :books="s.books" />
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
