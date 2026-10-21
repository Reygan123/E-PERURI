<script setup lang="ts">
interface Column {
  key: string;
  label: string;
  align?: 'left' | 'center' | 'right';
  width?: string;
}

interface Props {
  columns?: Column[];
  items?: Record<string, any>[];
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
  <div class="app-table-container">
    <div class="table-scroll">
      <table :class="['app-table', { 'dark-header': darkHeader }]">
        <thead>
          <tr>
            <slot name="header">
              <th
                v-for="col in columns"
                :key="col.key"
                :style="{ textAlign: col.align || 'left', width: col.width }"
              >
                {{ col.label }}
              </th>
            </slot>
          </tr>
        </thead>
        <tbody>
          <template v-if="items && items.length > 0">
            <tr v-for="(item, index) in items" :key="item.id || index">
              <slot name="row" :item="item" :index="index">
                <td
                  v-for="col in columns"
                  :key="col.key"
                  :style="{ textAlign: col.align || 'left' }"
                >
                  {{ item[col.key] }}
                </td>
              </slot>
            </tr>
          </template>
          <tr v-else>
            <td :colspan="columns.length || 10" class="empty-cell">
              <slot name="empty">
                <div class="empty-state-inner">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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
    <div v-if="$slots.footer" class="table-footer">
      <slot name="footer" />
    </div>
  </div>
</template>

<style scoped>
.app-table-container {
  background-color: var(--color-white);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.table-scroll {
  overflow-x: auto;
  width: 100%;
}

.app-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-family: var(--font-body);
  font-size: var(--text-sm);
}

.app-table th {
  padding: var(--space-3) var(--space-4);
  font-weight: 700;
  white-space: nowrap;
}

/* Dark Header (Peruri Navy) */
.dark-header thead th {
  background-color: var(--color-navy);
  color: var(--color-white);
  font-family: var(--font-heading);
  letter-spacing: 0.02em;
}

/* Light Header */
.app-table:not(.dark-header) thead th {
  background-color: #f8fafc;
  color: var(--color-navy);
  font-family: var(--font-heading);
  border-bottom: 2px solid var(--color-border);
}

.app-table td {
  padding: var(--space-3) var(--space-4);
  color: var(--color-text-body);
  border-bottom: 1px solid var(--color-border);
  vertical-align: middle;
}

.app-table tbody tr:last-child td {
  border-bottom: none;
}

.app-table tbody tr:hover {
  background-color: rgba(32, 40, 142, 0.03);
}

.empty-cell {
  text-align: center;
  padding: var(--space-7) var(--space-4) !important;
  color: #64748b;
}

.empty-state-inner {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: var(--space-2);
}
.empty-state-inner svg {
  width: 32px;
  height: 32px;
  color: #94a3b8;
}

.table-footer {
  padding: var(--space-3) var(--space-4);
  border-top: 1px solid var(--color-border);
  background-color: #fafbfe;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
</style>
