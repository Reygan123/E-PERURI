<script setup lang="ts">
import AppBadge from '@/Components/AppBadge.vue';
import AppButton from '@/Components/AppButton.vue';
import AppCard from '@/Components/AppCard.vue';
import InputError from '@/Components/InputError.vue';
import AppShell from '@/Layouts/AppShell.vue';
import { Link, router, useForm } from '@inertiajs/vue3';

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
    uploadForm.post(
        route('arsip.surat-keluar.upload-signed', props.letter.id),
        {
            forceFormData: true,
        },
    );
};

const submitApproval = () => {
    router.post(route('arsip.surat-keluar.submit', props.letter.id));
};

const getStatusBadge = (
    status: string,
): { variant: 'neutral' | 'warning' | 'error' | 'success'; label: string } => {
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
    <AppShell
        :title="`Surat Keluar: ${letter.document_number || 'Draf'} - E-Arsip PTKP`"
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
                            :href="route('arsip.surat-keluar.index')"
                            class="hover:underline"
                            >Surat Keluar</Link
                        >
                        <span>/</span>
                        <span>Detail Dokumen</span>
                    </div>
                    <h1
                        class="text-brand-navy-dark font-heading text-2xl font-bold tracking-tight"
                    >
                        {{ letter.document_number || 'Draf Surat Keluar' }}
                    </h1>
                    <p class="text-brand-slate-dark mt-0.5 font-body text-sm">
                        {{ letter.subject }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Link :href="route('arsip.surat-keluar.index')">
                        <AppButton variant="secondary">
                            &larr; Daftar Surat
                        </AppButton>
                    </Link>
                    <a
                        :href="route('arsip.letters.preview', letter.id)"
                        target="_blank"
                        class="inline-flex items-center justify-center rounded-lg border border-[#20288E] px-4 py-2 font-heading text-xs font-bold text-[#20288E] transition-all hover:bg-[#EEF0FB]"
                    >
                        Buka PDF Tab Baru
                    </a>
                    <AppButton
                        v-if="
                            letter.status === 'draft' ||
                            letter.status === 'revision_requested'
                        "
                        variant="primary"
                        @click="submitApproval"
                    >
                        Ajukan Persetujuan
                    </AppButton>
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
                                Surat Keluar Ini Memerlukan Perbaikan / Revisi
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
                                        'Harap periksa dan sesuaikan kembali isi draf surat keluar.'
                                    }}"</span
                                >
                            </p>
                        </div>
                    </div>

                    <Link :href="route('arsip.surat-keluar.edit', letter.id)">
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

            <!-- Double Upload Banner: HIDDEN via v-show="false" per keputusan user -->
            <div
                v-show="false"
                v-if="!letter.is_double_upload_complete"
                class="space-y-4 rounded-xl border border-amber-300 bg-amber-50 p-6 shadow-sm"
            >
                <div class="flex items-start gap-4">
                    <div class="rounded-xl bg-amber-200/60 p-3 text-amber-900">
                        <svg
                            class="h-6 w-6"
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
                    <div class="flex-1">
                        <h2
                            class="font-heading text-base font-bold text-amber-950"
                        >
                            Wajib Unggah Scan Tanda Tangan Basah Final
                        </h2>
                        <form
                            @submit.prevent="submitSignedScan"
                            class="mt-4 flex flex-col items-center gap-3 sm:flex-row"
                        >
                            <input
                                type="file"
                                class="text-brand-slate-dark block w-full rounded-lg border border-amber-300 bg-white p-1.5 text-xs file:mr-3 file:rounded-md file:border-0 file:bg-amber-800 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-amber-900 sm:w-80"
                                accept=".pdf"
                                @change="handleUploadChange"
                            />
                            <AppButton
                                variant="primary"
                                type="submit"
                                :disabled="uploadForm.processing"
                            >
                                {{
                                    uploadForm.processing
                                        ? 'Mengunggah...'
                                        : 'Unggah Berkas Scan Basah'
                                }}
                            </AppButton>
                        </form>
                        <InputError
                            :message="uploadForm.errors.signed_file"
                            class="mt-1"
                        />
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
                            Pratinjau Dokumen PDF (Digital PTKP)
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
                        title="Pratinjau Surat PDF"
                    ></iframe>
                </div>
            </AppCard>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Details Column -->
                <div class="space-y-6 lg:col-span-2">
                    <AppCard class="space-y-4 p-6">
                        <h2
                            class="text-brand-navy-dark border-brand-slate-border border-b pb-2 font-heading text-base font-bold"
                        >
                            Ringkasan Dokumen
                        </h2>

                        <dl
                            class="grid grid-cols-1 gap-4 font-body text-xs sm:grid-cols-2"
                        >
                            <div>
                                <dt class="text-brand-slate-light font-medium">
                                    Nomor Surat Resmi
                                </dt>
                                <dd
                                    class="text-brand-navy-dark mt-1 text-sm font-semibold"
                                >
                                    {{
                                        letter.document_number ||
                                        '(Menunggu persetujuan final)'
                                    }}
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
                                    Unit Asal
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
                                    Pembuat Dokumen
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

                            <div>
                                <dt class="text-brand-slate-light font-medium">
                                    Tanggal Surat
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
                                    Pihak Tujuan
                                </dt>
                                <dd
                                    class="text-brand-slate-dark mt-1 font-medium"
                                >
                                    {{ letter.destination_text || 'Internal' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-brand-slate-light font-medium">
                                    Sifat Keamanan
                                </dt>
                                <dd
                                    class="text-brand-slate-dark mt-1 font-medium uppercase"
                                >
                                    {{ letter.confidentiality }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-brand-slate-light font-medium">
                                    Tingkat Urgensi
                                </dt>
                                <dd
                                    class="text-brand-slate-dark mt-1 font-medium uppercase"
                                >
                                    {{ letter.urgency }}
                                </dd>
                            </div>
                        </dl>
                    </AppCard>
                </div>

                <!-- Approval Workflow Column -->
                <div class="space-y-6">
                    <AppCard class="space-y-4 p-6">
                        <h2
                            class="text-brand-navy-dark border-brand-slate-border border-b pb-2 font-heading text-base font-bold"
                        >
                            Alur Persetujuan Pejabat
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
                                <!-- Dot icon -->
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
                                        v-if="step.notes"
                                        class="bg-brand-slate-light/5 border-brand-slate-border text-brand-slate-dark mt-2 rounded border p-2 text-[11px] italic"
                                    >
                                        "{{ step.notes }}"
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="text-brand-slate-light p-4 text-center font-body text-xs italic"
                        >
                            Alur persetujuan belum dimulai. Silakan ajukan draf
                            surat.
                        </div>
                    </AppCard>
                </div>
            </div>
        </div>
    </AppShell>
</template>
