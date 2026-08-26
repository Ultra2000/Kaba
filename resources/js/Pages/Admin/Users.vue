<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPagination from '@/Components/AdminPagination.vue';

const props = defineProps({ users: Object, roles: Object, filters: Object, counts: Object });

/* Recherche et filtre */
const q = ref(props.filters?.q ?? '');
let timer;
watch(q, (v) => {
    clearTimeout(timer);
    timer = setTimeout(() => applyFilters({ q: v }), 350);
});
function applyFilters(changes) {
    router.get('/admin/utilisateurs', { ...props.filters, ...changes, page: 1 }, {
        preserveState: true, preserveScroll: true, replace: true,
    });
}

const page = usePage();
const meId = page.props.auth?.user?.id;

const ROLE = {
    user: { label: 'Particulier', class: 'bg-gray-100 text-gray-600' },
    pro: { label: 'Pro', class: 'bg-dark text-white' },
    admin: { label: 'Admin', class: 'bg-brand-600 text-white' },
};
const initials = (name) => name.split(/\s+/).map(w => w[0]).slice(0, 2).join('').toUpperCase();
const toggleVerified = (id) => router.post(`/admin/utilisateurs/${id}/verifier`, {}, { preserveScroll: true });

/* Changement de statut */
function changeRole(u, role) {
    if (role === u.role) return;
    router.post(`/admin/utilisateurs/${u.id}/statut`, { role }, { preserveScroll: true });
}

/* Création de compte */
const showCreate = ref(false);
const form = useForm({
    name: '', email: '', password: '', role: 'user', city: '', phone: '', is_verified: false,
});

function generatePassword() {
    const chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    form.password = Array.from({ length: 12 }, () => chars[Math.floor(Math.random() * chars.length)]).join('');
}

function submit() {
    form.post('/admin/utilisateurs', {
        preserveScroll: true,
        onSuccess: () => { form.reset(); showCreate.value = false; },
    });
}
</script>

<template>
    <Head title="Admin — Utilisateurs" />
    <AdminLayout title="Utilisateurs" :subtitle="`${counts.all} membres · ${counts.verified} vérifiés`">
        <!-- Création de compte -->
        <div class="mb-5">
            <button @click="showCreate = !showCreate"
                    class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white h-11 px-5 rounded-full text-sm font-bold transition-colors">
                <i class="fa-solid" :class="showCreate ? 'fa-xmark' : 'fa-user-plus'"></i>
                {{ showCreate ? 'Annuler' : 'Ajouter un utilisateur' }}
            </button>

            <form v-if="showCreate" @submit.prevent="submit" class="mt-4 bg-white rounded-2xl border border-gray-200 p-5">
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-dark mb-1.5">Nom</label>
                        <input v-model="form.name" required maxlength="100"
                               class="w-full h-10 px-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
                        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-dark mb-1.5">E-mail</label>
                        <input v-model="form.email" type="email" required maxlength="150"
                               class="w-full h-10 px-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
                        <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-dark mb-1.5">Mot de passe</label>
                        <div class="flex gap-1.5">
                            <input v-model="form.password" type="text" required minlength="8"
                                   class="flex-1 min-w-0 h-10 px-3 rounded-xl border border-gray-200 text-sm font-mono outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
                            <button type="button" @click="generatePassword" title="Générer"
                                    class="w-10 h-10 shrink-0 rounded-xl border border-gray-200 text-gray-500 hover:text-brand-600 hover:border-brand-300">
                                <i class="fa-solid fa-dice text-sm"></i>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-dark mb-1.5">Statut</label>
                        <select v-model="form.role"
                                class="w-full h-10 px-3 rounded-xl border border-gray-200 text-sm bg-white outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
                            <option v-for="(label, key) in roles" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-dark mb-1.5">Ville <span class="text-gray-400 font-medium">(facultatif)</span></label>
                        <input v-model="form.city" maxlength="100"
                               class="w-full h-10 px-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-dark mb-1.5">Téléphone <span class="text-gray-400 font-medium">(facultatif)</span></label>
                        <input v-model="form.phone" maxlength="30"
                               class="w-full h-10 px-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4 mt-4">
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input v-model="form.is_verified" type="checkbox" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        Marquer comme vérifié
                    </label>
                    <button type="submit" :disabled="form.processing"
                            class="ml-auto inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 disabled:opacity-60 text-white h-10 px-5 rounded-full text-sm font-bold transition-colors">
                        <i class="fa-solid fa-check"></i> Créer le compte
                    </button>
                </div>
                <p class="text-xs text-gray-400 mt-3">
                    <i class="fa-solid fa-circle-info"></i>
                    Notez le mot de passe : il ne sera plus affiché. Transmettez-le à la personne, qui pourra le changer depuis son profil.
                </p>
            </form>
        </div>

        <!-- Recherche -->
        <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-4 flex flex-wrap gap-2">
            <div class="relative flex-1 min-w-[200px]">
                <i class="fa-solid fa-magnifying-glass text-gray-400 text-xs absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input v-model="q" type="search" placeholder="Nom, e-mail ou ville…"
                       class="w-full h-9 pl-8 pr-3 rounded-lg border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
            </div>
            <select :value="filters.role" @change="applyFilters({ role: $event.target.value })"
                    class="h-9 px-3 rounded-lg border border-gray-200 text-sm bg-white outline-none focus:border-brand-500">
                <option value="all">Tous les statuts</option>
                <option v-for="(label, key) in roles" :key="key" :value="key">{{ label }}</option>
            </select>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[820px]">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="text-left font-bold px-4 py-3">Utilisateur</th>
                            <th class="text-left font-bold px-4 py-3">Type</th>
                            <th class="text-left font-bold px-4 py-3">Ville</th>
                            <th class="text-left font-bold px-4 py-3">Annonces</th>
                            <th class="text-left font-bold px-4 py-3">Note</th>
                            <th class="text-left font-bold px-4 py-3">Vérifié</th>
                            <th class="text-right font-bold px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="u in users.data" :key="u.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-black" :class="u.role === 'pro' || u.role === 'admin' ? 'bg-dark text-white' : 'bg-brand-100 text-brand-700'">{{ initials(u.name) }}</div>
                                    <div><Link :href="`/vendeurs/${u.id}`" class="font-bold text-dark hover:text-brand-600">{{ u.name }}</Link><p class="text-xs text-gray-400">{{ u.email }}</p></div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <!-- Son propre compte : statut figé, pour ne pas se verrouiller dehors. -->
                                <span v-if="u.id === meId" class="text-[11px] font-bold px-2 py-1 rounded-md" :class="ROLE[u.role].class"
                                      title="Vous ne pouvez pas modifier votre propre statut">
                                    {{ ROLE[u.role].label }} <i class="fa-solid fa-lock text-[9px] opacity-70"></i>
                                </span>
                                <select v-else :value="u.role" @change="changeRole(u, $event.target.value)"
                                        class="text-xs font-bold rounded-lg border border-gray-200 py-1.5 pl-2 pr-7 bg-white outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 cursor-pointer">
                                    <option v-for="(label, key) in roles" :key="key" :value="key">{{ label }}</option>
                                </select>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ u.city || '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="text-gray-600 font-medium">{{ u.listings_count }}</span>
                                <span v-if="u.sales_count > 0" class="text-xs text-gray-400 block">{{ u.sales_count }} vente{{ u.sales_count > 1 ? 's' : '' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span v-if="u.rating_avg > 0" class="text-xs font-bold" :class="u.rating_avg < 3 ? 'text-red-500' : 'text-dark'">
                                    <i class="fa-solid fa-star text-yellow-400"></i> {{ u.rating_avg }}
                                </span>
                                <span v-else class="text-xs text-gray-300">—</span>
                            </td>
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
                        <tr v-if="!users.data.length">
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400">Aucun membre ne correspond à cette recherche.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <AdminPagination :meta="users" />
        </div>
    </AdminLayout>
</template>
