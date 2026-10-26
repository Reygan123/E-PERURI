<script setup lang="ts">
import AppBadge from '@/Components/AppBadge.vue';
import AppButton from '@/Components/AppButton.vue';
import AppModal from '@/Components/AppModal.vue';
import AppShell from '@/Layouts/AppShell.vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface JobTask {
    id?: string;
    activity_name: string;
    activity_details: string;
    sort_order?: number;
}

interface WorkingRelationItem {
    id?: string;
    entity: string;
    matter: string;
}

interface JobCompetency {
    id?: string;
    sub_competency: string;
    proficiency_level: number;
    key_indicator: string;
    sort_order?: number;
}

interface JobSpecification {
    education_level?: string | null;
    work_experience?: string | null;
    certifications?: string | null;
    trainings?: string | null;
    hard_skills?: string[];
    soft_skills?: string[];
}

interface JobStandard {
    id?: string;
    indicator_name: string;
    target_value: string;
    sort_order?: number;
}

interface JobApproval {
    id?: string;
    stage: string;
    role_title: string;
    approver_name: string;
    decision: 'pending' | 'approved' | 'rejected';
    notes?: string | null;
    decided_at?: string | null;
}

interface JobDetail {
    id: string;
    document_number: string;
    revision_number: number;
    sotk_reference: string;
    status: 'draft' | 'in_review' | 'approved' | 'rejected' | 'obsolete';
    position_id: string;
    position_name: string;
    position_code: string;
    job_group: string;
    grade_recommendation: string;
    job_summary: string;
    direct_superior_id?: string | null;
    direct_superior_name?: string | null;
    subordinate_notes?: string | null;
    operating_challenges?: string | null;
    environmental_conditions?: string | null;
    incidental_duties?: string | null;
    tte_hash?: string | null;
    tte_signed_at?: string | null;
    created_by?: string;
    created_at?: string;
    org_unit: {
        id?: string | null;
        name: string;
        code: string;
        type?: string;
        parent_name?: string | null;
        grandparent_name?: string | null;
    };
    employee?: {
        id?: string | null;
        nip?: string;
        name: string;
        email?: string;
    } | null;
    tasks: JobTask[];
    governances: {
        responsibilities: string[];
        authorities: string[];
    };
    working_relations: {
        internal: WorkingRelationItem[];
        external: WorkingRelationItem[];
    };
    competencies: JobCompetency[];
    specification?: JobSpecification | null;
    standards: JobStandard[];
    approvals: JobApproval[];
}

const props = defineProps<{
    job: JobDetail;
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user as Record<string, any> | undefined);

// Modal Persetujuan / Catatan Revisi
const isApprovalModalOpen = ref(false);
const approvalDecision = ref<'approved' | 'rejected'>('approved');

const approvalForm = useForm({
    stage: 'checker',
    decision: 'approved',
    notes: '',
});

const openApprovalModal = (decision: 'approved' | 'rejected') => {
    approvalDecision.value = decision;
    approvalForm.decision = decision;

    // Ambil tahap persetujuan yang sedang pending jika ada, atau default ke approver
    const pendingApproval = props.job.approvals?.find(a => a.decision === 'pending');
    approvalForm.stage = pendingApproval?.stage || 'approver';
    approvalForm.notes = '';
    isApprovalModalOpen.value = true;
};

const submitApprovalDecision = () => {
    approvalForm.post(route('sdm.jobdesk.approve', props.job.id), {
        onSuccess: () => {
            isApprovalModalOpen.value = false;
        },
    });
};

const submitForReview = () => {
    if (confirm('Ajukan dokumen profil jabatan ini untuk verifikasi atasan berjenjang?')) {
        router.post(route('sdm.jobdesk.submit', props.job.id));
    }
};

const getStatusBadgeVariant = (status: string): 'navy' | 'violet' | 'success' | 'warning' | 'error' | 'neutral' => {
    switch (status) {
        case 'approved':
            return 'success';
        case 'in_review':
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
        case 'in_review':
            return 'Dalam Review Atasan';
        case 'rejected':
            return 'Perlu Revisi';
        case 'obsolete':
            return 'Kedaluwarsa';
        case 'draft':
        default:
            return 'Draf';
    }
};

const getPLDescription = (level: number): string => {
    switch (level) {
        case 1:
            return 'PL 1: Basic / Pemahaman Dasar';
        case 2:
            return 'PL 2: Capable / Praktisi Mandiri';
        case 3:
            return 'PL 3: Experienced / Mahir & Pembimbing';
        case 4:
            return 'PL 4: Advanced / Analis Ahli & Strategis';
        case 5:
            return 'PL 5: Expert / Pemimpin Transformasi';
        default:
            return `PL ${level}`;
    }
};
</script>

<template>
    <AppShell
        :title="`${job.position_name} - Job Description PTKP`"
        :breadcrumbs="[
            { label: 'Portal', href: '/dashboard' },
            { label: 'SDM & Organisasi', href: route('sdm.jobdesk.index') },
            { label: 'Katalog Jobdesk', href: route('sdm.jobdesk.index') },
            { label: job.position_name }
        ]"
    >
        <div class="space-y-6">
            <!-- Top Command Toolbar -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-xl bg-white p-4 shadow-xs border border-slate-200">
                <div class="flex flex-wrap items-center gap-3">
                    <Link :href="route('sdm.jobdesk.index')">
                        <AppButton variant="secondary" size="sm">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Kembali ke Katalog</span>
                        </AppButton>
                    </Link>

                    <div class="h-4 w-[1px] bg-slate-300"></div>

                    <div class="flex items-center gap-2">
                        <span class="font-mono text-xs font-bold text-ptkp-navy">{{ job.document_number }}</span>
                        <span class="text-xs text-slate-500 font-medium">Rev. {{ job.revision_number }}</span>
                        <AppBadge :variant="getStatusBadgeVariant(job.status)" size="sm">
                            {{ getStatusLabel(job.status) }}
                        </AppBadge>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Cetak A4 Presisi -->
                    <a :href="route('sdm.jobdesk.print', job.id)" target="_blank">
                        <AppButton variant="secondary" size="sm">
                            <svg class="h-4 w-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Cetak Dokumen Resmi A4</span>
                        </AppButton>
                    </a>

                    <!-- Tombol Edit (bila draft/rejected atau SDM) -->
                    <Link :href="route('sdm.jobdesk.edit', job.id)">
                        <AppButton variant="secondary" size="sm">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>Edit Jobdesk</span>
                        </AppButton>
                    </Link>

                    <!-- Tombol Ajukan Review (jika status draft atau rejected) -->
                    <AppButton
                        v-if="['draft', 'rejected'].includes(job.status)"
                        variant="accent"
                        size="sm"
                        @click="submitForReview"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Ajukan Review Atasan</span>
                    </AppButton>

                    <!-- Tombol Verifikasi / Setujui (jika dalam review) -->
                    <template v-if="job.status === 'in_review'">
                        <AppButton
                            variant="secondary"
                            size="sm"
                            class="text-red-700 hover:bg-red-50 hover:border-red-200"
                            @click="openApprovalModal('rejected')"
                        >
                            <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>Minta Revisi</span>
                        </AppButton>

                        <AppButton
                            variant="primary"
                            size="sm"
                            @click="openApprovalModal('approved')"
                        >
                            <svg class="h-4 w-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Setujui & Verifikasi</span>
                        </AppButton>
                    </template>
                </div>
            </div>

            <!-- Main Corporate Sheet (A4 Container Styling) -->
            <div class="mx-auto max-w-5xl rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <!-- Header Kop Dokumen Resmi PTKP -->
                <div class="border-b-2 border-ptkp-navy bg-gradient-to-r from-ptkp-deep to-ptkp-navy p-6 text-white sm:p-8">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex items-center gap-2 text-xs font-semibold tracking-wider text-emerald-300">
                                <span>PT KERTAS PADALARANG</span>
                                <span>•</span>
                                <span>PERURI SECURITY PAPER MILL</span>
                            </div>
                            <h1 class="mt-1 font-heading text-2xl font-bold tracking-tight sm:text-3xl text-white">
                                URAIAN JABATAN (JOB DESCRIPTION)
                            </h1>
                            <p class="mt-1 font-body text-xs text-white/80">
                                Berdasarkan {{ job.sotk_reference }} tentang Struktur Organisasi & Tata Kerja PTKP.
                            </p>
                        </div>

                        <!-- TTE Seal & Barcode Badge -->
                        <div class="flex shrink-0 items-center gap-3 rounded-lg border border-white/20 bg-white/10 p-3 backdrop-blur-xs">
                            <div class="text-right">
                                <span class="block font-heading text-[10px] font-bold uppercase tracking-wider text-white/70">Status Pengesahan</span>
                                <span class="font-heading text-sm font-bold text-white">{{ getStatusLabel(job.status) }}</span>
                                <span v-if="job.tte_signed_at" class="block font-mono text-[10px] text-emerald-300">
                                    TMT: {{ new Date(job.tte_signed_at).toLocaleDateString('id-ID') }}
                                </span>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-md bg-white/20 text-white">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6 sm:p-8 space-y-8">
                    <!-- SEKSI 1: IDENTITAS & POSISI JABATAN -->
                    <section class="space-y-4">
                        <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-ptkp-navy font-heading text-xs font-bold text-white">1</span>
                            <h2 class="font-heading text-base font-bold text-ptkp-navy uppercase tracking-wide">
                                Identifikasi Posisi Jabatan
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 gap-y-3 gap-x-6 rounded-lg border border-slate-200 bg-slate-50/60 p-4 sm:grid-cols-2 text-xs">
                            <div class="space-y-1">
                                <span class="text-slate-500 font-medium">Nama Jabatan Resmi:</span>
                                <p class="font-heading text-sm font-bold text-ptkp-navy">{{ job.position_name }}</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-slate-500 font-medium">Kode Posisi:</span>
                                <p class="font-mono font-bold text-slate-800">
                                    {{ job.position_code }}
                                </p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-slate-500 font-medium">Unit Organisasi (Divisi / Departemen):</span>
                                <p class="font-semibold text-ptkp-body">
                                    {{ job.org_unit.name }}
                                    <span class="font-mono text-slate-500 font-normal">({{ job.org_unit.code }})</span>
                                </p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-slate-500 font-medium">Kelompok & Rekomendasi Grade:</span>
                                <p class="font-semibold text-ptkp-body uppercase">
                                    {{ job.job_group }} <span class="font-mono font-normal text-slate-600">/ {{ job.grade_recommendation || '-' }}</span>
                                </p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-slate-500 font-medium">Atasan Langsung (Direct Superior):</span>
                                <p class="font-semibold text-ptkp-navy">
                                    {{ job.direct_superior_name || 'Direksi / Kepala Unit Tertinggi' }}
                                </p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-slate-500 font-medium">Pemangku Jabatan Saat Ini:</span>
                                <p class="font-semibold text-ptkp-body">
                                    {{ job.employee?.name || 'Belum Ditugaskan' }}
                                    <span v-if="job.employee?.nip" class="font-mono text-slate-500 font-normal">({{ job.employee.nip }})</span>
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- SEKSI 2: STRUKTUR ORGANISASI JABATAN (VISUAL TREE) -->
                    <section class="space-y-4">
                        <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-ptkp-navy font-heading text-xs font-bold text-white">2</span>
                            <h2 class="font-heading text-base font-bold text-ptkp-navy uppercase tracking-wide">
                                Posisi Dalam Struktur Organisasi
                            </h2>
                        </div>

                        <div class="flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-6">
                            <!-- Superior Box -->
                            <div class="flex flex-col items-center">
                                <div class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-center shadow-xs">
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Atasan Langsung</span>
                                    <span class="font-heading text-xs font-bold text-ptkp-navy">
                                        {{ job.direct_superior_name || 'Direksi / Kepala Departemen Terkait' }}
                                    </span>
                                </div>
                                <div class="h-6 w-0.5 bg-slate-400"></div>
                            </div>

                            <!-- Current Position (Highlight) -->
                            <div class="flex flex-col items-center">
                                <div class="relative rounded-xl border-2 border-ptkp-navy bg-ptkp-deep px-6 py-3 text-center text-white shadow-md">
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-emerald-300">Posisi Ini</span>
                                    <span class="font-heading text-sm font-bold text-white">{{ job.position_name }}</span>
                                    <div class="mt-0.5 font-mono text-[10px] text-white/70">{{ job.position_code }}</div>
                                </div>
                                <div v-if="job.subordinate_notes" class="h-6 w-0.5 bg-slate-400"></div>
                            </div>

                            <!-- Subordinates Indicator -->
                            <div v-if="job.subordinate_notes" class="flex flex-col items-center">
                                <div class="rounded-lg border border-dashed border-slate-300 bg-white px-4 py-2 text-center text-xs text-slate-600">
                                    <span class="font-bold text-ptkp-navy">Bawahan Langsung:</span> {{ job.subordinate_notes }}
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SEKSI 3: IKHTISAR JABATAN (JOB SUMMARY) -->
                    <section class="space-y-4">
                        <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-ptkp-navy font-heading text-xs font-bold text-white">3</span>
                            <h2 class="font-heading text-base font-bold text-ptkp-navy uppercase tracking-wide">
                                Ikhtisar Jabatan (Job Summary)
                            </h2>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-white p-4 font-body text-sm leading-relaxed text-ptkp-body shadow-xs">
                            {{ job.job_summary }}
                        </div>
                    </section>

                    <!-- SEKSI 4: TUGAS POKOK & AKTIVITAS UTAMA -->
                    <section class="space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                            <div class="flex items-center gap-2">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-ptkp-navy font-heading text-xs font-bold text-white">4</span>
                                <h2 class="font-heading text-base font-bold text-ptkp-navy uppercase tracking-wide">
                                    Tugas Pokok & Aktivitas Rinci
                                </h2>
                            </div>
                            <span class="font-mono text-xs text-slate-500">{{ job.tasks.length }} Butir Tugas</span>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-slate-200">
                            <table class="w-full border-collapse text-left font-body text-xs">
                                <thead>
                                    <tr class="border-b border-slate-200 bg-slate-100 font-heading font-bold text-ptkp-navy">
                                        <th class="px-3.5 py-2.5 text-center w-10">NO</th>
                                        <th class="px-3.5 py-2.5 w-1/3">AKTIVITAS KERJA</th>
                                        <th class="px-3.5 py-2.5">RINCIAN TAHAPAN & PROSEDUR KERJA</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="(task, idx) in job.tasks" :key="idx" class="hover:bg-slate-50">
                                        <td class="px-3.5 py-3 text-center font-medium text-slate-500">{{ idx + 1 }}</td>
                                        <td class="px-3.5 py-3 font-semibold text-ptkp-navy">{{ task.activity_name }}</td>
                                        <td class="px-3.5 py-3 text-slate-700 leading-relaxed">{{ task.activity_details }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <!-- SEKSI 5: WEWENANG & TANGGUNG JAWAB -->
                    <section class="space-y-4">
                        <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-ptkp-navy font-heading text-xs font-bold text-white">5</span>
                            <h2 class="font-heading text-base font-bold text-ptkp-navy uppercase tracking-wide">
                                Tanggung Jawab & Wewenang Jabatan
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <!-- Tanggung Jawab Utama -->
                            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-2xs">
                                <h3 class="mb-2 font-heading text-xs font-bold uppercase tracking-wider text-ptkp-navy flex items-center gap-1.5">
                                    <svg class="h-4 w-4 text-ptkp-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    Tanggung Jawab Utama
                                </h3>
                                <ul class="list-disc space-y-1.5 pl-4 text-xs text-slate-700">
                                    <li v-for="(resp, idx) in job.governances.responsibilities" :key="idx">
                                        {{ resp }}
                                    </li>
                                </ul>
                            </div>

                            <!-- Wewenang Keputusan & Operasional -->
                            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-2xs">
                                <h3 class="mb-2 font-heading text-xs font-bold uppercase tracking-wider text-ptkp-navy flex items-center gap-1.5">
                                    <svg class="h-4 w-4 text-ptkp-violet" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Wewenang Keputusan & Tindakan
                                </h3>
                                <ul class="list-disc space-y-1.5 pl-4 text-xs text-slate-700">
                                    <li v-for="(auth, idx) in job.governances.authorities" :key="idx">
                                        {{ auth }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <!-- SEKSI 6: HUBUNGAN KERJA INTERNAL & EKSTERNAL (ALA TELKOM UNIVERSITY) -->
                    <section class="space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                            <div class="flex items-center gap-2">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-ptkp-navy font-heading text-xs font-bold text-white">6</span>
                                <h2 class="font-heading text-base font-bold text-ptkp-navy uppercase tracking-wide">
                                    Hubungan Kerja (Working Relations)
                                </h2>
                            </div>
                            <span class="font-mono text-xs text-slate-500">Matriks Koordinasi "Dalam Hal"</span>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-slate-200">
                            <table class="w-full border-collapse text-left font-body text-xs">
                                <thead>
                                    <tr class="border-b border-slate-200 bg-slate-100 font-heading font-bold text-ptkp-navy">
                                        <th class="px-3.5 py-2.5 text-center w-10">NO</th>
                                        <th class="px-3.5 py-2.5 w-1/3">PIHAK / UNIT KERJA</th>
                                        <th class="px-3.5 py-2.5 text-center w-28">SIFAT HUBUNGAN</th>
                                        <th class="px-3.5 py-2.5">DALAM HAL (TUJUAN & RUANG LINGKUP)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <!-- Internal Relations -->
                                    <tr v-for="(rel, idx) in job.working_relations.internal" :key="`int-${idx}`" class="hover:bg-slate-50">
                                        <td class="px-3.5 py-3 text-center font-medium text-slate-500">{{ idx + 1 }}</td>
                                        <td class="px-3.5 py-3 font-semibold text-ptkp-navy">{{ rel.entity }}</td>
                                        <td class="px-3.5 py-3 text-center">
                                            <AppBadge variant="navy" size="sm">Internal PTKP</AppBadge>
                                        </td>
                                        <td class="px-3.5 py-3 text-slate-700 leading-relaxed font-medium">
                                            {{ rel.matter }}
                                        </td>
                                    </tr>

                                    <!-- External Relations -->
                                    <tr v-for="(rel, idx) in job.working_relations.external" :key="`ext-${idx}`" class="hover:bg-slate-50">
                                        <td class="px-3.5 py-3 text-center font-medium text-slate-500">{{ job.working_relations.internal.length + idx + 1 }}</td>
                                        <td class="px-3.5 py-3 font-semibold text-ptkp-navy">{{ rel.entity }}</td>
                                        <td class="px-3.5 py-3 text-center">
                                            <AppBadge variant="violet" size="sm">Pihak Eksternal</AppBadge>
                                        </td>
                                        <td class="px-3.5 py-3 text-slate-700 leading-relaxed font-medium">
                                            {{ rel.matter }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <!-- SEKSI 7: MASALAH & TANTANGAN KERJA -->
                    <section class="space-y-4">
                        <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-ptkp-navy font-heading text-xs font-bold text-white">7</span>
                            <h2 class="font-heading text-base font-bold text-ptkp-navy uppercase tracking-wide">
                                Masalah, Tantangan & Karakteristik Kerja
                            </h2>
                        </div>
                        <div class="rounded-lg border border-amber-200/70 bg-amber-50/50 p-4 font-body text-xs leading-relaxed text-amber-900">
                            <div class="flex items-start gap-2.5">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div class="space-y-2">
                                    <div>
                                        <span class="font-bold text-amber-950 block">Tantangan Operasional Kerja:</span>
                                        <span>{{ job.operating_challenges || 'Menjaga kepatuhan standar toleransi keamanan kertas berharga tinggi, kontinuitas jadwal produksi, dan akurasi pelaporan secara berkesinambungan.' }}</span>
                                    </div>
                                    <div v-if="job.environmental_conditions">
                                        <span class="font-bold text-amber-950 block">Kondisi Lingkungan Kerja:</span>
                                        <span>{{ job.environmental_conditions }}</span>
                                    </div>
                                    <div v-if="job.incidental_duties">
                                        <span class="font-bold text-amber-950 block">Tugas Insidental / Tambahan:</span>
                                        <span>{{ job.incidental_duties }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SEKSI 8: MATRIKS STANDAR KOMPETENSI (PL 1 - 5) -->
                    <section class="space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                            <div class="flex items-center gap-2">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-ptkp-navy font-heading text-xs font-bold text-white">8</span>
                                <h2 class="font-heading text-base font-bold text-ptkp-navy uppercase tracking-wide">
                                    Standar Kompetensi Jabatan (Proficiency Level 1–5)
                                </h2>
                            </div>
                            <span class="font-mono text-xs text-slate-500">Standar Skala PL 1 s/d PL 5</span>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-slate-200">
                            <table class="w-full border-collapse text-left font-body text-xs">
                                <thead>
                                    <tr class="border-b border-slate-200 bg-slate-100 font-heading font-bold text-ptkp-navy">
                                        <th class="px-3.5 py-2.5 text-center w-10">NO</th>
                                        <th class="px-3.5 py-2.5 w-1/3">NAMA KOMPETENSI</th>
                                        <th class="px-3.5 py-2.5 text-center w-40">PROFICIENCY LEVEL</th>
                                        <th class="px-3.5 py-2.5">INDIKATOR PERILAKU UTAMA (KEY BEHAVIOR)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="(comp, idx) in job.competencies" :key="idx" class="hover:bg-slate-50">
                                        <td class="px-3.5 py-3 text-center font-medium text-slate-500">{{ idx + 1 }}</td>
                                        <td class="px-3.5 py-3 font-semibold text-ptkp-navy">{{ comp.sub_competency }}</td>
                                        <td class="px-3.5 py-3 text-center">
                                            <div class="flex flex-col items-center gap-1">
                                                <div class="flex items-center gap-1">
                                                    <span
                                                        v-for="star in 5"
                                                        :key="star"
                                                        :class="[
                                                            'h-2 w-4 rounded-xs',
                                                            star <= comp.proficiency_level ? 'bg-ptkp-violet' : 'bg-slate-200'
                                                        ]"
                                                    />
                                                </div>
                                                <span class="font-mono text-[11px] font-bold text-ptkp-navy">
                                                    {{ getPLDescription(comp.proficiency_level) }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-3.5 py-3 text-slate-700 leading-relaxed font-medium">
                                            {{ comp.key_indicator }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <!-- SEKSI 9: SPESIFIKASI & KUALIFIKASI MINIMAL JABATAN -->
                    <section class="space-y-4">
                        <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-ptkp-navy font-heading text-xs font-bold text-white">9</span>
                            <h2 class="font-heading text-base font-bold text-ptkp-navy uppercase tracking-wide">
                                Spesifikasi & Kualifikasi Minimal Jabatan
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-xs">
                            <div class="rounded-lg border border-slate-200 bg-white p-4 space-y-2">
                                <span class="font-bold text-ptkp-navy block">Pendidikan Minimal:</span>
                                <p class="text-slate-700">
                                    {{ job.specification?.education_level || 'D3 / S1' }}
                                </p>

                                <span class="font-bold text-ptkp-navy block pt-2">Pengalaman Kerja:</span>
                                <p class="text-slate-700">
                                    {{ job.specification?.work_experience || 'Minimal 2-3 tahun di bidang terkait industri percetakan kertas uang/dokumen sekuriti.' }}
                                </p>
                            </div>

                            <div class="rounded-lg border border-slate-200 bg-white p-4 space-y-2">
                                <span class="font-bold text-ptkp-navy block">Sertifikasi & Keahlian Khusus:</span>
                                <p class="text-slate-700">
                                    {{ job.specification?.certifications || 'Sertifikasi K3, ISO 9001, atau sertifikasi keahlian teknis relevan.' }}
                                </p>

                                <span class="font-bold text-ptkp-navy block pt-2">Pelatihan yang Dibutuhkan:</span>
                                <p class="text-slate-700">
                                    {{ job.specification?.trainings || 'SOP Pengamanan Kertas Berharga, Tata Kelola Arsip, Sistem Mutu Pabrik.' }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- SEKSI 10: LEMBAR PENGESAHAN & PERSATUAN DIGITAL -->
                    <section class="space-y-4 border-t-2 border-slate-200 pt-6">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                            <div class="flex items-center gap-2">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-ptkp-navy font-heading text-xs font-bold text-white">10</span>
                                <h2 class="font-heading text-base font-bold text-ptkp-navy uppercase tracking-wide">
                                    Lembar Pengesahan Dokumen Jabatan
                                </h2>
                            </div>
                            <span class="font-mono text-xs text-slate-500">BSrE Digital Signature Audit Trail</span>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 text-center">
                            <!-- Maker Block -->
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Penyusun (Maker)</span>
                                <div class="my-3 flex h-14 items-center justify-center font-serif text-slate-400 italic text-xs">
                                    [Tersimpan Sistem]
                                </div>
                                <span class="block font-heading text-xs font-bold text-ptkp-navy">Pembuat Profil Jabatan</span>
                                <span class="block font-mono text-[10px] text-slate-500">{{ job.created_at ? new Date(job.created_at).toLocaleDateString('id-ID') : '-' }}</span>
                            </div>

                            <!-- Checker Block -->
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Pemeriksa (Checker / Atasan)</span>
                                <div class="my-3 flex h-14 items-center justify-center">
                                    <div v-if="job.status === 'approved' || (job.approvals && job.approvals.some(a => a.stage === 'checker' && a.decision === 'approved'))" class="text-center">
                                        <div class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Terverifikasi Atasan
                                        </div>
                                    </div>
                                    <span v-else class="text-[11px] text-slate-400 italic">Menunggu Review</span>
                                </div>
                                <span class="block font-heading text-xs font-bold text-ptkp-navy">Kasi / Atasan Terkait</span>
                                <span class="block font-mono text-[10px] text-slate-500">Pemeriksaan Kebutuhan</span>
                            </div>

                            <!-- Approver Block -->
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Pengesah (Approver / SDM)</span>
                                <div class="my-3 flex h-14 items-center justify-center">
                                    <div v-if="job.status === 'approved'" class="text-center">
                                        <div class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                            Disahkan Kadep SDM
                                        </div>
                                        <span v-if="job.tte_hash" class="block font-mono text-[9px] text-slate-400 mt-0.5 truncate max-w-[180px]">
                                            TTE: {{ job.tte_hash.substring(0, 16) }}...
                                        </span>
                                    </div>
                                    <span v-else class="text-[11px] text-slate-400 italic">Menunggu Pengesahan</span>
                                </div>
                                <span class="block font-heading text-xs font-bold text-ptkp-navy">Kepala Departemen SDM</span>
                                <span class="block font-mono text-[10px] text-slate-500">PT Kertas Padalarang</span>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <!-- Modal Verifikasi & Persetujuan -->
        <AppModal
            :show="isApprovalModalOpen"
            :title="approvalDecision === 'approved' ? 'Persetujuan Dokumen Jobdesk' : 'Pengembalian / Catatan Revisi Jobdesk'"
            @close="isApprovalModalOpen = false"
        >
            <form @submit.prevent="submitApprovalDecision" class="space-y-4 font-body text-sm">
                <div class="rounded-lg p-3 text-xs" :class="approvalDecision === 'approved' ? 'bg-emerald-50 text-emerald-800' : 'bg-red-50 text-red-800'">
                    <span v-if="approvalDecision === 'approved'">
                        Anda akan menyetujui dokumen profil jabatan ini untuk diteruskan ke tahap persetujuan berikutnya atau disahkan secara resmi.
                    </span>
                    <span v-else>
                        Dokumen akan dikembalikan ke status draf dengan catatan revisi yang Anda cantumkan di bawah ini.
                    </span>
                </div>

                <div>
                    <label class="mb-1 block font-heading text-xs font-bold text-ptkp-navy">
                        Catatan / Komentar Review
                    </label>
                    <textarea
                        v-model="approvalForm.notes"
                        rows="3"
                        placeholder="Tuliskan catatan verifikasi atau arahan perbaikan (opsional bila disetujui, disarankan bila revisi)..."
                        class="w-full rounded-lg border border-slate-200 p-2.5 text-xs text-ptkp-body focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                    />
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <AppButton variant="secondary" size="sm" @click="isApprovalModalOpen = false">
                        Batal
                    </AppButton>
                    <AppButton
                        :variant="approvalDecision === 'approved' ? 'primary' : 'danger'"
                        size="sm"
                        type="submit"
                        :loading="approvalForm.processing"
                    >
                        {{ approvalDecision === 'approved' ? 'Konfirmasi Persetujuan' : 'Kirim Catatan Revisi' }}
                    </AppButton>
                </div>
            </form>
        </AppModal>
    </AppShell>
</template>
