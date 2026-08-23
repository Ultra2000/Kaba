<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ reports: Array });

const STATUS = {
    open: { label: 'Ouvert', class: 'text-red-600' },
    resolved: { label: 'Résolu', class: 'text-green-600' },
    dismissed: { label: 'Ignoré', class: 'text-gray-500' },
};
const resolve = (id) => router.post(`/admin/signalements/${id}/resoudre`, {}, { preserveScroll: true });
const dismiss = (id) => router.post(`/admin/signalements/${id}/ignorer`, {}, { preserveScroll: true });
const fmtDate = (iso) => new Date(iso).toLocaleDateString('fr-FR');
</script>

<template>
    <Head title="Admin — Signalements" />
    <AdminLayout title="Signalements">
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[760px]">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="text-left font-bold px-4 py-3">Cible</th>
                            <th class="text-left font-bold px-4 py-3">Motif</th>
                            <th class="text-left font-bold px-4 py-3">Signalé par</th>
                            <th class="text-left font-bold px-4 py-3">Date</th>
                            <th class="text-left font-bold px-4 py-3">Statut</th>
                            <th class="text-right font-bold px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="r in reports" :key="r.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-bold text-dark">
                                <Link v-if="r.target_url" :href="r.target_url" class="hover:text-brand-600">{{ r.target }}</Link>
                                <span v-else>{{ r.target }}</span>
                            </td>
                            <td class="px-4 py-3"><span class="bg-red-100 text-red-600 text-[11px] font-bold px-2 py-1 rounded-md">{{ r.reason }}</span></td>
                            <td class="px-4 py-3 text-gray-500">{{ r.reporter }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ fmtDate(r.created_at) }}</td>
                            <td class="px-4 py-3"><span class="font-bold text-xs" :class="STATUS[r.status].class"><span class="inline-block w-2 h-2 rounded-full mr-1" :class="r.status==='open' ? 'bg-red-500' : r.status==='resolved' ? 'bg-green-500' : 'bg-gray-400'"></span>{{ STATUS[r.status].label }}</span></td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <template v-if="r.status === 'open'">
                                    <button @click="dismiss(r.id)" class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-xs font-bold hover:bg-gray-200">Ignorer</button>
                                    <button @click="resolve(r.id)" class="bg-brand-600 text-white px-3 py-1.5 rounded-full text-xs font-bold hover:bg-brand-700 ml-1"><i class="fa-solid fa-check"></i> Résoudre</button>
                                </template>
                                <span v-else class="text-gray-300 text-xs">Traité</span>
                            </td>
                        </tr>
                        <tr v-if="!reports.length"><td colspan="6" class="px-4 py-10 text-center text-gray-500">Aucun signalement.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
