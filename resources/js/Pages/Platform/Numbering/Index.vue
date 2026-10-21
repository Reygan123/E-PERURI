<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import AppShell from '@/Layouts/AppShell.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import AppBadge from '@/Components/AppBadge.vue';
import AppModal from '@/Components/AppModal.vue';
import AppInput from '@/Components/AppInput.vue';
import AppSelect from '@/Components/AppSelect.vue';
import AppTable from '@/Components/AppTable.vue';

interface SequenceItem {
  id: string;
  period_key: string;
  last_sequence: number;
}

interface FormatItem {
  id: string;
  code: string;
  module: string;
  template: string;
  reset_period: string;
  prefix: string | null;
  sequences?: SequenceItem[];
}

const props = defineProps<{
  formats: FormatItem[];
}>();

// Modal State
const isModalOpen = ref(false);
const editingFormat = ref<FormatItem | null>(null);

const form = useForm({
  code: '',
  module: 'layanan',
  template: '{PREFIX}/{SEQ}/{BULAN}/{TAHUN}',
  reset_period: 'yearly',
  prefix: 'PTKP',
});

// Live Preview State
const previewResult = ref('');
const isPreviewLoading = ref(false);

const fetchPreview = async () => {
  if (!form.template) return;
  try {
    isPreviewLoading.value = true;
    const res = await axios.post(route('platform.numbering.preview'), {
      template: form.template,
      prefix: form.prefix,
    });
    previewResult.value = res.data.preview;
  } catch (err) {
    previewResult.value = 'Format template tidak valid';
  } finally {
    isPreviewLoading.value = false;
  }
};

watch([() => form.template, () => form.prefix], () => {
  fetchPreview();
});

const openCreateModal = () => {
  editingFormat.value = null;
  form.reset();
  form.clearErrors();
  form.module = 'layanan';
  form.template = '{PREFIX}/{SEQ}/{BULAN}/{TAHUN}';
  form.reset_period = 'yearly';
  form.prefix = 'PTKP';
  isModalOpen.value = true;
  fetchPreview();
};

const openEditModal = (fmt: FormatItem) => {
  editingFormat.value = fmt;
  form.clearErrors();
  form.code = fmt.code;
  form.module = fmt.module;
  form.template = fmt.template;
  form.reset_period = fmt.reset_period;
  form.prefix = fmt.prefix || '';
  isModalOpen.value = true;
  fetchPreview();
};

const closeModal = () => {
  isModalOpen.value = false;
  editingFormat.value = null;
};

const saveFormat = () => {
  if (editingFormat.value) {
    form.put(route('platform.numbering.update', editingFormat.value.id), {
      onSuccess: () => closeModal(),
    });
  } else {
    form.post(route('platform.numbering.store'), {
      onSuccess: () => closeModal(),
    });
  }
};

const resetPeriodOptions = [
  { value: 'yearly', label: 'Tahunan (Reset setiap 1 Januari)' },
  { value: 'monthly', label: 'Bulanan (Reset setiap awal bulan)' },
  { value: 'never', label: 'Tidak Pernah (Sekuensial terus-menerus)' },
];

const moduleOptions = [
  { value: 'platform', label: 'Modul Platform' },
  { value: 'layanan', label: 'Modul Layanan (Persuratan & Tiket)' },
  { value: 'arsip', label: 'Modul Arsip (E-Arsip)' },
  { value: 'sdm', label: 'Modul SDM (SK & Kepegawaian)' },
  { value: 'manufaktur', label: 'Modul Manufaktur (Produksi & QC)' },
];

const columns = [
  { key: 'code', label: 'Kode Format', width: '160px' },
  { key: 'module', label: 'Modul Pemilik', width: '130px' },
  { key: 'template', label: 'Template Pola Penomoran' },
  { key: 'reset_period', label: 'Periode Reset', width: '140px' },
  { key: 'sequences', label: 'Sekuens Aktif', width: '140px' },
  { key: 'actions', label: 'Aksi', align: 'right' as const, width: '90px' },
];
</script>

<template>
  <AppShell
    title="Format Penomoran Dokumen"
    :breadcrumbs="[{ label: 'Platform', href: '/dashboard' }, { label: 'Penomoran Dokumen' }]"
  >
    <AppCard
      title="Konfigurasi Penomoran Otomatis Terintegrasi"
      subtitle="Pengaturan format nomor surat, dokumen dinas, dan arsip menggunakan NumberingService atomik"
    >
      <template #headerActions>
        <AppButton variant="primary" size="md" @click="openCreateModal">
          <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
          </svg>
          Tambah Format Nomor
        </AppButton>
      </template>

      <!-- Table View -->
      <div class="mt-2">
        <AppTable :columns="columns" :items="formats" dark-header>
          <template #row="{ item }">
            <td class="px-4 py-3 align-middle">
              <span class="font-mono text-xs font-bold text-ptkp-navy bg-slate-100 px-2 py-1 rounded-md">
                {{ item.code }}
              </span>
            </td>
            <td class="px-4 py-3 align-middle">
              <AppBadge variant="navy" size="sm">
                {{ item.module }}
              </AppBadge>
            </td>
            <td class="px-4 py-3 align-middle">
              <span class="font-mono text-xs font-bold text-ptkp-body">
                {{ item.template }}
              </span>
            </td>
            <td class="px-4 py-3 align-middle">
              <AppBadge v-if="item.reset_period === 'yearly'" variant="neutral" size="sm">
                Tahunan
              </AppBadge>
              <AppBadge v-else-if="item.reset_period === 'monthly'" variant="neutral" size="sm">
                Bulanan
              </AppBadge>
              <AppBadge v-else variant="neutral" size="sm">
                Tidak Reset
              </AppBadge>
            </td>
            <td class="px-4 py-3 align-middle text-xs">
              <div v-if="item.sequences && item.sequences.length > 0" class="flex flex-col">
                <span class="font-mono font-bold text-ptkp-navy">
                  Terakhir: {{ item.sequences[0].last_sequence }}
                </span>
                <span class="text-[11px] text-slate-400">
                  Periode: {{ item.sequences[0].period_key }}
                </span>
              </div>
              <span v-else class="text-slate-400 italic">Belum digenerate</span>
            </td>
            <td class="px-4 py-3 align-middle text-right">
              <AppButton variant="text" size="sm" @click="openEditModal(item)">
                Edit
              </AppButton>
            </td>
          </template>
        </AppTable>
      </div>
    </AppCard>

    <!-- Create / Edit Modal -->
    <AppModal
      :show="isModalOpen"
      :title="editingFormat ? 'Edit Format Penomoran' : 'Tambah Format Penomoran Baru'"
      max-width="md"
      @close="closeModal"
    >
      <form @submit.prevent="saveFormat" class="flex flex-col gap-3">
        <AppInput
          label="Kode Format (Unik)"
          placeholder="Contoh: surat-keluar, sk-direksi, berita-acara"
          v-model="form.code"
          :error="form.errors.code"
          required
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <AppSelect
            label="Modul Pemilik"
            :options="moduleOptions"
            v-model="form.module"
            :error="form.errors.module"
            required
          />
          <AppSelect
            label="Periode Reset Sekuens"
            :options="resetPeriodOptions"
            v-model="form.reset_period"
            :error="form.errors.reset_period"
            required
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <AppInput
            label="Prefix Dokumen (Opsional)"
            placeholder="Contoh: PTKP, SK, BA"
            v-model="form.prefix"
            :error="form.errors.prefix"
          />
          <AppInput
            label="Template Pola Format"
            placeholder="{PREFIX}/{SEQ}/{BULAN}/{TAHUN}"
            v-model="form.template"
            :error="form.errors.template"
            required
          />
        </div>

        <!-- Live Interactive Preview Box -->
        <div class="mt-2 p-4 bg-slate-50 rounded-xl flex flex-col gap-1.5 shadow-xs">
          <span class="font-heading text-xs font-bold text-ptkp-navy">
            Simulasi Nomor Tergenerate Saat Ini:
          </span>
          <div class="font-mono text-sm font-bold text-ptkp-body">
            {{ isPreviewLoading ? 'Memuat preview...' : previewResult }}
          </div>
          <span class="text-[11px] text-slate-500">
            Placeholder: {PREFIX} = Prefix, {SEQ} = Urutan (4 digit), {BULAN} = Bulan Romawi, {TAHUN} = 4 Digit Tahun
          </span>
        </div>
      </form>

      <template #footer>
        <AppButton variant="secondary" size="md" @click="closeModal">
          Batal
        </AppButton>
        <AppButton variant="primary" size="md" :loading="form.processing" @click="saveFormat">
          {{ editingFormat ? 'Perbarui Format' : 'Simpan Format' }}
        </AppButton>
      </template>
    </AppModal>
  </AppShell>
</template>
