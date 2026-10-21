<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

interface Props {
  href: string;
  active?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  active: false,
});

const isCurrentActive = computed(() => props.active);
</script>

<template>
  <Link
    :href="href"
    :class="[
      'flex items-center gap-3 px-3 py-2 rounded-sm font-body text-sm font-medium no-underline transition-all duration-150',
      isCurrentActive
        ? 'bg-ptkp-violet/20 text-white font-semibold border-l-[3px] border-ptkp-violet'
        : 'text-white/80 hover:bg-white/10 hover:text-white'
    ]"
    :aria-current="isCurrentActive ? 'page' : undefined"
  >
    <span v-if="$slots.icon" class="w-[18px] h-[18px] flex items-center justify-center flex-shrink-0" aria-hidden="true">
      <slot name="icon" />
    </span>
    <span class="flex-1 min-w-0 truncate">
      <slot />
    </span>
  </Link>
</template>
