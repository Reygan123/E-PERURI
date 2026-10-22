<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import AppBadge from '@/Components/AppBadge.vue';
import AppTable from '@/Components/AppTable.vue';
import AppInput from '@/Components/AppInput.vue';
import AppSelect from '@/Components/AppSelect.vue';
import AppAlert from '@/Components/AppAlert.vue';
import AppEmptyState from '@/Components/AppEmptyState.vue';

interface LetterItem {
  id: string;
  document_number: string | null;
  subject: string;
  letter_date: string;
  status: string;
  destination_text: string | null;
  is_double_upload_complete: boolean;
  type?: { name: string; code: string };
  created_at: string;
}

const props = defineProps<{
  letters: {
    data: LetterItem[];
    current_page: number;
    last_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  filters: { search?: string; status?: string };
  canCreate: boolean;
  incompleteLetter?: { id: string; subject: string; document_number: string | null; status: string } | null;
}>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

const handleFilter = () => {
  router.get(
    route('arsip.surat-keluar.index'),
    { search: search.value, status: statusFilter.value },
    { preserveState: true, replace: true }
  );
};

const getStatusBadge = (status: string): { variant: 'neutral' | 'warning' | 'error' | 'success'; label: string } => {
  switch (status) {
    case 'draft':
      return { variant: 'neutral', label: 'Draf' };
    case 'in_review':
      return { variant: 'warning', label: 'Dalam Review' };
    case 'revision_requested':
      return { variant: 'error', label: 'Perlu Revisi' };
    case 'approved':
      return { variant: 'success', label: 'Disetujui (Menunggu TTD)' };
    case 'signed':
      return { variant: 'success', label: 'Scan Basah Selesai' };
    case 'published':
      return { variant: 'success', label: 'Diterbitkan' };
    case 'rejected':
      return { variant: 'error', label: 'Ditolak' };
    default:
      return { variant: 'neutral', label: status };
  }
};
</script>

<template>
  <AppShell title="Surat Keluar (A) - E-Arsip PTKP">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="font-heading text-2xl font-bold text-brand-navy-dark tracking-tight">
            Surat Keluar (Kode A)
          </h1>
          <p class="font-body text-sm text-brand-slate-light mt-1">
            Pengelolaan surat keluar resmi PT Kertas Padalarang dengan penomoran otomatis dan validasi scan basah.
          </p>
        </div>

        <div class="flex items-center gap-3">
          <Link v-if="canCreate" :href="route('arsip.surat-keluar.create')">
            <AppButton variant="primary">
              <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Buat Surat Keluar
            </AppButton>
          </Link>
          <AppButton v-else variant="secondary" disabled title="Selesaikan upload scan surat sebelumnya terlebih dahulu">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            Buat Surat (Terkunci)
          </AppButton>
        </div>
      </div>

      <!-- Warning Alert: Double Upload Block -->
      <div v-if="!canCreate && incompleteLetter" class="rounded-xl border border-amber-300 bg-amber-50 p-4">
        <div class="flex items-start gap-3">
          <div class="p-2 bg-amber-200/50 rounded-lg text-amber-800">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="font-heading text-sm font-bold text-amber-900">
              Aturan Operasional Double Upload PTKP Aktif
            </h3>
            <p class="font-body text-xs text-amber-800 mt-1">
              Anda belum dapat membuat Surat Keluar baru karena surat sebelumnya (<strong>{{ incompleteLetter.document_number || 'Draf' }} - {{ incompleteLetter.subject }}</strong>) belum mengunggah hasil scan tanda tangan basah final.
            </p>
            <div class="mt-3">
              <Link :href="route('arsip.surat-keluar.show', incompleteLetter.id)">
                <AppButton variant="secondary" size="sm">
                  Unggah Scan Basah Sekarang &rarr;
                </AppButton>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Filter Controls -->
      <AppCard class="p-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <AppInput
            v-model="search"
            placeholder="Cari perihal, nomor, atau tujuan..."
            @keyup.enter="handleFilter"
          />
          <AppSelect
            v-model="statusFilter"
            :options="[
              { value: '', label: 'Semua Status' },
              { value: 'draft', label: 'Draf' },
              { value: 'in_review', label: 'Dalam Review' },
              { value: 'revision_requested', label: 'Perlu Revisi' },
              { value: 'approved', label: 'Disetujui' },
              { value: 'signed', label: 'Scan Basah Selesai' },
              { value: 'published', label: 'Diterbitkan' },
            ]"
            @change="handleFilter"
          />
          <div class="flex items-center gap-2">
            <AppButton variant="secondary" @click="handleFilter">
              Terapkan Filter
            </AppButton>
            <AppButton
              v-if="search || statusFilter"
              variant="secondary"
              @click="search = ''; statusFilter = ''; handleFilter();"
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
                <th class="py-3.5 px-4 font-semibold">Nomor Dokumen</th>
                <th class="py-3.5 px-4 font-semibold">Perihal</th>
                <th class="py-3.5 px-4 font-semibold">Tujuan</th>
                <th class="py-3.5 px-4 font-semibold">Tanggal</th>
                <th class="py-3.5 px-4 font-semibold">Status</th>
                <th class="py-3.5 px-4 font-semibold">Scan Basah</th>
                <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-brand-slate-border/50">
              <tr
                v-for="letter in letters.data"
                :key="letter.id"
                class="hover:bg-brand-slate-light/5 transition-colors"
              >
                <td class="py-3.5 px-4 font-medium text-brand-navy-dark">
                  {{ letter.document_number || 'Belum Terbit' }}
                </td>
                <td class="py-3.5 px-4 text-brand-slate-dark max-w-xs truncate" :title="letter.subject">
                  {{ letter.subject }}
                </td>
                <td class="py-3.5 px-4 text-brand-slate-light">
                  {{ letter.destination_text || 'Internal' }}
                </td>
                <td class="py-3.5 px-4 text-brand-slate-light whitespace-nowrap">
                  {{ new Date(letter.letter_date).toLocaleDateString('id-ID') }}
                </td>
                <td class="py-3.5 px-4">
                  <AppBadge :variant="getStatusBadge(letter.status).variant as any" size="sm">
                    {{ getStatusBadge(letter.status).label }}
                  </AppBadge>
                </td>
                <td class="py-3.5 px-4">
                  <span v-if="letter.is_double_upload_complete" class="inline-flex items-center text-emerald-600 text-xs font-medium">
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Lengkap
                  </span>
                  <span v-else class="inline-flex items-center text-amber-600 text-xs font-medium">
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Menunggu Scan
                  </span>
                </td>
                <td class="py-3.5 px-4 text-right">
                  <Link :href="route('arsip.surat-keluar.show', letter.id)">
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
            title="Belum Ada Surat Keluar"
            description="Tidak ada dokumen surat keluar yang sesuai dengan pencarian atau filter yang dipilih."
          />
        </div>
      </AppCard>
    </div>
  </AppShell>
</template>
