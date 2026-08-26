<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    // Objet de pagination Laravel (links, from, to, total…)
    meta: { type: Object, required: true },
});
</script>

<template>
    <div v-if="meta.total > 0" class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 border-t border-gray-100 bg-gray-50/60">
        <p class="text-xs text-gray-500 font-medium">
            {{ meta.from }}–{{ meta.to }} sur <span class="font-bold text-dark">{{ meta.total }}</span>
        </p>

        <div v-if="meta.last_page > 1" class="flex flex-wrap gap-1">
            <template v-for="(link, i) in meta.links" :key="i">
                <Link v-if="link.url" :href="link.url" preserve-scroll
                      class="min-w-8 h-8 px-2.5 rounded-lg flex items-center justify-center text-xs font-bold transition-colors"
                      :class="link.active ? 'bg-brand-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:border-brand-300'"
                      v-html="link.label" />
                <span v-else class="min-w-8 h-8 px-2.5 rounded-lg flex items-center justify-center text-xs text-gray-300" v-html="link.label" />
            </template>
        </div>
    </div>
</template>
