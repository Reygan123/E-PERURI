<script setup lang="ts">
defineProps<{
    message?: string;
}>();
</script>

<template>
    <div v-show="message">
        <p class="mt-1 font-body text-xs text-ptkp-error">
            {{ message }}
        </p>
    </div>
</template>
