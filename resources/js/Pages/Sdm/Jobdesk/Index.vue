<script setup lang="ts">
import AppBadge from '@/Components/AppBadge.vue';
import AppButton from '@/Components/AppButton.vue';
import AppCard from '@/Components/AppCard.vue';
import AppEmptyState from '@/Components/AppEmptyState.vue';
import AppPagination from '@/Components/AppPagination.vue';
import AppShell from '@/Layouts/AppShell.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface JobItem {
    id: string;
    document_number: string;
    revision_number: number;
    sotk_reference: string;
    position_id: string;
    position_name: string;
    position_code: string;
    job_group: string;
    grade_recommendation: string | null;
    org_unit_id: string;
    org_unit_name: string;
    org_unit_code: string;
    employee_name: string;
    status: 'draft' | 'review_kasi' | 'review_kadep' | 'review_sdm' | 'approved' | 'rejected' | 'obsolete';
    tte_hash: string | null;
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface OrgUnitOption {
    id: string;
    name: string;
    code: string;
    type: string;
}

const props = defineProps<{
    jobs: {
        data: JobItem[];
        current_page: number;
        last_page: number;
        total: number;
        from: number;
        to: number;
        links: PaginationLink[];
    };
    filters: {
        search?: string;
        status?: string;
        org_unit_id?: string;
        job_group?: string;
    };
    orgUnits: OrgUnitOption[];
}>();

const search = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');
const selectedOrgUnit = ref(props.filters.org_unit_id || '');
const selectedJobGroup = ref(props.filters.job_group || '');

const handleFilter = () => {
    router.get(
        route('sdm.jobdesk.index'),
        {
            search: search.value || undefined,
            status: selectedStatus.value || undefined,
            org_unit_id: selectedOrgUnit.value || undefined,
            job_group: selectedJobGroup.value || undefined,
        },
        { preserveState: true, replace: true }
    );
};

const resetFilter = () => {
    search.value = '';
    selectedStatus.value = '';
    selectedOrgUnit.value = '';
    selectedJobGroup.value = '';
    handleFilter();
};

const getStatusBadgeVariant = (status: string): 'navy' | 'violet' | 'success' | 'warning' | 'error' | 'neutral' => {
    switch (status) {
        case 'approved':
            return 'success';
        case 'review_kasi':
        case 'review_kadep':
        case 'review_sdm':
            return 'warning';
        case 'rejected':
        case 'obsolete':
            return 'error';
        case 'draft':
        default:
            return 'neutral';
    }
};

const getStatusLabel = (status: string): string => {
    switch (status) {
        case 'approved':
            return 'Disahkan (Berlaku)';
        case 'review_kasi':
            return 'Review Kasi';
        case 'review_kadep':
            return 'Review Kadep';
        case 'review_sdm':
            return 'Review SDM';
        case 'rejected':
            return 'Perlu Revisi';
        case 'obsolete':
            return 'Kedaluwarsa';
        case 'draft':
        default:
            return 'Draf';
    }
};

const getJobGroupBadge = (group: string): { label: string; variant: 'navy' | 'violet' | 'neutral' } => {
    switch (group) {
        case 'struktural':
            return { label: 'Struktural', variant: 'navy' };
        case 'fungsional':
            return { label: 'Fungsional', variant: 'violet' };
        case 'operasional':
        default:
            return { label: 'Operasional', variant: 'neutral' };
    }
};
</script>

<template>
    <AppShell
        title="Direktori Jobdesk Digital - PT Kertas Padalarang"
        :breadcrumbs="[
            { label: 'Portal', href: '/dashboard' },
            { label: 'SDM & Organisasi', href: route('sdm.jobdesk.index') },
            { label: 'Katalog Jobdesk' }
        ]"
    >
        <div class="space-y-6">
            <!-- Header Banner Peruri Style -->
            <div class="rounded-2xl border border-ptkp-navy/15 bg-gradient-to-r from-ptkp-deep via-ptkp-navy to-[#2a34ad] p-6 text-white shadow-md sm:p-8">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 font-heading text-xs font-semibold tracking-wider text-emerald-300">
                            <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            SOTK SK DIREKSI NO. 009/KPTS/PTKP/IV/2026
                        </div>
                        <h1 class="font-heading text-2xl font-bold tracking-tight sm:text-3xl">
                            Direktori & Katalog Jabatan (Digital Jobdesk)
                        </h1>
                        <p class="max-w-3xl font-body text-sm leading-relaxed text-white/80">
                            Pusat transparansi profil jabatan resmi PT Kertas Padalarang (Peruri Security Paper Mill).
                            Memuat 10 seksi komprehensif: Tugas Pokok, Wewenang, Hubungan Kerja "Dalam Hal", Matriks Kompetensi PL 1–5, KPI, dan Verifikasi Persetujuan Digital.
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center gap-3">
                        <Link :href="route('sdm.jobdesk.create')">
                            <AppButton variant="accent" size="lg" class="shadow-lg">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span>Susun Jobdesk Baru</span>
                            </AppButton>
                        </Link>
                    </div>
                </div>

                <!-- Fast Metric Pills -->
                <div class="mt-6 grid grid-cols-2 gap-3 border-t border-white/15 pt-5 sm:grid-cols-4">
                    <div class="rounded-xl bg-white/10 p-3 backdrop-blur-xs">
                        <span class="block font-body text-xs text-white/70">Total Dokumen Jabatan</span>
                        <span class="font-heading text-xl font-bold text-white">{{ jobs.total }} Posisi</span>
                    </div>
                    <div class="rounded-xl bg-white/10 p-3 backdrop-blur-xs">
                        <span class="block font-body text-xs text-white/70">Berlaku / Disahkan</span>
                        <span class="font-heading text-xl font-bold text-emerald-300">
                            {{ jobs.data.filter(j => j.status === 'approved').length }} Jabatan
                        </span>
                    </div>
                    <div class="rounded-xl bg-white/10 p-3 backdrop-blur-xs">
                        <span class="block font-body text-xs text-white/70">Dalam Proses Review</span>
                        <span class="font-heading text-xl font-bold text-amber-300">
                            {{ jobs.data.filter(j => ['review_kasi', 'review_kadep', 'review_sdm'].includes(j.status)).length }} Jabatan
                        </span>
                    </div>
                    <div class="rounded-xl bg-white/10 p-3 backdrop-blur-xs">
                        <span class="block font-body text-xs text-white/70">Draf Pembaruan</span>
                        <span class="font-heading text-xl font-bold text-white/90">
                            {{ jobs.data.filter(j => j.status === 'draft').length }} Draf
                        </span>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <AppCard>
                <form @submit.prevent="handleFilter" class="flex flex-col gap-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <!-- Pencarian Kata Kunci -->
                        <div>
                            <label class="mb-1 block font-heading text-xs font-semibold text-ptkp-navy">
                                Cari Jabatan / No Dokumen
                            </label>
                            <div class="relative">
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Contoh: Operator, QC, 001/JOB..."
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 pl-9 font-body text-sm text-ptkp-body placeholder:text-slate-400 focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                                    @keyup.enter="handleFilter"
                                />
                                <svg class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Filter Unit Organisasi -->
                        <div>
                            <label class="mb-1 block font-heading text-xs font-semibold text-ptkp-navy">
                                Unit Organisasi (Divisi / Departemen)
                            </label>
                            <select
                                v-model="selectedOrgUnit"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 font-body text-sm text-ptkp-body focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                                @change="handleFilter"
                            >
                                <option value="">Semua Unit Organisasi</option>
                                <option v-for="unit in orgUnits" :key="unit.id" :value="unit.id">
                                    [{{ unit.code }}] {{ unit.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Filter Kelompok Jabatan -->
                        <div>
                            <label class="mb-1 block font-heading text-xs font-semibold text-ptkp-navy">
                                Kelompok Jabatan
                            </label>
                            <select
                                v-model="selectedJobGroup"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 font-body text-sm text-ptkp-body focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                                @change="handleFilter"
                            >
                                <option value="">Semua Kelompok</option>
                                <option value="struktural">Struktural (Manajemen & Pimpinan)</option>
                                <option value="fungsional">Fungsional (Spesialis & Ahli)</option>
                                <option value="operasional">Operasional (Pelaksana Pabrik)</option>
                            </select>
                        </div>

                        <!-- Filter Status -->
                        <div>
                            <label class="mb-1 block font-heading text-xs font-semibold text-ptkp-navy">
                                Status Dokumen
                            </label>
                            <select
                                v-model="selectedStatus"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 font-body text-sm text-ptkp-body focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                                @change="handleFilter"
                            >
                                <option value="">Semua Status</option>
                                <option value="approved">Disahkan (Berlaku)</option>
                                <option value="review_kasi">Review Kasi</option>
                                <option value="review_kadep">Review Kadep</option>
                                <option value="review_sdm">Review SDM</option>
                                <option value="draft">Draf</option>
                                <option value="rejected">Perlu Revisi</option>
                                <option value="obsolete">Kedaluwarsa</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                        <AppButton
                            v-if="search || selectedStatus || selectedOrgUnit || selectedJobGroup"
                            variant="secondary"
                            size="sm"
                            @click="resetFilter"
                        >
                            Reset Filter
                        </AppButton>
                        <AppButton variant="primary" size="sm" type="submit">
                            Terapkan Filter
                        </AppButton>
                    </div>
                </form>
            </AppCard>

            <!-- Table Card / Catalog List ala Telkom University -->
            <AppCard>
                <div v-if="jobs.data.length === 0" class="py-12">
                    <AppEmptyState
                        title="Tidak Ada Dokumen Jabatan Ditemukan"
                        description="Gunakan kata kunci pencarian yang berbeda atau buat draf profil jabatan baru untuk unit organisasi ini."
                    >
                        <template #action>
                            <Link :href="route('sdm.jobdesk.create')">
                                <AppButton variant="primary">
                                    Susun Jobdesk Baru
                                </AppButton>
                            </Link>
                        </template>
                    </AppEmptyState>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full border-collapse text-left font-body text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 font-heading text-xs font-bold tracking-wider text-ptkp-navy">
                                <th class="px-4 py-3.5 text-center w-12">NO</th>
                                <th class="px-4 py-3.5">NAMA JABATAN & KODE</th>
                                <th class="px-4 py-3.5">UNIT KERJA (DIVISI/DEPARTEMEN)</th>
                                <th class="px-4 py-3.5">KELOMPOK & GRADE</th>
                                <th class="px-4 py-3.5">NO. DOKUMEN / REVISI</th>
                                <th class="px-4 py-3.5 text-center">STATUS</th>
                                <th class="px-4 py-3.5 text-center w-36">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="(job, index) in jobs.data"
                                :key="job.id"
                                class="transition-colors hover:bg-slate-50/80 group"
                            >
                                <td class="px-4 py-3.5 text-center font-medium text-slate-500 text-xs">
                                    {{ (jobs.from || 1) + index }}
                                </td>

                                <!-- Nama Jabatan & Kode -->
                                <td class="px-4 py-3.5">
                                    <div class="flex flex-col">
                                        <Link
                                            :href="route('sdm.jobdesk.show', job.id)"
                                            class="font-heading font-bold text-ptkp-navy hover:text-ptkp-violet hover:underline flex items-center gap-1.5"
                                        >
                                            <span>{{ job.position_name }}</span>
                                            <svg class="h-3.5 w-3.5 opacity-0 group-hover:opacity-100 text-ptkp-violet transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </Link>
                                        <div class="mt-0.5 flex items-center gap-2 text-xs text-slate-500">
                                            <span class="font-mono font-medium text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded">
                                                {{ job.position_code }}
                                            </span>
                                            <span>•</span>
                                            <span class="italic text-slate-500">
                                                Pemangku: {{ job.employee_name }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Unit Kerja -->
                                <td class="px-4 py-3.5">
                                    <div class="flex flex-col text-xs">
                                        <span class="font-semibold text-ptkp-body">{{ job.org_unit_name }}</span>
                                        <span class="text-slate-400 font-mono">{{ job.org_unit_code }}</span>
                                    </div>
                                </td>

                                <!-- Kelompok & Grade -->
                                <td class="px-4 py-3.5">
                                    <div class="flex flex-col gap-1 items-start">
                                        <AppBadge :variant="getJobGroupBadge(job.job_group).variant" size="sm">
                                            {{ getJobGroupBadge(job.job_group).label }}
                                        </AppBadge>
                                        <span v-if="job.grade_recommendation" class="font-mono text-xs text-slate-600 font-medium">
                                            Grade: {{ job.grade_recommendation }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Dokumen & Revisi -->
                                <td class="px-4 py-3.5">
                                    <div class="flex flex-col text-xs">
                                        <span class="font-mono font-semibold text-slate-700">
                                            {{ job.document_number }}
                                        </span>
                                        <span class="text-slate-500">
                                            Rev. {{ job.revision_number }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-4 py-3.5 text-center">
                                    <AppBadge :variant="getStatusBadgeVariant(job.status)" size="sm">
                                        {{ getStatusLabel(job.status) }}
                                    </AppBadge>
                                    <div v-if="job.tte_hash" class="mt-1 flex items-center justify-center gap-1 text-[10px] text-emerald-600 font-mono" title="Tervalidasi BSrE">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        <span>TTE Aktif</span>
                                    </div>
                                </td>

                                <!-- Aksi -->
                                <td class="px-4 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Lihat Pratinjau Dokumen -->
                                        <Link :href="route('sdm.jobdesk.show', job.id)" title="Buka Dokumen Resmi">
                                            <button class="rounded-md border border-slate-200 bg-white p-1.5 text-ptkp-navy hover:bg-ptkp-mist hover:border-ptkp-navy/30 transition-colors shadow-2xs">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            </button>
                                        </Link>

                                        <!-- Edit Jobdesk -->
                                        <Link :href="route('sdm.jobdesk.edit', job.id)" title="Edit Profil Jabatan">
                                            <button class="rounded-md border border-slate-200 bg-white p-1.5 text-ptkp-navy hover:bg-ptkp-mist hover:border-ptkp-navy/30 transition-colors shadow-2xs">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                            </button>
                                        </Link>

                                        <!-- Cetak Lembar A4 -->
                                        <a :href="route('sdm.jobdesk.print', job.id)" target="_blank" title="Cetak Lembar A4 Resmi">
                                            <button class="rounded-md border border-slate-200 bg-white p-1.5 text-ptkp-navy hover:bg-ptkp-mist hover:border-ptkp-navy/30 transition-colors shadow-2xs">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.04-.37-2.122-.37-3.229 0-4.418 3.582-8 8-8s8 3.582 8 8c0 1.107-.13 2.189-.37 3.229M6 18h12a2 2 0 002-2v-4a2 2 0 00-2-2H6a2 2 0 00-2 2v4a2 2 0 002 2zm2 0v4h8v-4" />
                                                </svg>
                                            </button>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Pagination -->
                <div class="mt-4 border-t border-slate-100 pt-4">
                    <AppPagination
                        :links="jobs.links"
                        :from="jobs.from"
                        :to="jobs.to"
                        :total="jobs.total"
                    />
                </div>
            </AppCard>
        </div>
    </AppShell>
</template>
