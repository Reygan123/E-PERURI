<script setup lang="ts">
defineProps<{
    message?: string;
}>();
</script>

<template>
    <div v-show="message">
        <p class="font-body text-xs text-ptkp-error mt-1">
            {{ message }}
        </p>
    </div>
</template>
