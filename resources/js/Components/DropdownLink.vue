<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    href: string;
}>();
</script>

<template>
    <Link
        :href="href"
        class="block w-full px-4 py-2 text-start font-body text-sm leading-5 text-ptkp-body transition-colors duration-150 hover:bg-ptkp-mist hover:text-ptkp-navy focus:bg-ptkp-mist focus:outline-none"
    >
        <slot />
    </Link>
</template>
