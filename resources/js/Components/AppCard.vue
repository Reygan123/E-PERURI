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
  <div :class="['app-card', { 'is-interactive': interactive, 'no-padding': noPadding }]">
    <div v-if="title || $slots.header" class="card-header">
      <slot name="header">
        <div>
          <h3 v-if="title" class="card-title">{{ title }}</h3>
          <p v-if="subtitle" class="card-subtitle">{{ subtitle }}</p>
        </div>
      </slot>
      <div v-if="$slots.headerActions" class="card-header-actions">
        <slot name="headerActions" />
      </div>
    </div>

    <div class="card-body">
      <slot />
    </div>

    <div v-if="$slots.footer" class="card-footer">
      <slot name="footer" />
    </div>
  </div>
</template>

<style scoped>
.app-card {
  background-color: var(--color-white);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  display: flex;
  flex-direction: column;
  transition: box-shadow 0.2s ease, border-color 0.2s ease;
  overflow: hidden;
}

.app-card.is-interactive {
  cursor: pointer;
}
.app-card.is-interactive:hover {
  box-shadow: var(--shadow-raised);
  border-color: rgba(32, 40, 142, 0.28);
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-4);
  padding: var(--space-5) var(--space-5) var(--space-3) var(--space-5);
  border-bottom: 1px solid var(--color-border);
}

.card-title {
  margin: 0;
  font-family: var(--font-heading);
  font-size: var(--text-lg);
  font-weight: 700;
  color: var(--color-navy);
}

.card-subtitle {
  margin: var(--space-1) 0 0 0;
  font-family: var(--font-body);
  font-size: var(--text-sm);
  color: #64748b;
}

.card-body {
  padding: var(--space-5);
  flex: 1 1 auto;
}

.no-padding .card-body {
  padding: 0;
}

.card-footer {
  padding: var(--space-3) var(--space-5);
  background-color: #fafbfe;
  border-top: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: var(--space-3);
}
</style>
