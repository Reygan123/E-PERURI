<script setup lang="ts">
import AppButton from '@/Components/AppButton.vue';
import AppShell from '@/Layouts/AppShell.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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
    canProcess: boolean;
}

const props = defineProps<Props>();

const copied = ref(false);
const showPhotoModal = ref(false);

const processForm = useForm({
    examination_result: props.ticket.examination_result || 'repaired_internally',
    estimated_duration: props.ticket.estimated_duration || '',
    technician_notes: props.ticket.technician_notes || '',
    take_ticket: false,
    is_completed: false,
    status: props.ticket.status,
});

const durationPresets = ['1 Jam', '2 Jam', '1 Hari Kerja', '2-3 Hari Kerja', '1 Minggu'];

const copyTicketNumber = () => {
    navigator.clipboard.writeText(props.ticket.ticket_number);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
};

const applyDurationPreset = (dur: string) => {
    processForm.estimated_duration = dur;
};

const takeTicket = () => {
    processForm.take_ticket = true;
    processForm.status = 'in_progress';
    processForm.post(`/layanan/work-orders/${props.ticket.id}/process`, {
        preserveScroll: true,
    });
};

const submitDiagnosis = () => {
    processForm.take_ticket = false;
    processForm.post(`/layanan/work-orders/${props.ticket.id}/process`, {
        preserveScroll: true,
    });
};

const completeTicket = () => {
    if (confirm('Konfirmasi: Tandai pekerjaan perbaikan ini selesai dan siap diserahterimakan?')) {
        processForm.take_ticket = false;
        processForm.is_completed = true;
        processForm.status = 'completed';
        processForm.post(`/layanan/work-orders/${props.ticket.id}/process`, {
            preserveScroll: true,
        });
    }
};

const formatDate = (dateStr: string | null): string => {
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

const getExaminationInfo = (val: string | null) => {
    switch (val) {
        case 'repaired_internally':
            return {
                title: 'Perbaikan Internal Mandiri',
                desc: 'Dapat diperbaiki langsung oleh tim teknisi IT PTKP tanpa pengadaan luar.',
                icon: 'tool',
            };
        case 'third_party_vendor':
            return {
                title: 'Perbaikan Pihak Ketiga (Vendor)',
                desc: 'Perlu dikirimkan ke service center / rekanan rekanan resmi di luar pabrik.',
                icon: 'external',
            };
        case 'part_replacement':
            return {
                title: 'Penggantian Suku Cadang',
                desc: 'Memerlukan pemesanan & penggantian komponen atau modul hardware baru.',
                icon: 'cpu',
            };
        case 'unrepairable':
            return {
                title: 'Usul Afkir (Write-off Aset)',
                desc: 'Kerusakan fatal / aus, direkomendasikan untuk penghapusan aset inventaris.',
                icon: 'trash',
            };
        default:
            return {
                title: 'Belum Dilakukan Diagnosa',
                desc: 'Menunggu pemeriksaan awal dari teknisi IT yang bertugas.',
                icon: 'clock',
            };
    }
};

const progressPercentage = computed(() => {
    if (props.ticket.status === 'completed') return 100;
    if (props.ticket.status === 'in_progress') return 55;
    return 15;
});
</script>

<template>
    <AppShell
        :title="`Tiket ${ticket.ticket_number} - Layanan IT`"
        :breadcrumbs="[
            { label: 'Portal', href: '/dashboard' },
            { label: 'Layanan IT', href: '/layanan/work-orders' },
            { label: ticket.ticket_number },
        ]"
    >
        <div class="mx-auto max-w-7xl space-y-8 animate-fadeIn">
            <!-- Hero Corporate Header -->
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-ptkp-deep via-ptkp-navy to-[#2631a3] p-6 text-white shadow-xl sm:p-8"
            >
                <div class="absolute -right-12 -top-12 h-64 w-64 rounded-full bg-white/5 blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col justify-between gap-6 md:flex-row md:items-center">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 font-mono text-xs font-semibold tracking-wider text-emerald-300 backdrop-blur-md">
                                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                FORMULIR STANDAR IT-FR-01/REV00
                            </span>

                            <!-- Status Badge -->
                            <span
                                v-if="ticket.status === 'pending'"
                                class="inline-flex items-center gap-1.5 rounded-full bg-amber-400/20 px-3 py-1 font-heading text-xs font-bold text-amber-200 border border-amber-400/30"
                            >
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                Menunggu Antrean
                            </span>
                            <span
                                v-else-if="ticket.status === 'in_progress'"
                                class="inline-flex items-center gap-1.5 rounded-full bg-blue-400/20 px-3 py-1 font-heading text-xs font-bold text-blue-200 border border-blue-400/30"
                            >
                                <span class="h-1.5 w-1.5 rounded-full bg-blue-400 animate-ping"></span>
                                Sedang Dikerjakan
                            </span>
                            <span
                                v-else-if="ticket.status === 'completed'"
                                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/20 px-3 py-1 font-heading text-xs font-bold text-emerald-200 border border-emerald-400/30"
                            >
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                Selesai Diperbaiki
                            </span>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <h1 class="font-heading text-2xl font-bold tracking-tight text-white sm:text-3xl">
                                Tiket {{ ticket.ticket_number }}
                            </h1>
                            <button
                                type="button"
                                @click="copyTicketNumber"
                                class="inline-flex items-center gap-1 rounded-lg bg-white/10 px-2.5 py-1 text-xs font-medium text-slate-200 transition-colors hover:bg-white/20 active:scale-95 cursor-pointer"
                                :title="copied ? 'Tersalin ke clipboard!' : 'Salin Nomor Tiket'"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <span>{{ copied ? 'Tersalin!' : 'Salin Nomor' }}</span>
                            </button>
                        </div>

                        <p class="mt-2 font-body text-sm text-slate-200">
                            Diajukan oleh <span class="font-semibold text-white">{{ ticket.requester.name }}</span> ({{ ticket.requester.org_unit_name }}) pada {{ formatDate(ticket.submitted_at) }}.
                        </p>
                    </div>

                    <!-- Header Actions -->
                    <div class="flex flex-wrap items-center gap-3">
                        <a
                            :href="`/layanan/work-orders/${ticket.id}/print`"
                            target="_blank"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 font-heading text-sm font-bold text-ptkp-deep shadow-sm transition-all duration-200 hover:bg-slate-50 hover:-translate-y-0.5 active:scale-95"
                        >
                            <svg class="h-4 w-4 text-ptkp-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Cetak Formulir A4</span>
                        </a>

                        <Link
                            href="/layanan/work-orders"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 font-heading text-sm font-semibold text-white shadow-xs backdrop-blur-md transition-all duration-200 hover:bg-white/20"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                            </svg>
                            <span>Antrean</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Animated Timeline Progress Stepper -->
            <div class="rounded-2xl border border-ptkp-border bg-white p-6 shadow-card transition-all duration-300">
                <div class="mb-2 flex items-center justify-between">
                    <span class="font-heading text-xs font-bold uppercase tracking-wider text-slate-500">
                        Progres Penanganan Operasional
                    </span>
                    <span class="font-mono text-xs font-semibold text-ptkp-navy">
                        {{ progressPercentage }}% Selesai
                    </span>
                </div>

                <!-- Progress Bar Track -->
                <div class="relative h-2 w-full overflow-hidden rounded-full bg-slate-100 mb-6">
                    <div
                        class="h-full rounded-full bg-gradient-to-r from-ptkp-navy to-emerald-500 transition-all duration-700 ease-out"
                        :style="{ width: `${progressPercentage}%` }"
                    ></div>
                </div>

                <!-- Steps Grid -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <!-- Step 1: Diterima -->
                    <div class="flex items-start gap-3 rounded-xl border border-emerald-100 bg-emerald-50/40 p-3.5">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500 text-white font-bold text-sm shadow-xs">
                            ✓
                        </div>
                        <div>
                            <span class="block font-heading text-[11px] font-semibold text-emerald-800 uppercase tracking-wider">Tahap 1: Registrasi</span>
                            <span class="font-heading text-sm font-bold text-slate-900">Tiket Terdaftar</span>
                            <span class="block font-mono text-xs text-slate-500 mt-0.5">{{ formatDate(ticket.submitted_at) }}</span>
                        </div>
                    </div>

                    <!-- Step 2: Dikerjakan -->
                    <div
                        class="flex items-start gap-3 rounded-xl border p-3.5 transition-all duration-200"
                        :class="[
                            ticket.started_at
                                ? 'border-blue-200 bg-blue-50/50'
                                : 'border-slate-200 bg-slate-50/50 opacity-60',
                        ]"
                    >
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl font-bold text-sm shadow-xs transition-colors"
                            :class="[
                                ticket.started_at
                                    ? 'bg-ptkp-navy text-white ring-2 ring-blue-300'
                                    : 'bg-slate-200 text-slate-500',
                            ]"
                        >
                            <span v-if="ticket.completed_at">✓</span>
                            <span v-else-if="ticket.started_at" class="animate-pulse">2</span>
                            <span v-else>2</span>
                        </div>
                        <div>
                            <span class="block font-heading text-[11px] font-semibold uppercase tracking-wider" :class="ticket.started_at ? 'text-ptkp-navy' : 'text-slate-500'">
                                Tahap 2: Diagnosa & Servis
                            </span>
                            <span class="font-heading text-sm font-bold text-slate-900">
                                {{ ticket.started_at ? 'Sedang Ditangani' : 'Menunggu Teknisi' }}
                            </span>
                            <span class="block font-body text-xs text-slate-600 mt-0.5">
                                {{ ticket.technician ? ticket.technician.name : 'Belum diambil' }}
                            </span>
                            <span v-if="ticket.started_at" class="block font-mono text-[11px] text-slate-400">
                                Mulai: {{ formatDate(ticket.started_at) }}
                            </span>
                        </div>
                    </div>

                    <!-- Step 3: Selesai -->
                    <div
                        class="flex items-start gap-3 rounded-xl border p-3.5 transition-all duration-200"
                        :class="[
                            ticket.completed_at
                                ? 'border-emerald-200 bg-emerald-50/50'
                                : 'border-slate-200 bg-slate-50/50 opacity-60',
                        ]"
                    >
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl font-bold text-sm shadow-xs transition-colors"
                            :class="[
                                ticket.completed_at
                                    ? 'bg-emerald-600 text-white shadow-xs'
                                    : 'bg-slate-200 text-slate-500',
                            ]"
                        >
                            {{ ticket.completed_at ? '✓' : '3' }}
                        </div>
                        <div>
                            <span class="block font-heading text-[11px] font-semibold uppercase tracking-wider" :class="ticket.completed_at ? 'text-emerald-800' : 'text-slate-500'">
                                Tahap 3: Penyelesaian
                            </span>
                            <span class="font-heading text-sm font-bold text-slate-900">
                                {{ ticket.completed_at ? 'Rampung & Diserahterimakan' : 'Menunggu Pengerjaan' }}
                            </span>
                            <span class="block font-mono text-xs text-slate-500 mt-0.5">
                                {{ ticket.completed_at ? formatDate(ticket.completed_at) : 'Estimasi: ' + (ticket.estimated_duration || '-') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2-Column Responsive Layout: Detail Permohonan (8 cols) & Meja Kerja Teknisi (4 cols) -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12 items-start">
                <!-- Left Column (8 Cols): Detail Perangkat & Log Riwayat -->
                <div class="lg:col-span-8 space-y-6">
                    <!-- Card Spesifikasi Perangkat & Masalah -->
                    <div class="rounded-2xl border border-ptkp-border bg-white p-6 shadow-card transition-all duration-300 hover:shadow-raised">
                        <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-ptkp-mist text-ptkp-navy font-bold shadow-xs">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="font-heading text-lg font-bold text-ptkp-deep">
                                        {{ ticket.device_name }}
                                    </h2>
                                    <span class="inline-flex items-center gap-1 font-mono text-xs text-slate-500">
                                        Kategori: <strong class="text-ptkp-navy">{{ ticket.device_type.name }}</strong>
                                    </span>
                                </div>
                            </div>

                            <span class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1 font-mono text-xs font-semibold text-slate-700">
                                IT-INV: {{ ticket.it_inventory_code || 'TIDAK TERDAFTAR' }}
                            </span>
                        </div>

                        <!-- 4-Grid Specs Overview -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 rounded-xl bg-slate-50/70 p-4 border border-slate-200/80 mb-6">
                            <div>
                                <span class="block font-heading text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Lokasi / Seksi</span>
                                <span class="font-heading text-sm font-bold text-ptkp-navy flex items-center gap-1 mt-0.5">
                                    <svg class="h-4 w-4 text-ptkp-navy/70 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                    </svg>
                                    {{ ticket.section_name }}
                                </span>
                            </div>

                            <div>
                                <span class="block font-heading text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Pemohon / Unit Kerja</span>
                                <span class="font-heading text-sm font-bold text-slate-800 mt-0.5 block">
                                    {{ ticket.requester.name }} ({{ ticket.requester.org_unit_name }})
                                </span>
                            </div>

                            <div>
                                <span class="block font-heading text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Nomor Memo / Nota Dinas</span>
                                <span class="font-mono text-xs font-semibold text-slate-800 mt-0.5 block">
                                    {{ ticket.reference_memo_number || 'Tidak Ada Memo' }}
                                </span>
                            </div>

                            <div>
                                <span class="block font-heading text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Waktu Submit</span>
                                <span class="font-mono text-xs font-semibold text-slate-800 mt-0.5 block">
                                    {{ formatDate(ticket.submitted_at) }}
                                </span>
                            </div>
                        </div>

                        <!-- Uraian Gejala Kerusakan Callout -->
                        <div class="space-y-2">
                            <span class="block font-heading text-xs font-bold uppercase tracking-wider text-slate-700">
                                Uraian Gejala Gangguan / Kerusakan
                            </span>
                            <div class="relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50/60 p-4 font-body text-sm text-slate-800 whitespace-pre-line leading-relaxed shadow-xs">
                                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-ptkp-navy rounded-l"></div>
                                <p class="pl-2">{{ ticket.issue_description }}</p>
                            </div>
                        </div>

                        <!-- Foto Fisik Preview (If Available) -->
                        <div v-if="ticket.photo_file_id" class="mt-6 border-t border-slate-100 pt-5">
                            <span class="block font-heading text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">
                                Lampiran Foto Kerusakan Fisik
                            </span>
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                <div
                                    @click="showPhotoModal = true"
                                    class="group relative h-36 w-52 overflow-hidden rounded-xl border border-slate-200 shadow-xs cursor-pointer"
                                >
                                    <img
                                        :src="`/layanan/work-orders/${ticket.id}/photo`"
                                        alt="Foto Bukti Kerusakan"
                                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110"
                                    />
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity duration-200 group-hover:opacity-100 text-white font-heading text-xs font-semibold gap-1.5">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6" />
                                        </svg>
                                        <span>Perbesar Foto</span>
                                    </div>
                                </div>
                                <div>
                                    <span class="font-heading text-xs font-semibold text-slate-800 block">
                                        Berkas Terverifikasi Sistem
                                    </span>
                                    <p class="font-body text-xs text-slate-500 mt-1 max-w-sm">
                                        Foto diambil oleh pemohon saat mengajukan permohonan sebagai bukti visual kondisi awal perangkat.
                                    </p>
                                    <button
                                        type="button"
                                        @click="showPhotoModal = true"
                                        class="mt-2 inline-flex items-center gap-1 font-heading text-xs font-bold text-ptkp-navy hover:underline cursor-pointer"
                                    >
                                        <span>Lihat Resolusi Penuh</span>
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Log Riwayat Tindakan Teknis -->
                    <div class="rounded-2xl border border-ptkp-border bg-white p-6 shadow-card transition-all duration-300 hover:shadow-raised">
                        <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
                            <div>
                                <h2 class="font-heading text-base font-bold text-ptkp-deep">
                                    Catatan Riwayat Tindakan Teknis & Audit Trail
                                </h2>
                                <p class="font-body text-xs text-slate-500">
                                    Rekam jejak setiap langkah penanganan yang dilakukan teknisi IT
                                </p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 font-mono text-xs font-semibold text-slate-600">
                                {{ ticket.actions.length }} Aktivitas
                            </span>
                        </div>

                        <!-- Empty State -->
                        <div v-if="ticket.actions.length === 0" class="py-8 text-center">
                            <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                                📋
                            </div>
                            <p class="font-body text-xs text-slate-500">
                                Belum ada tindakan teknis yang dicatat pada tiket ini.
                            </p>
                        </div>

                        <!-- Timeline Log -->
                        <div v-else class="relative space-y-4 before:absolute before:left-4 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                            <div
                                v-for="(act, idx) in ticket.actions"
                                :key="act.id"
                                class="relative flex items-start gap-3 pl-10"
                            >
                                <!-- Node Icon -->
                                <div class="absolute left-2.5 top-1 flex h-3.5 w-3.5 -translate-x-1/2 items-center justify-center rounded-full bg-ptkp-navy ring-4 ring-white shadow-xs">
                                </div>

                                <div class="flex-1 rounded-xl border border-slate-200/80 bg-slate-50/70 p-3.5 transition-all duration-200 hover:bg-slate-50">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span class="rounded-md bg-ptkp-navy/10 px-2.5 py-0.5 font-heading text-xs font-bold text-ptkp-navy">
                                            {{ act.action_type.toUpperCase() }}
                                        </span>
                                        <span class="font-mono text-[11px] text-slate-500">
                                            {{ formatDate(act.created_at) }}
                                        </span>
                                    </div>
                                    <p class="mt-2 font-body text-xs text-slate-700 leading-relaxed">
                                        {{ act.notes || 'Tindakan teknis dicatat oleh sistem.' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column (4 Cols): Meja Kerja Teknisi IT / Desk Panel -->
                <div class="lg:col-span-4 sticky top-6 space-y-6">
                    <!-- Panel Khusus Teknisi SMT (canProcess) -->
                    <div v-if="canProcess" class="rounded-2xl border-2 border-ptkp-navy bg-white p-6 shadow-raised overflow-hidden relative">
                        <div class="mb-4 border-b border-slate-100 pb-3">
                            <div class="flex items-center justify-between">
                                <span class="rounded-md bg-ptkp-navy px-2 py-0.5 font-heading text-[10px] font-bold uppercase tracking-wider text-white">
                                    Pusat Kendali Teknisi (SMT)
                                </span>
                                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            </div>
                            <h2 class="mt-2 font-heading text-base font-bold text-slate-900">
                                Meja Kerja Penanganan Tiket
                            </h2>
                        </div>

                        <!-- Scenario 1: Tiket Masih Pending (Belum Diambil) -->
                        <div v-if="ticket.status === 'pending'" class="space-y-4 text-center py-2">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 shadow-xs">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-heading text-sm font-bold text-slate-900">
                                    Tiket Menunggu Penugasan
                                </h3>
                                <p class="mt-1 font-body text-xs text-slate-600 leading-relaxed">
                                    Klik tombol di bawah untuk mencatat waktu mulai pengerjaan dan menetapkan Anda sebagai teknisi penanggung jawab.
                                </p>
                            </div>

                            <button
                                type="button"
                                @click="takeTicket"
                                :disabled="processForm.processing"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-ptkp-navy py-3 px-4 font-heading text-sm font-bold text-white shadow-md transition-all duration-200 hover:bg-ptkp-deep hover:-translate-y-0.5 hover:shadow-lg active:scale-95 cursor-pointer disabled:opacity-50"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9.75L16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" />
                                </svg>
                                <span>{{ processForm.processing ? 'Memproses...' : 'Ambil Tiket (Mulai Servis)' }}</span>
                            </button>
                        </div>

                        <!-- Scenario 2: Tiket In Progress / Siap Didiagnosis -->
                        <form v-else @submit.prevent="submitDiagnosis" class="space-y-4">
                            <!-- Teknisi Active Badge -->
                            <div class="rounded-xl border border-blue-200/80 bg-blue-50/60 p-3">
                                <span class="block font-heading text-[10px] font-semibold uppercase tracking-wider text-blue-700">Teknisi Penanggung Jawab</span>
                                <span class="font-heading text-xs font-bold text-blue-950 mt-0.5 block">
                                    {{ ticket.technician?.name || 'Anda (Teknisi Aktif)' }}
                                </span>
                            </div>

                            <!-- Estimasi Durasi -->
                            <div class="space-y-1.5">
                                <label for="estimated_duration" class="block font-heading text-xs font-semibold text-slate-800">
                                    Estimasi Durasi Pengerjaan
                                </label>
                                <input
                                    id="estimated_duration"
                                    v-model="processForm.estimated_duration"
                                    type="text"
                                    placeholder="Contoh: 2 Jam, 1 Hari Kerja..."
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2 font-body text-xs text-slate-900 focus:border-ptkp-navy focus:outline-none focus:ring-2 focus:ring-ptkp-navy/20"
                                />
                                <div class="flex flex-wrap gap-1 pt-1">
                                    <button
                                        v-for="dur in durationPresets"
                                        :key="dur"
                                        type="button"
                                        @click="applyDurationPreset(dur)"
                                        class="rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 font-body text-[10px] text-slate-600 hover:border-ptkp-navy hover:text-ptkp-navy cursor-pointer"
                                    >
                                        {{ dur }}
                                    </button>
                                </div>
                            </div>

                            <!-- Hasil Pemeriksaan (4 Pilihan Standar Form IT-FR-01) -->
                            <div class="space-y-2">
                                <label class="block font-heading text-xs font-semibold text-slate-800">
                                    Hasil Pemeriksaan Teknis <span class="text-red-500">*</span>
                                </label>

                                <div class="space-y-2">
                                    <label
                                        class="flex items-start gap-2.5 rounded-xl border p-2.5 transition-all duration-150 cursor-pointer"
                                        :class="[
                                            processForm.examination_result === 'repaired_internally'
                                                ? 'border-ptkp-navy bg-ptkp-mist/60 ring-1 ring-ptkp-navy'
                                                : 'border-slate-200 hover:bg-slate-50',
                                        ]"
                                    >
                                        <input
                                            v-model="processForm.examination_result"
                                            type="radio"
                                            value="repaired_internally"
                                            class="mt-0.5 text-ptkp-navy focus:ring-ptkp-navy"
                                        />
                                        <div>
                                            <span class="block font-heading text-xs font-bold text-slate-900">Perbaikan Mandiri IT</span>
                                            <span class="block font-body text-[11px] text-slate-500">Dikerjakan internal oleh teknisi SMT PTKP.</span>
                                        </div>
                                    </label>

                                    <label
                                        class="flex items-start gap-2.5 rounded-xl border p-2.5 transition-all duration-150 cursor-pointer"
                                        :class="[
                                            processForm.examination_result === 'third_party_vendor'
                                                ? 'border-ptkp-navy bg-ptkp-mist/60 ring-1 ring-ptkp-navy'
                                                : 'border-slate-200 hover:bg-slate-50',
                                        ]"
                                    >
                                        <input
                                            v-model="processForm.examination_result"
                                            type="radio"
                                            value="third_party_vendor"
                                            class="mt-0.5 text-ptkp-navy focus:ring-ptkp-navy"
                                        />
                                        <div>
                                            <span class="block font-heading text-xs font-bold text-slate-900">Pihak Ketiga (Vendor Luar)</span>
                                            <span class="block font-body text-[11px] text-slate-500">Perlu dikirimkan ke service center resmi luar.</span>
                                        </div>
                                    </label>

                                    <label
                                        class="flex items-start gap-2.5 rounded-xl border p-2.5 transition-all duration-150 cursor-pointer"
                                        :class="[
                                            processForm.examination_result === 'part_replacement'
                                                ? 'border-ptkp-navy bg-ptkp-mist/60 ring-1 ring-ptkp-navy'
                                                : 'border-slate-200 hover:bg-slate-50',
                                        ]"
                                    >
                                        <input
                                            v-model="processForm.examination_result"
                                            type="radio"
                                            value="part_replacement"
                                            class="mt-0.5 text-ptkp-navy focus:ring-ptkp-navy"
                                        />
                                        <div>
                                            <span class="block font-heading text-xs font-bold text-slate-900">Penggantian Sparepart</span>
                                            <span class="block font-body text-[11px] text-slate-500">Memerlukan pembelian komponen / part baru.</span>
                                        </div>
                                    </label>

                                    <label
                                        class="flex items-start gap-2.5 rounded-xl border p-2.5 transition-all duration-150 cursor-pointer"
                                        :class="[
                                            processForm.examination_result === 'unrepairable'
                                                ? 'border-ptkp-navy bg-ptkp-mist/60 ring-1 ring-ptkp-navy'
                                                : 'border-slate-200 hover:bg-slate-50',
                                        ]"
                                    >
                                        <input
                                            v-model="processForm.examination_result"
                                            type="radio"
                                            value="unrepairable"
                                            class="mt-0.5 text-ptkp-navy focus:ring-ptkp-navy"
                                        />
                                        <div>
                                            <span class="block font-heading text-xs font-bold text-slate-900">Usul Afkir (Write-off)</span>
                                            <span class="block font-body text-[11px] text-slate-500">Tidak dapat diperbaiki / usulan ganti baru.</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Catatan Teknisi -->
                            <div class="space-y-1.5">
                                <label for="technician_notes" class="block font-heading text-xs font-semibold text-slate-800">
                                    Catatan Tindakan Teknis
                                </label>
                                <textarea
                                    id="technician_notes"
                                    v-model="processForm.technician_notes"
                                    rows="3"
                                    placeholder="Rincian diagnosa, komponen yang diganti, atau instruksi serah terima..."
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2 font-body text-xs text-slate-900 focus:border-ptkp-navy focus:outline-none focus:ring-2 focus:ring-ptkp-navy/20"
                                ></textarea>
                            </div>

                            <!-- Tombol Aksi Simpan & Selesai -->
                            <div class="space-y-2 pt-2 border-t border-slate-100">
                                <button
                                    type="submit"
                                    :disabled="processForm.processing"
                                    class="w-full rounded-xl border border-slate-300 bg-white py-2.5 font-heading text-xs font-bold text-slate-700 shadow-xs hover:bg-slate-50 hover:text-slate-900 transition-colors cursor-pointer"
                                >
                                    {{ processForm.processing ? 'Menyimpan...' : 'Simpan Diagnosis / Catatan' }}
                                </button>

                                <button
                                    v-if="ticket.status !== 'completed'"
                                    type="button"
                                    @click="completeTicket"
                                    :disabled="processForm.processing"
                                    class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-emerald-600 py-2.5 font-heading text-xs font-bold text-white shadow-md hover:bg-emerald-700 transition-all duration-200 active:scale-95 cursor-pointer"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    <span>Tandai Selesai & Serah Terima</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Panel Informasi Status Bagi Pemohon -->
                    <div class="rounded-2xl border border-ptkp-border bg-white p-6 shadow-card">
                        <div class="mb-3 border-b border-slate-100 pb-2">
                            <h2 class="font-heading text-sm font-bold text-slate-900">
                                Ringkasan Status Penanganan
                            </h2>
                        </div>

                        <div class="space-y-3 font-body text-xs">
                            <div class="flex justify-between border-b border-slate-100 pb-2">
                                <span class="text-slate-500">Teknisi Bertugas:</span>
                                <span class="font-heading font-semibold text-slate-800 text-right">
                                    {{ ticket.technician?.name || 'Dalam Antrean' }}
                                </span>
                            </div>

                            <div class="flex justify-between border-b border-slate-100 pb-2">
                                <span class="text-slate-500">Estimasi Waktu:</span>
                                <span class="font-heading font-semibold text-slate-800 text-right">
                                    {{ ticket.estimated_duration || 'Menunggu Pemeriksaan' }}
                                </span>
                            </div>

                            <div class="pt-1">
                                <span class="block text-slate-500 mb-1">Hasil Diagnosis Teknis:</span>
                                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3">
                                    <span class="font-heading text-xs font-bold text-ptkp-navy block">
                                        {{ getExaminationInfo(ticket.examination_result).title }}
                                    </span>
                                    <p class="font-body text-[11px] text-slate-600 mt-0.5">
                                        {{ getExaminationInfo(ticket.examination_result).desc }}
                                    </p>
                                </div>
                            </div>

                            <div v-if="ticket.technician_notes" class="pt-1">
                                <span class="block text-slate-500 mb-1">Catatan Tambahan Teknisi:</span>
                                <div class="rounded-xl bg-slate-50 p-2.5 text-slate-700 border border-slate-200 text-xs whitespace-pre-line">
                                    {{ ticket.technician_notes }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hubungi Bantuan IT SMT -->
                    <div class="rounded-2xl border border-ptkp-border bg-slate-50/70 p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-ptkp-navy text-white font-bold text-sm shadow-xs">
                                SMT
                            </div>
                            <div>
                                <h3 class="font-heading text-xs font-bold text-slate-900">
                                    Unit Teknologi Informasi & SMT
                                </h3>
                                <p class="font-body text-[11px] text-slate-600">
                                    Gedung Kantor PTKP • Ext IT: 104 / 105
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lightbox Modal Foto Bukti Kerusakan -->
        <div
            v-if="showPhotoModal && ticket.photo_file_id"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4 backdrop-blur-xs animate-fadeIn"
            @click.self="showPhotoModal = false"
        >
            <div class="relative max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                    <span class="font-heading text-sm font-bold text-slate-900">
                        Foto Bukti Kerusakan Fisik - Tiket {{ ticket.ticket_number }}
                    </span>
                    <button
                        type="button"
                        @click="showPhotoModal = false"
                        class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 cursor-pointer"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-4 bg-slate-950 flex items-center justify-center">
                    <img
                        :src="`/layanan/work-orders/${ticket.id}/photo`"
                        alt="Foto Bukti Kerusakan Penuh"
                        class="max-h-[75vh] w-auto object-contain rounded-lg"
                    />
                </div>
            </div>
        </div>
    </AppShell>
</template>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fadeIn {
    animation: fadeIn 0.3s ease-out forwards;
}
</style>
