<script setup lang="ts">
import AppLogo from '@/Components/AppLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-ptkp-mist p-4 font-body">
    <div class="w-full max-w-[440px] flex flex-col items-center gap-4">
      <div class="bg-white px-6 py-3 rounded-sm border border-ptkp-border shadow-sm">
        <Link href="/">
          <AppLogo width="240px" theme="on-light" />
        </Link>
      </div>

      <div class="w-full bg-white border border-ptkp-border rounded-sm p-6 shadow-raised">
        <slot />
      </div>

      <div class="text-center text-xs text-slate-500">
        <p>&copy; 2026 PT Kertas Padalarang (Peruri Security Paper Mill)</p>
      </div>
    </div>
  </div>
</template>
