<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ stats: Object, repartition: Array });

const TYPE = {
    vente: { label: 'Vente', color: 'bg-brand-600' },
    don: { label: 'Don', color: 'bg-orange-500' },
    echange: { label: 'Échange', color: 'bg-blue-500' },
    recherche: { label: 'Recherche', color: 'bg-green-600' },
};
</script>

<template>
    <Head title="Admin — Tableau de bord" />
    <AdminLayout title="Tableau de bord">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-5"><i class="fa-solid fa-users text-brand-600 mb-2"></i><p class="text-2xl font-black text-dark">{{ stats.users }}</p><p class="text-xs text-gray-500 font-semibold">Utilisateurs</p></div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5"><i class="fa-solid fa-book text-brand-600 mb-2"></i><p class="text-2xl font-black text-dark">{{ stats.listings }}</p><p class="text-xs text-gray-500 font-semibold">Annonces en ligne</p></div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5"><i class="fa-solid fa-star text-yellow-400 mb-2"></i><p class="text-2xl font-black text-dark">{{ stats.reviews }}</p><p class="text-xs text-gray-500 font-semibold">Avis</p></div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5"><i class="fa-solid fa-flag text-red-500 mb-2"></i><p class="text-2xl font-black text-dark">{{ stats.reports }}</p><p class="text-xs text-gray-500 font-semibold">Signalements ouverts</p></div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-6 max-w-xl">
            <h3 class="font-bold text-dark mb-4">Répartition des annonces</h3>
            <div class="space-y-3 text-sm">
                <div v-for="r in repartition" :key="r.type">
                    <div class="flex justify-between mb-1"><span class="text-gray-600 font-medium">{{ TYPE[r.type].label }}</span><span class="font-bold">{{ r.percent }}%</span></div>
                    <div class="h-2 bg-gray-100 rounded-full"><div class="h-full rounded-full" :class="TYPE[r.type].color" :style="{ width: r.percent + '%' }"></div></div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
