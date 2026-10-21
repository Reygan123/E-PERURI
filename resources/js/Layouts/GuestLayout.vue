<script setup lang="ts">
import AppLogo from '@/Components/AppLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-ptkp-mist p-4 font-body">
    <div class="w-full max-w-[440px] flex flex-col items-center gap-5">
      <div class="bg-white px-7 py-4 rounded-xl shadow-card flex items-center justify-center">
        <Link href="/">
          <AppLogo width="220px" theme="on-light" />
        </Link>
      </div>

      <div class="w-full bg-white rounded-xl p-7 sm:p-8 shadow-raised">
        <slot />
      </div>

      <div class="text-center text-xs text-slate-500">
        <p>&copy; 2026 PT Kertas Padalarang (Peruri Security Paper Mill)</p>
      </div>
    </div>
  </div>
</template>
