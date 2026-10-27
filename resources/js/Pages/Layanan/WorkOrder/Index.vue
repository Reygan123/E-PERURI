<script setup lang="ts">
import AppBadge from '@/Components/AppBadge.vue';
import AppButton from '@/Components/AppButton.vue';
import AppCard from '@/Components/AppCard.vue';
import AppEmptyState from '@/Components/AppEmptyState.vue';
import AppPagination from '@/Components/AppPagination.vue';
import AppShell from '@/Layouts/AppShell.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface DeviceType {
    id: string;
    name: string;
    code: string;
}

interface WorkOrderItem {
    id: string;
    ticket_number: string;
    device_name: string;
    it_inventory_code: string | null;
    section_name: string;
    issue_description: string;
    status: 'pending' | 'in_progress' | 'completed' | 'cancelled';
    submitted_at: string;
    started_at: string | null;
    completed_at: string | null;
    device_type: DeviceType;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    workOrders: {
        data: WorkOrderItem[];
        current_page: number;
        last_page: number;
        total: number;
        from: number | null;
        to: number | null;
        links: PaginationLink[];
    };
    metrics: {
        total: number;
        pending: number;
        in_progress: number;
        completed: number;
    };
    filters: {
        status?: string;
        device_type_id?: string;
        search?: string;
    };
    deviceTypes: DeviceType[];
    isTechnician: boolean;
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');
const selectedDeviceType = ref(props.filters.device_type_id || '');

const applyFilters = (statusOverride?: string) => {
    if (statusOverride !== undefined) {
        selectedStatus.value = statusOverride;
    }

    router.get(
        '/layanan/work-orders',
        {
            search: search.value || undefined,
            status: selectedStatus.value || undefined,
            device_type_id: selectedDeviceType.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const resetFilters = () => {
    search.value = '';
    selectedStatus.value = '';
    selectedDeviceType.value = '';
    applyFilters('');
};

const hasActiveFilters = computed(() => {
    return Boolean(search.value || selectedStatus.value || selectedDeviceType.value);
});

const formatDate = (dateStr: string): string => {
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

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'pending':
            return {
                label: 'Menunggu Antrean',
                variant: 'warning' as const,
                classes: 'bg-amber-50 text-amber-800 border-amber-300 ring-1 ring-amber-200/70',
                dotClass: 'bg-amber-500',
            };
        case 'in_progress':
            return {
                label: 'Sedang Dikerjakan',
                variant: 'navy' as const,
                classes: 'bg-blue-50 text-ptkp-navy border-blue-200 ring-1 ring-blue-200/70',
                dotClass: 'bg-ptkp-navy animate-pulse',
            };
        case 'completed':
            return {
                label: 'Selesai',
                variant: 'success' as const,
                classes: 'bg-emerald-50 text-emerald-800 border-emerald-300 ring-1 ring-emerald-200/70',
                dotClass: 'bg-emerald-600',
            };
        case 'cancelled':
            return {
                label: 'Dibatalkan',
                variant: 'neutral' as const,
                classes: 'bg-slate-100 text-slate-700 border-slate-300 ring-1 ring-slate-200',
                dotClass: 'bg-slate-400',
            };
        default:
            return {
                label: status,
                variant: 'neutral' as const,
                classes: 'bg-slate-50 text-slate-700 border-slate-200',
                dotClass: 'bg-slate-400',
            };
    }
};
</script>

<template>
    <AppShell
        title="Papan Antrean Layanan IT (SI_WOPPIT) - PT Kertas Padalarang"
        :breadcrumbs="[
            { label: 'Portal', href: '/dashboard' },
            { label: 'Layanan IT', href: '/layanan/work-orders' },
            { label: 'Papan Antrean' },
        ]"
    >
        <div class="space-y-6 animate-fadeIn">
            <!-- ========================================== -->
            <!-- 1. EXECUTIVE CORPORATE BANNER (PERURI)     -->
            <!-- ========================================== -->
            <div class="relative overflow-hidden rounded-2xl border border-ptkp-navy/20 bg-gradient-to-r from-ptkp-deep via-ptkp-navy to-[#2631a3] p-6 text-white shadow-lg sm:p-8">
                <!-- Watermark Background Geometric Deco -->
                <div class="pointer-events-none absolute -right-8 -top-12 h-64 w-64 rounded-full bg-white/5 blur-2xl"></div>
                <div class="pointer-events-none absolute -bottom-10 right-40 h-48 w-48 rounded-full bg-ptkp-violet/10 blur-xl"></div>

                <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-3xl space-y-3">
                        <!-- Quality Form Badge & Status Indicator -->
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/10 px-3 py-1 font-heading text-xs font-semibold tracking-wider text-emerald-300 backdrop-blur-xs">
                                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                FORMULIR MUTU IT-FR-01/REV00
                            </span>
                            <span class="rounded-full bg-white/10 px-3 py-1 font-body text-xs font-medium text-white/80 backdrop-blur-xs">
                                Unit Teknologi Informasi & SMT
                            </span>
                        </div>

                        <!-- Main Heading -->
                        <h1 class="font-heading text-2xl font-bold tracking-tight text-white sm:text-3xl lg:text-4xl">
                            Papan Antrean Layanan IT (SI_WOPPIT)
                        </h1>

                        <!-- Explanatory Subtitle -->
                        <p class="font-body text-sm leading-relaxed text-white/85 sm:text-base">
                            Pusat kendali operasional penanganan keluhan dan perbaikan perangkat komputer, printer sekuriti, infrastruktur jaringan pabrik, dan aplikasi internal PT Kertas Padalarang.
                        </p>
                    </div>

                    <!-- Call To Action Button -->
                    <div class="flex shrink-0 items-center gap-3">
                        <Link href="/layanan/work-orders/create">
                            <AppButton variant="accent" size="lg" class="shadow-lg hover:shadow-xl transition-all">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span>Ajukan Permohonan IT</span>
                            </AppButton>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- 2. INTERACTIVE OPERATIONAL METRIC CARDS   -->
            <!-- ========================================== -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <!-- Card 1: Total Tiket Masuk -->
                <button
                    type="button"
                    class="group relative flex flex-col justify-between rounded-xl border bg-white p-5 text-left transition-all duration-150 hover:-translate-y-0.5 hover:shadow-md cursor-pointer"
                    :class="[
                        !selectedStatus
                            ? 'border-ptkp-navy ring-2 ring-ptkp-navy/20 shadow-xs'
                            : 'border-slate-200/90 shadow-2xs hover:border-ptkp-navy/40',
                    ]"
                    @click="applyFilters('')"
                >
                    <div class="flex items-center justify-between">
                        <span class="font-heading text-xs font-bold uppercase tracking-wider text-slate-500 group-hover:text-ptkp-navy">
                            Total Tiket Masuk
                        </span>
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-ptkp-navy/10 text-ptkp-navy transition-colors group-hover:bg-ptkp-navy group-hover:text-white">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </span>
                    </div>

                    <div class="mt-4">
                        <div class="flex items-baseline gap-2">
                            <span class="font-heading text-3xl font-extrabold text-ptkp-deep">
                                {{ metrics.total }}
                            </span>
                            <span class="font-body text-xs font-semibold text-slate-500">Berkas</span>
                        </div>
                        <p class="mt-1 font-body text-xs text-slate-500">
                            Semua pengajuan perbaikan aktif
                        </p>
                    </div>

                    <div class="mt-3 flex items-center gap-1 border-t border-slate-100 pt-2 text-[11px] font-medium text-slate-600">
                        <span :class="!selectedStatus ? 'text-ptkp-navy font-bold' : 'text-slate-500'">
                            {{ !selectedStatus ? '● Sedang Ditampilkan' : 'Klik untuk tampilkan semua' }}
                        </span>
                    </div>
                </button>

                <!-- Card 2: Menunggu Antrean (Pending) -->
                <button
                    type="button"
                    class="group relative flex flex-col justify-between rounded-xl border bg-white p-5 text-left transition-all duration-150 hover:-translate-y-0.5 hover:shadow-md cursor-pointer"
                    :class="[
                        selectedStatus === 'pending'
                            ? 'border-amber-500 ring-2 ring-amber-500/20 bg-amber-50/15 shadow-xs'
                            : 'border-slate-200/90 shadow-2xs hover:border-amber-400',
                    ]"
                    @click="applyFilters('pending')"
                >
                    <div class="flex items-center justify-between">
                        <span class="font-heading text-xs font-bold uppercase tracking-wider text-amber-700">
                            Menunggu Antrean
                        </span>
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-700 transition-colors group-hover:bg-amber-600 group-hover:text-white">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>

                    <div class="mt-4">
                        <div class="flex items-baseline gap-2">
                            <span class="font-heading text-3xl font-extrabold text-amber-600">
                                {{ metrics.pending }}
                            </span>
                            <span class="font-body text-xs font-semibold text-amber-800">Tiket</span>
                        </div>
                        <p class="mt-1 font-body text-xs text-slate-500">
                            Perlu penanganan oleh teknisi
                        </p>
                    </div>

                    <div class="mt-3 flex items-center gap-1 border-t border-slate-100 pt-2 text-[11px] font-medium text-amber-800">
                        <span :class="selectedStatus === 'pending' ? 'font-bold' : 'text-slate-500'">
                            {{ selectedStatus === 'pending' ? '● Filter Aktif' : 'Klik untuk filter pending' }}
                        </span>
                    </div>
                </button>

                <!-- Card 3: Sedang Dikerjakan (In Progress) -->
                <button
                    type="button"
                    class="group relative flex flex-col justify-between rounded-xl border bg-white p-5 text-left transition-all duration-150 hover:-translate-y-0.5 hover:shadow-md cursor-pointer"
                    :class="[
                        selectedStatus === 'in_progress'
                            ? 'border-blue-600 ring-2 ring-blue-600/20 bg-blue-50/15 shadow-xs'
                            : 'border-slate-200/90 shadow-2xs hover:border-blue-400',
                    ]"
                    @click="applyFilters('in_progress')"
                >
                    <div class="flex items-center justify-between">
                        <span class="font-heading text-xs font-bold uppercase tracking-wider text-blue-700">
                            Sedang Dikerjakan
                        </span>
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-700 transition-colors group-hover:bg-blue-600 group-hover:text-white">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </span>
                    </div>

                    <div class="mt-4">
                        <div class="flex items-baseline gap-2">
                            <span class="font-heading text-3xl font-extrabold text-blue-700">
                                {{ metrics.in_progress }}
                            </span>
                            <span class="font-body text-xs font-semibold text-blue-800">Tiket</span>
                        </div>
                        <p class="mt-1 font-body text-xs text-slate-500">
                            Dalam pengerjaan aktif teknisi IT
                        </p>
                    </div>

                    <div class="mt-3 flex items-center gap-1 border-t border-slate-100 pt-2 text-[11px] font-medium text-blue-800">
                        <span :class="selectedStatus === 'in_progress' ? 'font-bold' : 'text-slate-500'">
                            {{ selectedStatus === 'in_progress' ? '● Filter Aktif' : 'Klik untuk filter pengerjaan' }}
                        </span>
                    </div>
                </button>

                <!-- Card 4: Selesai Diperbaiki (Completed) -->
                <button
                    type="button"
                    class="group relative flex flex-col justify-between rounded-xl border bg-white p-5 text-left transition-all duration-150 hover:-translate-y-0.5 hover:shadow-md cursor-pointer"
                    :class="[
                        selectedStatus === 'completed'
                            ? 'border-emerald-600 ring-2 ring-emerald-600/20 bg-emerald-50/15 shadow-xs'
                            : 'border-slate-200/90 shadow-2xs hover:border-emerald-400',
                    ]"
                    @click="applyFilters('completed')"
                >
                    <div class="flex items-center justify-between">
                        <span class="font-heading text-xs font-bold uppercase tracking-wider text-emerald-700">
                            Selesai Diperbaiki
                        </span>
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 transition-colors group-hover:bg-emerald-600 group-hover:text-white">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>

                    <div class="mt-4">
                        <div class="flex items-baseline gap-2">
                            <span class="font-heading text-3xl font-extrabold text-emerald-700">
                                {{ metrics.completed }}
                            </span>
                            <span class="font-body text-xs font-semibold text-emerald-800">Tiket</span>
                        </div>
                        <p class="mt-1 font-body text-xs text-slate-500">
                            Siap cetak lembar serah terima
                        </p>
                    </div>

                    <div class="mt-3 flex items-center gap-1 border-t border-slate-100 pt-2 text-[11px] font-medium text-emerald-800">
                        <span :class="selectedStatus === 'completed' ? 'font-bold' : 'text-slate-500'">
                            {{ selectedStatus === 'completed' ? '● Filter Aktif' : 'Klik untuk filter selesai' }}
                        </span>
                    </div>
                </button>
            </div>

            <!-- ========================================== -->
            <!-- 3. SEARCH & SEGMENTED FILTER BAR           -->
            <!-- ========================================== -->
            <AppCard class="p-5">
                <!-- Status Tab Segmented Control -->
                <div class="flex flex-wrap items-center gap-2 border-b border-slate-200/80 pb-4">
                    <button
                        type="button"
                        class="rounded-lg px-3.5 py-1.5 font-heading text-xs font-bold transition-colors cursor-pointer"
                        :class="[
                            !selectedStatus
                                ? 'bg-ptkp-navy text-white shadow-xs'
                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900',
                        ]"
                        @click="applyFilters('')"
                    >
                        Semua Status ({{ metrics.total }})
                    </button>
                    <button
                        type="button"
                        class="rounded-lg px-3.5 py-1.5 font-heading text-xs font-bold transition-colors cursor-pointer flex items-center gap-1.5"
                        :class="[
                            selectedStatus === 'pending'
                                ? 'bg-amber-500 text-white shadow-xs'
                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900',
                        ]"
                        @click="applyFilters('pending')"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400" v-if="selectedStatus !== 'pending'"></span>
                        Menunggu Antrean ({{ metrics.pending }})
                    </button>
                    <button
                        type="button"
                        class="rounded-lg px-3.5 py-1.5 font-heading text-xs font-bold transition-colors cursor-pointer flex items-center gap-1.5"
                        :class="[
                            selectedStatus === 'in_progress'
                                ? 'bg-blue-600 text-white shadow-xs'
                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900',
                        ]"
                        @click="applyFilters('in_progress')"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-blue-400" v-if="selectedStatus !== 'in_progress'"></span>
                        Sedang Dikerjakan ({{ metrics.in_progress }})
                    </button>
                    <button
                        type="button"
                        class="rounded-lg px-3.5 py-1.5 font-heading text-xs font-bold transition-colors cursor-pointer flex items-center gap-1.5"
                        :class="[
                            selectedStatus === 'completed'
                                ? 'bg-emerald-600 text-white shadow-xs'
                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900',
                        ]"
                        @click="applyFilters('completed')"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400" v-if="selectedStatus !== 'completed'"></span>
                        Selesai Diperbaiki ({{ metrics.completed }})
                    </button>
                </div>

                <!-- Input & Dropdown Controls -->
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-12 md:items-end">
                    <!-- Search Input (7 cols) -->
                    <div class="md:col-span-7">
                        <label class="mb-1 block font-heading text-xs font-bold text-ptkp-navy">
                            Cari Tiket / Peralatan / Ruangan
                        </label>
                        <div class="relative">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Ketik nomor tiket (IT001), nama komputer/printer, barcode, atau seksi..."
                                class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 pl-10 font-body text-sm text-slate-900 placeholder-slate-400 focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                                @keyup.enter="applyFilters()"
                            />
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Kategori Perangkat (3 cols) -->
                    <div class="md:col-span-3">
                        <label class="mb-1 block font-heading text-xs font-bold text-ptkp-navy">
                            Kategori Peralatan IT
                        </label>
                        <select
                            v-model="selectedDeviceType"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 font-body text-sm text-slate-800 focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                            @change="applyFilters()"
                        >
                            <option value="">Semua Kategori Peralatan</option>
                            <option v-for="dev in deviceTypes" :key="dev.id" :value="dev.id">
                                {{ dev.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Action Buttons (2 cols) -->
                    <div class="flex items-center gap-2 md:col-span-2">
                        <AppButton
                            variant="primary"
                            size="md"
                            class="w-full justify-center"
                            @click="applyFilters()"
                        >
                            <span>Terapkan</span>
                        </AppButton>
                        <button
                            v-if="hasActiveFilters"
                            type="button"
                            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-slate-100 hover:text-red-600 transition-colors cursor-pointer"
                            title="Reset Semua Filter"
                            @click="resetFilters"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </AppCard>

            <!-- ========================================== -->
            <!-- 4. DATA TABLE SECTION (BESPOKE LAYOUT)     -->
            <!-- ========================================== -->
            <AppCard class="overflow-hidden">
                <!-- Empty State -->
                <div v-if="workOrders.data.length === 0" class="py-12">
                    <AppEmptyState
                        title="Tidak Ada Tiket Ditemukan"
                        description="Belum ada tiket antrean yang terdaftar atau kriteria pencarian tidak cocok dengan tiket manapun."
                    >
                        <template #action>
                            <div class="flex items-center gap-3">
                                <AppButton
                                    v-if="hasActiveFilters"
                                    variant="secondary"
                                    @click="resetFilters"
                                >
                                    Reset Filter Pencarian
                                </AppButton>
                                <Link href="/layanan/work-orders/create">
                                    <AppButton variant="primary">
                                        Ajukan Tiket Baru
                                    </AppButton>
                                </Link>
                            </div>
                        </template>
                    </AppEmptyState>
                </div>

                <!-- Table Content -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full border-collapse text-left font-body text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/90 font-heading text-xs font-bold uppercase tracking-wider text-ptkp-navy">
                                <th class="px-5 py-4 w-32">No. Tiket</th>
                                <th class="px-5 py-4 w-40">Tanggal Masuk</th>
                                <th class="px-5 py-4">Peralatan IT & Kategori</th>
                                <th class="px-5 py-4">Seksi / Lokasi</th>
                                <th class="px-5 py-4">Uraian Gejala Kerusakan</th>
                                <th class="px-5 py-4 text-center w-36">Status</th>
                                <th class="px-5 py-4 text-center w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="item in workOrders.data"
                                :key="item.id"
                                class="transition-colors hover:bg-slate-50/90 group"
                            >
                                <!-- No. Tiket -->
                                <td class="whitespace-nowrap px-5 py-4">
                                    <Link
                                        :href="`/layanan/work-orders/${item.id}`"
                                        class="inline-flex items-center gap-1.5 rounded-md bg-ptkp-navy/5 px-2.5 py-1 font-mono text-xs font-bold text-ptkp-navy border border-ptkp-navy/15 group-hover:bg-ptkp-navy group-hover:text-white transition-colors"
                                    >
                                        <span>{{ item.ticket_number }}</span>
                                    </Link>
                                    <span class="block mt-1 font-mono text-[10px] text-slate-600">
                                        IT-FR-01
                                    </span>
                                </td>

                                <!-- Tanggal Pengajuan -->
                                <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-600">
                                    <div class="font-semibold text-slate-800">
                                        {{ formatDate(item.submitted_at) }}
                                    </div>
                                    <div class="text-[11px] text-slate-600">
                                        {{ item.completed_at ? 'Selesai: ' + formatDate(item.completed_at) : 'Dalam antrean' }}
                                    </div>
                                </td>

                                <!-- Peralatan IT -->
                                <td class="px-5 py-4">
                                    <Link
                                        :href="`/layanan/work-orders/${item.id}`"
                                        class="font-heading font-bold text-slate-900 group-hover:text-ptkp-navy hover:underline block"
                                    >
                                        {{ item.device_name }}
                                    </Link>
                                    <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                        <span class="inline-flex items-center rounded bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700">
                                            {{ item.device_type?.name || 'Aset IT' }}
                                        </span>
                                        <span v-if="item.it_inventory_code" class="inline-flex items-center rounded border border-slate-200 bg-white px-2 py-0.5 font-mono text-[10px] text-slate-600">
                                            🏷️ {{ item.it_inventory_code }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Seksi / Ruangan -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-1.5 font-heading text-xs font-semibold text-slate-800">
                                        <svg class="h-3.5 w-3.5 text-slate-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>{{ item.section_name }}</span>
                                    </div>
                                </td>

                                <!-- Uraian Kerusakan -->
                                <td class="max-w-xs truncate px-5 py-4 font-body text-xs text-slate-600" :title="item.issue_description">
                                    <span class="line-clamp-2">
                                        {{ item.issue_description }}
                                    </span>
                                </td>

                                <!-- Status Badge -->
                                <td class="whitespace-nowrap px-5 py-4 text-center">
                                    <span
                                        :class="[
                                            'inline-flex items-center gap-1.5 rounded-full border px-3 py-1 font-heading text-xs font-semibold shadow-2xs',
                                            getStatusBadge(item.status).classes,
                                        ]"
                                    >
                                        <span :class="['h-1.5 w-1.5 rounded-full', getStatusBadge(item.status).dotClass]"></span>
                                        {{ getStatusBadge(item.status).label }}
                                    </span>
                                </td>

                                <!-- Aksi Cepat -->
                                <td class="whitespace-nowrap px-5 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <Link
                                            :href="`/layanan/work-orders/${item.id}`"
                                            class="inline-flex items-center gap-1 rounded-lg bg-ptkp-navy px-3 py-1.5 font-heading text-xs font-bold text-white shadow-xs hover:bg-ptkp-deep transition-all"
                                            title="Buka Lembar Kerja & Rincian"
                                        >
                                            <span>Detail</span>
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </Link>

                                        <a
                                            :href="`/layanan/work-orders/${item.id}/print`"
                                            target="_blank"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-2xs hover:bg-slate-100 hover:text-slate-900 transition-colors"
                                            title="Cetak Formulir Standar IT-FR-01 (A4)"
                                        >
                                            <svg class="h-4 w-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div v-if="workOrders.total > workOrders.data.length" class="border-t border-slate-200/80 bg-slate-50/50 p-4">
                    <AppPagination :links="workOrders.links" />
                </div>
            </AppCard>
        </div>
    </AppShell>
</template>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(6px);
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
