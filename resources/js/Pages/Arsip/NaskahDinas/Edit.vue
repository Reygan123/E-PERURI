<script setup lang="ts">
import AppButton from '@/Components/AppButton.vue';
import AppCard from '@/Components/AppCard.vue';
import AppInput from '@/Components/AppInput.vue';
import AppRichText from '@/Components/AppRichText.vue';
import AppSelect from '@/Components/AppSelect.vue';
import AppTextarea from '@/Components/AppTextarea.vue';
import InputError from '@/Components/InputError.vue';
import AppShell from '@/Layouts/AppShell.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface OrgUnitOption {
    id: string;
    code: string;
    name: string;
    level: number;
}

const props = defineProps<{
    letter: {
        id: string;
        document_number: string | null;
        subject: string;
        origin_unit_id: string;
        letter_date: string;
        confidentiality: string;
        urgency: string;
        content_body: Record<string, any> | null;
        status: string;
        type: { id: string; name: string; code: string };
        destinations?: Array<{ org_unit_id: string }>;
    };
    types: Array<{ id: string; name: string; code: string }>;
    orgUnits: OrgUnitOption[];
    revisionNote?: string | null;
}>();

// Pre-fill body states
const bodyHtml = ref(props.letter.content_body?.body_html || '');
const kepada = ref(props.letter.content_body?.kepada || '');
const dariJabatan = ref(
    props.letter.content_body?.dari_jabatan ||
        props.letter.content_body?.dari ||
        '',
);
const lampiran = ref(props.letter.content_body?.lampiran || '-');
const tembusanRaw = ref(
    Array.isArray(props.letter.content_body?.tembusan)
        ? props.letter.content_body.tembusan.join('\n')
        : '',
);

// Surat Tugas specific state
const dasarPenugasan = ref(
    props.letter.content_body?.dasar_penugasan ||
        'Berdasarkan kebutuhan operasional dan penugasan kedinasan di lingkungan perusahaan, PT. Kertas Padalarang menugaskan kepada :',
);
const petugasList = ref<Array<{ nama: string; nip: string; jabatan: string }>>(
    props.letter.content_body?.yang_ditugaskan &&
        props.letter.content_body.yang_ditugaskan.length > 0
        ? props.letter.content_body.yang_ditugaskan
        : [{ nama: '', nip: '', jabatan: '' }],
);
const stTujuan = ref(props.letter.content_body?.keperluan || '');
const stLokasi = ref(props.letter.content_body?.lokasi || '');
const stTglMulai = ref(
    props.letter.content_body?.tanggal_mulai ||
        new Date().toISOString().split('T')[0],
);
const stTglSelesai = ref(
    props.letter.content_body?.tanggal_selesai ||
        new Date().toISOString().split('T')[0],
);

// SK specific state
const menimbangList = ref<string[]>(
    props.letter.content_body?.menimbang &&
        props.letter.content_body.menimbang.length > 0
        ? props.letter.content_body.menimbang
        : [
              'Bahwa dalam rangka kelancaran operasional perusahaan dipandang perlu menetapkan ketentuan ini.',
          ],
);
const mengingatList = ref<string[]>(
    props.letter.content_body?.mengingat &&
        props.letter.content_body.mengingat.length > 0
        ? props.letter.content_body.mengingat
        : ['Anggaran Dasar PT. Kertas Padalarang.'],
);

const addPetugas = () => {
    petugasList.value.push({ nama: '', nip: '', jabatan: '' });
};

const removePetugas = (index: number) => {
    if (petugasList.value.length > 1) {
        petugasList.value.splice(index, 1);
    }
};

const addMenimbang = () => menimbangList.value.push('');
const removeMenimbang = (idx: number) => {
    if (menimbangList.value.length > 1) menimbangList.value.splice(idx, 1);
};

const addMengingat = () => mengingatList.value.push('');
const removeMengingat = (idx: number) => {
    if (mengingatList.value.length > 1) mengingatList.value.splice(idx, 1);
};

const form = useForm({
    _method: 'PUT',
    type_code: props.letter.type.code,
    subject: props.letter.subject,
    origin_unit_id: props.letter.origin_unit_id,
    destination_unit_ids:
        props.letter.destinations?.map((d) => d.org_unit_id) ||
        ([] as string[]),
    letter_date: props.letter.letter_date
        ? props.letter.letter_date.split('T')[0]
        : new Date().toISOString().split('T')[0],
    confidentiality: props.letter.confidentiality || 'biasa',
    urgency: props.letter.urgency || 'biasa',
    content_body: {} as Record<string, any>,
    draft_file: null as File | null,
    submit_now: true,
});

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.draft_file = target.files[0];
    }
};

const submit = () => {
    const tembusanList = tembusanRaw.value
        .split('\n')
        .map((t) => t.trim())
        .filter((t) => t.length > 0);

    let payload: Record<string, any> = {
        body_html: bodyHtml.value,
        lampiran: lampiran.value,
        tembusan: tembusanList.length > 0 ? tembusanList : ['Arsip'],
    };

    if (form.type_code === 'ND') {
        payload.kepada = kepada.value || 'Direktur Utama';
        payload.dari_jabatan = dariJabatan.value;
    } else if (form.type_code === 'ST') {
        payload.dasar_penugasan = dasarPenugasan.value;
        payload.yang_ditugaskan = petugasList.value;
        payload.keperluan = stTujuan.value;
        payload.lokasi = stLokasi.value;
        payload.tanggal_mulai = stTglMulai.value;
        payload.tanggal_selesai = stTglSelesai.value;
    } else if (form.type_code === 'E') {
        payload.kepada = kepada.value || 'Seluruh Unit Kerja / Karyawan';
        payload.dari = dariJabatan.value || 'Direksi PT. Kertas Padalarang';
    } else if (form.type_code === 'KPTS') {
        payload.menimbang = menimbangList.value.filter(
            (m) => m.trim().length > 0,
        );
        payload.mengingat = mengingatList.value.filter(
            (m) => m.trim().length > 0,
        );
    }

    form.content_body = payload;

    form.post(route('arsip.naskah-dinas.update', props.letter.id), {
        forceFormData: true,
    });
};
</script>

<template>
    <AppShell
        :title="`Perbaiki Naskah Dinas - ${letter.document_number || 'Draf'}`"
    >
        <div class="mx-auto max-w-4xl space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-brand-navy-dark font-heading text-2xl font-bold tracking-tight"
                    >
                        Perbaiki Draf Naskah Dinas
                    </h1>
                    <p class="text-brand-slate-light mt-1 font-body text-sm">
                        Sesuaikan isi draf naskah dinas berdasarkan catatan
                        revisi dari atasan atau penelaah.
                    </p>
                </div>

                <Link :href="route('arsip.naskah-dinas.show', letter.id)">
                    <AppButton variant="secondary">
                        &larr; Batal & Kembali
                    </AppButton>
                </Link>
            </div>

            <!-- Revision Note Banner -->
            <div
                v-if="revisionNote || letter.status === 'revision_requested'"
                class="space-y-1 rounded-xl border border-amber-300 bg-amber-50 p-4 text-amber-900"
            >
                <div
                    class="flex items-center gap-2 font-heading text-sm font-bold text-amber-950"
                >
                    <svg
                        class="h-5 w-5 text-amber-700"
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
                    Catatan Perbaikan dari Pejabat Penelaah:
                </div>
                <p class="pl-7 font-body text-xs italic text-amber-800">
                    "{{
                        revisionNote ||
                        'Harap periksa dan sesuaikan kembali draf naskah dinas ini.'
                    }}"
                </p>
                <p class="pl-7 pt-1 font-body text-[11px] text-amber-700/80">
                    * Catatan: Setelah Anda menyimpan dan mengajukan ulang, alur
                    persetujuan akan diulang dari langkah pertama (atasan
                    langsung) sesuai aturan persuratan PTKP.
                </p>
            </div>

            <!-- Form Card -->
            <form @submit.prevent="submit">
                <AppCard class="space-y-6 p-6">
                    <div class="space-y-4">
                        <h2
                            class="text-brand-navy-dark border-brand-slate-border border-b pb-2 font-heading text-base font-semibold"
                        >
                            1. Jenis & Pokok Naskah Dinas
                        </h2>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Jenis Naskah Dinas
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppSelect
                                    v-model="form.type_code"
                                    :options="
                                        types.map((t) => ({
                                            value: t.code,
                                            label: `${t.code} - ${t.name}`,
                                        }))
                                    "
                                    required
                                />
                                <InputError :message="form.errors.type_code" />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Unit Pembuat
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppSelect
                                    v-model="form.origin_unit_id"
                                    :options="
                                        orgUnits.map((u) => ({
                                            value: u.id,
                                            label: `${u.code} - ${u.name}`,
                                        }))
                                    "
                                    required
                                />
                                <InputError
                                    :message="form.errors.origin_unit_id"
                                />
                            </div>
                        </div>

                        <div>
                            <label
                                class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                            >
                                Perihal / Pokok Surat
                                <span class="text-rose-500">*</span>
                            </label>
                            <AppInput
                                v-model="form.subject"
                                placeholder="Contoh: Permohonan Pengadaan Kertas Pengaman / Penugasan Dinas"
                                required
                            />
                            <InputError :message="form.errors.subject" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Tanggal Naskah
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppInput
                                    v-model="form.letter_date"
                                    type="date"
                                    required
                                />
                                <InputError
                                    :message="form.errors.letter_date"
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Sifat Dokumen
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppSelect
                                    v-model="form.confidentiality"
                                    :options="[
                                        { value: 'biasa', label: 'Biasa' },
                                        { value: 'rahasia', label: 'Rahasia' },
                                        {
                                            value: 'sangat_rahasia',
                                            label: 'Sangat Rahasia',
                                        },
                                    ]"
                                    required
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Tingkat Urgensi
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppSelect
                                    v-model="form.urgency"
                                    :options="[
                                        { value: 'biasa', label: 'Biasa' },
                                        { value: 'segera', label: 'Segera' },
                                        {
                                            value: 'amat_segera',
                                            label: 'Amat Segera',
                                        },
                                    ]"
                                    required
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Form Section based on Type Code -->
                    <div
                        class="border-brand-slate-border space-y-4 border-t pt-4"
                    >
                        <h2
                            class="text-brand-navy-dark border-brand-slate-border border-b pb-2 font-heading text-base font-semibold"
                        >
                            2. Detail Dokumen ({{ form.type_code }})
                        </h2>

                        <!-- Specific for Nota Dinas (ND) or Memo (E) -->
                        <div
                            v-if="
                                form.type_code === 'ND' ||
                                form.type_code === 'E'
                            "
                            class="space-y-4"
                        >
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label
                                        class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                    >
                                        Kepada (Tujuan Naskah)
                                        <span class="text-rose-500">*</span>
                                    </label>
                                    <AppInput
                                        v-model="kepada"
                                        placeholder="Contoh: Direktur Utama / Kepala Departemen SDM"
                                        required
                                    />
                                </div>

                                <div>
                                    <label
                                        class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                    >
                                        Dari (Jabatan Pembuat)
                                        <span class="text-rose-500">*</span>
                                    </label>
                                    <AppInput
                                        v-model="dariJabatan"
                                        placeholder="Contoh: Kepala Departemen Teknik"
                                        required
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label
                                        class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                    >
                                        Lampiran
                                    </label>
                                    <AppInput
                                        v-model="lampiran"
                                        placeholder="Contoh: 1 (satu) Berkas / -"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                    >
                                        Tembusan (Satu baris per pihak)
                                    </label>
                                    <AppTextarea
                                        v-model="tembusanRaw"
                                        placeholder="Contoh:&#10;1. Direktur Produksi&#10;2. Arsip"
                                        :rows="2"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Specific for Surat Tugas (ST) -->
                        <div
                            v-else-if="form.type_code === 'ST'"
                            class="space-y-4"
                        >
                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Dasar Penugasan
                                </label>
                                <AppTextarea
                                    v-model="dasarPenugasan"
                                    :rows="2"
                                />
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <label
                                        class="text-brand-slate-dark block font-body text-xs font-semibold"
                                    >
                                        Pegawai yang Ditugaskan
                                    </label>
                                    <AppButton
                                        type="button"
                                        variant="secondary"
                                        size="sm"
                                        @click="addPetugas"
                                    >
                                        + Tambah Pegawai
                                    </AppButton>
                                </div>

                                <div
                                    v-for="(petugas, idx) in petugasList"
                                    :key="idx"
                                    class="bg-brand-slate-light/5 border-brand-slate-border grid grid-cols-1 gap-3 rounded-lg border p-3 sm:grid-cols-3"
                                >
                                    <AppInput
                                        v-model="petugas.nama"
                                        placeholder="Nama Pegawai"
                                        required
                                    />
                                    <AppInput
                                        v-model="petugas.nip"
                                        placeholder="NIP Pegawai"
                                        required
                                    />
                                    <div class="flex items-center gap-2">
                                        <AppInput
                                            v-model="petugas.jabatan"
                                            placeholder="Jabatan"
                                            class="flex-1"
                                            required
                                        />
                                        <button
                                            v-if="petugasList.length > 1"
                                            type="button"
                                            class="p-2 text-rose-500 hover:text-rose-700"
                                            @click="removePetugas(idx)"
                                        >
                                            &times;
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label
                                        class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                    >
                                        Keperluan / Maksud Tugas
                                    </label>
                                    <AppInput
                                        v-model="stTujuan"
                                        placeholder="Contoh: Mengikuti Rapat Koordinasi Teknis di Peruri Jakarta"
                                        required
                                    />
                                </div>

                                <div>
                                    <label
                                        class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                    >
                                        Lokasi Penugasan
                                    </label>
                                    <AppInput
                                        v-model="stLokasi"
                                        placeholder="Contoh: Kawasan Percetakan Uang RI, Karawang"
                                        required
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label
                                        class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                    >
                                        Tanggal Mulai
                                    </label>
                                    <AppInput
                                        v-model="stTglMulai"
                                        type="date"
                                        required
                                    />
                                </div>
                                <div>
                                    <label
                                        class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                    >
                                        Tanggal Selesai
                                    </label>
                                    <AppInput
                                        v-model="stTglSelesai"
                                        type="date"
                                        required
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Specific for SK (KPTS) -->
                        <div
                            v-else-if="form.type_code === 'KPTS'"
                            class="space-y-4"
                        >
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label
                                        class="text-brand-slate-dark block font-body text-xs font-semibold"
                                    >
                                        Poin Menimbang
                                    </label>
                                    <AppButton
                                        type="button"
                                        variant="secondary"
                                        size="sm"
                                        @click="addMenimbang"
                                    >
                                        + Tambah Poin
                                    </AppButton>
                                </div>
                                <div
                                    v-for="(m, idx) in menimbangList"
                                    :key="idx"
                                    class="flex items-center gap-2"
                                >
                                    <AppInput
                                        v-model="menimbangList[idx]"
                                        placeholder="Bahwa..."
                                        class="flex-1"
                                    />
                                    <button
                                        v-if="menimbangList.length > 1"
                                        type="button"
                                        class="p-2 text-rose-500"
                                        @click="removeMenimbang(idx)"
                                    >
                                        &times;
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label
                                        class="text-brand-slate-dark block font-body text-xs font-semibold"
                                    >
                                        Poin Mengingat
                                    </label>
                                    <AppButton
                                        type="button"
                                        variant="secondary"
                                        size="sm"
                                        @click="addMengingat"
                                    >
                                        + Tambah Poin
                                    </AppButton>
                                </div>
                                <div
                                    v-for="(m, idx) in mengingatList"
                                    :key="idx"
                                    class="flex items-center gap-2"
                                >
                                    <AppInput
                                        v-model="mengingatList[idx]"
                                        placeholder="1. Peraturan Perusahaan..."
                                        class="flex-1"
                                    />
                                    <button
                                        v-if="mengingatList.length > 1"
                                        type="button"
                                        class="p-2 text-rose-500"
                                        @click="removeMengingat(idx)"
                                    >
                                        &times;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Isi Dokumen (Rich Text / WYSIWYG) -->
                        <div>
                            <label
                                class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                            >
                                Batang Tubuh / Isi Dokumen
                                <span class="text-rose-500">*</span>
                            </label>
                            <AppRichText
                                v-model="bodyHtml"
                                placeholder="Tuliskan isi surat resmi naskah dinas di sini..."
                            />
                        </div>
                    </div>

                    <!-- Document Upload Option -->
                    <div
                        class="border-brand-slate-border space-y-4 border-t pt-4"
                    >
                        <h2
                            class="text-brand-navy-dark border-brand-slate-border border-b pb-2 font-heading text-base font-semibold"
                        >
                            3. Unggah Berkas PDF Baru (Opsional)
                        </h2>
                        <div
                            class="bg-brand-slate-light/5 border-brand-slate-border space-y-2 rounded-lg border border-dashed p-4"
                        >
                            <input
                                type="file"
                                accept=".pdf"
                                class="text-brand-slate-dark file:bg-brand-navy-dark hover:file:bg-brand-navy block w-full font-body text-xs file:mr-4 file:rounded-lg file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-white"
                                @change="handleFileChange"
                            />
                            <p
                                class="text-brand-slate-light font-body text-[11px]"
                            >
                                Biarkan kosong jika tetap menggunakan format
                                digital sistem yang otomatis di-render ke PDF
                                resmi.
                            </p>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div
                        class="border-brand-slate-border flex flex-col items-center justify-between gap-4 border-t pt-4 sm:flex-row"
                    >
                        <Link
                            :href="route('arsip.naskah-dinas.show', letter.id)"
                        >
                            <AppButton variant="secondary" type="button">
                                Batal
                            </AppButton>
                        </Link>

                        <div class="flex items-center gap-3">
                            <AppButton
                                variant="primary"
                                type="submit"
                                :disabled="form.processing"
                                class="bg-emerald-600 text-white hover:bg-emerald-700"
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
                                        d="M5 13l4 4L19 7"
                                    />
                                </svg>
                                {{
                                    form.processing
                                        ? 'Menyimpan...'
                                        : 'Simpan & Ajukan Ulang Persetujuan'
                                }}
                            </AppButton>
                        </div>
                    </div>
                </AppCard>
            </form>
        </div>
    </AppShell>
</template>
