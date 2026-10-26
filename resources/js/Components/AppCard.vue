<script setup lang="ts">
interface Props {
    title?: string;
    subtitle?: string;
    interactive?: boolean;
    noPadding?: boolean;
}

withDefaults(defineProps<Props>(), {
    title: '',
    subtitle: '',
    interactive: false,
    noPadding: false,
});
</script>

<template>
    <div
        :class="[
            'flex flex-col overflow-hidden rounded-xl bg-white shadow-card transition-all duration-200',
            interactive
                ? 'cursor-pointer hover:-translate-y-0.5 hover:shadow-raised'
                : '',
        ]"
    >
        <div
            v-if="title || $slots.header"
            class="flex flex-col justify-between gap-3 border-b border-slate-100/80 px-5 py-4 sm:flex-row sm:items-center sm:px-6 sm:py-4"
        >
            <slot name="header">
                <div>
                    <h3
                        v-if="title"
                        class="m-0 font-heading text-base font-bold text-ptkp-navy sm:text-lg"
                    >
                        {{ title }}
                    </h3>
                    <p
                        v-if="subtitle"
                        class="mb-0 mt-1 font-body text-xs text-slate-500 sm:text-sm"
                    >
                        {{ subtitle }}
                    </p>
                </div>
            </slot>
            <div
                v-if="$slots.headerActions"
                class="flex w-full flex-wrap items-center justify-end gap-2 sm:w-auto"
            >
                <slot name="headerActions" />
            </div>
        </div>

        <div :class="['flex-1', noPadding ? 'p-0' : 'p-4 sm:p-6']">
            <slot />
        </div>

        <div
            v-if="$slots.footer"
            class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100/80 bg-slate-50/60 px-5 py-3.5 sm:px-6 sm:py-3.5"
        >
            <slot name="footer" />
        </div>
    </div>
</template>
