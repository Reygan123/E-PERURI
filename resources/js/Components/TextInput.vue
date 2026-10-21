<script setup lang="ts">
import { onMounted, ref } from 'vue';

const model = defineModel<string>({ required: true });

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value?.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        class="rounded-sm border-ptkp-border text-ptkp-body shadow-sm focus:border-ptkp-navy focus:ring-2 focus:ring-ptkp-navy/20"
        v-model="model"
        ref="input"
    />
</template>
