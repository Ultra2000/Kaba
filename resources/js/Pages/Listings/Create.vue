<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    categories: Array,
    cities: Array,
    conditions: Object,
    languages: Array,
});

const form = useForm({
    type: 'vente',
    title: '',
    author: '',
    isbn: '',
    category_id: props.categories[0]?.id ?? null,
    condition: 'bon',
    language: 'Français',
    city: props.cities[0] ?? '',
    price: null,
    wants: '',
    budget: null,
    description: '',
    photos: [],
});

const previews = ref([]);
const MAX_PHOTOS = 10;

function onPhotos(e) {
    // On ajoute aux photos déjà choisies (au lieu de les remplacer).
    const incoming = Array.from(e.target.files);
    const room = MAX_PHOTOS - form.photos.length;
    const files = incoming.slice(0, Math.max(0, room));

    form.photos = [...form.photos, ...files];
    previews.value = [...previews.value, ...files.map((f) => URL.createObjectURL(f))];
    e.target.value = ''; // permet de re-sélectionner le même fichier
}

function removePhoto(i) {
    URL.revokeObjectURL(previews.value[i]);
    form.photos = form.photos.filter((_, idx) => idx !== i);
    previews.value = previews.value.filter((_, idx) => idx !== i);
}

function submit() {
    form.post('/livres', { forceFormData: true });
}

const TYPES = [
    { v: 'vente', label: 'Vendre', icon: 'fa-tag', active: 'peer-checked:border-brand-600 peer-checked:bg-brand-50 peer-checked:text-brand-700' },
    { v: 'don', label: 'Donner', icon: 'fa-hand-holding-heart', active: 'peer-checked:border-orange-400 peer-checked:bg-orange-50 peer-checked:text-orange-600' },
    { v: 'echange', label: 'Échanger', icon: 'fa-rotate', active: 'peer-checked:border-blue-400 peer-checked:bg-blue-50 peer-checked:text-blue-600' },
    { v: 'recherche', label: 'Rechercher', icon: 'fa-magnifying-glass', active: 'peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700' },
];
</script>

<template>
    <Head title="Publier une annonce" />
    <PublicLayout>
        <div class="max-w-3xl mx-auto px-4 py-10">
            <h1 class="text-3xl font-black text-dark mb-2">Publier une annonce</h1>
            <p class="text-gray-500 font-medium mb-8">Vendez, donnez, échangez ou recherchez un livre.</p>

            <form @submit.prevent="submit" class="space-y-8">
                <!-- Type -->
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h2 class="font-bold text-dark mb-4">Type d'annonce</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <label v-for="t in TYPES" :key="t.v" class="cursor-pointer">
                            <input type="radio" v-model="form.type" :value="t.v" class="peer sr-only">
                            <div class="text-center py-5 rounded-xl border-2 border-gray-200 text-gray-500 transition-colors" :class="t.active">
                                <i class="fa-solid text-2xl block mb-2" :class="t.icon"></i>
                                <span class="text-sm font-bold">{{ t.label }}</span>
                            </div>
                        </label>
                    </div>
                    <p v-if="form.type === 'recherche'" class="mt-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl p-3">
                        <i class="fa-solid fa-bell"></i> Vous serez notifié dès qu'un vendeur publie ce livre.
                    </p>
                </div>

                <!-- Photos -->
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h2 class="font-bold text-dark mb-1">Photos <span class="text-gray-400 font-medium text-sm">({{ form.photos.length }}/10)</span></h2>
                    <p class="text-sm text-gray-500 mb-4">
                        Montrez l'état réel du livre : la couverture, la tranche, quelques pages,
                        et les éventuels défauts (annotations, pages cornées…). Les annonces avec
                        plusieurs photos inspirent plus confiance.
                    </p>
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                        <label v-if="form.photos.length < 10"
                               class="aspect-square rounded-xl border-2 border-dashed border-brand-300 bg-brand-50 flex flex-col items-center justify-center text-brand-600 cursor-pointer hover:bg-brand-100 transition-colors">
                            <i class="fa-solid fa-camera text-xl mb-1"></i><span class="text-[11px] font-bold">Ajouter</span>
                            <input type="file" accept="image/*" multiple class="hidden" @change="onPhotos">
                        </label>
                        <div v-for="(src, i) in previews" :key="i" class="relative group aspect-square rounded-xl overflow-hidden border border-gray-200">
                            <img :src="src" class="w-full h-full object-cover">
                            <span v-if="i === 0" class="absolute bottom-0 inset-x-0 bg-brand-600/90 text-white text-[10px] font-bold text-center py-0.5">Principale</span>
                            <button type="button" @click="removePhoto(i)"
                                    class="absolute top-1 right-1 w-6 h-6 rounded-full bg-black/60 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 focus:opacity-100 transition-opacity hover:bg-red-500"
                                    :aria-label="`Retirer la photo ${i + 1}`">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <p v-if="form.errors.photos" class="text-red-500 text-xs mt-2">{{ form.errors.photos }}</p>
                    <p v-if="form.errors['photos.0']" class="text-red-500 text-xs mt-2">{{ form.errors['photos.0'] }}</p>
                </div>

                <!-- Détails -->
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h2 class="font-bold text-dark mb-4">Informations</h2>
                    <div class="grid md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-dark mb-2">Titre du livre *</label>
                            <input v-model="form.title" type="text" placeholder="Ex : L'Étranger" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 transition-all">
                            <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-dark mb-2">Auteur</label>
                            <input v-model="form.author" type="text" placeholder="Ex : Albert Camus" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-dark mb-2">ISBN (optionnel)</label>
                            <input v-model="form.isbn" type="text" placeholder="978-..." class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-dark mb-2">Catégorie *</label>
                            <select v-model="form.category_id" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 transition-all bg-white">
                                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-dark mb-2">État *</label>
                            <select v-model="form.condition" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 transition-all bg-white">
                                <option v-for="(label, key) in conditions" :key="key" :value="key">{{ label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-dark mb-2">Ville *</label>
                            <select v-model="form.city" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 transition-all bg-white">
                                <option v-for="c in cities" :key="c" :value="c">{{ c }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-dark mb-2">Langue *</label>
                            <select v-model="form.language" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 transition-all bg-white">
                                <option v-for="l in languages" :key="l" :value="l">{{ l }}</option>
                            </select>
                        </div>
                        <div v-if="form.type === 'vente'">
                            <label class="block text-sm font-bold text-dark mb-2">Prix (FCFA) *</label>
                            <input v-model="form.price" type="number" min="0" placeholder="Ex : 3000" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 transition-all">
                            <p v-if="form.errors.price" class="text-red-500 text-xs mt-1">{{ form.errors.price }}</p>
                        </div>
                        <div v-if="form.type === 'echange'" class="md:col-span-2">
                            <label class="block text-sm font-bold text-dark mb-2">Contre quel(s) livre(s) ?</label>
                            <input v-model="form.wants" type="text" placeholder="Ex : Romans policiers, Astérix..." class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 transition-all">
                        </div>
                        <div v-if="form.type === 'recherche'">
                            <label class="block text-sm font-bold text-dark mb-2">Budget max (FCFA)</label>
                            <input v-model="form.budget" type="number" min="0" placeholder="Ex : 5000" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 transition-all">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-dark mb-2">Description</label>
                            <textarea v-model="form.description" rows="4" placeholder="Décrivez l'état, l'édition, les annotations éventuelles..." class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 transition-all resize-none"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 justify-end">
                    <a href="/explorer" class="text-center bg-white text-dark border-2 border-gray-200 px-6 py-3.5 rounded-full font-bold hover:border-dark transition-colors">Annuler</a>
                    <button type="submit" :disabled="form.processing" class="bg-brand-600 hover:bg-brand-700 text-white px-8 py-3.5 rounded-full font-bold shadow-floating transition-all active:scale-95 disabled:opacity-60 flex items-center justify-center gap-2">
                        <i v-if="form.processing" class="fa-solid fa-spinner fa-spin"></i>
                        Publier l'annonce <i v-if="!form.processing" class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </form>
        </div>
    </PublicLayout>
</template>
