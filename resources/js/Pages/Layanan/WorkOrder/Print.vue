<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

interface ActionItem {
    id: string;
    action_type: string;
    notes: string | null;
    created_at: string;
}

interface TicketDetail {
    id: string;
    ticket_number: string;
    reference_memo_number: string | null;
    status: 'pending' | 'in_progress' | 'completed' | 'cancelled';
    submitted_at: string;
    started_at: string | null;
    completed_at: string | null;
    device_type: {
        id: string;
        name: string;
        code: string;
    };
    device_name: string;
    it_inventory_code: string | null;
    issue_description: string;
    photo_file_id: string | null;
    section_name: string;
    examination_result: 'repaired_internally' | 'third_party_vendor' | 'part_replacement' | 'unrepairable' | null;
    estimated_duration: string | null;
    technician_notes: string | null;
    created_by: string;
    requester: {
        id: string;
        name: string;
        nip: string;
        email: string;
        org_unit_name: string;
        org_unit_code: string;
    };
    technician: {
        id: string;
        name: string;
        nip: string;
    } | null;
    actions: ActionItem[];
}

interface Props {
    ticket: TicketDetail;
}

const props = defineProps<Props>();

const printDocument = () => {
    window.print();
};

const formatDate = (dateStr: string | null): string => {
    if (!dateStr) return '..........................';
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
};

const formatDateTime = (dateStr: string | null): string => {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head :title="`Cetak Formulir IT-FR-01 - ${ticket.ticket_number}`" />

    <div class="min-h-screen bg-slate-100 font-body text-slate-900 print:bg-white print:p-0">
        <!-- Floating Action Bar (Hidden on Print) -->
        <div class="sticky top-0 z-50 border-b border-slate-200 bg-white px-4 py-3 shadow-xs print:hidden">
            <div class="mx-auto flex max-w-4xl items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        :href="`/layanan/work-orders/${ticket.id}`"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 font-heading text-xs font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        <span>Kembali ke Tiket</span>
                    </Link>
                    <span class="font-heading text-xs font-semibold text-slate-500">
                        Pratinjau Cetak Lembar Kerja Standar IT-FR-01/REV00 (Format A4)
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg bg-ptkp-navy px-4 py-2 font-heading text-xs font-bold text-white shadow-xs hover:bg-ptkp-deep"
                        @click="printDocument"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Cetak Dokumen (Ctrl + P)</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- A4 Page Container -->
        <div class="mx-auto my-6 max-w-[210mm] bg-white p-[15mm] shadow-lg print:my-0 print:max-w-none print:p-0 print:shadow-none">
            <!-- Kop Formulir Standar Mutu ISO PTKP -->
            <table class="w-full border-collapse border-2 border-black text-xs">
                <tbody>
                    <tr>
                        <!-- Kolom Logo -->
                        <td class="w-[25%] border-r-2 border-black p-3 text-center align-middle">
                            <div class="font-heading text-sm font-black tracking-tight text-black uppercase">
                                PERURI
                            </div>
                            <div class="font-heading text-[10px] font-bold tracking-widest text-black uppercase">
                                SECURITY PAPER
                            </div>
                            <div class="mt-1 font-body text-[9px] text-slate-700">
                                PT Kertas Padalarang
                            </div>
                        </td>

                        <!-- Kolom Judul Formulir -->
                        <td class="w-[50%] border-r-2 border-black p-3 text-center align-middle">
                            <div class="font-heading text-[11px] font-bold text-black uppercase">
                                PT KERTAS PADALARANG
                            </div>
                            <div class="font-heading text-[10px] font-medium text-black uppercase">
                                UNIT TEKNOLOGI INFORMASI & SMT
                            </div>
                            <div class="mt-1 font-heading text-sm font-black text-black uppercase tracking-wide">
                                FORMULIR PERMOHONAN PERBAIKAN IT
                            </div>
                            <div class="font-heading text-[10px] font-bold text-black uppercase">
                                ( WORK ORDER IT )
                            </div>
                        </td>

                        <!-- Kolom Metadata Kendali Mutu -->
                        <td class="w-[25%] p-2 align-middle text-[10px]">
                            <div class="space-y-1">
                                <div class="flex justify-between border-b border-black/40 pb-0.5">
                                    <span class="font-semibold">No. Dokumen</span>
                                    <span class="font-mono font-bold">IT-FR-01</span>
                                </div>
                                <div class="flex justify-between border-b border-black/40 pb-0.5">
                                    <span class="font-semibold">No. Revisi</span>
                                    <span class="font-mono font-bold">REV00</span>
                                </div>
                                <div class="flex justify-between border-b border-black/40 pb-0.5">
                                    <span class="font-semibold">Tgl. Efektif</span>
                                    <span>01-04-2021</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-semibold">Halaman</span>
                                    <span>1 dari 1</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Barcode & Nomor Antrean Tiket -->
            <div class="mt-3 flex items-center justify-between border-b-2 border-black pb-2">
                <div>
                    <span class="font-heading text-xs font-semibold text-slate-700">NOMOR TIKET ANTREAN:</span>
                    <span class="ml-2 font-mono text-base font-black tracking-wider text-black">{{ ticket.ticket_number }}</span>
                </div>
                <div class="text-right text-xs">
                    <span class="font-semibold">Tanggal & Waktu Pengajuan: </span>
                    <span>{{ formatDateTime(ticket.submitted_at) }}</span>
                </div>
            </div>

            <!-- BAGIAN I: DATA PEMOHON & PERALATAN -->
            <div class="mt-4">
                <div class="bg-black px-2.5 py-1 font-heading text-xs font-bold text-white uppercase tracking-wider">
                    BAGIAN I: IDENTITAS PEMOHON & RINCIAN PERANGKAT (Diisi oleh Pemohon)
                </div>

                <table class="w-full border-collapse border border-black text-xs mt-1">
                    <tbody>
                        <tr class="border-b border-black">
                            <td class="w-[20%] bg-slate-100 p-2 font-semibold border-r border-black">Nama Pemohon</td>
                            <td class="w-[30%] p-2 border-r border-black font-bold">{{ ticket.requester.name }}</td>
                            <td class="w-[20%] bg-slate-100 p-2 font-semibold border-r border-black">NIP / Identitas</td>
                            <td class="w-[30%] p-2 font-mono font-semibold">{{ ticket.requester.nip }}</td>
                        </tr>
                        <tr class="border-b border-black">
                            <td class="bg-slate-100 p-2 font-semibold border-r border-black">Departemen / Divisi</td>
                            <td class="p-2 border-r border-black">{{ ticket.requester.org_unit_name }}</td>
                            <td class="bg-slate-100 p-2 font-semibold border-r border-black">Seksi / Ruangan</td>
                            <td class="p-2 font-bold">{{ ticket.section_name }}</td>
                        </tr>
                        <tr class="border-b border-black">
                            <td class="bg-slate-100 p-2 font-semibold border-r border-black">Nama / Merek Alat</td>
                            <td class="p-2 border-r border-black font-bold">{{ ticket.device_name }}</td>
                            <td class="bg-slate-100 p-2 font-semibold border-r border-black">Kategori Peralatan</td>
                            <td class="p-2">{{ ticket.device_type.name }}</td>
                        </tr>
                        <tr class="border-b border-black">
                            <td class="bg-slate-100 p-2 font-semibold border-r border-black">No. Barcode Label IT</td>
                            <td class="p-2 border-r border-black font-mono">{{ ticket.it_inventory_code || '-' }}</td>
                            <td class="bg-slate-100 p-2 font-semibold border-r border-black">No. Memo Pengantar</td>
                            <td class="p-2">{{ ticket.reference_memo_number || '-' }}</td>
                        </tr>
                        <tr>
                            <td class="bg-slate-100 p-2 font-semibold border-r border-black align-top">
                                Uraian Kerusakan / Gejala Gangguan
                            </td>
                            <td colspan="3" class="p-2.5 min-h-[60px] align-top whitespace-pre-line leading-relaxed">
                                {{ ticket.issue_description }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- BAGIAN II: PEMERIKSAAN & TINDAKAN TEKNISI IT -->
            <div class="mt-4">
                <div class="bg-black px-2.5 py-1 font-heading text-xs font-bold text-white uppercase tracking-wider">
                    BAGIAN II: HASIL PEMERIKSAAN TEKNIS & TINDAKAN (Diisi oleh Petugas IT)
                </div>

                <table class="w-full border-collapse border border-black text-xs mt-1">
                    <tbody>
                        <!-- 4 Opsi Standar Mutu IT-FR-01 -->
                        <tr class="border-b border-black">
                            <td class="w-[25%] bg-slate-100 p-2 font-semibold border-r border-black align-top">
                                Hasil Pemeriksaan Awal
                            </td>
                            <td colspan="3" class="p-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex h-4 w-4 items-center justify-center border border-black font-mono font-bold text-xs">
                                            {{ ticket.examination_result === 'repaired_internally' ? 'X' : '' }}
                                        </span>
                                        <span>1. Dapat diperbaiki sendiri</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex h-4 w-4 items-center justify-center border border-black font-mono font-bold text-xs">
                                            {{ ticket.examination_result === 'third_party_vendor' ? 'X' : '' }}
                                        </span>
                                        <span>2. Perbaikan pihak ketiga (vendor)</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex h-4 w-4 items-center justify-center border border-black font-mono font-bold text-xs">
                                            {{ ticket.examination_result === 'part_replacement' ? 'X' : '' }}
                                        </span>
                                        <span>3. Penggantian suku cadang</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex h-4 w-4 items-center justify-center border border-black font-mono font-bold text-xs">
                                            {{ ticket.examination_result === 'unrepairable' ? 'X' : '' }}
                                        </span>
                                        <span>4. Tidak dapat diperbaiki (afkir)</span>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Estimasi Waktu Penanganan -->
                        <tr class="border-b border-black">
                            <td class="bg-slate-100 p-2 font-semibold border-r border-black">Estimasi Waktu</td>
                            <td class="p-2 border-r border-black font-semibold">{{ ticket.estimated_duration || '-' }}</td>
                            <td class="bg-slate-100 p-2 font-semibold border-r border-black">Status Penanganan</td>
                            <td class="p-2 font-bold uppercase">{{ ticket.status }}</td>
                        </tr>

                        <!-- Catatan Tindakan / Solusi IT -->
                        <tr>
                            <td class="bg-slate-100 p-2 font-semibold border-r border-black align-top">
                                Uraian Tindakan Perbaikan / Suku Cadang
                            </td>
                            <td colspan="3" class="p-2.5 min-h-[70px] align-top whitespace-pre-line leading-relaxed">
                                {{ ticket.technician_notes || 'Sedang dalam penanganan teknisi IT.' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- BAGIAN III: SERAH TERIMA PERALATAN (TANDA TANGAN DUA PIHAK) -->
            <div class="mt-4">
                <div class="bg-black px-2.5 py-1 font-heading text-xs font-bold text-white uppercase tracking-wider">
                    BAGIAN III: SERAH TERIMA PERALATAN
                </div>

                <table class="w-full border-collapse border border-black text-xs mt-1">
                    <thead>
                        <tr class="border-b border-black bg-slate-100 font-heading">
                            <th class="w-1/2 border-r border-black p-2 text-center font-bold">
                                PEMOHON<br>
                                <span class="font-body text-[10px] font-normal text-slate-600">(Yang Menyerahkan / Menerima Kembali)</span>
                            </th>
                            <th class="w-1/2 p-2 text-center font-bold">
                                PETUGAS IT / TEKNISI<br>
                                <span class="font-body text-[10px] font-normal text-slate-600">(Yang Memeriksa & Memperbaiki)</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- Kolom TTD Pemohon -->
                            <td class="border-r border-black p-4 text-center align-bottom">
                                <div class="text-[10px] text-slate-600 mb-1">
                                    Tanggal: {{ formatDate(ticket.completed_at || ticket.submitted_at) }}
                                </div>
                                <div class="h-16"></div>
                                <div class="border-t border-dotted border-black pt-1">
                                    <span class="font-heading font-bold uppercase">{{ ticket.requester.name }}</span>
                                    <div class="font-mono text-[10px] text-slate-600">NIP: {{ ticket.requester.nip }}</div>
                                </div>
                            </td>

                            <!-- Kolom TTD Teknisi IT -->
                            <td class="p-4 text-center align-bottom">
                                <div class="text-[10px] text-slate-600 mb-1">
                                    Tanggal: {{ formatDate(ticket.completed_at || ticket.started_at) }}
                                </div>
                                <div class="h-16"></div>
                                <div class="border-t border-dotted border-black pt-1">
                                    <span class="font-heading font-bold uppercase">
                                        {{ ticket.technician?.name || '( Petugas IT Penanggung Jawab )' }}
                                    </span>
                                    <div class="font-mono text-[10px] text-slate-600">
                                        NIP: {{ ticket.technician?.nip || '..............................' }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Catatan Kaki Kendali Mutu PTKP -->
            <div class="mt-4 flex items-center justify-between border-t border-slate-300 pt-2 text-[9px] text-slate-500">
                <div>
                    PT Kertas Padalarang — Dokumen Terkendali Sistem Manajemen Mutu ISO 9001:2015.
                </div>
                <div>
                    Dicetak secara otomatis dari Modul Layanan IT (SI_WOPPIT) pada {{ new Date().toLocaleString('id-ID') }}.
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media print {
    body {
        background: white !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    @page {
        size: A4 portrait;
        margin: 10mm;
    }
}
</style>
