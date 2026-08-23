<script setup>
defineProps({
    form: Object,
    categories: Array,
    cities: Array,
    conditions: Object,
    languages: Array,
    types: Array,
    hideTitle: Boolean,
});
const emit = defineEmits(['apply', 'setType', 'reset']);
const fmt = (n) => Number(n) === 0 ? 'Gratuit' : new Intl.NumberFormat('fr-FR').format(n) + ' F';
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between" :class="{ 'justify-end': hideTitle }">
            <h2 v-if="!hideTitle" class="font-black text-dark text-lg">Filtres</h2>
            <button @click="emit('reset')" class="text-xs font-bold text-brand-600 hover:underline">Réinitialiser</button>
        </div>
        <div>
            <p class="text-sm font-bold text-dark mb-2">Type</p>
            <div class="flex flex-wrap gap-2">
                <button v-for="t in types" :key="t.v" @click="emit('setType', t.v)"
                        class="px-3 py-1.5 rounded-full border text-xs font-bold transition-colors"
                        :class="form.type === t.v ? 'bg-brand-600 text-white border-brand-600' : 'border-gray-200 text-gray-500 hover:border-brand-300'">
                    {{ t.label }}
                </button>
            </div>
        </div>
        <div>
            <label class="block text-sm font-bold text-dark mb-2">Catégorie</label>
            <select v-model="form.category" @change="emit('apply')" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 bg-white">
                <option value="all">Toutes</option>
                <option v-for="c in categories" :key="c.slug" :value="c.slug">{{ c.name }}</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-bold text-dark mb-2">Ville</label>
            <select v-model="form.city" @change="emit('apply')" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 bg-white">
                <option value="all">Toutes</option>
                <option v-for="c in cities" :key="c" :value="c">{{ c }}</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-bold text-dark mb-2">État</label>
            <select v-model="form.condition" @change="emit('apply')" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 bg-white">
                <option value="all">Tous</option>
                <option v-for="(label, key) in conditions" :key="key" :value="key">{{ label }}</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-bold text-dark mb-2">Langue</label>
            <select v-model="form.language" @change="emit('apply')" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 bg-white">
                <option value="all">Toutes</option>
                <option v-for="l in languages" :key="l" :value="l">{{ l }}</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-bold text-dark mb-2">Prix max : <span class="text-brand-600">{{ fmt(form.price_max) }}</span></label>
            <input type="range" min="0" max="40000" step="500" v-model="form.price_max" @change="emit('apply')" class="w-full accent-brand-600">
        </div>
    </div>
</template>
