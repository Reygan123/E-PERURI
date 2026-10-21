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
        class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 font-heading text-xs font-bold uppercase tracking-wider text-ptkp-navy shadow-xs transition-all duration-150 ease-in-out hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-ptkp-navy/20 disabled:opacity-25 active:scale-[0.99]"
    >
        <slot />
    </button>
</template>
