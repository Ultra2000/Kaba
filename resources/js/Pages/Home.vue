<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import BookCard from '@/Components/BookCard.vue';

defineProps({
    featured: Array,
    categories: Array,
});
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
                    <div class="w-full aspect-square bg-white rounded-2xl shadow-soft border border-gray-100 flex items-center justify-center group-hover:border-brand-300 group-hover:shadow-lg transition-all group-hover:-translate-y-1">
                        <i class="fa-solid text-2xl md:text-3xl text-gray-300 group-hover:text-brand-500 transition-colors" :class="c.icon"></i>
                    </div>
                    <span class="font-semibold text-dark text-xs md:text-sm group-hover:text-brand-600 transition-colors text-center leading-tight">{{ c.name }}</span>
                </Link>
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
