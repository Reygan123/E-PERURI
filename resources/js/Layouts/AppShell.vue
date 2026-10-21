<script setup lang="ts">
import { ref, computed } from 'vue';
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

const isMobileMenuOpen = ref(false);
</script>

<template>
  <div class="flex min-h-screen w-full bg-ptkp-mist">
    <Head :title="title ? `${title} - PT Kertas Padalarang` : 'PT Kertas Padalarang'" />

    <!-- Sidebar Deep Navy with responsive drawer on mobile -->
    <AppSidebar :mobile-open="isMobileMenuOpen" @close="isMobileMenuOpen = false" />

    <!-- Main Container -->
    <div class="flex-1 flex flex-col min-w-0 overflow-x-hidden">
      <!-- Header with mobile menu toggle -->
      <AppHeader
        :title="title"
        :breadcrumbs="breadcrumbs"
        @toggle-menu="isMobileMenuOpen = !isMobileMenuOpen"
      />

      <!-- Content Area -->
      <main class="flex-1 p-4 sm:p-6 lg:p-8 bg-ptkp-mist">
        <div class="max-w-[1280px] mx-auto flex flex-col gap-5 sm:gap-6">
          <!-- Flash Notifications -->
          <AppAlert v-if="flash.success" variant="success" dismissible>
            {{ flash.success }}
          </AppAlert>
          <AppAlert v-if="flash.error" variant="error" dismissible>
            {{ flash.error }}
          </AppAlert>
          <AppAlert v-if="flash.info" variant="info" dismissible>
            {{ flash.info }}
          </AppAlert>

          <!-- Page Content Slot -->
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>
