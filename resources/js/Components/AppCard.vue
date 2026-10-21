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
      'bg-white border border-ptkp-border rounded-sm flex flex-col overflow-hidden transition-all duration-200',
      interactive ? 'cursor-pointer hover:shadow-raised hover:border-ptkp-navy/30' : ''
    ]"
  >
    <div
      v-if="title || $slots.header"
      class="flex items-center justify-between gap-4 px-6 pt-6 pb-3 border-b border-ptkp-border"
    >
      <slot name="header">
        <div>
          <h3 v-if="title" class="m-0 font-heading text-lg font-bold text-ptkp-navy">
            {{ title }}
          </h3>
          <p v-if="subtitle" class="mt-1 mb-0 font-body text-sm text-slate-500">
            {{ subtitle }}
          </p>
        </div>
      </slot>
      <div v-if="$slots.headerActions" class="flex items-center gap-2">
        <slot name="headerActions" />
      </div>
    </div>

    <div :class="['flex-1', noPadding ? 'p-0' : 'p-6']">
      <slot />
    </div>

    <div
      v-if="$slots.footer"
      class="px-6 py-3 bg-[#fafbfe] border-t border-ptkp-border flex items-center justify-end gap-3"
    >
      <slot name="footer" />
    </div>
  </div>
</template>
