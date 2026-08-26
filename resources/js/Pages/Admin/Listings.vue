<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPagination from '@/Components/AdminPagination.vue';

const props = defineProps({ listings: Object, filters: Object, counts: Object });

const STATUS = {
    active: { label: 'En ligne', class: 'bg-green-50 text-green-700 border-green-200' },
    pending: { label: 'À valider', class: 'bg-amber-50 text-amber-700 border-amber-200' },
    hidden: { label: 'Masquée', class: 'bg-gray-100 text-gray-600 border-gray-200' },
    sold: { label: 'Vendue', class: 'bg-dark text-white border-dark' },
};
const TYPE = { vente: 'Vente', don: 'Don', echange: 'Échange', recherche: 'Recherche' };

const TABS = [
    { key: 'all', label: 'Toutes' },
    { key: 'pending', label: 'À valider' },
    { key: 'active', label: 'En ligne' },
    { key: 'hidden', label: 'Masquées' },
    { key: 'sold', label: 'Vendues' },
];

const q = ref(props.filters.q ?? '');
let timer;
watch(q, (v) => {
    clearTimeout(timer);
    timer = setTimeout(() => apply({ q: v }), 350); // on attend la fin de la frappe
});

function apply(changes) {
    router.get('/admin/annonces', { ...props.filters, ...changes, page: 1 }, {
        preserveState: true, preserveScroll: true, replace: true,
    });
}

const act = (id, action) => router.post(`/admin/annonces/${id}/${action}`, {}, { preserveScroll: true });
function destroy(l) {
    if (confirm(`Supprimer définitivement « ${l.title} » ? Cette action est irréversible.`)) {
        router.delete(`/admin/annonces/${l.id}`, { preserveScroll: true });
    }
}

const fmt = (n) => new Intl.NumberFormat('fr-FR').format(n);
const date = (iso) => new Date(iso).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: '2-digit' });
</script>

<template>
    <Head title="Admin — Annonces" />
    <AdminLayout title="Annonces" :subtitle="`${counts.all} au total`">

        <!-- Filtres -->
        <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-4">
            <div class="flex flex-wrap gap-2 mb-3">
                <button v-for="t in TABS" :key="t.key" @click="apply({ status: t.key })"
                        class="px-3.5 h-9 rounded-full text-xs font-bold border transition-colors"
                        :class="filters.status === t.key ? 'bg-brand-600 text-white border-brand-600' : 'bg-white border-gray-200 text-gray-600 hover:border-brand-300'">
                    {{ t.label }}
                    <span class="opacity-70">({{ counts[t.key] ?? 0 }})</span>
                </button>
            </div>

            <div class="flex flex-wrap gap-2">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass text-gray-400 text-xs absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input v-model="q" type="search" placeholder="Titre, auteur ou vendeur…"
                           class="w-full h-9 pl-8 pr-3 rounded-lg border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
                </div>
                <select :value="filters.type" @change="apply({ type: $event.target.value })"
                        class="h-9 px-3 rounded-lg border border-gray-200 text-sm bg-white outline-none focus:border-brand-500">
                    <option value="all">Tous les types</option>
                    <option v-for="(label, key) in TYPE" :key="key" :value="key">{{ label }}</option>
                </select>
            </div>
        </div>

        <!-- Tableau -->
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[820px]">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="text-left font-bold px-4 py-3">Annonce</th>
                            <th class="text-left font-bold px-4 py-3">Vendeur</th>
                            <th class="text-left font-bold px-4 py-3">Type</th>
                            <th class="text-left font-bold px-4 py-3">Prix</th>
                            <th class="text-left font-bold px-4 py-3">Statut</th>
                            <th class="text-left font-bold px-4 py-3">Publiée</th>
                            <th class="text-right font-bold px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="l in listings.data" :key="l.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <Link :href="`/livres/${l.id}`" class="font-bold text-dark hover:text-brand-600 line-clamp-1">{{ l.title }}</Link>
                                <p class="text-xs text-gray-400">{{ l.category }} · {{ l.views }} vues</p>
                            </td>
                            <td class="px-4 py-3">
                                <Link :href="`/vendeurs/${l.seller_id}`" class="text-gray-600 hover:text-brand-600">{{ l.seller }}</Link>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ TYPE[l.type] }}</td>
                            <td class="px-4 py-3 font-medium text-dark">{{ l.type === 'vente' ? fmt(l.price) + ' F' : '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="text-[11px] font-bold px-2 py-1 rounded-md border" :class="STATUS[l.status].class">{{ STATUS[l.status].label }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">{{ date(l.created_at) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button v-if="l.status === 'pending'" @click="act(l.id, 'valider')"
                                        class="px-3 py-1.5 rounded-full text-xs font-bold bg-green-600 text-white hover:bg-green-700">Valider</button>
                                <button v-else @click="act(l.id, 'masquer')"
                                        class="px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600 hover:bg-gray-200">
                                    {{ l.status === 'hidden' ? 'Réafficher' : 'Masquer' }}
                                </button>
                                <button @click="destroy(l)" title="Supprimer"
                                        class="ml-1 w-8 h-8 rounded-full text-gray-400 hover:text-red-600 hover:bg-red-50">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!listings.data.length">
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400">Aucune annonce ne correspond à ces critères.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <AdminPagination :meta="listings" />
        </div>
    </AdminLayout>
</template>
