<script setup lang="ts">
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppSidebar from '@/Components/AppSidebar.vue';
import AppHeader from '@/Components/AppHeader.vue';
import AppAlert from '@/Components/AppAlert.vue';

interface Props {
  title?: string;
  breadcrumbs?: Array<{ label: string; href?: string }>;
}

const props = withDefaults(defineProps<Props>(), {
  title: '',
  breadcrumbs: () => [],
});

const page = usePage();
const flash = computed(() => (page.props as any).flash || {});
</script>

<template>
  <div class="app-layout-root">
    <Head :title="title ? `${title} - PT Kertas Padalarang` : 'PT Kertas Padalarang'" />

    <!-- Sidebar Deep Navy -->
    <AppSidebar />

    <!-- Main Container -->
    <div class="app-main-wrapper">
      <!-- Header -->
      <AppHeader :title="title" :breadcrumbs="breadcrumbs" />

      <!-- Content Area -->
      <main class="app-content-area">
        <div class="app-content-container">
          <!-- Flash Notifications -->
          <div v-if="flash.success" class="flash-banner">
            <AppAlert variant="success" dismissible>
              {{ flash.success }}
            </AppAlert>
          </div>
          <div v-if="flash.error" class="flash-banner">
            <AppAlert variant="error" dismissible>
              {{ flash.error }}
            </AppAlert>
          </div>
          <div v-if="flash.info" class="flash-banner">
            <AppAlert variant="info" dismissible>
              {{ flash.info }}
            </AppAlert>
          </div>

          <!-- Page Content Slot -->
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<style scoped>
.app-layout-root {
  display: flex;
  min-height: 100vh;
  width: 100%;
  background-color: var(--color-mist);
}

.app-main-wrapper {
  flex: 1 1 auto;
  display: flex;
  flex-direction: column;
  min-width: 0;
  overflow-x: hidden;
}

.app-content-area {
  flex: 1 1 auto;
  padding: var(--space-6); /* 32px */
  background-color: var(--color-mist);
}

.app-content-container {
  max-width: 1280px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.flash-banner {
  margin-bottom: var(--space-2);
}
</style>
