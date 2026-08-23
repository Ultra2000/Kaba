<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-xl border-gray-200 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 transition-all"
        v-model="model"
        ref="input"
    />
</template>
