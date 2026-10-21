<script setup lang="ts" generic="T extends Record<string, any>">
interface Column {
  key: string;
  label: string;
  align?: 'left' | 'center' | 'right';
  width?: string;
}

interface Props {
  columns?: Column[];
  items?: T[];
  darkHeader?: boolean;
  emptyText?: string;
}

withDefaults(defineProps<Props>(), {
  columns: () => [],
  items: () => [],
  darkHeader: true,
  emptyText: 'Belum ada data yang tersedia.',
});
</script>

<template>
  <div class="w-full overflow-hidden flex flex-col rounded-lg">
    <div class="overflow-x-auto w-full scrollbar-thin">
      <table class="w-full border-collapse text-left font-body text-sm min-w-[600px]">
        <thead>
          <tr :class="darkHeader ? 'bg-ptkp-navy text-white font-heading font-bold' : 'bg-slate-50/80 text-ptkp-navy font-heading font-bold border-b border-slate-100'">
            <slot name="header">
              <th
                v-for="col in columns"
                :key="col.key"
                class="px-4 py-3 whitespace-nowrap text-xs uppercase tracking-wider font-semibold"
                :style="{ textAlign: col.align || 'left', width: col.width }"
              >
                {{ col.label }}
              </th>
            </slot>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <template v-if="items && items.length > 0">
            <tr
              v-for="(item, index) in items"
              :key="(item as any).id || index"
              class="hover:bg-ptkp-navy/[0.025] transition-colors"
            >
              <slot name="row" :item="item" :index="index">
                <td
                  v-for="col in columns"
                  :key="col.key"
                  class="px-4 py-3.5 text-ptkp-body align-middle"
                  :style="{ textAlign: col.align || 'left' }"
                >
                  {{ item[col.key] }}
                </td>
              </slot>
            </tr>
          </template>
          <tr v-else>
            <td :colspan="columns.length || 10" class="text-center py-12 px-4 text-slate-500">
              <slot name="empty">
                <div class="flex flex-col items-center justify-center gap-2">
                  <svg class="w-8 h-8 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                  </svg>
                  <span>{{ emptyText }}</span>
                </div>
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Table Footer / Pagination Slot -->
    <div
      v-if="$slots.footer"
      class="px-4 py-3 border-t border-slate-100 bg-[#fafbfe] flex items-center justify-between"
    >
      <slot name="footer" />
    </div>
  </div>
</template>
