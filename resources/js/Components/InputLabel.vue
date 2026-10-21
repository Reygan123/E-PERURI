<script setup lang="ts">
defineProps<{
    value?: string;
}>();
</script>

<template>
    <label class="block font-body text-sm font-medium text-ptkp-body mb-1">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
