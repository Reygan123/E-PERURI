<script setup lang="ts">
import AppBadge from '@/Components/AppBadge.vue';
import AppButton from '@/Components/AppButton.vue';
import AppCard from '@/Components/AppCard.vue';
import AppEmptyState from '@/Components/AppEmptyState.vue';
import AppTextarea from '@/Components/AppTextarea.vue';
import InputError from '@/Components/InputError.vue';
import AppShell from '@/Layouts/AppShell.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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

defineProps<{
    pendingApprovals: ApprovalItem[];
    historyApprovals: ApprovalItem[];
}>();

const activeTab = ref<'pending' | 'history'>('pending');

// Preview Modal State
const selectedPreview = ref<ApprovalItem | null>(null);

const openPreviewModal = (approval: ApprovalItem) => {
    selectedPreview.value = approval;
};

const closePreviewModal = () => {
    selectedPreview.value = null;
};

// Decision Modal State
const selectedApproval = ref<ApprovalItem | null>(null);
const decisionAction = ref<'approve' | 'review' | 'revise' | 'reject'>(
    'approve',
);

const form = useForm({
    action: 'approve',
    notes: '',
    passphrase: '',
});

const openDecisionModal = (
    approval: ApprovalItem,
    action: 'approve' | 'review' | 'revise' | 'reject',
) => {
    selectedPreview.value = null;
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
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1
                        class="text-brand-navy-dark font-heading text-2xl font-bold tracking-tight"
                    >
                        Persetujuan & Tanda Tangan Naskah
                    </h1>
                    <p class="text-brand-slate-light mt-1 font-body text-sm">
                        Meja kerja persetujuan berjenjang, penelaahan draf,
                        pembubuhan paraf, dan tanda tangan digital terenkripsi.
                    </p>
                </div>

                <div
                    class="border-brand-slate-border flex items-center gap-2 rounded-lg border bg-white p-1"
                >
                    <button
                        class="rounded-md px-3 py-1.5 font-body text-xs font-semibold transition-colors"
                        :class="
                            activeTab === 'pending'
                                ? 'bg-brand-navy-dark text-white'
                                : 'text-brand-slate-dark hover:text-brand-navy-dark'
                        "
                        @click="activeTab = 'pending'"
                    >
                        Menunggu ({{ pendingApprovals.length }})
                    </button>
                    <button
                        class="rounded-md px-3 py-1.5 font-body text-xs font-semibold transition-colors"
                        :class="
                            activeTab === 'history'
                                ? 'bg-brand-navy-dark text-white'
                                : 'text-brand-slate-dark hover:text-brand-navy-dark'
                        "
                        @click="activeTab = 'history'"
                    >
                        Riwayat Keputusan
                    </button>
                </div>
            </div>

            <!-- Pending Inbox Tab -->
            <div v-if="activeTab === 'pending'" class="space-y-4">
                <div
                    v-if="pendingApprovals.length > 0"
                    class="grid grid-cols-1 gap-4"
                >
                    <AppCard
                        v-for="item in pendingApprovals"
                        :key="item.id"
                        class="hover:border-brand-blue/50 space-y-4 p-5 transition-colors"
                    >
                        <div
                            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <AppBadge variant="navy" size="sm">
                                        {{ item.letter.type.name }} ({{
                                            item.letter.type.code
                                        }})
                                    </AppBadge>
                                    <span
                                        class="text-brand-slate-light font-body text-xs"
                                    >
                                        Langkah ke-{{ item.step_order }}:
                                        {{ item.approver_role_title }}
                                    </span>
                                </div>
                                <h3
                                    class="text-brand-navy-dark font-heading text-base font-bold"
                                >
                                    {{ item.letter.subject }}
                                </h3>
                                <p
                                    class="text-brand-slate-dark font-body text-xs"
                                >
                                    Nomor:
                                    <span class="font-semibold">{{
                                        item.letter.document_number ||
                                        'Belum Terbit (Draf)'
                                    }}</span>
                                    &bull; Tanggal:
                                    {{
                                        new Date(
                                            item.letter.letter_date,
                                        ).toLocaleDateString('id-ID')
                                    }}
                                </p>
                            </div>

                            <!-- Quick Decision Action Buttons -->
                            <div
                                class="flex flex-wrap items-center gap-2 self-start sm:self-auto"
                            >
                                <AppButton
                                    variant="secondary"
                                    size="sm"
                                    class="border-ptkp-navy font-semibold text-ptkp-navy hover:bg-ptkp-mist"
                                    @click="openPreviewModal(item)"
                                >
                                    <svg
                                        class="mr-1.5 h-4 w-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                        />
                                    </svg>
                                    Lihat / Pratinjau Surat
                                </AppButton>

                                <AppButton
                                    variant="primary"
                                    size="sm"
                                    class="bg-emerald-600 text-white hover:bg-emerald-700"
                                    @click="openDecisionModal(item, 'approve')"
                                >
                                    <svg
                                        class="mr-1.5 h-4 w-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                    Setujui / TTD
                                </AppButton>

                                <AppButton
                                    variant="secondary"
                                    size="sm"
                                    class="border-amber-300 text-amber-700 hover:bg-amber-50"
                                    @click="openDecisionModal(item, 'revise')"
                                >
                                    Minta Revisi
                                </AppButton>

                                <AppButton
                                    variant="secondary"
                                    size="sm"
                                    class="border-rose-300 text-rose-700 hover:bg-rose-50"
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
                    <div
                        v-if="historyApprovals.length > 0"
                        class="overflow-x-auto"
                    >
                        <table
                            class="w-full border-collapse text-left font-body text-sm"
                        >
                            <thead>
                                <tr
                                    class="border-brand-slate-border bg-brand-slate-light/5 text-brand-slate-dark border-b text-xs uppercase tracking-wider"
                                >
                                    <th class="px-4 py-3.5 font-semibold">
                                        Jenis & Nomor
                                    </th>
                                    <th class="px-4 py-3.5 font-semibold">
                                        Perihal
                                    </th>
                                    <th class="px-4 py-3.5 font-semibold">
                                        Keputusan
                                    </th>
                                    <th class="px-4 py-3.5 font-semibold">
                                        Catatan Pejabat
                                    </th>
                                    <th class="px-4 py-3.5 font-semibold">
                                        Tanggal Putusan
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-brand-slate-border/50 divide-y"
                            >
                                <tr
                                    v-for="item in historyApprovals"
                                    :key="item.id"
                                    class="hover:bg-brand-slate-light/5 transition-colors"
                                >
                                    <td class="whitespace-nowrap px-4 py-3.5">
                                        <div
                                            class="text-brand-navy-dark font-semibold"
                                        >
                                            {{ item.letter.type.name }}
                                        </div>
                                        <div
                                            class="text-brand-slate-light text-xs"
                                        >
                                            {{
                                                item.letter.document_number ||
                                                'Draf'
                                            }}
                                        </div>
                                    </td>
                                    <td
                                        class="text-brand-slate-dark max-w-sm truncate px-4 py-3.5 font-medium"
                                        :title="item.letter.subject"
                                    >
                                        {{ item.letter.subject }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <AppBadge
                                            :variant="
                                                item.status === 'approved'
                                                    ? 'success'
                                                    : item.status ===
                                                        'revision_requested'
                                                      ? 'warning'
                                                      : 'error'
                                            "
                                            size="sm"
                                        >
                                            {{
                                                item.status === 'approved'
                                                    ? 'Disetujui'
                                                    : item.status ===
                                                        'revision_requested'
                                                      ? 'Diminta Revisi'
                                                      : 'Ditolak'
                                            }}
                                        </AppBadge>
                                    </td>
                                    <td
                                        class="text-brand-slate-dark max-w-xs truncate px-4 py-3.5 text-xs italic"
                                    >
                                        {{ item.notes || '-' }}
                                    </td>
                                    <td
                                        class="text-brand-slate-light whitespace-nowrap px-4 py-3.5 text-xs"
                                    >
                                        {{
                                            item.decided_at
                                                ? new Date(
                                                      item.decided_at,
                                                  ).toLocaleString('id-ID')
                                                : '-'
                                        }}
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
            <div
                v-if="selectedApproval"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
            >
                <div
                    class="w-full max-w-lg space-y-4 rounded-xl bg-white p-6 shadow-xl"
                >
                    <div
                        class="border-brand-slate-border flex items-center justify-between border-b pb-2"
                    >
                        <h3
                            class="text-brand-navy-dark font-heading text-lg font-bold"
                        >
                            Konfirmasi Keputusan Persetujuan
                        </h3>
                        <button
                            class="text-brand-slate-light hover:text-brand-slate-dark"
                            @click="selectedApproval = null"
                        >
                            &times;
                        </button>
                    </div>

                    <div
                        class="bg-brand-slate-light/5 border-brand-slate-border space-y-1 rounded-lg border p-3 font-body text-xs"
                    >
                        <div class="text-brand-navy-dark font-bold">
                            {{ selectedApproval.letter.subject }}
                        </div>
                        <div class="text-brand-slate-light">
                            Jenis:
                            {{ selectedApproval.letter.type.name }} &bull;
                            Peran: {{ selectedApproval.approver_role_title }}
                        </div>
                    </div>

                    <form @submit.prevent="submitDecision" class="space-y-4">
                        <div>
                            <label
                                class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                            >
                                Catatan / Arahan Pejabat
                            </label>
                            <AppTextarea
                                v-model="form.notes"
                                :placeholder="
                                    decisionAction === 'revise'
                                        ? 'Jelaskan poin perbaikan yang harus direvisi oleh pembuat draf...'
                                        : 'Tambahkan catatan jika diperlukan...'
                                "
                                :rows="3"
                                :required="
                                    decisionAction === 'revise' ||
                                    decisionAction === 'reject'
                                "
                            />
                            <InputError :message="form.errors.notes" />
                        </div>

                        <div
                            v-if="
                                decisionAction === 'approve' &&
                                selectedApproval.letter.type.signature_type ===
                                    'digital'
                            "
                            class="bg-brand-blue/5 border-brand-blue/20 text-brand-navy-dark rounded-lg border p-3 font-body text-xs"
                        >
                            <div
                                class="flex items-center gap-1.5 font-semibold"
                            >
                                <svg
                                    class="text-brand-blue h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                    />
                                </svg>
                                Tanda Tangan Digital PTKP
                            </div>
                            <p class="text-brand-slate-dark mt-1 text-[11px]">
                                Persetujuan Anda akan dibubuhi tanda tangan
                                digital berbasis kriptografi HMAC-SHA256 yang
                                sah dan terekam dalam audit log perusahaan.
                            </p>
                        </div>

                        <div
                            class="border-brand-slate-border flex items-center justify-end gap-3 border-t pt-3"
                        >
                            <AppButton
                                variant="secondary"
                                type="button"
                                @click="selectedApproval = null"
                            >
                                Batal
                            </AppButton>
                            <AppButton
                                :variant="
                                    decisionAction === 'approve'
                                        ? 'primary'
                                        : 'secondary'
                                "
                                type="submit"
                                :disabled="form.processing"
                                :class="
                                    decisionAction === 'approve'
                                        ? 'bg-emerald-600 text-white hover:bg-emerald-700'
                                        : decisionAction === 'reject'
                                          ? 'bg-rose-600 text-white hover:bg-rose-700'
                                          : 'bg-amber-600 text-white hover:bg-amber-700'
                                "
                            >
                                {{
                                    form.processing
                                        ? 'Memproses...'
                                        : decisionAction === 'approve'
                                          ? 'Konfirmasi Setujui'
                                          : decisionAction === 'revise'
                                            ? 'Kirim Permintaan Revisi'
                                            : 'Konfirmasi Tolak'
                                }}
                            </AppButton>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Letter Preview Modal -->
            <div
                v-if="selectedPreview"
                class="backdrop-blur-xs fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-3 sm:p-5"
            >
                <div
                    class="border-brand-slate-border flex h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-xl border bg-white shadow-2xl"
                >
                    <!-- Modal Header -->
                    <div
                        class="flex shrink-0 items-center justify-between bg-ptkp-deep p-4 text-white"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <AppBadge variant="violet" size="sm">
                                    {{ selectedPreview.letter.type.name }} ({{
                                        selectedPreview.letter.type.code
                                    }})
                                </AppBadge>
                                <span class="text-xs text-white/80">
                                    Langkah ke-{{ selectedPreview.step_order }}:
                                    {{ selectedPreview.approver_role_title }}
                                </span>
                            </div>
                            <h3
                                class="mt-1 font-heading text-base font-bold text-white"
                            >
                                {{ selectedPreview.letter.subject }}
                            </h3>
                            <p class="font-body text-xs text-white/70">
                                Nomor:
                                {{
                                    selectedPreview.letter.document_number ||
                                    'Belum Terbit (Draf)'
                                }}
                                &bull; Tanggal:
                                {{
                                    new Date(
                                        selectedPreview.letter.letter_date,
                                    ).toLocaleDateString('id-ID')
                                }}
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <a
                                :href="
                                    route(
                                        'arsip.letters.preview',
                                        selectedPreview.letter.id,
                                    )
                                "
                                target="_blank"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 font-body text-xs font-semibold text-white transition-colors hover:bg-white/20"
                            >
                                <svg
                                    class="h-3.5 w-3.5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                    />
                                </svg>
                                Tab Baru
                            </a>
                            <button
                                type="button"
                                class="cursor-pointer rounded-lg p-1.5 text-lg font-bold text-white/70 transition-colors hover:bg-white/10 hover:text-white"
                                @click="closePreviewModal"
                            >
                                &times;
                            </button>
                        </div>
                    </div>

                    <!-- Modal PDF Preview Frame -->
                    <div class="relative min-h-0 w-full flex-1 bg-slate-100">
                        <iframe
                            :src="
                                route(
                                    'arsip.letters.preview',
                                    selectedPreview.letter.id,
                                )
                            "
                            class="h-full w-full border-0"
                            title="Pratinjau Surat PDF"
                        ></iframe>
                    </div>

                    <!-- Modal Footer with Fast Decision Actions -->
                    <div
                        class="border-brand-slate-border flex shrink-0 flex-col items-center justify-between gap-3 border-t bg-white p-3.5 sm:flex-row"
                    >
                        <div
                            class="text-brand-slate-dark hidden font-body text-xs sm:block"
                        >
                            Periksa seluruh isi naskah dinas sebelum membubuhkan
                            paraf atau tanda tangan.
                        </div>
                        <div
                            class="flex w-full items-center justify-end gap-2 sm:w-auto"
                        >
                            <AppButton
                                variant="secondary"
                                size="sm"
                                @click="closePreviewModal"
                            >
                                Tutup
                            </AppButton>
                            <AppButton
                                variant="secondary"
                                size="sm"
                                class="border-amber-300 text-amber-700 hover:bg-amber-50"
                                @click="
                                    openDecisionModal(selectedPreview, 'revise')
                                "
                            >
                                Minta Revisi
                            </AppButton>
                            <AppButton
                                variant="secondary"
                                size="sm"
                                class="border-rose-300 text-rose-700 hover:bg-rose-50"
                                @click="
                                    openDecisionModal(selectedPreview, 'reject')
                                "
                            >
                                Tolak
                            </AppButton>
                            <AppButton
                                variant="primary"
                                size="sm"
                                class="bg-emerald-600 text-white hover:bg-emerald-700"
                                @click="
                                    openDecisionModal(
                                        selectedPreview,
                                        'approve',
                                    )
                                "
                            >
                                <svg
                                    class="mr-1.5 h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 13l4 4L19 7"
                                    />
                                </svg>
                                Setujui / TTD
                            </AppButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppShell>
</template>
