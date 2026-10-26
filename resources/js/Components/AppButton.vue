<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    variant?: 'primary' | 'secondary' | 'accent' | 'text' | 'danger';
    size?: 'sm' | 'md' | 'lg';
    type?: 'button' | 'submit' | 'reset';
    disabled?: boolean;
    loading?: boolean;
    href?: string;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'primary',
    size: 'md',
    type: 'button',
    disabled: false,
    loading: false,
    href: undefined,
});

const isLink = computed(() => Boolean(props.href));

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'secondary':
            return 'bg-white text-ptkp-navy border border-slate-200 hover:bg-slate-50 hover:border-slate-300 shadow-xs';
        case 'accent':
            return 'bg-ptkp-violet text-white border border-ptkp-violet hover:bg-[#c447ee] hover:border-[#c447ee]';
        case 'text':
            return 'bg-transparent text-ptkp-navy border border-transparent hover:underline';
        case 'danger':
            return 'bg-ptkp-error text-white border border-ptkp-error hover:bg-[#7f1d1d] hover:border-[#7f1d1d]';
        case 'primary':
        default:
            return 'bg-ptkp-navy text-white border border-ptkp-navy hover:bg-ptkp-deep hover:border-ptkp-deep';
    }
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'px-3 py-1 text-xs min-h-[28px]';
        case 'lg':
            return 'px-6 py-3 text-base min-h-[48px]';
        case 'md':
        default:
            return 'px-4 py-2 text-sm min-h-[38px]';
    }
});
</script>

<template>
    <Link
        v-if="isLink"
        :href="href!"
        :class="[
            'text-decoration-none shadow-xs inline-flex cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-lg font-heading font-bold transition-all duration-150 hover:shadow active:scale-[0.99]',
            variantClasses,
            sizeClasses,
            {
                'pointer-events-none cursor-not-allowed opacity-50':
                    disabled || loading,
            },
        ]"
        :aria-disabled="disabled || loading"
    >
        <svg
            v-if="loading"
            class="h-4 w-4 animate-spin text-current"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            ></circle>
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
        </svg>
        <slot />
    </Link>
    <button
        v-else
        :type="type"
        :disabled="disabled || loading"
        :class="[
            'shadow-xs inline-flex cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-lg font-heading font-bold transition-all duration-150 hover:shadow active:scale-[0.99]',
            variantClasses,
            sizeClasses,
            {
                'pointer-events-none cursor-not-allowed opacity-50':
                    disabled || loading,
            },
        ]"
    >
        <svg
            v-if="loading"
            class="h-4 w-4 animate-spin text-current"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            ></circle>
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
        </svg>
        <slot />
    </button>
</template>
