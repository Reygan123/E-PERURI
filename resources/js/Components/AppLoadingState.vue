<script setup lang="ts">
interface Props {
  rows?: number;
  card?: boolean;
}

withDefaults(defineProps<Props>(), {
  rows: 3,
  card: true,
});
</script>

<template>
  <div :class="['flex flex-col gap-4', card ? 'bg-white border border-ptkp-border rounded-sm p-6 shadow-flat' : '']">
    <div class="flex flex-col gap-2">
      <div class="h-5 w-1/3 bg-slate-200 rounded-sm animate-pulse"></div>
      <div class="h-3.5 w-3/5 bg-slate-100 rounded-sm animate-pulse"></div>
    </div>
    <div class="flex flex-col gap-3">
      <div v-for="n in rows" :key="n" class="w-full">
        <div class="h-10 w-full bg-slate-100 rounded-sm animate-pulse"></div>
      </div>
    </div>
  </div>
</template>
