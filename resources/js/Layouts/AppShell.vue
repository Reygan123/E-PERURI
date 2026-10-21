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
  <div class="flex min-h-screen w-full bg-ptkp-mist">
    <Head :title="title ? `${title} - PT Kertas Padalarang` : 'PT Kertas Padalarang'" />

    <!-- Sidebar Deep Navy -->
    <AppSidebar />

    <!-- Main Container -->
    <div class="flex-1 flex flex-col min-w-0 overflow-x-hidden">
      <!-- Header -->
      <AppHeader :title="title" :breadcrumbs="breadcrumbs" />

      <!-- Content Area -->
      <main class="flex-1 p-8 bg-ptkp-mist">
        <div class="max-w-[1280px] mx-auto flex flex-col gap-6">
          <!-- Flash Notifications -->
          <div v-if="flash.success" class="mb-2">
            <AppAlert variant="success" dismissible>
              {{ flash.success }}
            </AppAlert>
          </div>
          <div v-if="flash.error" class="mb-2">
            <AppAlert variant="error" dismissible>
              {{ flash.error }}
            </AppAlert>
          </div>
          <div v-if="flash.info" class="mb-2">
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
