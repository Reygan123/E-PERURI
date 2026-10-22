<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import AppBadge from '@/Components/AppBadge.vue';
import AppTextarea from '@/Components/AppTextarea.vue';
import AppEmptyState from '@/Components/AppEmptyState.vue';
import InputError from '@/Components/InputError.vue';

interface ApprovalItem {
  id: string;
  step_order: number;
  status: string;
  approver_role_title: string;
  notes: string | null;
  signature_hash: string | null;
  decided_at: string | null;
  created_at: string;
  letter: {
    id: string;
    document_number: string | null;
    subject: string;
    letter_date: string;
    status: string;
    type: { name: string; code: string; signature_type: string };
  };
}

const props = defineProps<{
  pendingApprovals: ApprovalItem[];
  historyApprovals: ApprovalItem[];
}>();

const activeTab = ref<'pending' | 'history'>('pending');

// Decision Modal State
const selectedApproval = ref<ApprovalItem | null>(null);
const decisionAction = ref<'approve' | 'review' | 'revise' | 'reject'>('approve');

const form = useForm({
  action: 'approve',
  notes: '',
  passphrase: '',
});

const openDecisionModal = (approval: ApprovalItem, action: 'approve' | 'review' | 'revise' | 'reject') => {
  selectedApproval.value = approval;
  decisionAction.value = action;
  form.action = action;
  form.notes = '';
  form.passphrase = '';
};

const submitDecision = () => {
  if (!selectedApproval.value) return;

  form.post(route('arsip.persetujuan.decide', selectedApproval.value.id), {
    onSuccess: () => {
      selectedApproval.value = null;
      form.reset();
    },
  });
};
</script>

<template>
  <AppShell title="Persetujuan Naskah Dinas - E-Arsip PTKP">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="font-heading text-2xl font-bold text-brand-navy-dark tracking-tight">
            Persetujuan & Tanda Tangan Naskah
          </h1>
          <p class="font-body text-sm text-brand-slate-light mt-1">
            Meja kerja persetujuan berjenjang, penelaahan draf, pembubuhan paraf, dan tanda tangan digital terenkripsi.
          </p>
        </div>

        <div class="flex items-center gap-2 border border-brand-slate-border rounded-lg p-1 bg-white">
          <button
            class="px-3 py-1.5 rounded-md font-body text-xs font-semibold transition-colors"
            :class="activeTab === 'pending' ? 'bg-brand-navy-dark text-white' : 'text-brand-slate-dark hover:text-brand-navy-dark'"
            @click="activeTab = 'pending'"
          >
            Menunggu ({{ pendingApprovals.length }})
          </button>
          <button
            class="px-3 py-1.5 rounded-md font-body text-xs font-semibold transition-colors"
            :class="activeTab === 'history' ? 'bg-brand-navy-dark text-white' : 'text-brand-slate-dark hover:text-brand-navy-dark'"
            @click="activeTab = 'history'"
          >
            Riwayat Keputusan
          </button>
        </div>
      </div>

      <!-- Pending Inbox Tab -->
      <div v-if="activeTab === 'pending'" class="space-y-4">
        <div v-if="pendingApprovals.length > 0" class="grid grid-cols-1 gap-4">
          <AppCard
            v-for="item in pendingApprovals"
            :key="item.id"
            class="p-5 hover:border-brand-blue/50 transition-colors space-y-4"
          >
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
              <div class="space-y-1">
                <div class="flex items-center gap-2">
                  <AppBadge variant="blue" size="sm">
                    {{ item.letter.type.name }} ({{ item.letter.type.code }})
                  </AppBadge>
                  <span class="text-xs font-body text-brand-slate-light">
                    Langkah ke-{{ item.step_order }}: {{ item.approver_role_title }}
                  </span>
                </div>
                <h3 class="font-heading text-base font-bold text-brand-navy-dark">
                  {{ item.letter.subject }}
                </h3>
                <p class="font-body text-xs text-brand-slate-dark">
                  Nomor: <span class="font-semibold">{{ item.letter.document_number || 'Belum Terbit (Draf)' }}</span> &bull;
                  Tanggal: {{ new Date(item.letter.letter_date).toLocaleDateString('id-ID') }}
                </p>
              </div>

              <!-- Quick Decision Action Buttons -->
              <div class="flex items-center gap-2 self-start sm:self-auto">
                <AppButton
                  variant="primary"
                  size="sm"
                  class="bg-emerald-600 hover:bg-emerald-700 text-white"
                  @click="openDecisionModal(item, 'approve')"
                >
                  <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Setujui / TTD
                </AppButton>

                <AppButton
                  variant="secondary"
                  size="sm"
                  class="text-amber-700 border-amber-300 hover:bg-amber-50"
                  @click="openDecisionModal(item, 'revise')"
                >
                  Minta Revisi
                </AppButton>

                <AppButton
                  variant="secondary"
                  size="sm"
                  class="text-rose-700 border-rose-300 hover:bg-rose-50"
                  @click="openDecisionModal(item, 'reject')"
                >
                  Tolak
                </AppButton>
              </div>
            </div>
          </AppCard>
        </div>

        <div v-else class="p-8">
          <AppCard>
            <AppEmptyState
              title="Semua Dokumen Telah Diproses"
              description="Tidak ada dokumen atau naskah dinas yang sedang menunggu persetujuan Anda saat ini."
            />
          </AppCard>
        </div>
      </div>

      <!-- History Tab -->
      <div v-if="activeTab === 'history'" class="space-y-4">
        <AppCard class="overflow-hidden">
          <div v-if="historyApprovals.length > 0" class="overflow-x-auto">
            <table class="w-full text-left font-body text-sm border-collapse">
              <thead>
                <tr class="border-b border-brand-slate-border bg-brand-slate-light/5 text-brand-slate-dark text-xs uppercase tracking-wider">
                  <th class="py-3.5 px-4 font-semibold">Jenis & Nomor</th>
                  <th class="py-3.5 px-4 font-semibold">Perihal</th>
                  <th class="py-3.5 px-4 font-semibold">Keputusan</th>
                  <th class="py-3.5 px-4 font-semibold">Catatan Pejabat</th>
                  <th class="py-3.5 px-4 font-semibold">Tanggal Putusan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-brand-slate-border/50">
                <tr
                  v-for="item in historyApprovals"
                  :key="item.id"
                  class="hover:bg-brand-slate-light/5 transition-colors"
                >
                  <td class="py-3.5 px-4 whitespace-nowrap">
                    <div class="font-semibold text-brand-navy-dark">
                      {{ item.letter.type.name }}
                    </div>
                    <div class="text-xs text-brand-slate-light">
                      {{ item.letter.document_number || 'Draf' }}
                    </div>
                  </td>
                  <td class="py-3.5 px-4 text-brand-slate-dark font-medium max-w-sm truncate" :title="item.letter.subject">
                    {{ item.letter.subject }}
                  </td>
                  <td class="py-3.5 px-4">
                    <AppBadge
                      :variant="item.status === 'approved' ? 'emerald' : (item.status === 'revision_requested' ? 'orange' : 'rose')"
                      size="sm"
                    >
                      {{ item.status === 'approved' ? 'Disetujui' : (item.status === 'revision_requested' ? 'Diminta Revisi' : 'Ditolak') }}
                    </AppBadge>
                  </td>
                  <td class="py-3.5 px-4 text-xs text-brand-slate-dark italic max-w-xs truncate">
                    {{ item.notes || '-' }}
                  </td>
                  <td class="py-3.5 px-4 text-xs text-brand-slate-light whitespace-nowrap">
                    {{ item.decided_at ? new Date(item.decided_at).toLocaleString('id-ID') : '-' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="p-8">
            <AppEmptyState
              title="Belum Ada Riwayat"
              description="Anda belum memiliki riwayat persetujuan dokumen naskah dinas."
            />
          </div>
        </AppCard>
      </div>

      <!-- Decision Modal -->
      <div v-if="selectedApproval" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6 space-y-4">
          <div class="flex items-center justify-between pb-2 border-b border-brand-slate-border">
            <h3 class="font-heading text-lg font-bold text-brand-navy-dark">
              Konfirmasi Keputusan Persetujuan
            </h3>
            <button class="text-brand-slate-light hover:text-brand-slate-dark" @click="selectedApproval = null">
              &times;
            </button>
          </div>

          <div class="p-3 rounded-lg bg-brand-slate-light/5 border border-brand-slate-border text-xs font-body space-y-1">
            <div class="font-bold text-brand-navy-dark">
              {{ selectedApproval.letter.subject }}
            </div>
            <div class="text-brand-slate-light">
              Jenis: {{ selectedApproval.letter.type.name }} &bull; Peran: {{ selectedApproval.approver_role_title }}
            </div>
          </div>

          <form @submit.prevent="submitDecision" class="space-y-4">
            <div>
              <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                Catatan / Arahan Pejabat
              </label>
              <AppTextarea
                v-model="form.notes"
                :placeholder="decisionAction === 'revise' ? 'Jelaskan poin perbaikan yang harus direvisi oleh pembuat draf...' : 'Tambahkan catatan jika diperlukan...'"
                rows="3"
                :required="decisionAction === 'revise' || decisionAction === 'reject'"
              />
              <InputError :message="form.errors.notes" />
            </div>

            <div v-if="decisionAction === 'approve' && selectedApproval.letter.type.signature_type === 'digital'" class="p-3 bg-brand-blue/5 border border-brand-blue/20 rounded-lg text-xs font-body text-brand-navy-dark">
              <div class="font-semibold flex items-center gap-1.5">
                <svg class="w-4 h-4 text-brand-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Tanda Tangan Digital PTKP
              </div>
              <p class="text-[11px] text-brand-slate-dark mt-1">
                Persetujuan Anda akan dibubuhi tanda tangan digital berbasis kriptografi HMAC-SHA256 yang sah dan terekam dalam audit log perusahaan.
              </p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-brand-slate-border">
              <AppButton variant="secondary" type="button" @click="selectedApproval = null">
                Batal
              </AppButton>
              <AppButton
                :variant="decisionAction === 'approve' ? 'primary' : 'secondary'"
                type="submit"
                :disabled="form.processing"
                :class="decisionAction === 'approve' ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : (decisionAction === 'reject' ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-amber-600 hover:bg-amber-700 text-white')"
              >
                {{ form.processing ? 'Memproses...' : (decisionAction === 'approve' ? 'Konfirmasi Setujui' : (decisionAction === 'revise' ? 'Kirim Permintaan Revisi' : 'Konfirmasi Tolak')) }}
              </AppButton>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppShell>
</template>
