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

const variantClass = computed(() => `badge-${props.variant}`);
const sizeClass = computed(() => `badge-${props.size}`);
</script>

<template>
  <span :class="['app-badge', variantClass, sizeClass]">
    <slot />
  </span>
</template>

<style scoped>
.app-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-1);
  border-radius: var(--radius-pill); /* SATU-SATUNYA komponen yang boleh pill */
  font-family: var(--font-body);
  font-weight: 500;
  line-height: 1;
  white-space: nowrap;
}

/* Sizing */
.badge-sm {
  padding: 2px 8px;
  font-size: 11px;
}
.badge-md {
  padding: var(--space-1) var(--space-2);
  font-size: var(--text-xs);
}

/* Variants */
.badge-navy {
  background-color: var(--color-navy);
  color: var(--color-white);
}

.badge-violet {
  background-color: var(--color-violet);
  color: var(--color-white);
}

.badge-success {
  background-color: var(--color-success-bg);
  color: var(--color-success);
  border: 1px solid rgba(22, 101, 52, 0.2);
}

.badge-warning {
  background-color: var(--color-warning-bg);
  color: var(--color-warning);
  border: 1px solid rgba(133, 77, 14, 0.2);
}

.badge-error {
  background-color: var(--color-error-bg);
  color: var(--color-error);
  border: 1px solid rgba(153, 27, 27, 0.2);
}

.badge-neutral {
  background-color: var(--color-neutral-bg);
  color: var(--color-neutral);
  border: 1px solid rgba(71, 85, 105, 0.2);
}
</style>
