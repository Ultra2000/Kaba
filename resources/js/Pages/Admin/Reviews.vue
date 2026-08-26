<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPagination from '@/Components/AdminPagination.vue';

const props = defineProps({ reviews: Object, filters: Object, average: Number });

const q = ref(props.filters.q ?? '');
let timer;
watch(q, (v) => {
    clearTimeout(timer);
    timer = setTimeout(() => apply({ q: v }), 350);
});

function apply(changes) {
    router.get('/admin/avis', { ...props.filters, ...changes, page: 1 }, {
        preserveState: true, preserveScroll: true, replace: true,
    });
}

function destroy(r) {
    if (confirm(`Supprimer cet avis de ${r.author} ? La note de ${r.seller} sera recalculée.`)) {
        router.delete(`/admin/avis/${r.id}`, { preserveScroll: true });
    }
}

const date = (iso) => new Date(iso).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: '2-digit' });
</script>

<template>
    <Head title="Admin — Avis" />
    <AdminLayout title="Avis" :subtitle="`${reviews.total} avis · note moyenne ${average}/5`">

        <!-- Filtres -->
        <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-4 flex flex-wrap gap-2">
            <div class="relative flex-1 min-w-[200px]">
                <i class="fa-solid fa-magnifying-glass text-gray-400 text-xs absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input v-model="q" type="search" placeholder="Rechercher dans les commentaires…"
                       class="w-full h-9 pl-8 pr-3 rounded-lg border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
            </div>
            <select :value="filters.rating" @change="apply({ rating: $event.target.value })"
                    class="h-9 px-3 rounded-lg border border-gray-200 text-sm bg-white outline-none focus:border-brand-500">
                <option value="all">Toutes les notes</option>
                <option v-for="n in 5" :key="n" :value="n">{{ n }} étoile{{ n > 1 ? 's' : '' }}</option>
            </select>
        </div>

        <!-- Liste -->
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <ul v-if="reviews.data.length" class="divide-y divide-gray-100">
                <li v-for="r in reviews.data" :key="r.id" class="px-5 py-4 hover:bg-gray-50">
                    <div class="flex flex-wrap items-start gap-3">
                        <div class="flex items-center gap-0.5 shrink-0">
                            <i v-for="n in 5" :key="n" class="fa-solid fa-star text-xs"
                               :class="n <= r.rating ? 'text-yellow-400' : 'text-gray-200'"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-700">
                                <span class="font-bold text-dark">{{ r.author }}</span>
                                <span class="text-gray-400"> a évalué </span>
                                <Link :href="`/vendeurs/${r.seller_id}`" class="font-bold text-dark hover:text-brand-600">{{ r.seller }}</Link>
                                <span v-if="r.listing" class="text-gray-400"> · {{ r.listing }}</span>
                            </p>
                            <p v-if="r.comment" class="text-sm text-gray-600 italic mt-1">« {{ r.comment }} »</p>
                            <p v-else class="text-xs text-gray-300 italic mt-1">Sans commentaire</p>
                        </div>

                        <span class="text-xs text-gray-400 shrink-0">{{ date(r.created_at) }}</span>
                        <button @click="destroy(r)" title="Supprimer cet avis"
                                class="w-8 h-8 shrink-0 rounded-full text-gray-400 hover:text-red-600 hover:bg-red-50">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>
                </li>
            </ul>
            <p v-else class="px-5 py-12 text-center text-gray-400">Aucun avis ne correspond à ces critères.</p>

            <AdminPagination :meta="reviews" />
        </div>

        <p class="text-xs text-gray-400 mt-3">
            <i class="fa-solid fa-circle-info"></i>
            Supprimez uniquement les avis injurieux, diffamatoires ou manifestement faux.
            La note du membre évalué est recalculée automatiquement.
        </p>
    </AdminLayout>
</template>
