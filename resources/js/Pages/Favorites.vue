<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import BookCard from '@/Components/BookCard.vue';

defineProps({
    listings: Array,
    sellers: Array,
});

const tab = ref('livres');
const initials = (name) => name.split(/\s+/).map(w => w[0]).slice(0, 2).join('').toUpperCase();
</script>

<template>
    <Head title="Mes favoris" />
    <PublicLayout>
        <div class="max-w-[1400px] mx-auto px-4 py-8">
            <h1 class="text-3xl font-black text-dark mb-6">Mes favoris</h1>

            <div class="border-b border-gray-200 flex gap-8 mb-8">
                <button @click="tab = 'livres'" class="pb-3 border-b-2 font-bold text-sm transition-colors" :class="tab === 'livres' ? 'text-brand-600 border-brand-600' : 'text-gray-500 border-transparent hover:text-dark'">Livres ({{ listings.length }})</button>
                <button @click="tab = 'vendeurs'" class="pb-3 border-b-2 font-bold text-sm transition-colors" :class="tab === 'vendeurs' ? 'text-brand-600 border-brand-600' : 'text-gray-500 border-transparent hover:text-dark'">Vendeurs suivis ({{ sellers.length }})</button>
            </div>

            <!-- Livres -->
            <div v-if="tab === 'livres'">
                <div v-if="listings.length" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-x-5 gap-y-10 md:gap-x-8">
                    <BookCard v-for="l in listings" :key="l.id" :listing="l" />
                </div>
                <div v-else class="text-center py-16">
                    <i class="fa-regular fa-heart text-5xl text-gray-200 mb-4"></i>
                    <p class="text-gray-500 font-medium mb-4">Aucun livre en favori pour le moment.</p>
                    <Link href="/explorer" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white px-6 py-3 rounded-full font-bold shadow-floating transition-all"><i class="fa-solid fa-magnifying-glass"></i> Explorer les livres</Link>
                </div>
            </div>

            <!-- Vendeurs -->
            <div v-else>
                <div v-if="sellers.length" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="s in sellers" :key="s.id" class="bg-white border border-gray-200 rounded-2xl p-5 hover:border-brand-300 hover:shadow-soft transition-all">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-lg font-black shrink-0" :class="s.role === 'pro' ? 'bg-dark text-white' : 'bg-brand-100 text-brand-700'">{{ initials(s.name) }}</div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5"><Link :href="`/vendeurs/${s.id}`" class="font-bold text-dark hover:text-brand-600 truncate">{{ s.name }}</Link><i v-if="s.is_verified" class="fa-solid fa-circle-check text-blue-500 text-xs"></i></div>
                                <p class="text-xs text-gray-500 font-medium"><i class="fa-solid fa-location-dot"></i> {{ s.city || 'Bénin' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500 font-medium border-t border-gray-100 pt-3">
                            <span><i class="fa-solid fa-star text-yellow-400"></i> {{ s.rating_avg }}</span>
                            <span>{{ s.listings_count }} annonces</span>
                            <Link :href="`/vendeurs/${s.id}`" class="text-brand-600 font-bold hover:underline">Voir</Link>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-16">
                    <i class="fa-regular fa-user text-5xl text-gray-200 mb-4"></i>
                    <p class="text-gray-500 font-medium mb-4">Vous ne suivez aucun vendeur.</p>
                    <Link href="/vendeurs" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white px-6 py-3 rounded-full font-bold shadow-floating transition-all"><i class="fa-solid fa-users"></i> Découvrir les vendeurs</Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
