<script setup lang="ts">
import AppBadge from '@/Components/AppBadge.vue';
import AppButton from '@/Components/AppButton.vue';
import AppCard from '@/Components/AppCard.vue';
import AppShell from '@/Layouts/AppShell.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface OrgUnitOption {
    id: string;
    name: string;
    code: string;
    type: string;
}

interface PositionOption {
    id: string;
    name: string;
    code: string;
    level?: number;
    echelon: string | null;
}

interface EmployeeOption {
    id: string;
    nip: string;
    full_name: string;
}

interface Props {
    mode: 'create' | 'edit';
    orgUnits: OrgUnitOption[];
    positions: PositionOption[];
    employees: EmployeeOption[];
    job?: any | null;
}

const props = defineProps<Props>();

const activeTab = ref<'identity' | 'tasks' | 'governances' | 'relations' | 'competencies'>('identity');

// Inisialisasi data form
const form = useForm({
    position_id: props.job?.position_id || '',
    org_unit_id: props.job?.org_unit_id || '',
    employee_id: props.job?.employee?.id || props.job?.employee_id || '',
    direct_superior_id: props.job?.direct_superior_id || '',
    job_group: props.job?.job_group || 'Operasional & Manufaktur',
    grade_recommendation: props.job?.grade_recommendation || 'Grade 10',
    sotk_reference: props.job?.sotk_reference || 'SK 009/KPTS/PTKP/IV/2026',
    job_summary: props.job?.job_summary || '',
    subordinate_notes: props.job?.subordinate_notes || '',
    operating_challenges: props.job?.operating_challenges || '',
    environmental_conditions: props.job?.environmental_conditions || '',
    incidental_duties: props.job?.incidental_duties || '',

    // Sub-tables
    tasks: props.job?.tasks && props.job.tasks.length > 0
        ? props.job.tasks.map((t: any) => ({
            activity_name: t.activity_name || '',
            activity_details: t.activity_details || '',
        }))
        : [
            { activity_name: '', activity_details: '' },
        ],

    responsibilities: props.job?.governances?.responsibilities && props.job.governances.responsibilities.length > 0
        ? [...props.job.governances.responsibilities]
        : ['Menjaga kerahasiaan dan integritas dokumen/produk sekuriti PTKP.'],

    authorities: props.job?.governances?.authorities && props.job.governances.authorities.length > 0
        ? [...props.job.governances.authorities]
        : ['Menghentikan sementara lini produksi bila ditemukan ketidaksesuaian spesifikasi toleransi mutu.'],

    working_relations: {
        internal: props.job?.working_relations?.internal && props.job.working_relations.internal.length > 0
            ? props.job.working_relations.internal.map((r: any) => ({
                entity: r.entity || '',
                matter: r.matter || '',
            }))
            : [{ entity: '', matter: '' }],
        external: props.job?.working_relations?.external && props.job.working_relations.external.length > 0
            ? props.job.working_relations.external.map((r: any) => ({
                entity: r.entity || '',
                matter: r.matter || '',
            }))
            : [{ entity: '', matter: '' }],
    },

    competencies: props.job?.competencies && props.job.competencies.length > 0
        ? props.job.competencies.map((c: any) => ({
            sub_competency: c.sub_competency || '',
            proficiency_level: c.proficiency_level || 3,
            key_indicator: c.key_indicator || '',
        }))
        : [
            { sub_competency: 'Integritas & Etika Sekuriti', proficiency_level: 4, key_indicator: 'Menjunjung standar zero-defect dan anti-fraud dalam penanganan kertas uang.' },
            { sub_competency: 'Keahlian Teknis Spesifik', proficiency_level: 3, key_indicator: 'Menguasai parameter operasional dan penanganan deviasi proses.' },
        ],

    specification: {
        education_level: props.job?.specification?.education_level || 'D3 / S1 Teknik atau Manajemen',
        work_experience: props.job?.specification?.work_experience || 'Pengalaman kerja minimal 2 tahun di industri manufaktur atau bidang relevan.',
        certifications: props.job?.specification?.certifications || 'Sertifikasi K3 Umum, ISO 9001/14001/27001 (nilai tambah).',
        trainings: props.job?.specification?.trainings || 'Pelatihan Sistem Pengamanan Kertas Berharga, Tata Kelola Mutu PTKP.',
        hard_skills: props.job?.specification?.hard_skills || [],
        soft_skills: props.job?.specification?.soft_skills || [],
    },
});

// Dynamic List Helpers
const addTask = () => {
    form.tasks.push({ activity_name: '', activity_details: '' });
};
const removeTask = (index: number) => {
    if (form.tasks.length > 1) {
        form.tasks.splice(index, 1);
    }
};

const addResponsibility = () => {
    form.responsibilities.push('');
};
const removeResponsibility = (index: number) => {
    if (form.responsibilities.length > 1) {
        form.responsibilities.splice(index, 1);
    }
};

const addAuthority = () => {
    form.authorities.push('');
};
const removeAuthority = (index: number) => {
    if (form.authorities.length > 1) {
        form.authorities.splice(index, 1);
    }
};

const addInternalRelation = () => {
    form.working_relations.internal.push({ entity: '', matter: '' });
};
const removeInternalRelation = (index: number) => {
    if (form.working_relations.internal.length > 1) {
        form.working_relations.internal.splice(index, 1);
    }
};

const addExternalRelation = () => {
    form.working_relations.external.push({ entity: '', matter: '' });
};
const removeExternalRelation = (index: number) => {
    if (form.working_relations.external.length > 1) {
        form.working_relations.external.splice(index, 1);
    }
};

const addCompetency = () => {
    form.competencies.push({
        sub_competency: '',
        proficiency_level: 3,
        key_indicator: '',
    });
};
const removeCompetency = (index: number) => {
    if (form.competencies.length > 1) {
        form.competencies.splice(index, 1);
    }
};

const submit = () => {
    // Sanitize sub-table items to prevent validation rejection on empty rows
    if (Array.isArray(form.tasks)) {
        form.tasks = form.tasks.filter((t: any) => t.activity_name && t.activity_name.trim().length > 0);
        if (form.tasks.length === 0) {
            form.tasks = [{ activity_name: 'Melaksanakan pengawasan operasional dan pemenuhan standar mutu', activity_details: 'Instruksi kerja standar pabrik PTKP' }];
        }
    }

    if (form.working_relations) {
        if (Array.isArray(form.working_relations.internal)) {
            form.working_relations.internal = form.working_relations.internal.filter((r: any) => r.entity && r.entity.trim().length > 0);
            if (form.working_relations.internal.length === 0) {
                form.working_relations.internal = [{ entity: 'Departemen Terkait', matter: 'Koordinasi teknis lintas departemen' }];
            }
        }
        if (Array.isArray(form.working_relations.external)) {
            form.working_relations.external = form.working_relations.external.filter((r: any) => r.entity && r.entity.trim().length > 0);
            if (form.working_relations.external.length === 0) {
                form.working_relations.external = [{ entity: 'Auditor & Regulator Eksternal', matter: 'Pemeriksaan kepatuhan standar' }];
            }
        }
    }

    if (props.mode === 'create') {
        form.post(route('sdm.jobdesk.store'));
    } else {
        form.put(route('sdm.jobdesk.update', props.job.id));
    }
};

const selectedPosition = computed(() => {
    return props.positions.find(p => p.id === form.position_id);
});
</script>

<template>
    <AppShell
        :title="mode === 'create' ? 'Penyusunan Jobdesk Baru - PTKP' : `Edit ${job?.position_name || 'Jobdesk'} - PTKP`"
        :breadcrumbs="[
            { label: 'Portal', href: '/dashboard' },
            { label: 'SDM & Organisasi', href: route('sdm.jobdesk.index') },
            { label: 'Katalog Jobdesk', href: route('sdm.jobdesk.index') },
            { label: mode === 'create' ? 'Penyusunan Baru' : 'Edit Dokumen' }
        ]"
    >
        <div class="space-y-6">
            <!-- Header Bar -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-xl bg-white p-4 shadow-xs border border-slate-200">
                <div class="flex items-center gap-3">
                    <Link :href="mode === 'edit' ? route('sdm.jobdesk.show', job.id) : route('sdm.jobdesk.index')">
                        <AppButton variant="secondary" size="sm">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Batal / Kembali</span>
                        </AppButton>
                    </Link>

                    <div>
                        <h1 class="font-heading text-lg font-bold text-ptkp-navy">
                            {{ mode === 'create' ? 'Studio Penyusunan Jobdesk Digital' : `Perbarui Uraian Jabatan: ${job?.position_name}` }}
                        </h1>
                        <p class="font-body text-xs text-slate-500">
                            Format Baku SK Direksi 009/KPTS/PTKP/IV/2026 & Matriks Relasi Kerja ala Telkom University.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <AppButton
                        variant="primary"
                        size="md"
                        :loading="form.processing"
                        @click="submit"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ mode === 'create' ? 'Simpan Draf Jobdesk' : 'Simpan Perubahan' }}</span>
                    </AppButton>
                </div>
            </div>

            <!-- Tab Navigation Bar -->
            <div class="flex border-b border-slate-200 bg-white px-4 pt-2 rounded-t-xl overflow-x-auto shadow-2xs">
                <button
                    type="button"
                    :class="[
                        'px-4 py-3 font-heading text-xs font-bold transition-all border-b-2 flex items-center gap-2 whitespace-nowrap',
                        activeTab === 'identity'
                            ? 'border-ptkp-navy text-ptkp-navy'
                            : 'border-transparent text-slate-500 hover:text-slate-800'
                    ]"
                    @click="activeTab = 'identity'"
                >
                    <span class="flex h-5 w-5 items-center justify-center rounded-full text-[10px]" :class="activeTab === 'identity' ? 'bg-ptkp-navy text-white' : 'bg-slate-100 text-slate-600'">1</span>
                    Identitas & Organisasi
                </button>

                <button
                    type="button"
                    :class="[
                        'px-4 py-3 font-heading text-xs font-bold transition-all border-b-2 flex items-center gap-2 whitespace-nowrap',
                        activeTab === 'tasks'
                            ? 'border-ptkp-navy text-ptkp-navy'
                            : 'border-transparent text-slate-500 hover:text-slate-800'
                    ]"
                    @click="activeTab = 'tasks'"
                >
                    <span class="flex h-5 w-5 items-center justify-center rounded-full text-[10px]" :class="activeTab === 'tasks' ? 'bg-ptkp-navy text-white' : 'bg-slate-100 text-slate-600'">2</span>
                    Ikhtisar & Tugas Pokok
                </button>

                <button
                    type="button"
                    :class="[
                        'px-4 py-3 font-heading text-xs font-bold transition-all border-b-2 flex items-center gap-2 whitespace-nowrap',
                        activeTab === 'governances'
                            ? 'border-ptkp-navy text-ptkp-navy'
                            : 'border-transparent text-slate-500 hover:text-slate-800'
                    ]"
                    @click="activeTab = 'governances'"
                >
                    <span class="flex h-5 w-5 items-center justify-center rounded-full text-[10px]" :class="activeTab === 'governances' ? 'bg-ptkp-navy text-white' : 'bg-slate-100 text-slate-600'">3</span>
                    Tanggung Jawab & Wewenang
                </button>

                <button
                    type="button"
                    :class="[
                        'px-4 py-3 font-heading text-xs font-bold transition-all border-b-2 flex items-center gap-2 whitespace-nowrap',
                        activeTab === 'relations'
                            ? 'border-ptkp-navy text-ptkp-navy'
                            : 'border-transparent text-slate-500 hover:text-slate-800'
                    ]"
                    @click="activeTab = 'relations'"
                >
                    <span class="flex h-5 w-5 items-center justify-center rounded-full text-[10px]" :class="activeTab === 'relations' ? 'bg-ptkp-navy text-white' : 'bg-slate-100 text-slate-600'">4</span>
                    Hubungan Kerja ("Dalam Hal")
                </button>

                <button
                    type="button"
                    :class="[
                        'px-4 py-3 font-heading text-xs font-bold transition-all border-b-2 flex items-center gap-2 whitespace-nowrap',
                        activeTab === 'competencies'
                            ? 'border-ptkp-navy text-ptkp-navy'
                            : 'border-transparent text-slate-500 hover:text-slate-800'
                    ]"
                    @click="activeTab = 'competencies'"
                >
                    <span class="flex h-5 w-5 items-center justify-center rounded-full text-[10px]" :class="activeTab === 'competencies' ? 'bg-ptkp-navy text-white' : 'bg-slate-100 text-slate-600'">5</span>
                    Kompetensi (PL 1–5) & Spesifikasi
                </button>
            </div>

            <!-- Tab Content Forms -->
            <AppCard class="rounded-t-none">
                <!-- TAB 1: IDENTITAS & ORGANISASI -->
                <div v-show="activeTab === 'identity'" class="space-y-6">
                    <h2 class="font-heading text-sm font-bold text-ptkp-navy uppercase tracking-wide border-b border-slate-200 pb-2">
                        1. Identifikasi Posisi & Penempatan Organisasi
                    </h2>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Posisi / Jabatan -->
                        <div>
                            <label class="mb-1 block font-heading text-xs font-bold text-ptkp-navy">
                                Nama Jabatan (Master Posisi) *
                            </label>
                            <select
                                v-model="form.position_id"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-body text-ptkp-body focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                                required
                            >
                                <option value="">-- Pilih Posisi Terdaftar --</option>
                                <option v-for="pos in positions" :key="pos.id" :value="pos.id">
                                    [{{ pos.code }}] {{ pos.name }} ({{ pos.echelon || 'Non-Eselon' }})
                                </option>
                            </select>
                            <p v-if="form.errors.position_id" class="mt-1 text-xs text-red-600 font-medium">
                                {{ form.errors.position_id }}
                            </p>
                        </div>

                        <!-- Unit Organisasi -->
                        <div>
                            <label class="mb-1 block font-heading text-xs font-bold text-ptkp-navy">
                                Unit Organisasi (Divisi / Departemen) *
                            </label>
                            <select
                                v-model="form.org_unit_id"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-body text-ptkp-body focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                                required
                            >
                                <option value="">-- Pilih Unit Organisasi --</option>
                                <option v-for="unit in orgUnits" :key="unit.id" :value="unit.id">
                                    [{{ unit.code }}] {{ unit.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.org_unit_id" class="mt-1 text-xs text-red-600 font-medium">
                                {{ form.errors.org_unit_id }}
                            </p>
                        </div>

                        <!-- Atasan Langsung -->
                        <div>
                            <label class="mb-1 block font-heading text-xs font-bold text-ptkp-navy">
                                Atasan Langsung (Direct Superior Position)
                            </label>
                            <select
                                v-model="form.direct_superior_id"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-body text-ptkp-body focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                            >
                                <option value="">-- Tidak Ada / Melapor ke Direksi --</option>
                                <option v-for="pos in positions" :key="pos.id" :value="pos.id">
                                    [{{ pos.code }}] {{ pos.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Pemangku Jabatan -->
                        <div>
                            <label class="mb-1 block font-heading text-xs font-bold text-ptkp-navy">
                                Pemangku Jabatan Saat Ini (Opsional)
                            </label>
                            <select
                                v-model="form.employee_id"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-body text-ptkp-body focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                            >
                                <option value="">-- Posisi Kosong / Belum Ditetapkan --</option>
                                <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                    {{ emp.full_name }} (NIP: {{ emp.nip }})
                                </option>
                            </select>
                        </div>

                        <!-- Kelompok Jabatan -->
                        <div>
                            <label class="mb-1 block font-heading text-xs font-bold text-ptkp-navy">
                                Kelompok Jabatan *
                            </label>
                            <select
                                v-model="form.job_group"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-body text-ptkp-body focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                                required
                            >
                                <option value="Operasional & Manufaktur">Operasional & Manufaktur Pabrik</option>
                                <option value="Fungsional & Spesialis Teknis">Fungsional & Spesialis Teknis</option>
                                <option value="Struktural & Manajemen">Struktural & Manajemen</option>
                                <option value="Administrasi & Layanan Dukungan">Administrasi & Layanan Dukungan</option>
                            </select>
                        </div>

                        <!-- Rekomendasi Grade -->
                        <div>
                            <label class="mb-1 block font-heading text-xs font-bold text-ptkp-navy">
                                Rekomendasi Golongan / Grade *
                            </label>
                            <input
                                v-model="form.grade_recommendation"
                                type="text"
                                placeholder="Contoh: Grade 10, Grade 12..."
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs font-body text-ptkp-body focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                                required
                            />
                        </div>

                        <!-- Referensi SOTK -->
                        <div class="sm:col-span-2">
                            <label class="mb-1 block font-heading text-xs font-bold text-ptkp-navy">
                                Dasar Hukum SOTK / SK Direksi
                            </label>
                            <input
                                v-model="form.sotk_reference"
                                type="text"
                                placeholder="Contoh: SK Direksi No. 009/KPTS/PTKP/IV/2026"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs font-body text-ptkp-body focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                            />
                        </div>

                        <!-- Catatan Bawahan Langsung -->
                        <div class="sm:col-span-2">
                            <label class="mb-1 block font-heading text-xs font-bold text-ptkp-navy">
                                Posisi / Jumlah Bawahan Langsung
                            </label>
                            <input
                                v-model="form.subordinate_notes"
                                type="text"
                                placeholder="Contoh: Membawahi 4 Operator Mesin PK1 dan 2 Helper..."
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs font-body text-ptkp-body focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                            />
                        </div>
                    </div>
                </div>

                <!-- TAB 2: IKHTISAR & TUGAS POKOK -->
                <div v-show="activeTab === 'tasks'" class="space-y-6">
                    <h2 class="font-heading text-sm font-bold text-ptkp-navy uppercase tracking-wide border-b border-slate-200 pb-2">
                        2. Ikhtisar Jabatan & Butir Aktivitas Tugas Pokok
                    </h2>

                    <!-- Job Summary -->
                    <div>
                        <label class="mb-1 block font-heading text-xs font-bold text-ptkp-navy">
                            Ikhtisar Jabatan (Job Summary) *
                        </label>
                        <p class="mb-1 text-[11px] text-slate-500">
                            Deskripsikan peran esensial dan kontribusi strategis posisi ini dalam operasional PT Kertas Padalarang.
                        </p>
                        <textarea
                            v-model="form.job_summary"
                            rows="4"
                            placeholder="Contoh: Mengoperasikan mesin pembentuk lembaran kertas pengaman (PK1) secara presisi sesuai SOP teknis..."
                            class="w-full rounded-lg border border-slate-200 p-3 text-xs font-body text-ptkp-body focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy leading-relaxed"
                            required
                        />
                        <p v-if="form.errors.job_summary" class="mt-1 text-xs text-red-600 font-medium">
                            {{ form.errors.job_summary }}
                        </p>
                    </div>

                    <!-- Dynamic Tasks List -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <span class="font-heading text-xs font-bold text-ptkp-navy uppercase">
                                Rincian Tugas Pokok & Tahapan Prosedur (Dynamic Builder)
                            </span>
                            <AppButton variant="secondary" size="sm" type="button" @click="addTask">
                                + Tambah Butir Tugas
                            </AppButton>
                        </div>

                        <div
                            v-for="(task, idx) in form.tasks"
                            :key="idx"
                            class="flex flex-col gap-2 rounded-lg border border-slate-200 bg-slate-50/50 p-3"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-heading text-xs font-bold text-ptkp-navy">
                                    Butir Tugas #{{ idx + 1 }}
                                </span>
                                <button
                                    v-if="form.tasks.length > 1"
                                    type="button"
                                    class="text-xs text-red-600 hover:text-red-800 font-medium"
                                    @click="removeTask(idx)"
                                >
                                    Hapus
                                </button>
                            </div>

                            <input
                                v-model="task.activity_name"
                                type="text"
                                placeholder="Nama Aktivitas Pokok (Contoh: Menyiapkan Bahan Baku Pulp & Bahan Kimia)..."
                                class="w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-ptkp-navy focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                                required
                            />

                            <textarea
                                v-model="task.activity_details"
                                rows="2"
                                placeholder="Rincian prosedur kerja, urutan teknis, dan standar kepatuhan toleransi..."
                                class="w-full rounded-md border border-slate-200 bg-white p-2 text-xs text-slate-700 focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy leading-relaxed"
                            />
                        </div>
                    </div>
                </div>

                <!-- TAB 3: TANGGUNG JAWAB & WEWENANG -->
                <div v-show="activeTab === 'governances'" class="space-y-6">
                    <h2 class="font-heading text-sm font-bold text-ptkp-navy uppercase tracking-wide border-b border-slate-200 pb-2">
                        3. Batasan Tanggung Jawab & Wewenang Jabatan
                    </h2>

                    <!-- Tanggung Jawab -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <div>
                                <span class="font-heading text-xs font-bold text-ptkp-navy uppercase block">
                                    Tanggung Jawab Utama
                                </span>
                                <span class="text-[11px] text-slate-500">Hal-hal yang menjadi akuntabilitas hasil kerja dari jabatan ini.</span>
                            </div>
                            <AppButton variant="secondary" size="sm" type="button" @click="addResponsibility">
                                + Tambah Tanggung Jawab
                            </AppButton>
                        </div>

                        <div v-for="(resp, idx) in form.responsibilities" :key="idx" class="flex items-center gap-2">
                            <span class="w-6 text-center text-xs font-bold text-slate-400">{{ idx + 1 }}.</span>
                            <input
                                v-model="form.responsibilities[idx]"
                                type="text"
                                placeholder="Tuliskan butir tanggung jawab..."
                                class="flex-1 rounded-lg border border-slate-200 px-3 py-2 text-xs text-ptkp-body focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                                required
                            />
                            <button
                                v-if="form.responsibilities.length > 1"
                                type="button"
                                class="p-1 text-slate-400 hover:text-red-600"
                                @click="removeResponsibility(idx)"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Wewenang -->
                    <div class="space-y-3 pt-4 border-t border-slate-100">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <div>
                                <span class="font-heading text-xs font-bold text-ptkp-navy uppercase block">
                                    Wewenang Keputusan & Tindakan
                                </span>
                                <span class="text-[11px] text-slate-500">Hak dan kuasa dalam membuat keputusan teknis, operasional, atau koordinasi.</span>
                            </div>
                            <AppButton variant="secondary" size="sm" type="button" @click="addAuthority">
                                + Tambah Wewenang
                            </AppButton>
                        </div>

                        <div v-for="(auth, idx) in form.authorities" :key="idx" class="flex items-center gap-2">
                            <span class="w-6 text-center text-xs font-bold text-slate-400">{{ idx + 1 }}.</span>
                            <input
                                v-model="form.authorities[idx]"
                                type="text"
                                placeholder="Tuliskan butir wewenang keputusan..."
                                class="flex-1 rounded-lg border border-slate-200 px-3 py-2 text-xs text-ptkp-body focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                                required
                            />
                            <button
                                v-if="form.authorities.length > 1"
                                type="button"
                                class="p-1 text-slate-400 hover:text-red-600"
                                @click="removeAuthority(idx)"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: HUBUNGAN KERJA ("DALAM HAL") -->
                <div v-show="activeTab === 'relations'" class="space-y-6">
                    <h2 class="font-heading text-sm font-bold text-ptkp-navy uppercase tracking-wide border-b border-slate-200 pb-2">
                        4. Matriks Hubungan Kerja Internal & Eksternal (Format "Dalam Hal")
                    </h2>

                    <!-- Hubungan Internal -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <div>
                                <span class="font-heading text-xs font-bold text-ptkp-navy uppercase block">
                                    A. Hubungan Kerja Internal PTKP
                                </span>
                                <span class="text-[11px] text-slate-500">Unit, Departemen, atau Bagian lain di dalam lingkungan PTKP.</span>
                            </div>
                            <AppButton variant="secondary" size="sm" type="button" @click="addInternalRelation">
                                + Tambah Relasi Internal
                            </AppButton>
                        </div>

                        <div
                            v-for="(rel, idx) in form.working_relations.internal"
                            :key="`int-${idx}`"
                            class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 bg-slate-50/50 p-3 sm:grid-cols-12"
                        >
                            <div class="sm:col-span-4">
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Pihak / Bagian Terkait</label>
                                <input
                                    v-model="rel.entity"
                                    type="text"
                                    placeholder="Contoh: Unit QC & Laboratorium..."
                                    class="w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs text-ptkp-navy font-semibold focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                                    required
                                />
                            </div>

                            <div class="sm:col-span-7">
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Dalam Hal (Tujuan & Ruang Lingkup Koordinasi)</label>
                                <input
                                    v-model="rel.matter"
                                    type="text"
                                    placeholder="Contoh: Pengujian sampel pulp dan verifikasi gramatur..."
                                    class="w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-700 focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                                    required
                                />
                            </div>

                            <div class="sm:col-span-1 flex items-end justify-center pb-1">
                                <button
                                    v-if="form.working_relations.internal.length > 1"
                                    type="button"
                                    class="text-xs text-red-600 hover:text-red-800"
                                    @click="removeInternalRelation(idx)"
                                >
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Hubungan Eksternal -->
                    <div class="space-y-3 pt-4 border-t border-slate-100">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <div>
                                <span class="font-heading text-xs font-bold text-ptkp-navy uppercase block">
                                    B. Hubungan Kerja Pihak Eksternal
                                </span>
                                <span class="text-[11px] text-slate-500">Peruri Holding, Vendor Mesin, Auditor Eksternal, Pelanggan, atau Instansi Terkait.</span>
                            </div>
                            <AppButton variant="secondary" size="sm" type="button" @click="addExternalRelation">
                                + Tambah Relasi Eksternal
                            </AppButton>
                        </div>

                        <div
                            v-for="(rel, idx) in form.working_relations.external"
                            :key="`ext-${idx}`"
                            class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 bg-slate-50/50 p-3 sm:grid-cols-12"
                        >
                            <div class="sm:col-span-4">
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Pihak Eksternal</label>
                                <input
                                    v-model="rel.entity"
                                    type="text"
                                    placeholder="Contoh: Teknisi Vendor Mesin Voith..."
                                    class="w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs text-ptkp-navy font-semibold focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                                    required
                                />
                            </div>

                            <div class="sm:col-span-7">
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Dalam Hal (Tujuan & Ruang Lingkup Koordinasi)</label>
                                <input
                                    v-model="rel.matter"
                                    type="text"
                                    placeholder="Contoh: Pemeliharaan berkala kalibrasi roll silinder..."
                                    class="w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-700 focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                                    required
                                />
                            </div>

                            <div class="sm:col-span-1 flex items-end justify-center pb-1">
                                <button
                                    v-if="form.working_relations.external.length > 1"
                                    type="button"
                                    class="text-xs text-red-600 hover:text-red-800"
                                    @click="removeExternalRelation(idx)"
                                >
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 5: KOMPETENSI (PL 1-5), TANTANGAN & KUALIFIKASI -->
                <div v-show="activeTab === 'competencies'" class="space-y-6">
                    <h2 class="font-heading text-sm font-bold text-ptkp-navy uppercase tracking-wide border-b border-slate-200 pb-2">
                        5. Standar Kompetensi (PL 1–5), Tantangan Operasional & Kualifikasi Minimal
                    </h2>

                    <!-- Competencies Dynamic Builder -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <div>
                                <span class="font-heading text-xs font-bold text-ptkp-navy uppercase block">
                                    Standar Kompetensi Jabatan (Proficiency Level 1–5)
                                </span>
                                <span class="text-[11px] text-slate-500">Skala 1: Dasar, 2: Mandiri, 3: Mahir, 4: Ahli, 5: Master.</span>
                            </div>
                            <AppButton variant="secondary" size="sm" type="button" @click="addCompetency">
                                + Tambah Kompetensi
                            </AppButton>
                        </div>

                        <div
                            v-for="(comp, idx) in form.competencies"
                            :key="idx"
                            class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 bg-slate-50/50 p-3 sm:grid-cols-12"
                        >
                            <div class="sm:col-span-4">
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Nama Kompetensi</label>
                                <input
                                    v-model="comp.sub_competency"
                                    type="text"
                                    placeholder="Contoh: Operasi Mesin Kertas Pengaman..."
                                    class="w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs text-ptkp-navy font-semibold focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                                    required
                                />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Tingkat PL (1 - 5)</label>
                                <select
                                    v-model.number="comp.proficiency_level"
                                    class="w-full rounded-md border border-slate-200 bg-white px-2 py-1.5 text-xs font-mono font-bold text-ptkp-navy focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                                >
                                    <option :value="1">PL 1 (Dasar)</option>
                                    <option :value="2">PL 2 (Mandiri)</option>
                                    <option :value="3">PL 3 (Mahir)</option>
                                    <option :value="4">PL 4 (Ahli)</option>
                                    <option :value="5">PL 5 (Master)</option>
                                </select>
                            </div>

                            <div class="sm:col-span-5">
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Indikator Perilaku Utama (Key Behavior)</label>
                                <input
                                    v-model="comp.key_indicator"
                                    type="text"
                                    placeholder="Contoh: Mampu mendeteksi cacat gramatur lembaran secara cepat..."
                                    class="w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-700 focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                                    required
                                />
                            </div>

                            <div class="sm:col-span-1 flex items-end justify-center pb-1">
                                <button
                                    v-if="form.competencies.length > 1"
                                    type="button"
                                    class="text-xs text-red-600 hover:text-red-800"
                                    @click="removeCompetency(idx)"
                                >
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tantangan Operasional & Kondisi Kerja -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 pt-4 border-t border-slate-100">
                        <div>
                            <label class="mb-1 block font-heading text-xs font-bold text-ptkp-navy">
                                Tantangan Operasional Kerja
                            </label>
                            <textarea
                                v-model="form.operating_challenges"
                                rows="3"
                                placeholder="Contoh: Menjaga konsistensi toleransi ketebalan kertas uang pada putaran mesin kecepatan tinggi..."
                                class="w-full rounded-lg border border-slate-200 p-2.5 text-xs text-ptkp-body focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                            />
                        </div>

                        <div>
                            <label class="mb-1 block font-heading text-xs font-bold text-ptkp-navy">
                                Kondisi Lingkungan & Tuntutan Kerja
                            </label>
                            <textarea
                                v-model="form.environmental_conditions"
                                rows="3"
                                placeholder="Contoh: Lingkungan pabrik dengan suhu mesin hangat, standar kebersihan steril sekuriti..."
                                class="w-full rounded-lg border border-slate-200 p-2.5 text-xs text-ptkp-body focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                            />
                        </div>
                    </div>

                    <!-- Kualifikasi Minimal -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 pt-4 border-t border-slate-100">
                        <div>
                            <label class="mb-1 block font-heading text-xs font-bold text-ptkp-navy">
                                Pendidikan Minimal
                            </label>
                            <input
                                v-model="form.specification.education_level"
                                type="text"
                                placeholder="Contoh: SMK Grafika / D3 / S1 Teknik Mesin..."
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-ptkp-body focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                            />
                        </div>

                        <div>
                            <label class="mb-1 block font-heading text-xs font-bold text-ptkp-navy">
                                Pengalaman Kerja Relevan
                            </label>
                            <input
                                v-model="form.specification.work_experience"
                                type="text"
                                placeholder="Contoh: Minimal 2 tahun di industri kertas atau manufaktur..."
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-ptkp-body focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                            />
                        </div>

                        <div>
                            <label class="mb-1 block font-heading text-xs font-bold text-ptkp-navy">
                                Sertifikasi yang Dibutuhkan
                            </label>
                            <input
                                v-model="form.specification.certifications"
                                type="text"
                                placeholder="Contoh: Sertifikasi K3, Operator Boiler/Mesin..."
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-ptkp-body focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                            />
                        </div>

                        <div>
                            <label class="mb-1 block font-heading text-xs font-bold text-ptkp-navy">
                                Pelatihan Wajib
                            </label>
                            <input
                                v-model="form.specification.trainings"
                                type="text"
                                placeholder="Contoh: Pengamanan Kertas Berharga, Tata Kelola Mutu PTKP..."
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-ptkp-body focus:border-ptkp-navy focus:outline-hidden focus:ring-1 focus:ring-ptkp-navy"
                            />
                        </div>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="mt-8 flex items-center justify-between border-t border-slate-200 pt-4">
                    <div class="text-xs text-slate-500">
                        * Pastikan seluruh isian telah akurat sesuai standar SOTK SK 009/2026 sebelum diajukan.
                    </div>

                    <div class="flex items-center gap-2">
                        <AppButton
                            variant="primary"
                            size="md"
                            :loading="form.processing"
                            type="button"
                            @click="submit"
                        >
                            {{ mode === 'create' ? 'Simpan Draf Jobdesk' : 'Simpan Perubahan' }}
                        </AppButton>
                    </div>
                </div>
            </AppCard>
        </div>
    </AppShell>
</template>
