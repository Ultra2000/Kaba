<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPagination from '@/Components/AdminPagination.vue';

defineProps({ reports: Object, filters: Object, counts: Object });

const STATUS = {
    open: { label: 'Ouvert', class: 'bg-red-50 text-red-600 border-red-200' },
    resolved: { label: 'Traité', class: 'bg-green-50 text-green-700 border-green-200' },
    dismissed: { label: 'Ignoré', class: 'bg-gray-100 text-gray-500 border-gray-200' },
};

const TABS = [
    { key: 'open', label: 'À traiter' },
    { key: 'resolved', label: 'Traités' },
    { key: 'dismissed', label: 'Ignorés' },
    { key: 'all', label: 'Tous' },
];

const apply = (status) => router.get('/admin/signalements', { status, page: 1 }, {
    preserveState: true, preserveScroll: true, replace: true,
});

const act = (id, action) => router.post(`/admin/signalements/${id}/${action}`, {}, { preserveScroll: true });
// Agir sur l'annonce signalée sans quitter la page.
const hideListing = (id) => router.post(`/admin/annonces/${id}/masquer`, {}, { preserveScroll: true });

const date = (iso) => new Date(iso).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: '2-digit' });
</script>

<template>
    <Head title="Admin — Signalements" />
    <AdminLayout title="Signalements" :subtitle="`${counts.open} en attente de traitement`">

        <!-- Filtres -->
        <div class="flex flex-wrap gap-2 mb-4">
            <button v-for="t in TABS" :key="t.key" @click="apply(t.key)"
                    class="px-3.5 h-9 rounded-full text-xs font-bold border transition-colors"
                    :class="filters.status === t.key ? 'bg-brand-600 text-white border-brand-600' : 'bg-white border-gray-200 text-gray-600 hover:border-brand-300'">
                {{ t.label }}
                <span v-if="counts[t.key] !== undefined" class="opacity-70">({{ counts[t.key] }})</span>
            </button>
        </div>

        <!-- Liste -->
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <ul v-if="reports.data.length" class="divide-y divide-gray-100">
                <li v-for="r in reports.data" :key="r.id" class="px-5 py-4">
                    <div class="flex flex-wrap items-start gap-3">
                        <span class="w-9 h-9 rounded-full bg-red-50 text-red-500 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-flag text-sm"></i>
                        </span>

                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-bold text-dark text-sm">{{ r.reason }}</span>
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-md border" :class="STATUS[r.status].class">{{ STATUS[r.status].label }}</span>
                                <span v-if="r.listing_hidden" class="text-[11px] font-bold px-2 py-0.5 rounded-md bg-gray-100 text-gray-500 border border-gray-200">
                                    Annonce masquée
                                </span>
                            </div>

                            <p class="text-sm text-gray-600 mt-1">
                                Cible :
                                <Link v-if="r.target_url" :href="r.target_url" class="font-medium text-dark hover:text-brand-600">{{ r.target }}</Link>
                                <span v-else class="text-gray-400 italic">{{ r.target }}</span>
                            </p>
                            <p v-if="r.details" class="text-sm text-gray-500 italic mt-1">« {{ r.details }} »</p>
                            <p class="text-xs text-gray-400 mt-1.5">Signalé par {{ r.reporter }} · {{ date(r.created_at) }}</p>
                        </div>

                        <!-- Actions -->
                        <div v-if="r.status === 'open'" class="flex flex-wrap gap-1.5 shrink-0">
                            <button v-if="r.listing_id && !r.listing_hidden" @click="hideListing(r.listing_id)"
                                    class="px-3 py-1.5 rounded-full text-xs font-bold bg-amber-500 text-white hover:bg-amber-600">
                                <i class="fa-solid fa-eye-slash"></i> Masquer l'annonce
                            </button>
                            <button @click="act(r.id, 'resoudre')"
                                    class="px-3 py-1.5 rounded-full text-xs font-bold bg-green-600 text-white hover:bg-green-700">Traité</button>
                            <button @click="act(r.id, 'ignorer')"
                                    class="px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600 hover:bg-gray-200">Ignorer</button>
                        </div>
                    </div>
                </li>
            </ul>

            <div v-else class="px-5 py-14 text-center">
                <i class="fa-solid fa-circle-check text-4xl text-green-200 mb-3"></i>
                <p class="text-gray-500 font-medium">Aucun signalement à traiter.</p>
            </div>

            <AdminPagination :meta="reports" />
        </div>
    </AdminLayout>
</template>
