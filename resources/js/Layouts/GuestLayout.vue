<script setup lang="ts">
import AppLogo from '@/Components/AppLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="guest-layout-root">
    <div class="guest-card-container">
      <div class="logo-wrapper">
        <Link href="/">
          <AppLogo width="240px" theme="on-light" />
        </Link>
      </div>

      <div class="guest-card">
        <slot />
      </div>

      <div class="guest-footer">
        <p>&copy; 2026 PT Kertas Padalarang (Peruri Security Paper Mill)</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.guest-layout-root {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--color-mist);
  padding: var(--space-4);
  font-family: var(--font-body);
}

.guest-card-container {
  width: 100%;
  max-width: 440px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--space-4);
}

.logo-wrapper {
  background-color: var(--color-white);
  padding: var(--space-3) var(--space-5);
  border-radius: var(--radius-sm);
  border: 1px solid var(--color-border);
  box-shadow: 0 1px 3px rgba(32, 40, 142, 0.06);
}

.guest-card {
  width: 100%;
  background-color: var(--color-white);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  padding: var(--space-6);
  box-shadow: var(--shadow-raised);
}

.guest-footer {
  text-align: center;
  font-size: var(--text-xs);
  color: #64748b;
}
</style>
