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
    :class="['app-nav-item', { 'is-active': isCurrentActive }]"
    :aria-current="isCurrentActive ? 'page' : undefined"
  >
    <span v-if="$slots.icon" class="nav-icon" aria-hidden="true">
      <slot name="icon" />
    </span>
    <span class="nav-label">
      <slot />
    </span>
  </Link>
</template>

<style scoped>
.app-nav-item {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-2) var(--space-3);
  border-radius: var(--radius-sm);
  color: rgba(255, 255, 255, 0.8);
  font-family: var(--font-body);
  font-size: var(--text-sm);
  font-weight: 500;
  text-decoration: none;
  transition: all 0.15s ease;
  position: relative;
}

.app-nav-item:hover {
  background-color: rgba(255, 255, 255, 0.08);
  color: var(--color-white);
}

.app-nav-item.is-active {
  background-color: rgba(216, 97, 255, 0.16);
  color: var(--color-white);
  font-weight: 600;
  border-left: 3px solid var(--color-violet); /* Spark Violet highlight */
}

.nav-icon {
  width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.nav-icon :deep(svg) {
  width: 18px;
  height: 18px;
}

.nav-label {
  flex: 1 1 auto;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
