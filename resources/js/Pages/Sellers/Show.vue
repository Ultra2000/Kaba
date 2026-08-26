<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import BookCard from '@/Components/BookCard.vue';

const props = defineProps({
    seller: Object,
    listings: Array,
    reviews: Array,
    isFollowing: Boolean,
    canReview: Boolean,
});

const page = usePage();
const tab = ref('annonces');
const initials = computed(() => props.seller.name.split(/\s+/).map(w => w[0]).slice(0, 2).join('').toUpperCase());

function toggleFollow() {
    if (!page.props.auth?.user) { router.get('/login'); return; }
    router.post(`/vendeurs/${props.seller.id}/suivre`, {}, { preserveScroll: true });
}

const reviewForm = useForm({ rating: 5, comment: '' });
function submitReview() {
    reviewForm.post(`/vendeurs/${props.seller.id}/avis`, {
        preserveScroll: true,
        onSuccess: () => reviewForm.reset('comment'),
    });
}
const stars = (n) => Array.from({ length: 5 }, (_, i) => i < n);
</script>

<template>
    <Head :title="seller.name" />
    <PublicLayout>
        <!-- Bandeau -->
        <div class="bg-brand-100">
            <div class="max-w-[1400px] mx-auto px-4 pt-10 pb-8">
                <div class="flex flex-col md:flex-row items-center md:items-end gap-6">
                    <div class="w-28 h-28 rounded-3xl bg-white text-brand-700 flex items-center justify-center text-4xl font-black shadow-soft shrink-0">{{ initials }}</div>
                    <div class="flex-1 text-center md:text-left">
                        <div class="flex items-center justify-center md:justify-start gap-2 mb-1">
                            <h1 class="text-3xl font-black text-dark">{{ seller.name }}</h1>
                            <span v-if="seller.is_verified" class="text-blue-500 text-xl" title="Vérifié"><i class="fa-solid fa-circle-check"></i></span>
                        </div>
                        <p class="text-gray-600 font-medium mb-3">
                            <span v-if="seller.role === 'pro'" class="font-bold text-dark"><i class="fa-solid fa-store text-brand-600"></i> Boutique · </span>
                            <i class="fa-solid fa-location-dot text-brand-600"></i> {{ seller.city || 'Bénin' }}
                        </p>
                        <!-- Présentation écrite par le membre -->
                        <p v-if="seller.bio" class="text-gray-700 leading-relaxed max-w-2xl mb-3 whitespace-pre-line">{{ seller.bio }}</p>

                        <div class="flex flex-wrap justify-center md:justify-start gap-2">
                            <span v-if="seller.is_verified" class="bg-white/70 text-brand-700 text-xs font-bold px-3 py-1.5 rounded-full"><i class="fa-solid fa-phone"></i> Vérifié</span>
                            <span class="bg-white/70 text-brand-700 text-xs font-bold px-3 py-1.5 rounded-full"><i class="fa-solid fa-star text-yellow-500"></i> {{ seller.rating_avg }} · {{ seller.reviews_count }} avis</span>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button @click="toggleFollow" class="px-6 py-3 rounded-full font-bold shadow-floating transition-all active:scale-95 flex items-center gap-2"
                                :class="isFollowing ? 'bg-white text-brand-700 border-2 border-brand-200' : 'bg-brand-600 hover:bg-brand-700 text-white'">
                            <i class="fa-solid" :class="isFollowing ? 'fa-check' : 'fa-user-plus'"></i> {{ isFollowing ? 'Suivi' : 'Suivre' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="max-w-[1400px] mx-auto px-4 -mt-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl shadow-soft border border-gray-100 p-5 text-center"><p class="text-3xl font-black text-dark">{{ seller.rating_avg }}</p><p class="text-xs text-gray-500 font-semibold uppercase mt-1"><i class="fa-solid fa-star text-yellow-400"></i> Note</p></div>
                <div class="bg-white rounded-2xl shadow-soft border border-gray-100 p-5 text-center"><p class="text-3xl font-black text-dark">{{ seller.sales_count }}</p><p class="text-xs text-gray-500 font-semibold uppercase mt-1">Ventes</p></div>
                <div class="bg-white rounded-2xl shadow-soft border border-gray-100 p-5 text-center"><p class="text-3xl font-black text-dark">{{ seller.active_count }}</p><p class="text-xs text-gray-500 font-semibold uppercase mt-1">Annonces</p></div>
                <div class="bg-white rounded-2xl shadow-soft border border-gray-100 p-5 text-center"><p class="text-3xl font-black text-dark">{{ seller.followers_count }}</p><p class="text-xs text-gray-500 font-semibold uppercase mt-1">Abonnés</p></div>
            </div>
        </div>

        <!-- Onglets -->
        <div class="max-w-[1400px] mx-auto px-4 mt-10 pb-16">
            <div class="border-b border-gray-200 flex gap-8 mb-8">
                <button @click="tab = 'annonces'" class="pb-3 border-b-2 font-bold text-sm transition-colors" :class="tab === 'annonces' ? 'text-brand-600 border-brand-600' : 'text-gray-500 border-transparent hover:text-dark'">Annonces ({{ listings.length }})</button>
                <button @click="tab = 'avis'" class="pb-3 border-b-2 font-bold text-sm transition-colors" :class="tab === 'avis' ? 'text-brand-600 border-brand-600' : 'text-gray-500 border-transparent hover:text-dark'">Avis ({{ reviews.length }})</button>
            </div>

            <!-- Annonces -->
            <div v-if="tab === 'annonces'">
                <div v-if="listings.length" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-x-5 gap-y-10 md:gap-x-8">
                    <BookCard v-for="l in listings" :key="l.id" :listing="l" />
                </div>
                <p v-else class="text-gray-500 text-center py-12">Aucune annonce active.</p>
            </div>

            <!-- Avis -->
            <div v-else class="grid md:grid-cols-3 gap-6">
                <div class="md:col-span-2 space-y-4">
                    <div v-for="r in reviews" :key="r.id" class="bg-white border border-gray-200 rounded-2xl p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold">{{ r.author.name.split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase() }}</div>
                            <div><p class="font-bold text-dark text-sm">{{ r.author.name }}</p><p class="text-xs text-gray-400">{{ new Date(r.created_at).toLocaleDateString('fr-FR') }}</p></div>
                            <div class="ml-auto text-yellow-400 text-xs"><i v-for="(f,i) in stars(r.rating)" :key="i" class="fa-star" :class="f ? 'fa-solid' : 'fa-regular'"></i></div>
                        </div>
                        <p v-if="r.comment" class="text-sm text-gray-600">{{ r.comment }}</p>
                    </div>
                    <p v-if="!reviews.length" class="text-gray-500 text-center py-8">Aucun avis pour le moment.</p>
                </div>

                <!-- Formulaire d'avis -->
                <div>
                    <div v-if="canReview" class="bg-gray-50 border border-gray-100 rounded-2xl p-5">
                        <h3 class="font-bold text-dark mb-3">Laisser un avis</h3>
                        <div class="flex gap-1 text-2xl mb-3">
                            <button v-for="n in 5" :key="n" type="button" @click="reviewForm.rating = n" class="transition-colors" :class="n <= reviewForm.rating ? 'text-yellow-400' : 'text-gray-300 hover:text-yellow-300'"><i class="fa-solid fa-star"></i></button>
                        </div>
                        <textarea v-model="reviewForm.comment" rows="3" placeholder="Votre commentaire (optionnel)" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 resize-none mb-3"></textarea>
                        <button @click="submitReview" :disabled="reviewForm.processing" class="w-full bg-brand-600 hover:bg-brand-700 text-white py-2.5 rounded-full font-bold transition-colors disabled:opacity-60">Publier mon avis</button>
                    </div>
                    <div v-else-if="page.props.auth?.user && page.props.auth.user.id !== seller.id" class="bg-green-50 border border-green-200 text-green-700 rounded-2xl p-5 text-sm">
                        <i class="fa-solid fa-circle-check"></i> Vous avez déjà évalué ce vendeur.
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
