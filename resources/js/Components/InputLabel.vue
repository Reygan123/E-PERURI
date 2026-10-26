<script setup lang="ts">
defineProps<{
    value?: string;
}>();
</script>

<template>
    <label class="mb-1 block font-body text-sm font-medium text-ptkp-body">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
