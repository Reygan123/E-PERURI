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
      'bg-white rounded-xl shadow-card flex flex-col overflow-hidden transition-all duration-200',
      interactive ? 'cursor-pointer hover:shadow-raised hover:-translate-y-0.5' : ''
    ]"
  >
    <div
      v-if="title || $slots.header"
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 sm:px-6 sm:py-4 border-b border-slate-100/80"
    >
      <slot name="header">
        <div>
          <h3 v-if="title" class="m-0 font-heading text-base sm:text-lg font-bold text-ptkp-navy">
            {{ title }}
          </h3>
          <p v-if="subtitle" class="mt-1 mb-0 font-body text-xs sm:text-sm text-slate-500">
            {{ subtitle }}
          </p>
        </div>
      </slot>
      <div v-if="$slots.headerActions" class="flex items-center gap-2 w-full sm:w-auto justify-end flex-wrap">
        <slot name="headerActions" />
      </div>
    </div>

    <div :class="['flex-1', noPadding ? 'p-0' : 'p-4 sm:p-6']">
      <slot />
    </div>

    <div
      v-if="$slots.footer"
      class="px-5 py-3.5 sm:px-6 sm:py-3.5 bg-slate-50/60 border-t border-slate-100/80 flex items-center justify-end gap-3 flex-wrap"
    >
      <slot name="footer" />
    </div>
  </div>
</template>
