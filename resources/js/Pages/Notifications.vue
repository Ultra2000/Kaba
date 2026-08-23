<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    notifications: Array,
});

const GROUPS = { today: "Aujourd'hui", week: 'Cette semaine', old: 'Plus ancien' };

function groupOf(iso) {
    const d = new Date(iso);
    const now = new Date();
    const days = (now - d) / 86400000;
    if (d.toDateString() === now.toDateString()) return 'today';
    if (days < 7) return 'week';
    return 'old';
}

const grouped = computed(() => {
    const out = { today: [], week: [], old: [] };
    for (const n of props.notifications) out[groupOf(n.created_at)].push(n);
    return Object.entries(GROUPS)
        .map(([key, label]) => ({ key, label, items: out[key] }))
        .filter((g) => g.items.length);
});

function timeAgo(iso) {
    const mins = Math.round((Date.now() - new Date(iso)) / 60000);
    if (mins < 60) return `il y a ${mins} min`;
    const h = Math.round(mins / 60);
    if (h < 24) return `il y a ${h} h`;
    return new Date(iso).toLocaleDateString('fr-FR');
}

function markAll() {
    router.post('/notifications/lire', {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Notifications" />
    <PublicLayout>
        <div class="max-w-2xl mx-auto px-4 py-10">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-black text-dark">Notifications</h1>
                <button @click="markAll" class="text-sm font-bold text-brand-600 hover:underline"><i class="fa-solid fa-check-double mr-1"></i>Tout marquer lu</button>
            </div>

            <div v-if="grouped.length" class="space-y-6">
                <div v-for="g in grouped" :key="g.key">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">{{ g.label }}</p>
                    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden divide-y divide-gray-50">
                        <Link v-for="n in g.items" :key="n.id" :href="n.data.url ?? '#'" class="flex gap-3 px-4 py-4 hover:bg-gray-50 transition-colors" :class="{ 'bg-brand-50/40': !n.read }">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0" :class="n.data.color">
                                <i class="fa-solid" :class="n.data.icon"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-700 leading-snug">{{ n.data.message }}</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">{{ timeAgo(n.created_at) }}</p>
                            </div>
                            <span v-if="!n.read" class="w-2 h-2 bg-brand-600 rounded-full shrink-0 mt-2"></span>
                        </Link>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-20 text-gray-500">
                <i class="fa-regular fa-bell-slash text-5xl text-gray-200 mb-4 block"></i>
                Aucune notification pour le moment.
            </div>
        </div>
    </PublicLayout>
</template>
