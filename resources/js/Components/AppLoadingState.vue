<script setup lang="ts">
interface Props {
  rows?: number;
  card?: boolean;
}

withDefaults(defineProps<Props>(), {
  rows: 3,
  card: true,
});
</script>

<template>
  <div :class="['app-loading-state', { 'is-card': card }]">
    <div class="skeleton-header">
      <div class="skeleton-bar title-bar"></div>
      <div class="skeleton-bar subtitle-bar"></div>
    </div>
    <div class="skeleton-body">
      <div v-for="n in rows" :key="n" class="skeleton-row">
        <div class="skeleton-bar row-bar"></div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.app-loading-state {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.is-card {
  background-color: var(--color-white);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  padding: var(--space-5);
}

.skeleton-header {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.skeleton-body {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.skeleton-bar {
  background: linear-gradient(
    90deg,
    var(--color-mist) 25%,
    rgba(32, 40, 142, 0.08) 50%,
    var(--color-mist) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: var(--radius-sm);
}

.title-bar {
  height: 20px;
  width: 35%;
}

.subtitle-bar {
  height: 14px;
  width: 60%;
}

.row-bar {
  height: 38px;
  width: 100%;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}
</style>
