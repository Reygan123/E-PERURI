<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLogo from './AppLogo.vue';
import AppNavItem from './AppNavItem.vue';
import AppBadge from './AppBadge.vue';

interface Props {
  mobileOpen?: boolean;
}

withDefaults(defineProps<Props>(), {
  mobileOpen: false,
});

const emit = defineEmits<{
  (e: 'close'): void;
}>();

const page = usePage();
const currentUrl = computed(() => page.url);

const isRouteActive = (pattern: string) => {
  return currentUrl.value.startsWith(pattern);
};

const handleNavClick = () => {
  emit('close');
};
</script>

<template>
  <!-- Mobile Backdrop Overlay -->
  <Transition
    enter-active-class="transition-opacity duration-300 ease-out"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition-opacity duration-200 ease-in"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="mobileOpen"
      class="fixed inset-0 bg-ptkp-deep/75 backdrop-blur-sm z-40 lg:hidden"
      aria-hidden="true"
      @click="emit('close')"
    />
  </Transition>

  <!-- Sidebar Component -->
  <aside
    :class="[
      'fixed inset-y-0 left-0 z-50 w-72 bg-ptkp-deep text-white flex flex-col select-none transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:h-screen lg:w-64 lg:min-w-[256px] lg:z-20 shadow-2xl lg:shadow-none',
      mobileOpen ? 'translate-x-0' : '-translate-x-full'
    ]"
  >
    <!-- Brand Header -->
    <div class="p-4 flex items-center justify-between gap-3 bg-ptkp-deep">
      <div class="bg-white rounded-lg px-3.5 py-2 shadow-sm flex items-center justify-center flex-1">
        <AppLogo width="100%" theme="on-light" />
      </div>
      <!-- Mobile Close Button -->
      <button
        type="button"
        class="lg:hidden p-2 text-white/70 hover:text-white hover:bg-white/10 rounded-lg transition-colors cursor-pointer"
        aria-label="Tutup navigasi"
        @click="emit('close')"
      >
        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto p-3 flex flex-col gap-5">
      <!-- Section: Platform Core -->
      <div class="flex flex-col gap-1">
        <span class="font-heading text-[10px] font-bold tracking-wider text-white/50 px-3 pb-1">
          PLATFORM CORE
        </span>

        <AppNavItem href="/dashboard" :active="currentUrl === '/dashboard'" @click="handleNavClick">
          <template #icon>
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
            </svg>
          </template>
          Dashboard
        </AppNavItem>

        <AppNavItem href="/platform/org-units" :active="isRouteActive('/platform/org-units')" @click="handleNavClick">
          <template #icon>
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
            </svg>
          </template>
          Unit Organisasi
        </AppNavItem>

        <AppNavItem href="/platform/positions" :active="isRouteActive('/platform/positions')" @click="handleNavClick">
          <template #icon>
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0" />
            </svg>
          </template>
          Master Jabatan
        </AppNavItem>

        <AppNavItem href="/platform/employees" :active="isRouteActive('/platform/employees')" @click="handleNavClick">
          <template #icon>
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
          </template>
          Data Pegawai
        </AppNavItem>

        <AppNavItem href="/platform/users" :active="isRouteActive('/platform/users')" @click="handleNavClick">
          <template #icon>
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
            </svg>
          </template>
          Pengguna & Peran
        </AppNavItem>

        <AppNavItem href="/platform/numbering" :active="isRouteActive('/platform/numbering')" @click="handleNavClick">
          <template #icon>
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5-3.9 19.5m-2.1-19.5-3.9 19.5" />
            </svg>
          </template>
          Penomoran Dokumen
        </AppNavItem>

        <AppNavItem href="/platform/audit-logs" :active="isRouteActive('/platform/audit-logs')" @click="handleNavClick">
          <template #icon>
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
            </svg>
          </template>
          Audit Log
        </AppNavItem>
      </div>

      <!-- Section: Roadmap Ekosistem -->
      <div class="flex flex-col gap-1 pt-2">
        <span class="font-heading text-[10px] font-bold tracking-wider text-white/50 px-3 pb-1">
          MODUL BISNIS
        </span>

        <div class="flex items-center justify-between px-3 py-2 rounded-md bg-brand-navy-dark text-white font-body text-xs font-semibold border border-white/10">
          <span class="truncate">E-Arsip (Persuratan)</span>
          <AppBadge variant="violet" size="sm">Aktif</AppBadge>
        </div>

        <div class="pl-2 flex flex-col gap-0.5 border-l border-white/10 ml-3 my-1">
          <AppNavItem href="/arsip/surat-masuk" :active="isRouteActive('/arsip/surat-masuk')" @click="handleNavClick">
            <template #icon>
              <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
              </svg>
            </template>
            Surat Masuk
          </AppNavItem>

          <AppNavItem href="/arsip/surat-keluar" :active="isRouteActive('/arsip/surat-keluar')" @click="handleNavClick">
            <template #icon>
              <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
              </svg>
            </template>
            Surat Keluar
          </AppNavItem>

          <AppNavItem href="/arsip/naskah-dinas" :active="isRouteActive('/arsip/naskah-dinas')" @click="handleNavClick">
            <template #icon>
              <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
              </svg>
            </template>
            Naskah Dinas
          </AppNavItem>

          <AppNavItem href="/arsip/khusus" :active="isRouteActive('/arsip/khusus')" @click="handleNavClick">
            <template #icon>
              <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
              </svg>
            </template>
            Formulir Khusus
          </AppNavItem>

          <AppNavItem href="/arsip/persetujuan" :active="isRouteActive('/arsip/persetujuan')" @click="handleNavClick">
            <template #icon>
              <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
              </svg>
            </template>
            Persetujuan
          </AppNavItem>
        </div>

        <div class="flex items-center justify-between px-3 py-2 rounded-md text-white/50 font-body text-xs">
          <span class="truncate">Layanan Umum (Fasilitas)</span>
          <AppBadge variant="neutral" size="sm">Roadmap</AppBadge>
        </div>

        <div class="flex items-center justify-between px-3 py-2 rounded-md text-white/50 font-body text-xs">
          <span class="truncate">Pembelajaran (LMS)</span>
          <AppBadge variant="neutral" size="sm">Roadmap</AppBadge>
        </div>

        <div class="flex items-center justify-between px-3 py-2 rounded-md text-white/50 font-body text-xs">
          <span class="truncate">SDM (HRIS)</span>
          <AppBadge variant="neutral" size="sm">Roadmap</AppBadge>
        </div>

        <div class="flex items-center justify-between px-3 py-2 rounded-md text-white/50 font-body text-xs">
          <span class="truncate">Manufaktur (Kertas)</span>
          <AppBadge variant="neutral" size="sm">Roadmap</AppBadge>
        </div>
      </div>
    </nav>

    <!-- Sidebar Footer -->
    <div class="p-4 font-body bg-black/20">
      <div class="text-xs font-semibold text-white/90">PT Kertas Padalarang</div>
      <div class="text-[11px] text-white/50">Ekosistem Internal v2.0</div>
    </div>
  </aside>
</template>
