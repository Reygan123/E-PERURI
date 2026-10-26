<script setup lang="ts">
interface Props {
    width?: string;
    theme?: 'on-light' | 'boxed';
    alt?: string;
}

withDefaults(defineProps<Props>(), {
    width: '180px',
    theme: 'boxed',
    alt: 'Logo PT Kertas Padalarang (Peruri Security Paper Mill)',
});
</script>

<template>
    <div
        :class="[
            'inline-flex items-center justify-center transition-all duration-150',
            theme === 'boxed'
                ? 'rounded-lg bg-white px-3.5 py-2 shadow-sm'
                : '',
        ]"
        :style="{ width, minWidth: '100px' }"
        role="img"
        :aria-label="alt"
    >
        <img
            src="/assets/logo.png"
            :alt="alt"
            class="block h-auto w-full object-contain"
            loading="eager"
        />
    </div>
</template>
