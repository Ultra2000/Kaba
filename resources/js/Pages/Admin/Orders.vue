<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPagination from '@/Components/AdminPagination.vue';

const props = defineProps({ orders: Object, filters: Object, counts: Object });

const STATUS = {
    pending: 'bg-amber-50 text-amber-700 border-amber-200',
    accepted: 'bg-green-50 text-green-700 border-green-200',
    declined: 'bg-red-50 text-red-600 border-red-200',
    completed: 'bg-brand-50 text-brand-700 border-brand-200',
    cancelled: 'bg-gray-100 text-gray-500 border-gray-200',
};

const apply = (status) => router.get('/admin/demandes', { status, page: 1 }, {
    preserveState: true, preserveScroll: true, replace: true,
});

const fmt = (n) => new Intl.NumberFormat('fr-FR').format(n);
const date = (iso) => new Date(iso).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: '2-digit' });
</script>

<template>
    <Head title="Admin — Demandes" />
    <AdminLayout title="Demandes de disponibilité" subtitle="Les transactions entre membres">

        <!-- Filtres -->
        <div class="flex flex-wrap gap-2 mb-4">
            <button @click="apply('all')"
                    class="px-3.5 h-9 rounded-full text-xs font-bold border transition-colors"
                    :class="filters.status === 'all' ? 'bg-brand-600 text-white border-brand-600' : 'bg-white border-gray-200 text-gray-600 hover:border-brand-300'">
                Toutes <span class="opacity-70">({{ orders.total }})</span>
            </button>
            <button v-for="(c, key) in counts" :key="key" @click="apply(key)"
                    class="px-3.5 h-9 rounded-full text-xs font-bold border transition-colors"
                    :class="filters.status === key ? 'bg-brand-600 text-white border-brand-600' : 'bg-white border-gray-200 text-gray-600 hover:border-brand-300'">
                {{ c.label }} <span class="opacity-70">({{ c.count }})</span>
            </button>
        </div>

        <!-- Tableau -->
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[720px]">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="text-left font-bold px-4 py-3">Nº</th>
                            <th class="text-left font-bold px-4 py-3">Acheteur</th>
                            <th class="text-left font-bold px-4 py-3">Vendeur</th>
                            <th class="text-left font-bold px-4 py-3">Livres</th>
                            <th class="text-left font-bold px-4 py-3">Montant</th>
                            <th class="text-left font-bold px-4 py-3">Statut</th>
                            <th class="text-left font-bold px-4 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="o in orders.data" :key="o.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs text-gray-400">#{{ o.id }}</td>
                            <td class="px-4 py-3">
                                <Link :href="`/vendeurs/${o.buyer_id}`" class="font-medium text-dark hover:text-brand-600">{{ o.buyer }}</Link>
                            </td>
                            <td class="px-4 py-3">
                                <Link :href="`/vendeurs/${o.seller_id}`" class="font-medium text-dark hover:text-brand-600">{{ o.seller }}</Link>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ o.items }}</td>
                            <td class="px-4 py-3 font-bold text-dark">{{ o.total > 0 ? fmt(o.total) + ' F' : '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="text-[11px] font-bold px-2 py-1 rounded-md border" :class="STATUS[o.status]">{{ o.label }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">{{ date(o.created_at) }}</td>
                        </tr>
                        <tr v-if="!orders.data.length">
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400">Aucune demande pour ce filtre.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <AdminPagination :meta="orders" />
        </div>

        <p class="text-xs text-gray-400 mt-3">
            <i class="fa-solid fa-circle-info"></i>
            Les transactions se concluent entre membres : cette page sert au suivi et à la
            résolution de litiges, l'administration n'intervient pas dans les échanges.
        </p>
    </AdminLayout>
</template>
