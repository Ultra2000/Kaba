<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ users: Array });

const ROLE = {
    user: { label: 'Particulier', class: 'bg-gray-100 text-gray-600' },
    pro: { label: 'Pro', class: 'bg-dark text-white' },
    admin: { label: 'Admin', class: 'bg-brand-600 text-white' },
};
const initials = (name) => name.split(/\s+/).map(w => w[0]).slice(0, 2).join('').toUpperCase();
const toggleVerified = (id) => router.post(`/admin/utilisateurs/${id}/verifier`, {}, { preserveScroll: true });
</script>

<template>
    <Head title="Admin — Utilisateurs" />
    <AdminLayout title="Utilisateurs">
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[760px]">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="text-left font-bold px-4 py-3">Utilisateur</th>
                            <th class="text-left font-bold px-4 py-3">Type</th>
                            <th class="text-left font-bold px-4 py-3">Ville</th>
                            <th class="text-left font-bold px-4 py-3">Annonces</th>
                            <th class="text-left font-bold px-4 py-3">Vérifié</th>
                            <th class="text-right font-bold px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="u in users" :key="u.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-black" :class="u.role === 'pro' || u.role === 'admin' ? 'bg-dark text-white' : 'bg-brand-100 text-brand-700'">{{ initials(u.name) }}</div>
                                    <div><Link :href="`/vendeurs/${u.id}`" class="font-bold text-dark hover:text-brand-600">{{ u.name }}</Link><p class="text-xs text-gray-400">{{ u.email }}</p></div>
                                </div>
                            </td>
                            <td class="px-4 py-3"><span class="text-[11px] font-bold px-2 py-1 rounded-md" :class="ROLE[u.role].class">{{ ROLE[u.role].label }}</span></td>
                            <td class="px-4 py-3 text-gray-500">{{ u.city || '—' }}</td>
                            <td class="px-4 py-3 text-gray-600 font-medium">{{ u.listings_count }}</td>
                            <td class="px-4 py-3">
                                <span v-if="u.is_verified" class="text-blue-500 font-bold text-xs"><i class="fa-solid fa-circle-check"></i> Oui</span>
                                <span v-else class="text-gray-400 text-xs">Non</span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button @click="toggleVerified(u.id)" class="px-3 py-1.5 rounded-full text-xs font-bold" :class="u.is_verified ? 'bg-gray-100 text-gray-600 hover:bg-gray-200' : 'bg-blue-500 text-white hover:bg-blue-600'">
                                    {{ u.is_verified ? 'Retirer' : 'Vérifier' }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
