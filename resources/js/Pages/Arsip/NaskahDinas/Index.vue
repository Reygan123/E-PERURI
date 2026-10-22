<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import AppBadge from '@/Components/AppBadge.vue';
import AppInput from '@/Components/AppInput.vue';
import AppSelect from '@/Components/AppSelect.vue';
import AppEmptyState from '@/Components/AppEmptyState.vue';

interface LetterItem {
  id: string;
  document_number: string | null;
  subject: string;
  letter_date: string;
  status: string;
  type: { name: string; code: string };
  created_at: string;
}

const props = defineProps<{
  letters: {
    data: LetterItem[];
    current_page: number;
    last_page: number;
    total: number;
  };
  filters: { search?: string; type_code?: string };
  typeOptions: Array<{ id: string; name: string; code: string }>;
}>();

const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type_code || '');

const handleFilter = () => {
  router.get(
    route('arsip.naskah-dinas.index'),
    { search: search.value, type_code: typeFilter.value },
    { preserveState: true, replace: true }
  );
};

const getStatusBadge = (status: string) => {
  switch (status) {
    case 'draft':
      return { variant: 'neutral', label: 'Draf' };
    case 'in_review':
      return { variant: 'amber', label: 'Dalam Review' };
    case 'revision_requested':
      return { variant: 'orange', label: 'Perlu Revisi' };
    case 'approved':
      return { variant: 'blue', label: 'Disetujui' };
    case 'published':
      return { variant: 'emerald', label: 'Diterbitkan (Sah)' };
    case 'rejected':
      return { variant: 'rose', label: 'Ditolak' };
    default:
      return { variant: 'neutral', label: status };
  }
};
</script>

<template>
  <AppShell title="Naskah Dinas - E-Arsip PTKP">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="font-heading text-2xl font-bold text-brand-navy-dark tracking-tight">
            Naskah Dinas Internal
          </h1>
          <p class="font-body text-sm text-brand-slate-light mt-1">
            Pengelolaan Nota Dinas (ND), Surat Memo Direksi (E), Surat Tugas (ST), Pengumuman (PENG), dan SK.
          </p>
        </div>

        <Link :href="route('arsip.naskah-dinas.create')">
          <AppButton variant="primary">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Buat Naskah Dinas
          </AppButton>
        </Link>
      </div>

      <!-- Filter Controls -->
      <AppCard class="p-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <AppInput
            v-model="search"
            placeholder="Cari perihal atau nomor naskah dinas..."
            @keyup.enter="handleFilter"
          />
          <AppSelect
            v-model="typeFilter"
            :options="[
              { value: '', label: 'Semua Jenis Naskah' },
              ...typeOptions.map(t => ({ value: t.code, label: `${t.code} - ${t.name}` }))
            ]"
            @change="handleFilter"
          />
          <div class="flex items-center gap-2">
            <AppButton variant="secondary" @click="handleFilter">
              Terapkan Filter
            </AppButton>
            <AppButton
              v-if="search || typeFilter"
              variant="neutral"
              @click="search = ''; typeFilter = ''; handleFilter();"
            >
              Reset
            </AppButton>
          </div>
        </div>
      </AppCard>

      <!-- Table Card -->
      <AppCard class="overflow-hidden">
        <div v-if="letters.data.length > 0" class="overflow-x-auto">
          <table class="w-full text-left font-body text-sm border-collapse">
            <thead>
              <tr class="border-b border-brand-slate-border bg-brand-slate-light/5 text-brand-slate-dark text-xs uppercase tracking-wider">
                <th class="py-3.5 px-4 font-semibold">Jenis</th>
                <th class="py-3.5 px-4 font-semibold">Nomor Naskah</th>
                <th class="py-3.5 px-4 font-semibold">Perihal</th>
                <th class="py-3.5 px-4 font-semibold">Tanggal</th>
                <th class="py-3.5 px-4 font-semibold">Status</th>
                <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-brand-slate-border/50">
              <tr
                v-for="letter in letters.data"
                :key="letter.id"
                class="hover:bg-brand-slate-light/5 transition-colors"
              >
                <td class="py-3.5 px-4">
                  <AppBadge variant="blue" size="sm">
                    {{ letter.type.code }}
                  </AppBadge>
                </td>
                <td class="py-3.5 px-4 font-semibold text-brand-navy-dark">
                  {{ letter.document_number || 'Draf' }}
                </td>
                <td class="py-3.5 px-4 text-brand-slate-dark max-w-sm truncate" :title="letter.subject">
                  {{ letter.subject }}
                </td>
                <td class="py-3.5 px-4 text-brand-slate-light whitespace-nowrap">
                  {{ new Date(letter.letter_date).toLocaleDateString('id-ID') }}
                </td>
                <td class="py-3.5 px-4">
                  <AppBadge :variant="getStatusBadge(letter.status).variant as any" size="sm">
                    {{ getStatusBadge(letter.status).label }}
                  </AppBadge>
                </td>
                <td class="py-3.5 px-4 text-right">
                  <Link :href="route('arsip.naskah-dinas.show', letter.id)">
                    <AppButton variant="secondary" size="sm">
                      Detail
                    </AppButton>
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="p-8">
          <AppEmptyState
            title="Belum Ada Naskah Dinas"
            description="Tidak ada dokumen naskah dinas internal yang tercatat pada sistem."
          />
        </div>
      </AppCard>
    </div>
  </AppShell>
</template>
