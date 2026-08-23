<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ listings: Array });

const fmt = (n) => new Intl.NumberFormat('fr-FR').format(n) + ' F';
const TYPE = { vente: 'Vente', don: 'Don', echange: 'Échange', recherche: 'Recherche' };
const STATUS = {
    active: { label: 'En ligne', dot: 'bg-green-500', text: 'text-green-600' },
    pending: { label: 'À valider', dot: 'bg-orange-500', text: 'text-orange-500' },
    hidden: { label: 'Masquée', dot: 'bg-gray-400', text: 'text-gray-500' },
    sold: { label: 'Vendue', dot: 'bg-gray-400', text: 'text-gray-500' },
};

const approve = (id) => router.post(`/admin/annonces/${id}/valider`, {}, { preserveScroll: true });
const toggle = (id) => router.post(`/admin/annonces/${id}/masquer`, {}, { preserveScroll: true });
const destroy = (id) => {
    if (confirm('Supprimer définitivement cette annonce ?')) router.delete(`/admin/annonces/${id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Admin — Annonces" />
    <AdminLayout title="Modération des annonces">
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[760px]">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="text-left font-bold px-4 py-3">Livre</th>
                            <th class="text-left font-bold px-4 py-3">Vendeur</th>
                            <th class="text-left font-bold px-4 py-3">Type</th>
                            <th class="text-left font-bold px-4 py-3">Prix</th>
                            <th class="text-left font-bold px-4 py-3">Statut</th>
                            <th class="text-right font-bold px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="l in listings" :key="l.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3"><Link :href="`/livres/${l.id}`" class="font-bold text-dark hover:text-brand-600">{{ l.title }}</Link><p class="text-xs text-gray-400">{{ l.category }}</p></td>
                            <td class="px-4 py-3 text-gray-600 font-medium">{{ l.seller }}</td>
                            <td class="px-4 py-3"><span class="bg-gray-100 text-gray-600 text-[11px] font-bold px-2 py-1 rounded-md">{{ TYPE[l.type] }}</span></td>
                            <td class="px-4 py-3 font-bold text-brand-600">{{ l.type === 'vente' ? fmt(l.price) : (l.type === 'don' ? 'Gratuit' : '—') }}</td>
                            <td class="px-4 py-3"><span class="font-bold text-xs" :class="STATUS[l.status]?.text"><span class="inline-block w-2 h-2 rounded-full mr-1" :class="STATUS[l.status]?.dot"></span>{{ STATUS[l.status]?.label }}</span></td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button v-if="l.status === 'pending'" @click="approve(l.id)" class="bg-green-500 text-white px-3 py-1.5 rounded-full text-xs font-bold hover:bg-green-600"><i class="fa-solid fa-check"></i> Valider</button>
                                <button @click="toggle(l.id)" class="text-gray-500 hover:text-brand-600 px-2 ml-1" :title="l.status === 'hidden' ? 'Rendre visible' : 'Masquer'"><i class="fa-solid" :class="l.status === 'hidden' ? 'fa-eye' : 'fa-eye-slash'"></i></button>
                                <button @click="destroy(l.id)" class="text-gray-400 hover:text-red-500 px-2" title="Supprimer"><i class="fa-solid fa-trash-can"></i></button>
                            </td>
                        </tr>
                        <tr v-if="!listings.length"><td colspan="6" class="px-4 py-10 text-center text-gray-500">Aucune annonce.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
