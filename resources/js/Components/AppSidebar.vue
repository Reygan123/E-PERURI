<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLogo from './AppLogo.vue';
import AppNavItem from './AppNavItem.vue';
import AppBadge from './AppBadge.vue';

const page = usePage();
const currentUrl = computed(() => page.url);

const isRouteActive = (pattern: string) => {
  return currentUrl.value.startsWith(pattern);
};
</script>

<template>
  <aside class="app-sidebar">
    <!-- Brand Header -->
    <div class="sidebar-header">
      <AppLogo width="200px" theme="boxed" />
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
      <!-- Section: Platform Core -->
      <div class="nav-section">
        <span class="nav-section-title">PLATFORM CORE</span>

        <AppNavItem href="/dashboard" :active="currentUrl === '/dashboard'">
          <template #icon>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
            </svg>
          </template>
          Dashboard
        </AppNavItem>

        <AppNavItem href="/platform/org-units" :active="isRouteActive('/platform/org-units')">
          <template #icon>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
            </svg>
          </template>
          Unit Organisasi
        </AppNavItem>

        <AppNavItem href="/platform/positions" :active="isRouteActive('/platform/positions')">
          <template #icon>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0" />
            </svg>
          </template>
          Master Jabatan
        </AppNavItem>

        <AppNavItem href="/platform/employees" :active="isRouteActive('/platform/employees')">
          <template #icon>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
          </template>
          Data Pegawai
        </AppNavItem>

        <AppNavItem href="/platform/users" :active="isRouteActive('/platform/users')">
          <template #icon>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
            </svg>
          </template>
          Pengguna & Peran
        </AppNavItem>

        <AppNavItem href="/platform/numbering" :active="isRouteActive('/platform/numbering')">
          <template #icon>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5-3.9 19.5m-2.1-19.5-3.9 19.5" />
            </svg>
          </template>
          Penomoran Dokumen
        </AppNavItem>

        <AppNavItem href="/platform/audit-logs" :active="isRouteActive('/platform/audit-logs')">
          <template #icon>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
          </template>
          Audit Log
        </AppNavItem>
      </div>

      <!-- Section: Ekosistem Modul PTKP -->
      <div class="nav-section">
        <span class="nav-section-title">MODUL EKOSISTEM</span>

        <div class="nav-module-item">
          <span class="nav-module-name">Layanan (Persuratan)</span>
          <AppBadge variant="violet" size="sm">Fase 9</AppBadge>
        </div>

        <div class="nav-module-item">
          <span class="nav-module-name">E-Arsip</span>
          <AppBadge variant="violet" size="sm">Fase 10</AppBadge>
        </div>

        <div class="nav-module-item">
          <span class="nav-module-name">Pembelajaran (LMS)</span>
          <AppBadge variant="violet" size="sm">Fase 11</AppBadge>
        </div>

        <div class="nav-module-item">
          <span class="nav-module-name">SDM (HRIS)</span>
          <AppBadge variant="violet" size="sm">Fase 12</AppBadge>
        </div>

        <div class="nav-module-item">
          <span class="nav-module-name">Manufaktur (Kertas)</span>
          <AppBadge variant="violet" size="sm">Fase 13</AppBadge>
        </div>
      </div>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
      <div class="version-label">PT Kertas Padalarang</div>
      <div class="sub-label">Ekosistem Internal v2.0</div>
    </div>
  </aside>
</template>

<style scoped>
.app-sidebar {
  width: 240px;
  min-width: 240px;
  height: 100vh;
  background-color: var(--color-deep-navy); /* Deep Navy #141A5C */
  color: var(--color-white);
  display: flex;
  flex-direction: column;
  position: sticky;
  top: 0;
  border-right: 1px solid rgba(255, 255, 255, 0.08);
  box-sizing: border-box;
  user-select: none;
}

.sidebar-header {
  padding: var(--space-4);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  align-items: center;
  justify-content: center;
}

.sidebar-nav {
  flex: 1 1 auto;
  overflow-y: auto;
  padding: var(--space-4) var(--space-3);
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.nav-section {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.nav-section-title {
  font-family: var(--font-heading);
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.1em;
  color: rgba(255, 255, 255, 0.45);
  padding: 0 var(--space-3) var(--space-2) var(--space-3);
}

.nav-module-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--space-2) var(--space-3);
  border-radius: var(--radius-sm);
  color: rgba(255, 255, 255, 0.5);
  font-family: var(--font-body);
  font-size: var(--text-xs);
}

.nav-module-name {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sidebar-footer {
  padding: var(--space-3) var(--space-4);
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  font-family: var(--font-body);
  background-color: rgba(0, 0, 0, 0.15);
}

.version-label {
  font-size: var(--text-xs);
  font-weight: 600;
  color: rgba(255, 255, 255, 0.9);
}

.sub-label {
  font-size: 11px;
  color: rgba(255, 255, 255, 0.5);
}
</style>
