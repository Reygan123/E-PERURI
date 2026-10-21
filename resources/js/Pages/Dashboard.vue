<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import AppBadge from '@/Components/AppBadge.vue';

interface Stats {
  orgUnitsCount?: number;
  positionsCount?: number;
  employeesCount?: number;
  usersCount?: number;
}

const props = withDefaults(defineProps<{ stats?: Stats }>(), {
  stats: () => ({
    orgUnitsCount: 37,
    positionsCount: 12,
    employeesCount: 5,
    usersCount: 4,
  }),
});

const page = usePage();
const user = computed(() => page.props.auth?.user as Record<string, any> | undefined);
</script>

<template>
  <AppShell title="Dashboard Utama" :breadcrumbs="[{ label: 'Beranda' }, { label: 'Dashboard' }]">
    <!-- Welcome Card -->
    <AppCard>
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex flex-col gap-2">
          <div class="flex items-center gap-2">
            <AppBadge variant="navy" size="md">PT KERTAS PADALARANG</AppBadge>
            <AppBadge variant="neutral" size="md">Ekosistem Internal v2.0</AppBadge>
          </div>
          <h2 class="m-0 font-heading text-xl sm:text-2xl font-bold text-ptkp-navy">
            Selamat Datang, {{ user?.name || user?.username || 'Pegawai PTKP' }}
          </h2>
          <p class="m-0 font-body text-xs sm:text-sm text-slate-600 max-w-3xl leading-relaxed">
            Portal terintegrasi ekosistem internal PT Kertas Padalarang. Seluruh master data organisasi, otorisasi peran, penomoran dokumen, dan log audit dikelola secara terpusat pada Modul Platform.
          </p>
        </div>
        <div class="hidden md:flex items-center justify-center p-3 bg-white rounded-xl shadow-xs shrink-0">
          <img src="/assets/logo.png" alt="PT Kertas Padalarang" class="h-12 w-auto object-contain" />
        </div>
      </div>
    </AppCard>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
      <AppCard interactive>
        <div class="flex items-center justify-between">
          <div class="flex flex-col">
            <span class="font-body text-xs font-medium text-slate-500">Unit Organisasi</span>
            <span class="font-heading text-3xl font-bold text-ptkp-navy leading-tight">{{ stats.orgUnitsCount }}</span>
            <span class="font-body text-[11px] text-slate-400 mt-0.5">Hierarki 4 Level SK 009/2026</span>
          </div>
          <div class="w-12 h-12 rounded-xl bg-ptkp-mist text-ptkp-navy flex items-center justify-center shadow-xs">
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
            </svg>
          </div>
        </div>
      </AppCard>

      <AppCard interactive>
        <div class="flex items-center justify-between">
          <div class="flex flex-col">
            <span class="font-body text-xs font-medium text-slate-500">Data Pegawai</span>
            <span class="font-heading text-3xl font-bold text-ptkp-navy leading-tight">{{ stats.employeesCount }}</span>
            <span class="font-body text-[11px] text-slate-400 mt-0.5">Terenkripsi & Tercatat Mutasi</span>
          </div>
          <div class="w-12 h-12 rounded-xl bg-ptkp-mist text-ptkp-navy flex items-center justify-center shadow-xs">
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
          </div>
        </div>
      </AppCard>

      <AppCard interactive>
        <div class="flex items-center justify-between">
          <div class="flex flex-col">
            <span class="font-body text-xs font-medium text-slate-500">Akun Pengguna</span>
            <span class="font-heading text-3xl font-bold text-ptkp-navy leading-tight">{{ stats.usersCount }}</span>
            <span class="font-body text-[11px] text-slate-400 mt-0.5">Role & Scope Otorisasi</span>
          </div>
          <div class="w-12 h-12 rounded-xl bg-ptkp-mist text-ptkp-navy flex items-center justify-center shadow-xs">
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
            </svg>
          </div>
        </div>
      </AppCard>

      <AppCard interactive>
        <div class="flex items-center justify-between">
          <div class="flex flex-col">
            <span class="font-body text-xs font-medium text-slate-500">Master Jabatan</span>
            <span class="font-heading text-3xl font-bold text-ptkp-navy leading-tight">{{ stats.positionsCount }}</span>
            <span class="font-body text-[11px] text-slate-400 mt-0.5">Struktural & Fungsional</span>
          </div>
          <div class="w-12 h-12 rounded-xl bg-ptkp-mist text-ptkp-navy flex items-center justify-center shadow-xs">
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0" />
            </svg>
          </div>
        </div>
      </AppCard>
    </div>

    <!-- Quick Navigation / Module Status -->
    <AppCard title="Pusat Manajemen Master Data Platform" subtitle="Akses cepat pengelolaan master data organisasi dan konfigurasi inti">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5 mt-2">
        <AppButton href="/platform/org-units" variant="secondary" size="md">
          Kelola Unit Organisasi
        </AppButton>
        <AppButton href="/platform/positions" variant="secondary" size="md">
          Kelola Master Jabatan
        </AppButton>
        <AppButton href="/platform/employees" variant="secondary" size="md">
          Kelola Data Pegawai
        </AppButton>
        <AppButton href="/platform/users" variant="secondary" size="md">
          Kelola Pengguna & Peran
        </AppButton>
        <AppButton href="/platform/numbering" variant="secondary" size="md">
          Format Penomoran
        </AppButton>
        <AppButton href="/platform/audit-logs" variant="secondary" size="md">
          Buku Audit Log
        </AppButton>
      </div>
    </AppCard>
  </AppShell>
</template>
