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

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'success':
      return 'bg-ptkp-success-bg text-ptkp-success border-green-700/20';
    case 'warning':
      return 'bg-ptkp-warning-bg text-ptkp-warning border-amber-800/20';
    case 'error':
      return 'bg-ptkp-error-bg text-ptkp-error border-red-800/20';
    case 'info':
    default:
      return 'bg-ptkp-mist text-ptkp-navy border-ptkp-navy/20';
  }
});
</script>

<template>
  <div
    v-if="!isDismissed"
    :class="[
      'flex items-start gap-3.5 px-4.5 py-3.5 rounded-md font-body border shadow-sm',
      variantClasses
    ]"
    role="alert"
  >
    <!-- Icon -->
    <div class="flex-shrink-0 w-5 h-5 flex items-center justify-center mt-0.5" aria-hidden="true">
      <svg v-if="variant === 'success'" class="w-5 h-5 text-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
        <path
          fill-rule="evenodd"
          d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z"
          clip-rule="evenodd"
        />
      </svg>
      <svg v-else-if="variant === 'warning'" class="w-5 h-5 text-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
        <path
          fill-rule="evenodd"
          d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"
          clip-rule="evenodd"
        />
      </svg>
      <svg v-else-if="variant === 'error'" class="w-5 h-5 text-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
        <path
          fill-rule="evenodd"
          d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z"
          clip-rule="evenodd"
        />
      </svg>
      <svg v-else class="w-5 h-5 text-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
        <path
          fill-rule="evenodd"
          d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z"
          clip-rule="evenodd"
        />
      </svg>
    </div>

    <!-- Content -->
    <div class="flex-1 min-w-0">
      <h4 v-if="title" class="m-0 mb-1 font-heading text-sm font-bold">
        {{ title }}
      </h4>
      <div class="text-sm leading-relaxed">
        <slot />
      </div>
    </div>

    <!-- Dismiss Button -->
    <button
      v-if="dismissible"
      type="button"
      class="flex-shrink-0 bg-transparent border-none cursor-pointer p-0 w-5 h-5 text-current opacity-60 hover:opacity-100 transition-opacity"
      aria-label="Tutup notifikasi"
      @click="dismiss"
    >
      <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
        <path
          d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"
        />
      </svg>
    </button>
  </div>
</template>
