<script setup lang="ts">
interface Props {
  title?: string;
  description?: string;
}

withDefaults(defineProps<Props>(), {
  title: 'Belum Ada Data',
  description: 'Data belum ditambahkan atau filter pencarian tidak menemukan hasil.',
});
</script>

<template>
  <div class="app-empty-state">
    <!-- Ilustrasi Geometri Atap Limas Warisan Pabrik PTKP -->
    <div class="empty-illustration" aria-hidden="true">
      <svg viewBox="0 0 120 90" fill="none" xmlns="http://www.w3.org/2000/svg" class="limas-svg">
        <!-- Bayangan dasar -->
        <ellipse cx="60" cy="80" rx="45" ry="6" fill="#20288E" fill-opacity="0.08" />
        <!-- Dinding dasar -->
        <rect x="35" y="45" width="50" height="30" rx="2" fill="#FFFFFF" stroke="#20288E" stroke-width="1.5" stroke-opacity="0.2" />
        <!-- Pintu / gerbang pabrik -->
        <path d="M52 75V58C52 56.8954 52.8954 56 54 56H66C67.1046 56 68 56.8954 68 58V75" fill="#EEF0FB" stroke="#20288E" stroke-width="1.5" stroke-opacity="0.3" />
        <!-- Atap Limas Tingkat 1 -->
        <path d="M25 45L40 28H80L95 45H25Z" fill="#EEF0FB" stroke="#20288E" stroke-width="1.5" />
        <!-- Atap Limas Tingkat 2 (Puncak) -->
        <path d="M42 28L55 15H65L78 28H42Z" fill="#20288E" fill-opacity="0.9" />
        <!-- Cerobong berpita subtle -->
        <rect x="74" y="8" width="6" height="15" fill="#FFFFFF" stroke="#20288E" stroke-width="1.2" />
        <line x1="74" y1="12" x2="80" y2="12" stroke="#D861FF" stroke-width="1.5" />
      </svg>
    </div>

    <h3 class="empty-title">{{ title }}</h3>
    <p class="empty-description">{{ description }}</p>

    <div v-if="$slots.action" class="empty-action">
      <slot name="action" />
    </div>
  </div>
</template>

<style scoped>
.app-empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: var(--space-7) var(--space-5);
  background-color: var(--color-white);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
}

.empty-illustration {
  width: 120px;
  height: 90px;
  margin-bottom: var(--space-3);
}

.limas-svg {
  width: 100%;
  height: 100%;
}

.empty-title {
  margin: 0;
  font-family: var(--font-heading);
  font-size: var(--text-base);
  font-weight: 700;
  color: var(--color-navy);
}

.empty-description {
  margin: var(--space-1) 0 0 0;
  font-family: var(--font-body);
  font-size: var(--text-sm);
  color: #64748b;
  max-width: 420px;
}

.empty-action {
  margin-top: var(--space-4);
}
</style>
