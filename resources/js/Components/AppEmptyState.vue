<script setup lang="ts">
interface Props {
  title?: string;
  description?: string;
}

withDefaults(defineProps<Props>(), {
  title: 'Belum Ada Data',
  description: 'Data belum ditambahkan atau filter pencarian tidak menemukan hasil.',
});
</script>

<template>
  <div class="flex flex-col items-center justify-center text-center py-12 px-6 bg-white border border-ptkp-border rounded-sm shadow-flat">
    <!-- Ilustrasi Geometri Atap Limas Warisan Pabrik PTKP -->
    <div class="w-28 h-20 mb-3" aria-hidden="true">
      <svg viewBox="0 0 120 90" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
        <!-- Bayangan dasar -->
        <ellipse cx="60" cy="80" rx="45" ry="6" fill="#20288E" fill-opacity="0.08" />
        <!-- Dinding dasar -->
        <rect x="35" y="45" width="50" height="30" rx="2" fill="#FFFFFF" stroke="#20288E" stroke-width="1.5" stroke-opacity="0.2" />
        <!-- Pintu / gerbang pabrik -->
        <path d="M52 75V58C52 56.8954 52.8954 56 54 56H66C67.1046 56 68 56.8954 68 58V75" fill="#EEF0FB" stroke="#20288E" stroke-width="1.5" stroke-opacity="0.3" />
        <!-- Atap Limas Tingkat 1 -->
        <path d="M25 45L40 28H80L95 45H25Z" fill="#EEF0FB" stroke="#20288E" stroke-width="1.5" />
        <!-- Atap Limas Tingkat 2 (Puncak) -->
        <path d="M42 28L55 15H65L78 28H42Z" fill="#20288E" fill-opacity="0.9" />
        <!-- Cerobong berpita subtle -->
        <rect x="74" y="8" width="6" height="15" fill="#FFFFFF" stroke="#20288E" stroke-width="1.2" />
        <line x1="74" y1="12" x2="80" y2="12" stroke="#D861FF" stroke-width="1.5" />
      </svg>
    </div>

    <h3 class="m-0 font-heading text-base font-bold text-ptkp-navy">
      {{ title }}
    </h3>
    <p class="mt-1 mb-0 font-body text-sm text-slate-500 max-w-md">
      {{ description }}
    </p>

    <div v-if="$slots.action" class="mt-4">
      <slot name="action" />
    </div>
  </div>
</template>
