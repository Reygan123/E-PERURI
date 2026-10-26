<script setup lang="ts">
import AppBadge from '@/Components/AppBadge.vue';
import AppButton from '@/Components/AppButton.vue';
import AppCard from '@/Components/AppCard.vue';
import AppShell from '@/Layouts/AppShell.vue';
import { Link } from '@inertiajs/vue3';

interface LetterData {
    id: string;
    document_number: string | null;
    subject: string;
    letter_date: string;
    status: string;
    confidentiality: string;
    urgency: string;
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
}

defineProps<{
    letter: LetterData;
    originUnit?: { id: string; name: string; code: string } | null;
    creator?: { id: string; name: string; nip: string } | null;
}>();

const getStatusBadge = (
    status: string,
): {
    variant: 'navy' | 'violet' | 'success' | 'warning' | 'error' | 'neutral';
    label: string;
} => {
    switch (status) {
        case 'draft':
            return { variant: 'neutral', label: 'Draf' };
        case 'in_review':
            return { variant: 'warning', label: 'Dalam Review' };
        case 'revision_requested':
            return { variant: 'warning', label: 'Perlu Revisi' };
        case 'approved':
            return { variant: 'navy', label: 'Disetujui' };
        case 'published':
            return { variant: 'success', label: 'Diterbitkan (Sah)' };
        case 'rejected':
            return { variant: 'error', label: 'Ditolak' };
        default:
            return { variant: 'neutral', label: status };
    }
};
</script>

<template>
    <AppShell
        :title="`Naskah Dinas: ${letter.document_number || 'Draf'} - E-Arsip PTKP`"
    >
        <div class="mx-auto max-w-5xl space-y-6">
            <!-- Breadcrumb & Top Bar -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <div
                        class="text-brand-slate-light mb-1 flex items-center gap-2 font-body text-xs"
                    >
                        <Link
                            :href="route('arsip.naskah-dinas.index')"
                            class="hover:underline"
                            >Naskah Dinas</Link
                        >
                        <span>/</span>
                        <span>Detail Dokumen</span>
                    </div>
                    <h1
                        class="text-brand-navy-dark font-heading text-2xl font-bold tracking-tight"
                    >
                        {{ letter.document_number || 'Draf Naskah Dinas' }}
                    </h1>
                    <p class="text-brand-slate-dark mt-0.5 font-body text-sm">
                        {{ letter.subject }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Link :href="route('arsip.naskah-dinas.index')">
                        <AppButton variant="secondary">
                            &larr; Daftar Naskah
                        </AppButton>
                    </Link>
                    <Link
                        v-if="
                            letter.status === 'revision_requested' ||
                            letter.status === 'draft'
                        "
                        :href="route('arsip.naskah-dinas.edit', letter.id)"
                    >
                        <AppButton
                            variant="primary"
                            class="bg-amber-600 text-white hover:bg-amber-700"
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
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                />
                            </svg>
                            Perbaiki Draf
                        </AppButton>
                    </Link>
                    <a
                        :href="route('arsip.letters.preview', letter.id)"
                        target="_blank"
                        class="inline-flex items-center justify-center rounded-lg border border-[#20288E] px-4 py-2 font-heading text-xs font-bold text-[#20288E] transition-all hover:bg-[#EEF0FB]"
                    >
                        Buka PDF Tab Baru
                    </a>
                </div>
            </div>

            <!-- Revision Requested Banner (If Revision Requested) -->
            <div
                v-if="letter.status === 'revision_requested'"
                class="shadow-xs space-y-3 rounded-xl border border-amber-300 bg-amber-50/90 p-5"
            >
                <div
                    class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start"
                >
                    <div class="flex items-start gap-3">
                        <div
                            class="mt-0.5 shrink-0 rounded-lg bg-amber-200/70 p-2 text-amber-900"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                />
                            </svg>
                        </div>
                        <div>
                            <h3
                                class="font-heading text-sm font-bold text-amber-950"
                            >
                                Dokumen Ini Memerlukan Perbaikan / Revisi
                            </h3>
                            <p class="mt-1 font-body text-xs text-amber-800">
                                Catatan dari Pejabat Penelaah:
                                <span class="font-semibold italic"
                                    >"{{
                                        letter.approvals.find(
                                            (a) =>
                                                a.status ===
                                                'revision_requested',
                                        )?.notes ||
                                        'Harap periksa dan sesuaikan kembali isi draf naskah dinas.'
                                    }}"</span
                                >
                            </p>
                        </div>
                    </div>

                    <Link :href="route('arsip.naskah-dinas.edit', letter.id)">
                        <AppButton
                            variant="primary"
                            size="sm"
                            class="shrink-0 bg-amber-600 text-white hover:bg-amber-700"
                        >
                            Perbaiki & Ajukan Ulang
                        </AppButton>
                    </Link>
                </div>
            </div>

            <!-- Digital Signature Certificate Banner (If Published) -->
            <div
                v-if="letter.status === 'published'"
                class="space-y-2 rounded-xl border border-emerald-300 bg-emerald-50/80 p-5"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="rounded-lg bg-emerald-200/60 p-2 text-emerald-900"
                    >
                        <svg
                            class="h-5 w-5"
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
                    </div>
                    <div>
                        <h3
                            class="font-heading text-sm font-bold text-emerald-950"
                        >
                            Dokumen Resmi Sah & Bertanda Tangan Digital PT
                            Kertas Padalarang
                        </h3>
                        <p class="font-body text-xs text-emerald-800">
                            Naskah dinas ini telah diverifikasi dan
                            ditandatangani secara digital menggunakan sistem
                            resmi perusahaan.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Preview PDF Section -->
            <AppCard class="space-y-3 p-4">
                <div
                    class="border-brand-slate-border flex items-center justify-between border-b pb-2"
                >
                    <div class="flex items-center gap-2">
                        <svg
                            class="h-5 w-5 text-[#20288E]"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                        <h2
                            class="text-brand-navy-dark font-heading text-base font-bold"
                        >
                            Pratinjau Dokumen PDF
                        </h2>
                    </div>
                    <a
                        :href="route('arsip.letters.preview', letter.id)"
                        target="_blank"
                        class="text-xs font-semibold text-[#20288E] hover:underline"
                    >
                        Buka di jendela baru &rarr;
                    </a>
                </div>

                <div
                    class="h-[650px] w-full overflow-hidden rounded-lg border border-slate-200 bg-slate-100"
                >
                    <iframe
                        :src="route('arsip.letters.preview', letter.id)"
                        class="h-full w-full border-0"
                        title="Pratinjau Naskah Dinas PDF"
                    ></iframe>
                </div>
            </AppCard>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Details -->
                <div class="space-y-6 lg:col-span-2">
                    <AppCard class="space-y-4 p-6">
                        <h2
                            class="text-brand-navy-dark border-brand-slate-border border-b pb-2 font-heading text-base font-bold"
                        >
                            Informasi Naskah Dinas
                        </h2>

                        <dl
                            class="grid grid-cols-1 gap-4 font-body text-xs sm:grid-cols-2"
                        >
                            <div>
                                <dt class="text-brand-slate-light font-medium">
                                    Jenis Naskah
                                </dt>
                                <dd
                                    class="text-brand-navy-dark mt-1 font-semibold"
                                >
                                    {{ letter.type.name }} ({{
                                        letter.type.code
                                    }})
                                </dd>
                            </div>

                            <div>
                                <dt class="text-brand-slate-light font-medium">
                                    Status Dokumen
                                </dt>
                                <dd class="mt-1">
                                    <AppBadge
                                        :variant="
                                            getStatusBadge(letter.status)
                                                .variant as any
                                        "
                                        size="sm"
                                    >
                                        {{
                                            getStatusBadge(letter.status).label
                                        }}
                                    </AppBadge>
                                </dd>
                            </div>

                            <div>
                                <dt class="text-brand-slate-light font-medium">
                                    Nomor Dokumen
                                </dt>
                                <dd
                                    class="text-brand-slate-dark mt-1 font-medium"
                                >
                                    {{
                                        letter.document_number || 'Belum Terbit'
                                    }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-brand-slate-light font-medium">
                                    Unit Pembuat
                                </dt>
                                <dd
                                    class="text-brand-slate-dark mt-1 font-medium"
                                >
                                    {{
                                        originUnit
                                            ? `${originUnit.code} - ${originUnit.name}`
                                            : '-'
                                    }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-brand-slate-light font-medium">
                                    Tanggal Naskah
                                </dt>
                                <dd class="text-brand-slate-dark mt-1">
                                    {{
                                        new Date(
                                            letter.letter_date,
                                        ).toLocaleDateString('id-ID', {
                                            dateStyle: 'full',
                                        })
                                    }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-brand-slate-light font-medium">
                                    Pembuat / Inisiator
                                </dt>
                                <dd
                                    class="text-brand-slate-dark mt-1 font-medium"
                                >
                                    {{
                                        creator
                                            ? `${creator.name} (${creator.nip})`
                                            : '-'
                                    }}
                                </dd>
                            </div>
                        </dl>
                    </AppCard>
                </div>

                <!-- Approvals Column -->
                <div class="space-y-6">
                    <AppCard class="space-y-4 p-6">
                        <h2
                            class="text-brand-navy-dark border-brand-slate-border border-b pb-2 font-heading text-base font-bold"
                        >
                            Alur Persetujuan & TTD
                        </h2>

                        <div
                            v-if="
                                letter.approvals && letter.approvals.length > 0
                            "
                            class="space-y-4"
                        >
                            <div
                                v-for="step in letter.approvals"
                                :key="step.id"
                                class="relative border-l-2 pb-4 pl-6 last:border-0"
                                :class="
                                    step.status === 'approved'
                                        ? 'border-emerald-500'
                                        : step.status === 'pending'
                                          ? 'border-amber-500'
                                          : 'border-brand-slate-border'
                                "
                            >
                                <div
                                    class="absolute -left-2 top-0 flex h-4 w-4 items-center justify-center rounded-full border-2 bg-white text-[10px]"
                                    :class="
                                        step.status === 'approved'
                                            ? 'border-emerald-500 text-emerald-600'
                                            : step.status === 'pending'
                                              ? 'border-amber-500 text-amber-600'
                                              : 'border-brand-slate-border text-brand-slate-light'
                                    "
                                >
                                    <span v-if="step.status === 'approved'"
                                        >&#10003;</span
                                    >
                                    <span v-else>{{ step.step_order }}</span>
                                </div>

                                <div class="font-body text-xs">
                                    <div class="text-brand-navy-dark font-bold">
                                        {{ step.approver_role_title }}
                                    </div>
                                    <div class="mt-0.5 flex items-center gap-2">
                                        <AppBadge
                                            :variant="
                                                step.status === 'approved'
                                                    ? 'success'
                                                    : step.status === 'pending'
                                                      ? 'warning'
                                                      : 'neutral'
                                            "
                                            size="sm"
                                        >
                                            {{
                                                step.status === 'approved'
                                                    ? 'Disetujui'
                                                    : step.status === 'pending'
                                                      ? 'Menunggu'
                                                      : 'Antrean'
                                            }}
                                        </AppBadge>
                                        <span
                                            v-if="step.decided_at"
                                            class="text-brand-slate-light text-[10px]"
                                        >
                                            {{
                                                new Date(
                                                    step.decided_at,
                                                ).toLocaleString('id-ID')
                                            }}
                                        </span>
                                    </div>

                                    <div
                                        v-if="step.signature_hash"
                                        class="text-brand-slate-light mt-1 truncate font-mono text-[9px]"
                                        :title="step.signature_hash"
                                    >
                                        HASH: {{ step.signature_hash }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="text-brand-slate-light p-4 text-center font-body text-xs italic"
                        >
                            Alur persetujuan belum dimulai.
                        </div>
                    </AppCard>
                </div>
            </div>
        </div>
    </AppShell>
</template>
