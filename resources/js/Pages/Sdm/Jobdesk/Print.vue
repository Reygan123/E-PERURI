<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';

interface JobDetail {
    id: string;
    document_number: string;
    revision_number: number;
    sotk_reference: string;
    status: string;
    position_id: string;
    position_name: string;
    position_code: string;
    job_group: string;
    grade_recommendation: string;
    job_summary: string;
    direct_superior_name?: string | null;
    subordinate_notes?: string | null;
    operating_challenges?: string | null;
    environmental_conditions?: string | null;
    incidental_duties?: string | null;
    tte_hash?: string | null;
    tte_signed_at?: string | null;
    created_at?: string;
    org_unit: {
        name: string;
        code: string;
    };
    employee?: {
        name: string;
        nip?: string;
    } | null;
    tasks: Array<{
        activity_name: string;
        activity_details: string;
    }>;
    governances: {
        responsibilities: string[];
        authorities: string[];
    };
    working_relations: {
        internal: Array<{ entity: string; matter: string }>;
        external: Array<{ entity: string; matter: string }>;
    };
    competencies: Array<{
        sub_competency: string;
        proficiency_level: number;
        key_indicator: string;
    }>;
    specification?: {
        education_level?: string | null;
        work_experience?: string | null;
        certifications?: string | null;
        trainings?: string | null;
    } | null;
    standards: Array<{
        indicator_name: string;
        target_value: string;
    }>;
    approvals: Array<{
        stage: string;
        role_title: string;
        approver_name: string;
        decision: string;
        notes?: string | null;
        decided_at?: string | null;
    }>;
}

const props = defineProps<{
    job: JobDetail;
}>();

const triggerPrint = () => {
    window.print();
};

onMounted(() => {
    // Optionally trigger print after render
});
</script>

<template>
    <div class="min-h-screen bg-slate-100 p-4 font-serif text-black print:bg-white print:p-0">
        <Head :title="`Cetak Jobdesk - ${job.position_name} - PTKP`" />

        <!-- Floating Action Bar (Hidden on print) -->
        <div class="mx-auto mb-6 flex max-w-4xl items-center justify-between rounded-xl bg-slate-900 px-6 py-3 text-white shadow-lg print:hidden font-sans">
            <div class="flex items-center gap-3">
                <Link
                    :href="route('sdm.jobdesk.show', job.id)"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-semibold hover:bg-white/20 transition-colors"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali ke Dokumen</span>
                </Link>
                <div class="text-xs">
                    <span class="text-white/60">No Dokumen:</span>
                    <span class="font-mono font-bold text-white ml-1">{{ job.document_number }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-500 shadow-sm transition-all"
                    @click="triggerPrint"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak Sekarang (A4 Margin 20mm)</span>
                </button>
            </div>
        </div>

        <!-- A4 Page Container -->
        <div class="a4-sheet mx-auto max-w-4xl bg-white p-10 shadow-md print:max-w-none print:shadow-none print:p-0">
            <!-- Kop Surat Resmi PTKP -->
            <div class="kop-header border-b-2 border-black pb-3 text-center mb-6">
                <div class="font-sans text-xs font-bold tracking-widest uppercase text-slate-800">
                    PT KERTAS PADALARANG
                </div>
                <div class="font-sans text-[10px] tracking-wider uppercase text-slate-600 mt-0.5">
                    PERURI SECURITY PAPER MILL • JL. CIKERUH NO. 1 PADALARANG 40553
                </div>
                <div class="mt-3 border-t border-black/30 pt-3">
                    <h1 class="font-sans text-xl font-bold uppercase tracking-wider text-black">
                        URAIAN JABATAN (JOB DESCRIPTION)
                    </h1>
                    <div class="font-mono text-xs text-slate-700 mt-1">
                        NOMOR: {{ job.document_number }} | EDISI / REVISI: {{ job.revision_number }}
                    </div>
                    <div class="font-sans text-[10px] italic text-slate-600 mt-0.5">
                        Berdasarkan: {{ job.sotk_reference }}
                    </div>
                </div>
            </div>

            <div class="space-y-6 text-[11pt] leading-normal text-black">
                <!-- 1. IDENTIFIKASI JABATAN -->
                <div>
                    <div class="border-b border-black pb-1 mb-2 font-sans text-xs font-bold uppercase tracking-wider">
                        I. Identifikasi Posisi Jabatan
                    </div>
                    <table class="w-full text-xs">
                        <tbody>
                            <tr>
                                <td class="w-48 py-1 font-sans font-bold">1. Nama Jabatan Resmi</td>
                                <td class="w-4 py-1">:</td>
                                <td class="py-1 font-bold">{{ job.position_name }}</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-sans font-bold">2. Kode Posisi</td>
                                <td class="py-1">:</td>
                                <td class="py-1 font-mono">{{ job.position_code }}</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-sans font-bold">3. Unit Organisasi</td>
                                <td class="py-1">:</td>
                                <td class="py-1">{{ job.org_unit.name }} ({{ job.org_unit.code }})</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-sans font-bold">4. Kelompok & Grade</td>
                                <td class="py-1">:</td>
                                <td class="py-1">{{ job.job_group }} / {{ job.grade_recommendation }}</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-sans font-bold">5. Atasan Langsung</td>
                                <td class="py-1">:</td>
                                <td class="py-1">{{ job.direct_superior_name || 'Direksi PTKP' }}</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-sans font-bold">6. Pemangku Jabatan</td>
                                <td class="py-1">:</td>
                                <td class="py-1">
                                    {{ job.employee?.name || 'Belum Ditetapkan' }}
                                    <span v-if="job.employee?.nip"> (NIP: {{ job.employee.nip }})</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 2. STRUKTUR ORGANISASI -->
                <div>
                    <div class="border-b border-black pb-1 mb-2 font-sans text-xs font-bold uppercase tracking-wider">
                        II. Posisi Dalam Struktur Organisasi
                    </div>
                    <div class="border border-black p-3 text-center text-xs space-y-2">
                        <div class="font-sans font-bold text-slate-800">
                            [ATASAN LANGSUNG] {{ job.direct_superior_name || 'Direksi PTKP' }}
                        </div>
                        <div class="text-slate-400">▼</div>
                        <div class="font-sans font-bold text-black border border-black bg-slate-100 inline-block px-4 py-1.5">
                            [POSISI INI] {{ job.position_name }} ({{ job.position_code }})
                        </div>
                        <div v-if="job.subordinate_notes" class="text-slate-400">▼</div>
                        <div v-if="job.subordinate_notes" class="font-sans text-[10px] text-slate-700 italic">
                            Bawahan Langsung: {{ job.subordinate_notes }}
                        </div>
                    </div>
                </div>

                <!-- 3. IKHTISAR JABATAN -->
                <div>
                    <div class="border-b border-black pb-1 mb-2 font-sans text-xs font-bold uppercase tracking-wider">
                        III. Ikhtisar Jabatan (Job Summary)
                    </div>
                    <p class="text-xs leading-relaxed text-justify">
                        {{ job.job_summary }}
                    </p>
                </div>

                <!-- 4. TUGAS POKOK -->
                <div>
                    <div class="border-b border-black pb-1 mb-2 font-sans text-xs font-bold uppercase tracking-wider">
                        IV. Tugas Pokok & Tahapan Prosedur Kerja
                    </div>
                    <table class="w-full border-collapse border border-black text-xs">
                        <thead>
                            <tr class="bg-slate-100 font-sans font-bold">
                                <th class="border border-black px-2 py-1.5 text-center w-8">No</th>
                                <th class="border border-black px-2 py-1.5 w-1/3">Aktivitas Kerja</th>
                                <th class="border border-black px-2 py-1.5">Rincian Tahapan & Standar Prosedur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(task, idx) in job.tasks" :key="idx">
                                <td class="border border-black px-2 py-1.5 text-center">{{ idx + 1 }}</td>
                                <td class="border border-black px-2 py-1.5 font-bold">{{ task.activity_name }}</td>
                                <td class="border border-black px-2 py-1.5 leading-relaxed">{{ task.activity_details }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 5. TANGGUNG JAWAB & WEWENANG -->
                <div>
                    <div class="border-b border-black pb-1 mb-2 font-sans text-xs font-bold uppercase tracking-wider">
                        V. Tanggung Jawab & Wewenang Jabatan
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div class="border border-black p-2.5">
                            <div class="font-sans font-bold mb-1.5 border-b border-black/30 pb-1">Tanggung Jawab:</div>
                            <ol class="list-decimal pl-4 space-y-1">
                                <li v-for="(resp, idx) in job.governances.responsibilities" :key="idx">{{ resp }}</li>
                            </ol>
                        </div>
                        <div class="border border-black p-2.5">
                            <div class="font-sans font-bold mb-1.5 border-b border-black/30 pb-1">Wewenang:</div>
                            <ol class="list-decimal pl-4 space-y-1">
                                <li v-for="(auth, idx) in job.governances.authorities" :key="idx">{{ auth }}</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- 6. HUBUNGAN KERJA -->
                <div>
                    <div class="border-b border-black pb-1 mb-2 font-sans text-xs font-bold uppercase tracking-wider">
                        VI. Hubungan Kerja Internal & Eksternal (Format "Dalam Hal")
                    </div>
                    <table class="w-full border-collapse border border-black text-xs">
                        <thead>
                            <tr class="bg-slate-100 font-sans font-bold">
                                <th class="border border-black px-2 py-1.5 text-center w-8">No</th>
                                <th class="border border-black px-2 py-1.5 w-1/3">Pihak yang Dihubungi</th>
                                <th class="border border-black px-2 py-1.5 text-center w-24">Sifat</th>
                                <th class="border border-black px-2 py-1.5">Dalam Hal (Tujuan & Ruang Lingkup)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(rel, idx) in job.working_relations.internal" :key="`int-${idx}`">
                                <td class="border border-black px-2 py-1.5 text-center">{{ idx + 1 }}</td>
                                <td class="border border-black px-2 py-1.5 font-bold">{{ rel.entity }}</td>
                                <td class="border border-black px-2 py-1.5 text-center">Internal</td>
                                <td class="border border-black px-2 py-1.5">{{ rel.matter }}</td>
                            </tr>
                            <tr v-for="(rel, idx) in job.working_relations.external" :key="`ext-${idx}`">
                                <td class="border border-black px-2 py-1.5 text-center">{{ job.working_relations.internal.length + idx + 1 }}</td>
                                <td class="border border-black px-2 py-1.5 font-bold">{{ rel.entity }}</td>
                                <td class="border border-black px-2 py-1.5 text-center">Eksternal</td>
                                <td class="border border-black px-2 py-1.5">{{ rel.matter }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 7. MASALAH & TANTANGAN -->
                <div>
                    <div class="border-b border-black pb-1 mb-2 font-sans text-xs font-bold uppercase tracking-wider">
                        VII. Masalah & Tantangan Kerja Operasional
                    </div>
                    <p class="text-xs leading-relaxed text-justify">
                        {{ job.operating_challenges || 'Menjaga kepatuhan standar toleransi keamanan kertas berharga tinggi, kontinuitas jadwal produksi, dan akurasi pelaporan secara berkesinambungan.' }}
                    </p>
                </div>

                <!-- 8. STANDAR KOMPETENSI (PL 1-5) -->
                <div>
                    <div class="border-b border-black pb-1 mb-2 font-sans text-xs font-bold uppercase tracking-wider">
                        VIII. Standar Kompetensi Jabatan (Proficiency Level 1–5)
                    </div>
                    <table class="w-full border-collapse border border-black text-xs">
                        <thead>
                            <tr class="bg-slate-100 font-sans font-bold">
                                <th class="border border-black px-2 py-1.5 text-center w-8">No</th>
                                <th class="border border-black px-2 py-1.5 w-1/3">Kompetensi</th>
                                <th class="border border-black px-2 py-1.5 text-center w-24">Tingkat (PL)</th>
                                <th class="border border-black px-2 py-1.5">Indikator Perilaku Utama (Key Behavior)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(comp, idx) in job.competencies" :key="idx">
                                <td class="border border-black px-2 py-1.5 text-center">{{ idx + 1 }}</td>
                                <td class="border border-black px-2 py-1.5 font-bold">{{ comp.sub_competency }}</td>
                                <td class="border border-black px-2 py-1.5 text-center font-mono font-bold">PL {{ comp.proficiency_level }}</td>
                                <td class="border border-black px-2 py-1.5">{{ comp.key_indicator }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 9. KUALIFIKASI MINIMAL -->
                <div>
                    <div class="border-b border-black pb-1 mb-2 font-sans text-xs font-bold uppercase tracking-wider">
                        IX. Spesifikasi & Kualifikasi Minimal Jabatan
                    </div>
                    <table class="w-full text-xs">
                        <tbody>
                            <tr>
                                <td class="w-48 py-1 font-sans font-bold">Pendidikan Minimal</td>
                                <td class="w-4 py-1">:</td>
                                <td class="py-1">{{ job.specification?.education_level || 'D3 / S1' }}</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-sans font-bold">Pengalaman Kerja</td>
                                <td class="py-1">:</td>
                                <td class="py-1">{{ job.specification?.work_experience || 'Minimal 2 tahun di bidang relevan' }}</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-sans font-bold">Sertifikasi Keahlian</td>
                                <td class="py-1">:</td>
                                <td class="py-1">{{ job.specification?.certifications || 'Sertifikasi K3 / Keahlian Teknis Relevan' }}</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-sans font-bold">Pelatihan Wajib</td>
                                <td class="py-1">:</td>
                                <td class="py-1">{{ job.specification?.trainings || 'Sistem Pengamanan Kertas Berharga PTKP' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 10. LEMBAR PENGESAHAN DOKUMEN RESMI -->
                <div class="pt-6 page-break-inside-avoid">
                    <div class="border-b border-black pb-1 mb-4 font-sans text-xs font-bold uppercase tracking-wider">
                        X. Lembar Pengesahan Dokumen Jabatan
                    </div>

                    <div class="grid grid-cols-3 gap-4 text-center text-xs">
                        <div class="border border-black p-3">
                            <div class="font-sans font-bold text-[10px] uppercase text-slate-600">Penyusun (Maker)</div>
                            <div class="my-8 font-mono text-[9px] text-slate-400">[Tersimpan Sistem]</div>
                            <div class="font-sans font-bold text-black border-t border-black/30 pt-1">Pengusul Jabatan</div>
                            <div class="font-sans text-[10px] text-slate-600">Unit Terkait</div>
                        </div>

                        <div class="border border-black p-3">
                            <div class="font-sans font-bold text-[10px] uppercase text-slate-600">Pemeriksa (Checker)</div>
                            <div class="my-8 font-mono text-[9px] text-emerald-700 font-bold">
                                {{ job.status === 'approved' ? '[TERVERIFIKASI ATASAN]' : '[DIPERIKSA]' }}
                            </div>
                            <div class="font-sans font-bold text-black border-t border-black/30 pt-1">Atasan Langsung / Kasi</div>
                            <div class="font-sans text-[10px] text-slate-600">PT Kertas Padalarang</div>
                        </div>

                        <div class="border border-black p-3">
                            <div class="font-sans font-bold text-[10px] uppercase text-slate-600">Pengesah (Approver)</div>
                            <div class="my-8 font-mono text-[9px] text-emerald-700 font-bold">
                                {{ job.status === 'approved' ? '[DISAHKAN SECARA RESMI]' : '[MENUNGGU PENGESAHAN]' }}
                            </div>
                            <div class="font-sans font-bold text-black border-t border-black/30 pt-1">Kepala Departemen SDM</div>
                            <div class="font-sans text-[10px] text-slate-600">PT Kertas Padalarang</div>
                        </div>
                    </div>

                    <div v-if="job.tte_hash" class="mt-4 border border-dashed border-black/40 p-2 text-center text-[9px] font-mono text-slate-600">
                        Keabsahan dokumen ini dapat diverifikasi melalui TTE Balai Sertifikasi Elektronik (BSrE):
                        <br />
                        <span class="font-bold text-black">{{ job.tte_hash }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@page {
    size: A4 portrait;
    margin: 20mm 20mm 20mm 20mm;
}

@media print {
    body {
        margin: 0;
        padding: 0;
        background: white;
    }
    .a4-sheet {
        box-shadow: none !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: none !important;
    }
    .page-break-inside-avoid {
        page-break-inside: avoid;
    }
}
</style>
