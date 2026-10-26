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

defineProps<{
    letter: LetterData;
    originUnit?: { id: string; name: string; code: string } | null;
    creator?: { id: string; name: string; nip: string } | null;
}>();

const formatRupiah = (val: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
};
</script>

<template>
    <AppShell :title="`Formulir: ${letter.type.code} - E-Arsip PTKP`">
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
                            :href="route('arsip.khusus.index')"
                            class="hover:underline"
                            >Formulir Khusus</Link
                        >
                        <span>/</span>
                        <span>Detail {{ letter.type.name }}</span>
                    </div>
                    <h1
                        class="text-brand-navy-dark font-heading text-2xl font-bold tracking-tight"
                    >
                        {{ letter.document_number || 'Draf Formulir' }}
                    </h1>
                    <p class="text-brand-slate-dark mt-0.5 font-body text-sm">
                        {{ letter.subject }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Link :href="route('arsip.khusus.index')">
                        <AppButton variant="secondary">
                            &larr; Daftar Formulir
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
                                Formulir Ini Memerlukan Perbaikan / Revisi
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
                                        'Harap periksa dan sesuaikan kembali isi draf formulir ini.'
                                    }}"</span
                                >
                            </p>
                        </div>
                    </div>

                    <Link :href="route('arsip.khusus.edit', letter.id)">
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
                            Pratinjau Dokumen Formulir (PDF Otomatis)
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
                        title="Pratinjau Formulir PDF"
                    ></iframe>
                </div>
            </AppCard>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Form Payload -->
                <div class="space-y-6 lg:col-span-2">
                    <AppCard class="space-y-4 p-6">
                        <h2
                            class="text-brand-navy-dark border-brand-slate-border border-b pb-2 font-heading text-base font-bold"
                        >
                            Rincian Formulir: {{ letter.type.name }}
                        </h2>

                        <!-- SPPD Details View -->
                        <div
                            v-if="
                                letter.type.code === 'SPPD' &&
                                letter.content_body
                            "
                            class="space-y-4"
                        >
                            <div
                                class="bg-brand-blue/5 border-brand-blue/20 flex items-center justify-between rounded-lg border p-4"
                            >
                                <div>
                                    <span
                                        class="text-brand-navy-dark block text-xs font-bold"
                                        >Bundel Dokumen SPPD PTKP</span
                                    >
                                    <span
                                        class="text-brand-slate-light text-[11px]"
                                        >Surat Perintah Perjalanan Dinas dan
                                        Rincian Biaya</span
                                    >
                                </div>
                                <AppBadge variant="violet" size="sm"
                                    >Reset Bulanan</AppBadge
                                >
                            </div>

                            <dl
                                class="grid grid-cols-1 gap-4 font-body text-xs sm:grid-cols-2"
                            >
                                <div>
                                    <dt
                                        class="text-brand-slate-light font-medium"
                                    >
                                        Kota / Instansi Tujuan Dinas
                                    </dt>
                                    <dd
                                        class="text-brand-navy-dark mt-1 text-sm font-semibold"
                                    >
                                        {{
                                            letter.content_body
                                                .destination_city || '-'
                                        }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-brand-slate-light font-medium"
                                    >
                                        Moda Transportasi
                                    </dt>
                                    <dd
                                        class="text-brand-slate-dark mt-1 font-medium"
                                    >
                                        {{
                                            letter.content_body
                                                .transport_mode || '-'
                                        }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-brand-slate-light font-medium"
                                    >
                                        Tanggal Berangkat
                                    </dt>
                                    <dd class="text-brand-slate-dark mt-1">
                                        {{
                                            letter.content_body.departure_date
                                                ? new Date(
                                                      letter.content_body
                                                          .departure_date,
                                                  ).toLocaleDateString('id-ID')
                                                : '-'
                                        }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-brand-slate-light font-medium"
                                    >
                                        Tanggal Kembali
                                    </dt>
                                    <dd class="text-brand-slate-dark mt-1">
                                        {{
                                            letter.content_body.return_date
                                                ? new Date(
                                                      letter.content_body
                                                          .return_date,
                                                  ).toLocaleDateString('id-ID')
                                                : '-'
                                        }}
                                    </dd>
                                </div>

                                <div class="sm:col-span-2">
                                    <dt
                                        class="text-brand-slate-light font-medium"
                                    >
                                        Maksud & Tujuan Perjalanan
                                    </dt>
                                    <dd
                                        class="text-brand-slate-dark bg-brand-slate-light/5 border-brand-slate-border mt-1 whitespace-pre-line rounded-lg border p-3"
                                    >
                                        {{ letter.content_body.purpose || '-' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- SPB Details View -->
                        <div
                            v-else-if="
                                letter.type.code === 'SPB' &&
                                letter.content_body
                            "
                            class="space-y-4"
                        >
                            <h3
                                class="text-brand-navy-dark font-heading text-xs font-bold uppercase tracking-wider"
                            >
                                Daftar Permintaan Barang Gudang
                            </h3>

                            <div class="overflow-x-auto">
                                <table
                                    class="w-full border-collapse text-left font-body text-xs"
                                >
                                    <thead>
                                        <tr
                                            class="border-brand-slate-border bg-brand-slate-light/5 text-brand-slate-dark border-b"
                                        >
                                            <th
                                                class="px-3 py-2.5 font-semibold"
                                            >
                                                No
                                            </th>
                                            <th
                                                class="px-3 py-2.5 font-semibold"
                                            >
                                                Item Code
                                            </th>
                                            <th
                                                class="px-3 py-2.5 font-semibold"
                                            >
                                                Nama Barang
                                            </th>
                                            <th
                                                class="px-3 py-2.5 font-semibold"
                                            >
                                                Spesifikasi
                                            </th>
                                            <th
                                                class="px-3 py-2.5 font-semibold"
                                            >
                                                Jumlah
                                            </th>
                                            <th
                                                class="px-3 py-2.5 font-semibold"
                                            >
                                                Satuan
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-brand-slate-border/50 divide-y"
                                    >
                                        <tr
                                            v-for="(item, idx) in letter
                                                .content_body.items || []"
                                            :key="idx"
                                        >
                                            <td
                                                class="text-brand-slate-light px-3 py-2.5"
                                            >
                                                {{ idx + 1 }}
                                            </td>
                                            <td
                                                class="px-3 py-2.5 font-mono text-[11px]"
                                            >
                                                {{ item.item_code || '-' }}
                                            </td>
                                            <td
                                                class="text-brand-navy-dark px-3 py-2.5 font-medium"
                                            >
                                                {{
                                                    item.nama_barang ||
                                                    item.item_name
                                                }}
                                            </td>
                                            <td
                                                class="text-brand-slate-dark whitespace-pre-line px-3 py-2.5"
                                            >
                                                {{ item.spesifikasi || '-' }}
                                            </td>
                                            <td
                                                class="px-3 py-2.5 font-semibold"
                                            >
                                                {{ item.jumlah || item.qty }}
                                            </td>
                                            <td
                                                class="text-brand-slate-dark px-3 py-2.5"
                                            >
                                                {{ item.satuan || item.unit }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- BP Details View -->
                        <div
                            v-else-if="
                                letter.type.code === 'BP' && letter.content_body
                            "
                            class="space-y-4"
                        >
                            <dl
                                class="grid grid-cols-1 gap-4 font-body text-xs sm:grid-cols-2"
                            >
                                <div>
                                    <dt
                                        class="text-brand-slate-light font-medium"
                                    >
                                        Nama Pasien / Pegawai
                                    </dt>
                                    <dd
                                        class="text-brand-navy-dark mt-1 text-sm font-semibold"
                                    >
                                        {{
                                            letter.content_body.patient_name ||
                                            '-'
                                        }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-brand-slate-light font-medium"
                                    >
                                        Klinik / Faskes
                                    </dt>
                                    <dd
                                        class="text-brand-slate-dark mt-1 font-medium"
                                    >
                                        {{
                                            letter.content_body.clinic_name ||
                                            '-'
                                        }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-brand-slate-light font-medium"
                                    >
                                        Tanggal Berobat
                                    </dt>
                                    <dd class="text-brand-slate-dark mt-1">
                                        {{
                                            letter.content_body.treatment_date
                                                ? new Date(
                                                      letter.content_body
                                                          .treatment_date,
                                                  ).toLocaleDateString('id-ID')
                                                : '-'
                                        }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-brand-slate-light font-medium"
                                    >
                                        Total Biaya Pengobatan
                                    </dt>
                                    <dd
                                        class="mt-1 text-sm font-bold text-emerald-700"
                                    >
                                        {{
                                            formatRupiah(
                                                letter.content_body
                                                    .total_cost || 0,
                                            )
                                        }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </AppCard>
                </div>

                <!-- Approvals Column -->
                <div class="space-y-6">
                    <AppCard class="space-y-4 p-6">
                        <h2
                            class="text-brand-navy-dark border-brand-slate-border border-b pb-2 font-heading text-base font-bold"
                        >
                            Persetujuan & Pengesahan
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
