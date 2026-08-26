<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    stats: Object, trends: Object, todo: Object,
    repartition: Array, activity: Array, topCities: Array,
});

const TYPE = {
    vente: { label: 'Vente', color: 'bg-brand-600' },
    don: { label: 'Don', color: 'bg-orange-500' },
    echange: { label: 'Échange', color: 'bg-sky-500' },
    recherche: { label: 'Recherche', color: 'bg-green-600' },
};

const ACTIVITY_COLOR = {
    brand: 'bg-brand-50 text-brand-600',
    green: 'bg-green-50 text-green-600',
    amber: 'bg-amber-50 text-amber-600',
};

// Les points d'attention, masqués quand il n'y a rien à faire.
const alerts = computed(() => [
    { key: 'reports', count: props.todo.reports, label: 'signalement à traiter', plural: 'signalements à traiter',
      href: '/admin/signalements', icon: 'fa-flag', tone: 'red' },
    { key: 'pendingListings', count: props.todo.pendingListings, label: 'annonce à valider', plural: 'annonces à valider',
      href: '/admin/annonces?status=pending', icon: 'fa-clock', tone: 'amber' },
    { key: 'pendingOrders', count: props.todo.pendingOrders, label: 'demande sans réponse', plural: 'demandes sans réponse',
      href: '/admin/demandes?status=pending', icon: 'fa-basket-shopping', tone: 'sky' },
    { key: 'lowRatedUsers', count: props.todo.lowRatedUsers, label: 'membre mal noté', plural: 'membres mal notés',
      href: '/admin/utilisateurs', icon: 'fa-triangle-exclamation', tone: 'orange' },
].filter((a) => a.count > 0));

const TONE = {
    red: 'bg-red-50 border-red-200 text-red-700 hover:border-red-300',
    amber: 'bg-amber-50 border-amber-200 text-amber-800 hover:border-amber-300',
    sky: 'bg-sky-50 border-sky-200 text-sky-700 hover:border-sky-300',
    orange: 'bg-orange-50 border-orange-200 text-orange-700 hover:border-orange-300',
};

const fmt = (n) => new Intl.NumberFormat('fr-FR').format(n);
const ago = (iso) => {
    const s = (Date.now() - new Date(iso)) / 1000;
    if (s < 60) return "à l'instant";
    if (s < 3600) return `il y a ${Math.floor(s / 60)} min`;
    if (s < 86400) return `il y a ${Math.floor(s / 3600)} h`;
    return `il y a ${Math.floor(s / 86400)} j`;
};
</script>

<template>
    <Head title="Admin — Tableau de bord" />
    <AdminLayout title="Tableau de bord" subtitle="Vue d'ensemble de la plateforme">

        <!-- À traiter -->
        <section v-if="alerts.length" class="mb-6">
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">À traiter</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <Link v-for="a in alerts" :key="a.key" :href="a.href"
                      class="flex items-center gap-3 border rounded-2xl px-4 py-3.5 transition-colors" :class="TONE[a.tone]">
                    <i class="fa-solid text-lg shrink-0" :class="a.icon"></i>
                    <div class="min-w-0 flex-1">
                        <p class="font-black text-lg leading-none">{{ a.count }}</p>
                        <p class="text-xs font-semibold leading-tight mt-0.5">{{ a.count > 1 ? a.plural : a.label }}</p>
                    </div>
                    <i class="fa-solid fa-chevron-right text-xs shrink-0 opacity-50"></i>
                </Link>
            </div>
        </section>

        <div v-else class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-2xl px-4 py-3.5">
            <i class="fa-solid fa-circle-check text-green-600"></i>
            <p class="text-sm font-medium">Rien à traiter pour le moment — tout est à jour.</p>
        </div>

        <!-- Chiffres clés -->
        <section class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-1.5">
                    <i class="fa-solid fa-users text-brand-600"></i>
                    <span class="text-[11px] font-bold" :class="trends.users.percent >= 0 ? 'text-green-600' : 'text-red-500'">
                        {{ trends.users.percent >= 0 ? '+' : '' }}{{ trends.users.percent }}%
                    </span>
                </div>
                <p class="text-2xl font-black text-dark">{{ fmt(stats.users) }}</p>
                <p class="text-xs text-gray-500 font-semibold">Membres</p>
                <p class="text-[11px] text-gray-400 mt-1">{{ trends.users.count }} ce mois-ci</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-1.5">
                    <i class="fa-solid fa-book text-brand-600"></i>
                    <span class="text-[11px] font-bold" :class="trends.listings.percent >= 0 ? 'text-green-600' : 'text-red-500'">
                        {{ trends.listings.percent >= 0 ? '+' : '' }}{{ trends.listings.percent }}%
                    </span>
                </div>
                <p class="text-2xl font-black text-dark">{{ fmt(stats.listings) }}</p>
                <p class="text-xs text-gray-500 font-semibold">Annonces en ligne</p>
                <p class="text-[11px] text-gray-400 mt-1">{{ trends.listings.count }} ce mois-ci</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-1.5">
                    <i class="fa-solid fa-basket-shopping text-amber-500"></i>
                    <span class="text-[11px] font-bold" :class="trends.orders.percent >= 0 ? 'text-green-600' : 'text-red-500'">
                        {{ trends.orders.percent >= 0 ? '+' : '' }}{{ trends.orders.percent }}%
                    </span>
                </div>
                <p class="text-2xl font-black text-dark">{{ fmt(stats.orders) }}</p>
                <p class="text-xs text-gray-500 font-semibold">Demandes</p>
                <p class="text-[11px] text-gray-400 mt-1">{{ trends.orders.count }} ce mois-ci</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <i class="fa-solid fa-handshake text-green-600 mb-1.5 block"></i>
                <p class="text-2xl font-black text-dark">{{ fmt(stats.completed) }}</p>
                <p class="text-xs text-gray-500 font-semibold">Remises effectuées</p>
                <p class="text-[11px] text-gray-400 mt-1">
                    {{ stats.orders ? Math.round(stats.completed / stats.orders * 100) : 0 }}% des demandes
                </p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 p-4 col-span-2 lg:col-span-1">
                <i class="fa-solid fa-star text-yellow-400 mb-1.5 block"></i>
                <p class="text-2xl font-black text-dark">{{ fmt(stats.reviews) }}</p>
                <p class="text-xs text-gray-500 font-semibold">Avis déposés</p>
                <Link href="/admin/avis" class="text-[11px] text-brand-600 font-bold mt-1 inline-block hover:underline">Modérer →</Link>
            </div>
        </section>

        <div class="grid lg:grid-cols-3 gap-5">
            <!-- Activité récente -->
            <section class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <h2 class="font-black text-dark px-5 py-4 border-b border-gray-100">Activité récente</h2>
                <ul v-if="activity.length" class="divide-y divide-gray-50">
                    <li v-for="(a, i) in activity" :key="i">
                        <Link :href="a.url" class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition-colors">
                            <span class="w-9 h-9 rounded-full flex items-center justify-center shrink-0" :class="ACTIVITY_COLOR[a.color]">
                                <i class="fa-solid text-sm" :class="a.icon"></i>
                            </span>
                            <p class="flex-1 min-w-0 text-sm text-gray-700 truncate">{{ a.text }}</p>
                            <span class="text-xs text-gray-400 shrink-0">{{ ago(a.at) }}</span>
                        </Link>
                    </li>
                </ul>
                <p v-else class="px-5 py-10 text-center text-sm text-gray-400">Aucune activité pour le moment.</p>
            </section>

            <div class="space-y-5">
                <!-- Répartition -->
                <section class="bg-white rounded-2xl border border-gray-200 p-5">
                    <h2 class="font-black text-dark mb-4">Types d'annonces</h2>
                    <div class="space-y-3 text-sm">
                        <div v-for="r in repartition" :key="r.type">
                            <div class="flex justify-between mb-1">
                                <span class="text-gray-600 font-medium">{{ TYPE[r.type].label }}</span>
                                <span class="font-bold text-dark">{{ r.count }} <span class="text-gray-400 font-medium">· {{ r.percent }}%</span></span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all" :class="TYPE[r.type].color" :style="{ width: r.percent + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Villes -->
                <section v-if="topCities.length" class="bg-white rounded-2xl border border-gray-200 p-5">
                    <h2 class="font-black text-dark mb-4">Villes les plus actives</h2>
                    <ul class="space-y-2.5 text-sm">
                        <li v-for="(c, i) in topCities" :key="c.city" class="flex items-center gap-3">
                            <span class="w-5 text-xs font-black text-gray-300">{{ i + 1 }}</span>
                            <span class="flex-1 text-gray-700 font-medium truncate">{{ c.city }}</span>
                            <span class="font-bold text-dark">{{ c.count }}</span>
                        </li>
                    </ul>
                </section>
            </div>
        </div>
    </AdminLayout>
</template>
