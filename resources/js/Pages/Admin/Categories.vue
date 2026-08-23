<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ categories: Array });

const open = ref(false);
const editingId = ref(null);
const form = useForm({ name: '', icon: 'fa-book' });

function add() { editingId.value = null; form.reset(); form.icon = 'fa-book'; open.value = true; }
function edit(c) { editingId.value = c.id; form.name = c.name; form.icon = c.icon; open.value = true; }
function submit() {
    const opts = { preserveScroll: true, onSuccess: () => { open.value = false; } };
    if (editingId.value) form.put(`/admin/categories/${editingId.value}`, opts);
    else form.post('/admin/categories', opts);
}
function destroy(c) {
    if (!confirm(`Supprimer la catégorie « ${c.name} » ?`)) return;
    router.delete(`/admin/categories/${c.id}`, {
        preserveScroll: true,
        onError: (e) => alert(e.category ?? 'Suppression impossible.'),
    });
}
</script>

<template>
    <Head title="Admin — Catégories" />
    <AdminLayout title="Catégories">
        <div class="flex justify-between items-center mb-4">
            <p class="text-gray-500 text-sm font-medium">{{ categories.length }} catégories</p>
            <button @click="add" class="bg-brand-600 text-white px-4 py-2 rounded-full text-sm font-bold hover:bg-brand-700 shadow-floating"><i class="fa-solid fa-plus text-xs"></i> Ajouter</button>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <div v-for="c in categories" :key="c.id" class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center"><i class="fa-solid" :class="c.icon"></i></div>
                <div class="flex-1 min-w-0"><p class="font-bold text-dark text-sm truncate">{{ c.name }}</p><p class="text-xs text-gray-400">{{ c.listings_count }} annonce{{ c.listings_count > 1 ? 's' : '' }}</p></div>
                <button @click="edit(c)" class="text-gray-400 hover:text-brand-600 px-1"><i class="fa-solid fa-pen text-xs"></i></button>
                <button @click="destroy(c)" class="text-gray-400 hover:text-red-500 px-1"><i class="fa-solid fa-trash-can text-xs"></i></button>
            </div>
        </div>

        <!-- Modale ajout/édition -->
        <div v-if="open" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-dark/50 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
                <h3 class="text-lg font-black text-dark mb-4">{{ editingId ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-dark mb-2">Nom</label>
                        <input v-model="form.name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200" placeholder="Ex : Poésie">
                        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-dark mb-2">Icône <span class="text-gray-400 font-medium">(classe FontAwesome)</span></label>
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0"><i class="fa-solid" :class="form.icon"></i></div>
                            <input v-model="form.icon" type="text" class="flex-1 px-4 py-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200" placeholder="fa-book">
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button @click="open = false" class="px-4 py-2.5 rounded-full font-bold text-sm text-gray-600 hover:bg-gray-100">Annuler</button>
                    <button @click="submit" :disabled="form.processing" class="bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 rounded-full font-bold text-sm shadow-floating disabled:opacity-60">Enregistrer</button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
