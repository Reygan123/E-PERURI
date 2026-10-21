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
  <div class="app-pagination-wrapper">
    <div v-if="hasMeta" class="pagination-meta">
      Menampilkan
      <span class="meta-highlight">{{ from || 0 }}</span>
      sampai
      <span class="meta-highlight">{{ to || 0 }}</span>
      dari
      <span class="meta-highlight">{{ total }}</span>
      data
    </div>

    <nav v-if="links && links.length > 3" class="pagination-nav" aria-label="Navigasi Halaman">
      <template v-for="(link, index) in links" :key="index">
        <span
          v-if="!link.url"
          class="pagination-item is-disabled"
          v-html="cleanLabel(link.label)"
        />
        <Link
          v-else
          :href="link.url"
          :class="['pagination-item', { 'is-active': link.active }]"
          :aria-current="link.active ? 'page' : undefined"
          preserve-scroll
          v-html="cleanLabel(link.label)"
        />
      </template>
    </nav>
  </div>
</template>

<style scoped>
.app-pagination-wrapper {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  gap: var(--space-4);
  flex-wrap: wrap;
}

.pagination-meta {
  font-family: var(--font-body);
  font-size: var(--text-xs);
  color: #64748b;
}

.meta-highlight {
  font-weight: 600;
  color: var(--color-text-body);
}

.pagination-nav {
  display: inline-flex;
  align-items: center;
  gap: var(--space-1);
}

.pagination-item {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 32px;
  height: 32px;
  padding: 0 var(--space-2);
  font-family: var(--font-body);
  font-size: var(--text-xs);
  font-weight: 500;
  text-decoration: none;
  border-radius: var(--radius-sm);
  border: 1px solid var(--color-border);
  background-color: var(--color-white);
  color: var(--color-text-body);
  transition: all 0.15s ease;
}

.pagination-item:hover:not(.is-disabled):not(.is-active) {
  background-color: var(--color-mist);
  border-color: var(--color-navy);
  color: var(--color-navy);
}

.pagination-item.is-active {
  background-color: var(--color-navy);
  border-color: var(--color-navy);
  color: var(--color-white);
  font-weight: 700;
}

.pagination-item.is-disabled {
  opacity: 0.45;
  cursor: not-allowed;
  background-color: transparent;
  border-color: transparent;
}
</style>
