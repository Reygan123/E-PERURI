<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    href: string;
    active?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    active: false,
});

const isCurrentActive = computed(() => props.active);
</script>

<template>
    <Link
        :href="href"
        :class="[
            'flex items-center gap-3 rounded-md px-3.5 py-2.5 font-body text-sm font-medium no-underline transition-all duration-150',
            isCurrentActive
                ? 'bg-ptkp-violet/20 font-semibold text-white shadow-sm ring-1 ring-ptkp-violet/50'
                : 'text-white/80 hover:bg-white/10 hover:text-white',
        ]"
        :aria-current="isCurrentActive ? 'page' : undefined"
    >
        <span
            v-if="$slots.icon"
            class="flex h-[18px] w-[18px] flex-shrink-0 items-center justify-center"
            aria-hidden="true"
        >
            <slot name="icon" />
        </span>
        <span class="min-w-0 flex-1 truncate">
            <slot />
        </span>
    </Link>
</template>
