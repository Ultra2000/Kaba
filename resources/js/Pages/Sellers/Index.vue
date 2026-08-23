<script setup>
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    sellers: Array,
    cities: Array,
    filters: Object,
});

const form = reactive({ ...props.filters });

function apply() {
    const params = {};
    if (form.type !== 'all') params.type = form.type;
    if (form.city !== 'all') params.city = form.city;
    if (form.sort !== 'rating') params.sort = form.sort;
    router.get('/vendeurs', params, { preserveState: true, preserveScroll: true, replace: true });
}
function setType(t) { form.type = t; apply(); }
const initials = (name) => name.split(/\s+/).map(w => w[0]).slice(0, 2).join('').toUpperCase();
</script>

<template>
    <Head title="Tous les vendeurs" />
    <PublicLayout>
        <div class="max-w-[1400px] mx-auto px-4 py-8">
            <div class="flex items-center gap-2 text-sm text-gray-500 font-medium mb-4">
                <Link href="/" class="hover:text-brand-600">Accueil</Link>
                <i class="fa-solid fa-chevron-right text-[10px] text-gray-300"></i>
                <span class="text-dark font-semibold">Vendeurs</span>
            </div>

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-black text-dark">Nos vendeurs</h1>
                    <p class="text-gray-500 text-sm font-medium mt-1"><span class="font-bold text-dark">{{ sellers.length }}</span> vendeur{{ sellers.length > 1 ? 's' : '' }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button v-for="t in [['all','Tous'],['particulier','Particuliers'],['pro','Professionnels']]" :key="t[0]" @click="setType(t[0])"
                            class="px-4 py-2 rounded-full border text-sm font-bold transition-colors"
                            :class="form.type === t[0] ? 'bg-brand-600 text-white border-brand-600' : 'border-gray-200 text-gray-500 hover:border-brand-300'">
                        {{ t[1] }}
                    </button>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 mb-8">
                <select v-model="form.city" @change="apply" class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-700 bg-white outline-none focus:border-brand-500">
                    <option value="all">Toutes les villes</option>
                    <option v-for="c in cities" :key="c" :value="c">{{ c }}</option>
                </select>
                <select v-model="form.sort" @change="apply" class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-700 bg-white outline-none focus:border-brand-500">
                    <option value="rating">Mieux notés</option>
                    <option value="sales">Plus de ventes</option>
                    <option value="listings">Plus d'annonces</option>
                </select>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="s in sellers" :key="s.id" class="bg-white border border-gray-200 rounded-2xl p-5 hover:border-brand-300 hover:shadow-soft transition-all">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-lg font-black shrink-0" :class="s.role === 'pro' ? 'bg-dark text-white' : 'bg-brand-100 text-brand-700'">{{ initials(s.name) }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5">
                                <Link :href="`/vendeurs/${s.id}`" class="font-bold text-dark hover:text-brand-600 truncate">{{ s.name }}</Link>
                                <i v-if="s.is_verified" class="fa-solid fa-circle-check text-blue-500 text-xs" title="Vérifié"></i>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">
                                <span v-if="s.role === 'pro'" class="text-dark font-bold"><i class="fa-solid fa-store text-brand-600"></i> Boutique · </span>
                                <i class="fa-solid fa-location-dot"></i> {{ s.city || 'Bénin' }}
                            </p>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center mb-4">
                        <div class="bg-gray-50 rounded-xl py-2"><p class="font-black text-dark text-sm"><i class="fa-solid fa-star text-yellow-400 text-xs"></i> {{ s.rating_avg }}</p><p class="text-[10px] text-gray-400 font-semibold uppercase">Note</p></div>
                        <div class="bg-gray-50 rounded-xl py-2"><p class="font-black text-dark text-sm">{{ s.listings_count }}</p><p class="text-[10px] text-gray-400 font-semibold uppercase">Annonces</p></div>
                        <div class="bg-gray-50 rounded-xl py-2"><p class="font-black text-dark text-sm">{{ s.sales_count }}</p><p class="text-[10px] text-gray-400 font-semibold uppercase">Ventes</p></div>
                    </div>
                    <Link :href="`/vendeurs/${s.id}`" class="block text-center bg-brand-600 hover:bg-brand-700 text-white py-2.5 rounded-full text-sm font-bold transition-colors">Voir la boutique</Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
