<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import AppBadge from '@/Components/AppBadge.vue';

interface LetterData {
  id: string;
  document_number: string | null;
  subject: string;
  letter_date: string;
  status: string;
  confidentiality: string;
  urgency: string;
  draft_file_id: string | null;
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
}

const props = defineProps<{
  letter: LetterData;
  originUnit?: { id: string; name: string; code: string } | null;
  creator?: { id: string; name: string; nip: string } | null;
}>();

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
  <AppShell :title="`Naskah Dinas: ${letter.document_number || 'Draf'} - E-Arsip PTKP`">
    <div class="max-w-5xl mx-auto space-y-6">
      <!-- Breadcrumb & Top Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 text-xs font-body text-brand-slate-light mb-1">
            <Link :href="route('arsip.naskah-dinas.index')" class="hover:underline">Naskah Dinas</Link>
            <span>/</span>
            <span>Detail Dokumen</span>
          </div>
          <h1 class="font-heading text-2xl font-bold text-brand-navy-dark tracking-tight">
            {{ letter.document_number || 'Draf Naskah Dinas' }}
          </h1>
          <p class="font-body text-sm text-brand-slate-dark mt-0.5">
            {{ letter.subject }}
          </p>
        </div>

        <Link :href="route('arsip.naskah-dinas.index')">
          <AppButton variant="secondary">
            &larr; Daftar Naskah
          </AppButton>
        </Link>
      </div>

      <!-- Digital Signature Certificate Banner (If Published) -->
      <div v-if="letter.status === 'published'" class="rounded-xl border border-emerald-300 bg-emerald-50/80 p-5 space-y-2">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-emerald-200/60 rounded-lg text-emerald-900">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <div>
            <h3 class="font-heading text-sm font-bold text-emerald-950">
              Dokumen Resmi Sah & Bertanda Tangan Digital PT Kertas Padalarang
            </h3>
            <p class="font-body text-xs text-emerald-800">
              Naskah dinas ini telah diverifikasi dan ditandatangani secara digital menggunakan kunci enkripsi resmi perusahaan.
            </p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-6">
          <AppCard class="p-6 space-y-4">
            <h2 class="font-heading text-base font-bold text-brand-navy-dark pb-2 border-b border-brand-slate-border">
              Informasi Naskah Dinas
            </h2>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs font-body">
              <div>
                <dt class="text-brand-slate-light font-medium">Jenis Naskah</dt>
                <dd class="mt-1 font-semibold text-brand-navy-dark">
                  {{ letter.type.name }} ({{ letter.type.code }})
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
                <dt class="text-brand-slate-light font-medium">Nomor Dokumen</dt>
                <dd class="mt-1 text-brand-slate-dark font-medium">
                  {{ letter.document_number || 'Belum Terbit' }}
                </dd>
              </div>

              <div>
                <dt class="text-brand-slate-light font-medium">Unit Pembuat</dt>
                <dd class="mt-1 text-brand-slate-dark font-medium">
                  {{ originUnit ? `${originUnit.code} - ${originUnit.name}` : '-' }}
                </dd>
              </div>

              <div>
                <dt class="text-brand-slate-light font-medium">Tanggal Naskah</dt>
                <dd class="mt-1 text-brand-slate-dark">
                  {{ new Date(letter.letter_date).toLocaleDateString('id-ID', { dateStyle: 'full' }) }}
                </dd>
              </div>

              <div>
                <dt class="text-brand-slate-light font-medium">Pembuat / Inisiator</dt>
                <dd class="mt-1 text-brand-slate-dark font-medium">
                  {{ creator ? `${creator.name} (${creator.nip})` : '-' }}
                </dd>
              </div>
            </dl>
          </AppCard>
        </div>

        <!-- Approvals Column -->
        <div class="space-y-6">
          <AppCard class="p-6 space-y-4">
            <h2 class="font-heading text-base font-bold text-brand-navy-dark pb-2 border-b border-brand-slate-border">
              Alur Persetujuan & TTD
            </h2>

            <div v-if="letter.approvals && letter.approvals.length > 0" class="space-y-4">
              <div
                v-for="step in letter.approvals"
                :key="step.id"
                class="relative pl-6 pb-4 border-l-2 last:border-0"
                :class="step.status === 'approved' ? 'border-emerald-500' : (step.status === 'pending' ? 'border-amber-500' : 'border-brand-slate-border')"
              >
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
                      :variant="step.status === 'approved' ? 'emerald' : (step.status === 'pending' ? 'amber' : 'neutral')"
                      size="sm"
                    >
                      {{ step.status === 'approved' ? 'Disetujui' : (step.status === 'pending' ? 'Menunggu' : 'Antrean') }}
                    </AppBadge>
                    <span v-if="step.decided_at" class="text-[10px] text-brand-slate-light">
                      {{ new Date(step.decided_at).toLocaleString('id-ID') }}
                    </span>
                  </div>

                  <div v-if="step.signature_hash" class="mt-1 font-mono text-[9px] text-brand-slate-light truncate" :title="step.signature_hash">
                    HASH: {{ step.signature_hash }}
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="text-xs font-body text-brand-slate-light italic p-4 text-center">
              Alur persetujuan belum dimulai.
            </div>
          </AppCard>
        </div>
      </div>
    </div>
  </AppShell>
</template>
