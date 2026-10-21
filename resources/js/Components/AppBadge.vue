<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  variant?: 'navy' | 'violet' | 'success' | 'warning' | 'error' | 'neutral';
  size?: 'sm' | 'md';
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'navy',
  size: 'md',
});

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'violet':
      return 'bg-ptkp-violet text-white';
    case 'success':
      return 'bg-ptkp-success-bg text-ptkp-success border border-green-700/20';
    case 'warning':
      return 'bg-ptkp-warning-bg text-ptkp-warning border border-amber-800/20';
    case 'error':
      return 'bg-ptkp-error-bg text-ptkp-error border border-red-800/20';
    case 'neutral':
      return 'bg-ptkp-neutral-bg text-ptkp-neutral border border-slate-600/20';
    case 'navy':
    default:
      return 'bg-ptkp-navy text-white';
  }
});

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'px-2 py-0.5 text-[11px]';
    case 'md':
    default:
      return 'px-2.5 py-1 text-xs';
  }
});
</script>

<template>
  <span
    :class="[
      'inline-flex items-center justify-center gap-1 rounded-full font-body font-medium leading-none whitespace-nowrap',
      variantClasses,
      sizeClasses
    ]"
  >
    <slot />
  </span>
</template>
