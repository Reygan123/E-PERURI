<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    links: PaginationLink[];
    from?: number;
    to?: number;
    total?: number;
}

const props = withDefaults(defineProps<Props>(), {
    from: undefined,
    to: undefined,
    total: undefined,
});

const cleanLabel = (label: string) => {
    if (label.includes('Previous') || label.includes('&laquo;')) {
        return '&laquo; Sebelumnya';
    }
    if (label.includes('Next') || label.includes('&raquo;')) {
        return 'Berikutnya &raquo;';
    }
    return label;
};

const hasMeta = computed(() => props.total !== undefined);
</script>

<template>
    <div
        class="flex w-full flex-col items-center justify-between gap-3 sm:flex-row"
    >
        <div
            v-if="hasMeta"
            class="text-center font-body text-xs text-slate-500 sm:text-left"
        >
            Menampilkan
            <span class="font-semibold text-ptkp-body">{{ from || 0 }}</span>
            sampai
            <span class="font-semibold text-ptkp-body">{{ to || 0 }}</span>
            dari
            <span class="font-semibold text-ptkp-body">{{ total }}</span>
            data
        </div>

        <nav
            v-if="links && links.length > 3"
            class="inline-flex flex-wrap items-center justify-center gap-1"
            aria-label="Navigasi Halaman"
        >
            <template v-for="(link, index) in links" :key="index">
                <span
                    v-if="!link.url"
                    class="inline-flex h-8 min-w-[32px] cursor-not-allowed items-center justify-center rounded-lg px-2 font-body text-xs font-medium text-slate-400 opacity-40"
                    v-html="cleanLabel(link.label)"
                />
                <Link
                    v-else
                    :href="link.url"
                    :class="[
                        'inline-flex h-8 min-w-[32px] items-center justify-center rounded-lg px-2.5 font-body text-xs transition-all',
                        link.active
                            ? 'shadow-xs bg-ptkp-navy font-bold text-white'
                            : 'shadow-2xs bg-white font-medium text-ptkp-body hover:bg-slate-100 hover:text-ptkp-navy',
                    ]"
                    :aria-current="link.active ? 'page' : undefined"
                    preserve-scroll
                >
                    <span v-html="cleanLabel(link.label)" />
                </Link>
            </template>
        </nav>
    </div>
</template>
