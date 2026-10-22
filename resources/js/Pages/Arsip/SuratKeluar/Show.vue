<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import AppBadge from '@/Components/AppBadge.vue';
import InputError from '@/Components/InputError.vue';

interface LetterData {
  id: string;
  document_number: string | null;
  subject: string;
  letter_date: string;
  status: string;
  destination_type: string;
  destination_text: string | null;
  confidentiality: string;
  urgency: string;
  is_double_upload_complete: boolean;
  draft_file_id: string | null;
  signed_file_id: string | null;
  created_at: string;
  type: { name: string; code: string; signature_type: string };
  approvals: Array<{
    id: string;
    approver_role_title: string;
    step_order: number;
    status: string;
    notes: string | null;
    signature_hash: string | null;
    decided_at: string | null;
  }>;
  attachments: Array<{
    id: string;
    caption: string;
  }>;
}

const props = defineProps<{
  letter: LetterData;
  originUnit?: { id: string; name: string; code: string } | null;
  creator?: { id: string; name: string; nip: string } | null;
}>();

const uploadForm = useForm({
  signed_file: null as File | null,
});

const handleUploadChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    uploadForm.signed_file = target.files[0];
  }
};

const submitSignedScan = () => {
  uploadForm.post(route('arsip.surat-keluar.upload-signed', props.letter.id), {
    forceFormData: true,
  });
};

const submitApproval = () => {
  router.post(route('arsip.surat-keluar.submit', props.letter.id));
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
      return { variant: 'success', label: 'Disetujui' };
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
  <AppShell :title="`Surat Keluar: ${letter.document_number || 'Draf'} - E-Arsip PTKP`">
    <div class="max-w-5xl mx-auto space-y-6">
      <!-- Breadcrumb & Top Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 text-xs font-body text-brand-slate-light mb-1">
            <Link :href="route('arsip.surat-keluar.index')" class="hover:underline">Surat Keluar</Link>
            <span>/</span>
            <span>Detail Dokumen</span>
          </div>
          <h1 class="font-heading text-2xl font-bold text-brand-navy-dark tracking-tight">
            {{ letter.document_number || 'Draf Surat Keluar' }}
          </h1>
          <p class="font-body text-sm text-brand-slate-dark mt-0.5">
            {{ letter.subject }}
          </p>
        </div>

        <div class="flex items-center gap-3">
          <Link :href="route('arsip.surat-keluar.index')">
            <AppButton variant="secondary">
              &larr; Daftar Surat
            </AppButton>
          </Link>
          <AppButton
            v-if="letter.status === 'draft' || letter.status === 'revision_requested'"
            variant="primary"
            @click="submitApproval"
          >
            Ajukan Persetujuan
          </AppButton>
        </div>
      </div>

      <!-- Double Upload Banner (If incomplete) -->
      <div
        v-if="!letter.is_double_upload_complete"
        class="rounded-xl border border-amber-300 bg-amber-50 p-6 space-y-4 shadow-sm"
      >
        <div class="flex items-start gap-4">
          <div class="p-3 bg-amber-200/60 rounded-xl text-amber-900">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div class="flex-1">
            <h2 class="font-heading text-base font-bold text-amber-950">
              Wajib Unggah Scan Tanda Tangan Basah Final
            </h2>
            <p class="font-body text-xs text-amber-800 mt-1 leading-relaxed">
              Surat Keluar PTKP menggunakan tanda tangan basah para pejabat berwenang. Setelah dokumen disetujui, dicetak, dan ditandatangani basah secara fisik, silakan unggah hasil scan PDF final di bawah ini. Sebelum berkas final diunggah, Anda tidak dapat membuat surat keluar baru.
            </p>

            <form @submit.prevent="submitSignedScan" class="mt-4 flex flex-col sm:flex-row items-center gap-3">
              <input
                type="file"
                class="block w-full sm:w-80 text-xs text-brand-slate-dark file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-amber-800 file:text-white hover:file:bg-amber-900 border border-amber-300 rounded-lg p-1.5 bg-white"
                accept=".pdf"
                @change="handleUploadChange"
                required
              />
              <AppButton variant="primary" type="submit" :disabled="uploadForm.processing">
                {{ uploadForm.processing ? 'Mengunggah...' : 'Unggah Berkas Scan Basah' }}
              </AppButton>
            </form>
            <InputError :message="uploadForm.errors.signed_file" class="mt-1" />
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Details Column -->
        <div class="lg:col-span-2 space-y-6">
          <AppCard class="p-6 space-y-4">
            <h2 class="font-heading text-base font-bold text-brand-navy-dark pb-2 border-b border-brand-slate-border">
              Ringkasan Dokumen
            </h2>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs font-body">
              <div>
                <dt class="text-brand-slate-light font-medium">Nomor Surat Resmi</dt>
                <dd class="mt-1 font-semibold text-brand-navy-dark text-sm">
                  {{ letter.document_number || '(Menunggu persetujuan final)' }}
                </dd>
              </div>

              <div>
                <dt class="text-brand-slate-light font-medium">Status Dokumen</dt>
                <dd class="mt-1">
                  <AppBadge :variant="getStatusBadge(letter.status).variant as any" size="sm">
                    {{ getStatusBadge(letter.status).label }}
                  </AppBadge>
                </dd>
              </div>

              <div>
                <dt class="text-brand-slate-light font-medium">Unit Asal</dt>
                <dd class="mt-1 text-brand-slate-dark font-medium">
                  {{ originUnit ? `${originUnit.code} - ${originUnit.name}` : '-' }}
                </dd>
              </div>

              <div>
                <dt class="text-brand-slate-light font-medium">Pembuat Dokumen</dt>
                <dd class="mt-1 text-brand-slate-dark font-medium">
                  {{ creator ? `${creator.name} (${creator.nip})` : '-' }}
                </dd>
              </div>

              <div>
                <dt class="text-brand-slate-light font-medium">Tanggal Surat</dt>
                <dd class="mt-1 text-brand-slate-dark">
                  {{ new Date(letter.letter_date).toLocaleDateString('id-ID', { dateStyle: 'full' }) }}
                </dd>
              </div>

              <div>
                <dt class="text-brand-slate-light font-medium">Pihak Tujuan</dt>
                <dd class="mt-1 text-brand-slate-dark font-medium">
                  {{ letter.destination_text || 'Internal' }}
                </dd>
              </div>

              <div>
                <dt class="text-brand-slate-light font-medium">Sifat Keamanan</dt>
                <dd class="mt-1 uppercase text-brand-slate-dark font-medium">
                  {{ letter.confidentiality }}
                </dd>
              </div>

              <div>
                <dt class="text-brand-slate-light font-medium">Tingkat Urgensi</dt>
                <dd class="mt-1 uppercase text-brand-slate-dark font-medium">
                  {{ letter.urgency }}
                </dd>
              </div>
            </dl>
          </AppCard>

          <!-- Attachments Card -->
          <AppCard class="p-6 space-y-4">
            <h2 class="font-heading text-base font-bold text-brand-navy-dark pb-2 border-b border-brand-slate-border">
              Berkas Terkait
            </h2>

            <div class="space-y-2">
              <div v-if="letter.draft_file_id" class="flex items-center justify-between p-3 rounded-lg border border-brand-slate-border bg-brand-slate-light/5">
                <div class="flex items-center gap-2 text-xs font-body text-brand-slate-dark font-medium">
                  <svg class="w-4 h-4 text-brand-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  </svg>
                  Berkas Draf Awal
                </div>
                <AppBadge variant="neutral" size="sm">Tersimpan</AppBadge>
              </div>

              <div v-if="letter.signed_file_id" class="flex items-center justify-between p-3 rounded-lg border border-emerald-200 bg-emerald-50">
                <div class="flex items-center gap-2 text-xs font-body text-emerald-900 font-semibold">
                  <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Berkas Scan Basah Resmi (Final)
                </div>
                <AppBadge variant="success" size="sm">Resmi</AppBadge>
              </div>

              <div v-if="!letter.draft_file_id && !letter.signed_file_id" class="text-xs font-body text-brand-slate-light italic p-2">
                Tidak ada berkas yang diunggah.
              </div>
            </div>
          </AppCard>
        </div>

        <!-- Approval Workflow Column -->
        <div class="space-y-6">
          <AppCard class="p-6 space-y-4">
            <h2 class="font-heading text-base font-bold text-brand-navy-dark pb-2 border-b border-brand-slate-border">
              Alur Persetujuan Pejabat
            </h2>

            <div v-if="letter.approvals && letter.approvals.length > 0" class="space-y-4">
              <div
                v-for="step in letter.approvals"
                :key="step.id"
                class="relative pl-6 pb-4 border-l-2 last:border-0"
                :class="step.status === 'approved' ? 'border-emerald-500' : (step.status === 'pending' ? 'border-amber-500' : 'border-brand-slate-border')"
              >
                <!-- Dot icon -->
                <div
                  class="absolute -left-2 top-0 w-4 h-4 rounded-full border-2 bg-white flex items-center justify-center text-[10px]"
                  :class="step.status === 'approved' ? 'border-emerald-500 text-emerald-600' : (step.status === 'pending' ? 'border-amber-500 text-amber-600' : 'border-brand-slate-border text-brand-slate-light')"
                >
                  <span v-if="step.status === 'approved'">&#10003;</span>
                  <span v-else>{{ step.step_order }}</span>
                </div>

                <div class="text-xs font-body">
                  <div class="font-bold text-brand-navy-dark">
                    {{ step.approver_role_title }}
                  </div>
                  <div class="mt-0.5 flex items-center gap-2">
                    <AppBadge
                      :variant="step.status === 'approved' ? 'success' : (step.status === 'pending' ? 'warning' : 'neutral')"
                      size="sm"
                    >
                      {{ step.status === 'approved' ? 'Disetujui' : (step.status === 'pending' ? 'Menunggu' : 'Antrean') }}
                    </AppBadge>
                    <span v-if="step.decided_at" class="text-[10px] text-brand-slate-light">
                      {{ new Date(step.decided_at).toLocaleString('id-ID') }}
                    </span>
                  </div>

                  <div v-if="step.notes" class="mt-2 p-2 bg-brand-slate-light/5 rounded border border-brand-slate-border text-brand-slate-dark italic text-[11px]">
                    "{{ step.notes }}"
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="text-xs font-body text-brand-slate-light italic p-4 text-center">
              Alur persetujuan belum dimulai. Silakan ajukan draf surat.
            </div>
          </AppCard>
        </div>
      </div>
    </div>
  </AppShell>
</template>
