<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

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
      return 'btn-secondary';
    case 'accent':
      return 'btn-accent';
    case 'text':
      return 'btn-text';
    case 'danger':
      return 'btn-danger';
    case 'primary':
    default:
      return 'btn-primary';
  }
});

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'btn-sm';
    case 'lg':
      return 'btn-lg';
    case 'md':
    default:
      return 'btn-md';
  }
});
</script>

<template>
  <Link
    v-if="isLink"
    :href="href!"
    :class="['app-button', variantClasses, sizeClasses, { 'is-disabled': disabled || loading }]"
    :aria-disabled="disabled || loading"
  >
    <svg
      v-if="loading"
      class="btn-spinner"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
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
    :class="['app-button', variantClasses, sizeClasses, { 'is-disabled': disabled || loading }]"
  >
    <svg
      v-if="loading"
      class="btn-spinner"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      ></path>
    </svg>
    <slot />
  </button>
</template>

<style scoped>
.app-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-2);
  border-radius: var(--radius-sm);
  font-family: var(--font-heading);
  font-weight: 700;
  cursor: pointer;
  text-decoration: none;
  transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
  white-space: nowrap;
}

/* Sizing */
.btn-sm {
  padding: var(--space-1) var(--space-3);
  font-size: var(--text-xs);
  min-height: 28px;
}
.btn-md {
  padding: var(--space-2) var(--space-4);
  font-size: var(--text-sm);
  min-height: 38px;
}
.btn-lg {
  padding: var(--space-3) var(--space-5);
  font-size: var(--text-base);
  min-height: 48px;
}

/* Variants */
.btn-primary {
  background-color: var(--color-navy);
  color: var(--color-white);
  border: 1px solid var(--color-navy);
}
.btn-primary:hover:not(:disabled):not(.is-disabled) {
  background-color: var(--color-deep-navy);
  border-color: var(--color-deep-navy);
}

.btn-secondary {
  background-color: transparent;
  color: var(--color-navy);
  border: 1px solid var(--color-navy);
}
.btn-secondary:hover:not(:disabled):not(.is-disabled) {
  background-color: rgba(32, 40, 142, 0.06);
}

.btn-accent {
  background-color: var(--color-violet);
  color: var(--color-white);
  border: 1px solid var(--color-violet);
}
.btn-accent:hover:not(:disabled):not(.is-disabled) {
  background-color: #c447ee;
  border-color: #c447ee;
}

.btn-text {
  background-color: transparent;
  color: var(--color-navy);
  border: 1px solid transparent;
}
.btn-text:hover:not(:disabled):not(.is-disabled) {
  text-decoration: underline;
}

.btn-danger {
  background-color: var(--color-error);
  color: var(--color-white);
  border: 1px solid var(--color-error);
}
.btn-danger:hover:not(:disabled):not(.is-disabled) {
  background-color: #7f1d1d;
  border-color: #7f1d1d;
}

/* Disabled State */
.is-disabled,
.app-button:disabled {
  opacity: 0.55;
  cursor: not-allowed;
  pointer-events: none;
}

/* Spinner */
.btn-spinner {
  width: 16px;
  height: 16px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
