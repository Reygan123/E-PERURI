<script setup lang="ts">
withDefaults(
    defineProps<{
        type?: 'button' | 'submit' | 'reset';
    }>(),
    {
        type: 'button',
    },
);
</script>

<template>
    <button
        :type="type"
        class="shadow-xs inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 font-heading text-xs font-bold uppercase tracking-wider text-ptkp-navy transition-all duration-150 ease-in-out hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-ptkp-navy/20 active:scale-[0.99] disabled:opacity-25"
    >
        <slot />
    </button>
</template>
