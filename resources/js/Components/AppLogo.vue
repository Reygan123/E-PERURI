<script setup lang="ts">
interface Props {
  width?: string;
  theme?: 'on-light' | 'boxed';
}

withDefaults(defineProps<Props>(), {
  width: '180px',
  theme: 'boxed',
});
</script>

<template>
  <div
    :class="['app-logo-lockup', { 'is-boxed': theme === 'boxed' }]"
    :style="{ width, minWidth: '120px' }"
    role="img"
    aria-label="Logo Resmi PERURI Security Paper Mill"
  >
    <!-- SVG Brand Lockup -->
    <svg
      viewBox="0 0 240 64"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      class="brand-svg"
    >
      <!-- Kata PERURI -->
      <text
        x="6"
        y="36"
        font-family="Lexend, Inter, sans-serif"
        font-size="30"
        font-weight="900"
        letter-spacing="1.5"
        fill="#20288E"
      >PERURI</text>

      <!-- Simbol Pancaran Cahaya (Spark / Starburst) -->
      <!-- Pusat sinar di (154, 25) -->
      <g transform="translate(156, 24)">
        <!-- Sinar Vertikal & Horisontal -->
        <path d="M0 -15 L3 -4 L14 0 L3 4 L0 15 L-3 4 L-14 0 L-3 -4 Z" fill="#D861FF" />
        <!-- Sinar Diagonal Lembut -->
        <path d="M-8 -8 L-1 -2 L0 0 L-2 -1 Z" fill="#20288E" opacity="0.8" />
        <path d="M8 -8 L1 -2 L0 0 L2 -1 Z" fill="#20288E" opacity="0.8" />
        <path d="M8 8 L1 2 L0 0 L2 1 Z" fill="#20288E" opacity="0.8" />
        <path d="M-8 8 L-1 2 L0 0 L-2 1 Z" fill="#20288E" opacity="0.8" />
      </g>

      <!-- Keterangan Subtitle SECURITY PAPER MILL -->
      <text
        x="8"
        y="53"
        font-family="Inter, sans-serif"
        font-size="8.5"
        font-weight="700"
        letter-spacing="3"
        fill="#20288E"
      >SECURITY PAPER MILL</text>

      <!-- Garis Aksen Halus Mist/Navy -->
      <line x1="8" y1="41" x2="230" y2="41" stroke="#20288E" stroke-opacity="0.15" stroke-width="1" />
    </svg>
  </div>
</template>

<style scoped>
.app-logo-lockup {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

/* Sesuai aturan: Logo hanya dipakai pada latar Putih atau Mist */
.is-boxed {
  background-color: var(--color-white);
  padding: 6px 14px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--color-border);
}

.brand-svg {
  width: 100%;
  height: auto;
  display: block;
}
</style>
