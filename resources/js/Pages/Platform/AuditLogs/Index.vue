<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import AppBadge from '@/Components/AppBadge.vue';
import AppModal from '@/Components/AppModal.vue';
import AppInput from '@/Components/AppInput.vue';
import AppSelect from '@/Components/AppSelect.vue';
import AppTable from '@/Components/AppTable.vue';
import AppPagination from '@/Components/AppPagination.vue';

interface AuditLogItem {
  id: string;
  user_id: string | null;
  employee_id: string | null;
  action: string;
  module: string;
  entity_type: string;
  entity_id: string | null;
  before: Record<string, any> | null;
  after: Record<string, any> | null;
  ip_address: string | null;
  user_agent: string | null;
  created_at: string;
}

interface PaginatedAuditLogs {
  data: AuditLogItem[];
  links: any[];
  from?: number;
  to?: number;
  total?: number;
}

const props = defineProps<{
  auditLogs: PaginatedAuditLogs;
  modules: string[];
  actions: string[];
  filters: {
    search?: string;
    module?: string;
    action?: string;
    date_from?: string;
    date_to?: string;
  };
}>();

const search = ref(props.filters.search || '');
const moduleFilter = ref(props.filters.module || '');
const actionFilter = ref(props.filters.action || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

const handleFilter = () => {
  router.get(
    route('platform.audit-logs.index'),
    {
      search: search.value,
      module: moduleFilter.value,
      action: actionFilter.value,
      date_from: dateFrom.value,
      date_to: dateTo.value,
    },
    { preserveState: true, replace: true }
  );
};

// Detail Diff Modal
const isDetailModalOpen = ref(false);
const activeLog = ref<AuditLogItem | null>(null);

const viewDetails = (log: AuditLogItem) => {
  activeLog.value = log;
  isDetailModalOpen.value = true;
};

const closeDetailModal = () => {
  isDetailModalOpen.value = false;
  activeLog.value = null;
};

const columns = [
  { key: 'timestamp', label: 'Waktu Kejadian', width: '170px' },
  { key: 'module', label: 'Modul', width: '110px' },
  { key: 'action', label: 'Aksi', width: '120px' },
  { key: 'entity', label: 'Entitas Target' },
  { key: 'client', label: 'IP & Client', width: '160px' },
  { key: 'actions', label: 'Detail', align: 'right' as const, width: '90px' },
];
</script>

<template>
  <AppShell
    title="Buku Audit Log"
    :breadcrumbs="[{ label: 'Platform', href: '/dashboard' }, { label: 'Buku Audit Log' }]"
  >
    <AppCard
      title="Buku Catatan Audit Log Terpusat"
      subtitle="Catatan jejak audit append-only yang tidak dapat diubah maupun dihapus (immutable)"
    >
      <!-- Filter Bar -->
      <div class="flex flex-col gap-3 mb-5">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <AppInput
            placeholder="Cari entitas atau user agent..."
            v-model="search"
            @keyup.enter="handleFilter"
          />
          <AppSelect
            :options="modules"
            placeholder="-- Semua Modul --"
            v-model="moduleFilter"
            @change="handleFilter"
          />
          <AppSelect
            :options="actions"
            placeholder="-- Semua Aksi --"
            v-model="actionFilter"
            @change="handleFilter"
          />
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3">
            <div class="flex items-center gap-2">
              <span class="text-xs text-slate-500 font-medium shrink-0">Dari:</span>
              <AppInput type="date" v-model="dateFrom" />
            </div>
            <div class="flex items-center gap-2">
              <span class="text-xs text-slate-500 font-medium shrink-0">Sampai:</span>
              <AppInput type="date" v-model="dateTo" />
            </div>
          </div>
          <AppButton variant="secondary" size="md" class="shrink-0" @click="handleFilter">
            Terapkan Filter
          </AppButton>
        </div>
      </div>

      <!-- Table View -->
      <div class="mt-2">
        <AppTable :columns="columns" :items="auditLogs.data" dark-header>
          <template #row="{ item }">
            <td class="px-4 py-3 align-middle font-mono text-xs text-ptkp-body">
              {{ new Date(item.created_at).toLocaleString('id-ID') }}
            </td>
            <td class="px-4 py-3 align-middle">
              <AppBadge variant="navy" size="sm">{{ item.module }}</AppBadge>
            </td>
            <td class="px-4 py-3 align-middle font-bold text-xs uppercase tracking-wide">
              <span
                :class="[
                  item.action.includes('delete') || item.action.includes('revoke')
                    ? 'text-ptkp-error'
                    : item.action.includes('create') || item.action.includes('assign')
                    ? 'text-ptkp-success'
                    : 'text-ptkp-navy'
                ]"
              >
                {{ item.action }}
              </span>
            </td>
            <td class="px-4 py-3 align-middle text-xs">
              <div class="flex flex-col">
                <span class="font-bold text-ptkp-body">{{ item.entity_type }}</span>
                <span v-if="item.entity_id" class="font-mono text-[11px] text-slate-400">
                  ID: {{ item.entity_id }}
                </span>
              </div>
            </td>
            <td class="px-4 py-3 align-middle font-mono text-xs text-slate-500">
              {{ item.ip_address || '127.0.0.1' }}
            </td>
            <td class="px-4 py-3 align-middle text-right">
              <AppButton variant="text" size="sm" @click="viewDetails(item)">
                Lihat Diff
              </AppButton>
            </td>
          </template>

          <template #footer>
            <AppPagination
              :links="auditLogs.links"
              :from="auditLogs.from"
              :to="auditLogs.to"
              :total="auditLogs.total"
            />
          </template>
        </AppTable>
      </div>
    </AppCard>

    <!-- Detail JSON Diff Modal -->
    <AppModal
      :show="isDetailModalOpen"
      :title="`Detail Audit Log: ${activeLog?.action} (${activeLog?.entity_type})`"
      max-width="xl"
      @close="closeDetailModal"
    >
      <div v-if="activeLog" class="flex flex-col gap-4">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 p-4 bg-slate-50 rounded-xl text-xs">
          <div>
            <span class="text-slate-500 block">ID Log:</span>
            <span class="font-mono font-bold">{{ activeLog.id }}</span>
          </div>
          <div>
            <span class="text-slate-500 block">Waktu:</span>
            <span class="font-mono">{{ new Date(activeLog.created_at).toLocaleString('id-ID') }}</span>
          </div>
          <div>
            <span class="text-slate-500 block">IP Address:</span>
            <span class="font-mono">{{ activeLog.ip_address || '-' }}</span>
          </div>
          <div>
            <span class="text-slate-500 block">Pelaku (User ID):</span>
            <span class="font-mono">{{ activeLog.user_id || 'Sistem' }}</span>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Before State -->
          <div class="flex flex-col gap-1.5">
            <span class="font-heading text-xs font-bold text-slate-600">
              Data Sebelum (Before State):
            </span>
            <pre class="m-0 p-3.5 bg-slate-900 text-slate-100 font-mono text-xs rounded-xl overflow-x-auto max-h-72 shadow-inner">{{ activeLog.before ? JSON.stringify(activeLog.before, null, 2) : 'null (Data Baru)' }}</pre>
          </div>

          <!-- After State -->
          <div class="flex flex-col gap-1.5">
            <span class="font-heading text-xs font-bold text-ptkp-navy">
              Data Sesudah (After State):
            </span>
            <pre class="m-0 p-3.5 bg-slate-900 text-slate-100 font-mono text-xs rounded-xl overflow-x-auto max-h-72 shadow-inner">{{ activeLog.after ? JSON.stringify(activeLog.after, null, 2) : 'null (Dihapus)' }}</pre>
          </div>
        </div>

        <div v-if="activeLog.user_agent" class="text-xs text-slate-500">
          <span class="font-semibold">User Agent:</span> {{ activeLog.user_agent }}
        </div>
      </div>

      <template #footer>
        <AppButton variant="secondary" size="md" @click="closeDetailModal">
          Tutup
        </AppButton>
      </template>
    </AppModal>
  </AppShell>
</template>
