<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    href: string;
    active?: boolean;
}>();

const classes = computed(() =>
    props.active
        ? 'block w-full ps-3.5 pe-4 py-2.5 rounded-md font-heading font-bold text-start text-sm text-ptkp-navy bg-ptkp-mist border-l-4 border-ptkp-navy transition duration-150 ease-in-out'
        : 'block w-full ps-3.5 pe-4 py-2.5 rounded-md font-body text-start text-sm font-medium text-slate-600 hover:text-ptkp-navy hover:bg-ptkp-mist/50 transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
