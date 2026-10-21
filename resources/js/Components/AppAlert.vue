<script setup lang="ts">
import { computed, ref } from 'vue';

interface Props {
  variant?: 'info' | 'success' | 'warning' | 'error';
  title?: string;
  dismissible?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'info',
  title: '',
  dismissible: false,
});

const isDismissed = ref(false);

const dismiss = () => {
  isDismissed.value = true;
};

const variantClass = computed(() => `alert-${props.variant}`);
</script>

<template>
  <div v-if="!isDismissed" :class="['app-alert', variantClass]" role="alert">
    <!-- Icon -->
    <div class="alert-icon" aria-hidden="true">
      <!-- Success Icon -->
      <svg v-if="variant === 'success'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
        <path
          fill-rule="evenodd"
          d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z"
          clip-rule="evenodd"
        />
      </svg>
      <!-- Warning Icon -->
      <svg v-else-if="variant === 'warning'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
        <path
          fill-rule="evenodd"
          d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"
          clip-rule="evenodd"
        />
      </svg>
      <!-- Error Icon -->
      <svg v-else-if="variant === 'error'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
        <path
          fill-rule="evenodd"
          d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z"
          clip-rule="evenodd"
        />
      </svg>
      <!-- Info Icon (Navy) -->
      <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
        <path
          fill-rule="evenodd"
          d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z"
          clip-rule="evenodd"
        />
      </svg>
    </div>

    <!-- Content -->
    <div class="alert-content">
      <h4 v-if="title" class="alert-title">{{ title }}</h4>
      <div class="alert-message">
        <slot />
      </div>
    </div>

    <!-- Dismiss Button -->
    <button
      v-if="dismissible"
      type="button"
      class="alert-close"
      aria-label="Tutup notifikasi"
      @click="dismiss"
    >
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
        <path
          d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"
        />
      </svg>
    </button>
  </div>
</template>

<style scoped>
.app-alert {
  display: flex;
  align-items: flex-start;
  gap: var(--space-3);
  padding: var(--space-3) var(--space-4);
  border-radius: var(--radius-sm);
  font-family: var(--font-body);
  border: 1px solid transparent;
}

.alert-icon {
  flex-shrink: 0;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 1px;
}
.alert-icon svg {
  width: 20px;
  height: 20px;
}

.alert-content {
  flex: 1 1 auto;
}

.alert-title {
  margin: 0 0 var(--space-1) 0;
  font-family: var(--font-heading);
  font-size: var(--text-sm);
  font-weight: 700;
}

.alert-message {
  font-size: var(--text-sm);
  line-height: 1.4;
}

.alert-close {
  flex-shrink: 0;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0;
  width: 18px;
  height: 18px;
  opacity: 0.6;
  transition: opacity 0.15s ease;
}
.alert-close:hover {
  opacity: 1;
}
.alert-close svg {
  width: 18px;
  height: 18px;
}

/* Alert Variants */
.alert-info {
  background-color: var(--color-info-bg);
  color: var(--color-navy);
  border-color: rgba(32, 40, 142, 0.2);
}
.alert-info .alert-icon {
  color: var(--color-navy);
}

.alert-success {
  background-color: var(--color-success-bg);
  color: var(--color-success);
  border-color: rgba(22, 101, 52, 0.2);
}
.alert-success .alert-icon {
  color: var(--color-success);
}

.alert-warning {
  background-color: var(--color-warning-bg);
  color: var(--color-warning);
  border-color: rgba(133, 77, 14, 0.2);
}
.alert-warning .alert-icon {
  color: var(--color-warning);
}

.alert-error {
  background-color: var(--color-error-bg);
  color: var(--color-error);
  border-color: rgba(153, 27, 27, 0.2);
}
.alert-error .alert-icon {
  color: var(--color-error);
}
</style>
