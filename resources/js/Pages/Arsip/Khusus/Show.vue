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
  content_body: Record<string, any> | null;
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

const formatRupiah = (val: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
};
</script>

<template>
  <AppShell :title="`Formulir: ${letter.type.code} - E-Arsip PTKP`">
    <div class="max-w-5xl mx-auto space-y-6">
      <!-- Breadcrumb & Top Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 text-xs font-body text-brand-slate-light mb-1">
            <Link :href="route('arsip.khusus.index')" class="hover:underline">Formulir Khusus</Link>
            <span>/</span>
            <span>Detail {{ letter.type.name }}</span>
          </div>
          <h1 class="font-heading text-2xl font-bold text-brand-navy-dark tracking-tight">
            {{ letter.document_number || 'Draf Formulir' }}
          </h1>
          <p class="font-body text-sm text-brand-slate-dark mt-0.5">
            {{ letter.subject }}
          </p>
        </div>

        <Link :href="route('arsip.khusus.index')">
          <AppButton variant="secondary">
            &larr; Daftar Formulir
          </AppButton>
        </Link>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form Payload -->
        <div class="lg:col-span-2 space-y-6">
          <AppCard class="p-6 space-y-4">
            <h2 class="font-heading text-base font-bold text-brand-navy-dark pb-2 border-b border-brand-slate-border">
              Rincian Formulir: {{ letter.type.name }}
            </h2>

            <!-- SPPD Details View -->
            <div v-if="letter.type.code === 'SPPD' && letter.content_body" class="space-y-4">
              <div class="p-4 rounded-lg bg-brand-blue/5 border border-brand-blue/20 flex items-center justify-between">
                <div>
                  <span class="text-xs font-bold text-brand-navy-dark block">Bundel 4 Berkas SPPD PTKP</span>
                  <span class="text-[11px] text-brand-slate-light">Meliputi SPPD, Nota Dinas Permohonan, SKPDLK, dan Rincian Biaya</span>
                </div>
                <AppBadge variant="violet" size="sm">Reset Bulanan</AppBadge>
              </div>

              <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs font-body">
                <div>
                  <dt class="text-brand-slate-light font-medium">Kota Tujuan Dinas</dt>
                  <dd class="mt-1 font-semibold text-brand-navy-dark text-sm">
                    {{ letter.content_body.destination_city || '-' }}
                  </dd>
                </div>

                <div>
                  <dt class="text-brand-slate-light font-medium">Moda Transportasi</dt>
                  <dd class="mt-1 font-medium text-brand-slate-dark">
                    {{ letter.content_body.transport_mode || '-' }}
                  </dd>
                </div>

                <div>
                  <dt class="text-brand-slate-light font-medium">Tanggal Berangkat</dt>
                  <dd class="mt-1 text-brand-slate-dark">
                    {{ letter.content_body.departure_date ? new Date(letter.content_body.departure_date).toLocaleDateString('id-ID') : '-' }}
                  </dd>
                </div>

                <div>
                  <dt class="text-brand-slate-light font-medium">Tanggal Kembali</dt>
                  <dd class="mt-1 text-brand-slate-dark">
                    {{ letter.content_body.return_date ? new Date(letter.content_body.return_date).toLocaleDateString('id-ID') : '-' }}
                  </dd>
                </div>

                <div class="sm:col-span-2">
                  <dt class="text-brand-slate-light font-medium">Maksud & Tujuan Perjalanan</dt>
                  <dd class="mt-1 text-brand-slate-dark whitespace-pre-line bg-brand-slate-light/5 p-3 rounded-lg border border-brand-slate-border">
                    {{ letter.content_body.purpose || '-' }}
                  </dd>
                </div>
              </dl>
            </div>

            <!-- SPB Details View -->
            <div v-else-if="letter.type.code === 'SPB' && letter.content_body" class="space-y-4">
              <h3 class="font-heading text-xs font-bold text-brand-navy-dark uppercase tracking-wider">
                Daftar Barang Diminta ke Gudang
              </h3>

              <div class="overflow-x-auto">
                <table class="w-full text-left font-body text-xs border-collapse">
                  <thead>
                    <tr class="border-b border-brand-slate-border bg-brand-slate-light/5 text-brand-slate-dark">
                      <th class="py-2.5 px-3 font-semibold">No</th>
                      <th class="py-2.5 px-3 font-semibold">Nama Barang</th>
                      <th class="py-2.5 px-3 font-semibold">Jumlah</th>
                      <th class="py-2.5 px-3 font-semibold">Satuan</th>
                      <th class="py-2.5 px-3 font-semibold">Keperluan</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-brand-slate-border/50">
                    <tr v-for="(item, idx) in (letter.content_body.items || [])" :key="idx">
                      <td class="py-2.5 px-3 text-brand-slate-light">{{ idx + 1 }}</td>
                      <td class="py-2.5 px-3 font-medium text-brand-navy-dark">{{ item.item_name }}</td>
                      <td class="py-2.5 px-3 font-semibold">{{ item.qty }}</td>
                      <td class="py-2.5 px-3 text-brand-slate-dark">{{ item.unit }}</td>
                      <td class="py-2.5 px-3 text-brand-slate-light">{{ item.purpose || '-' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- BP Details View -->
            <div v-else-if="letter.type.code === 'BP' && letter.content_body" class="space-y-4">
              <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs font-body">
                <div>
                  <dt class="text-brand-slate-light font-medium">Nama Pasien / Pegawai</dt>
                  <dd class="mt-1 font-semibold text-brand-navy-dark text-sm">
                    {{ letter.content_body.patient_name || '-' }}
                  </dd>
                </div>

                <div>
                  <dt class="text-brand-slate-light font-medium">Klinik / Faskes</dt>
                  <dd class="mt-1 font-medium text-brand-slate-dark">
                    {{ letter.content_body.clinic_name || '-' }}
                  </dd>
                </div>

                <div>
                  <dt class="text-brand-slate-light font-medium">Tanggal Berobat</dt>
                  <dd class="mt-1 text-brand-slate-dark">
                    {{ letter.content_body.treatment_date ? new Date(letter.content_body.treatment_date).toLocaleDateString('id-ID') : '-' }}
                  </dd>
                </div>

                <div>
                  <dt class="text-brand-slate-light font-medium">Total Biaya Pengobatan</dt>
                  <dd class="mt-1 font-bold text-emerald-700 text-sm">
                    {{ formatRupiah(letter.content_body.total_cost || 0) }}
                  </dd>
                </div>
              </dl>
            </div>
          </AppCard>
        </div>

        <!-- Approvals Column -->
        <div class="space-y-6">
          <AppCard class="p-6 space-y-4">
            <h2 class="font-heading text-base font-bold text-brand-navy-dark pb-2 border-b border-brand-slate-border">
              Persetujuan & Pengesahan
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
