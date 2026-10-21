<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface Props {
  links: PaginationLink[];
  from?: number;
  to?: number;
  total?: number;
}

const props = withDefaults(defineProps<Props>(), {
  from: undefined,
  to: undefined,
  total: undefined,
});

const cleanLabel = (label: string) => {
  if (label.includes('Previous') || label.includes('&laquo;')) {
    return '&laquo; Sebelumnya';
  }
  if (label.includes('Next') || label.includes('&raquo;')) {
    return 'Berikutnya &raquo;';
  }
  return label;
};

const hasMeta = computed(() => props.total !== undefined);
</script>

<template>
  <div class="flex flex-col sm:flex-row items-center justify-between w-full gap-3">
    <div v-if="hasMeta" class="font-body text-xs text-slate-500 text-center sm:text-left">
      Menampilkan
      <span class="font-semibold text-ptkp-body">{{ from || 0 }}</span>
      sampai
      <span class="font-semibold text-ptkp-body">{{ to || 0 }}</span>
      dari
      <span class="font-semibold text-ptkp-body">{{ total }}</span>
      data
    </div>

    <nav v-if="links && links.length > 3" class="inline-flex items-center gap-1 flex-wrap justify-center" aria-label="Navigasi Halaman">
      <template v-for="(link, index) in links" :key="index">
        <span
          v-if="!link.url"
          class="inline-flex items-center justify-center min-w-[32px] h-8 px-2 font-body text-xs font-medium rounded-lg opacity-40 cursor-not-allowed text-slate-400"
          v-html="cleanLabel(link.label)"
        />
        <Link
          v-else
          :href="link.url"
          :class="[
            'inline-flex items-center justify-center min-w-[32px] h-8 px-2.5 font-body text-xs rounded-lg transition-all',
            link.active
              ? 'bg-ptkp-navy text-white font-bold shadow-xs'
              : 'bg-white text-ptkp-body font-medium hover:bg-slate-100 hover:text-ptkp-navy shadow-2xs'
          ]"
          :aria-current="link.active ? 'page' : undefined"
          preserve-scroll
          v-html="cleanLabel(link.label)"
        />
      </template>
    </nav>
  </div>
</template>
