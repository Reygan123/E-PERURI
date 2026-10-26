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
    types: Array<{ id: string; name: string; code: string }>;
    orgUnits: OrgUnitOption[];
    defaultTypeCode?: string;
}>();

// Form body states
const bodyHtml = ref('');
const kepada = ref('');
const dariJabatan = ref('');
const lampiran = ref('-');
const tembusanRaw = ref('');

// Surat Tugas specific state
const dasarPenugasan = ref(
    'Berdasarkan kebutuhan operasional dan penugasan kedinasan di lingkungan perusahaan, PT. Kertas Padalarang menugaskan kepada :',
);
const petugasList = ref<Array<{ nama: string; nip: string; jabatan: string }>>([
    { nama: '', nip: '', jabatan: '' },
]);
const stTujuan = ref('');
const stLokasi = ref('');
const stTglMulai = ref(new Date().toISOString().split('T')[0]);
const stTglSelesai = ref(new Date().toISOString().split('T')[0]);

// SK specific state
const menimbangList = ref<string[]>([
    'Bahwa dalam rangka kelancaran operasional perusahaan dipandang perlu menetapkan ketentuan ini.',
]);
const mengingatList = ref<string[]>(['Anggaran Dasar PT. Kertas Padalarang.']);

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
    type_code: props.defaultTypeCode || props.types[0]?.code || 'ND',
    subject: '',
    origin_unit_id: props.orgUnits[0]?.id || '',
    destination_unit_ids: [] as string[],
    letter_date: new Date().toISOString().split('T')[0],
    confidentiality: 'biasa',
    urgency: 'biasa',
    content_body: {} as Record<string, any>,
    draft_file: null as File | null,
    submit_now: false,
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

    form.post(route('arsip.naskah-dinas.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <AppShell title="Buat Naskah Dinas - E-Arsip PTKP">
        <div class="mx-auto max-w-4xl space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-brand-navy-dark font-heading text-2xl font-bold tracking-tight"
                    >
                        Buat Naskah Dinas Digital
                    </h1>
                    <p class="text-brand-slate-light mt-1 font-body text-sm">
                        Penyusunan naskah dinas resmi PT Kertas Padalarang
                        dengan pembuatan dokumen langsung di sistem.
                    </p>
                </div>

                <Link :href="route('arsip.naskah-dinas.index')">
                    <AppButton variant="secondary"> &larr; Kembali </AppButton>
                </Link>
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
                                Perihal / Judul Naskah
                                <span class="text-rose-500">*</span>
                            </label>
                            <AppInput
                                v-model="form.subject"
                                placeholder="Contoh: Permohonan Penandatanganan Kerahasiaan Informasi (NDA)"
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
                                    Sifat Keamanan
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppSelect
                                    v-model="form.confidentiality"
                                    :options="[
                                        { value: 'biasa', label: 'Biasa' },
                                        {
                                            value: 'terbatas',
                                            label: 'Terbatas',
                                        },
                                        { value: 'rahasia', label: 'Rahasia' },
                                        {
                                            value: 'sangat_rahasia',
                                            label: 'Sangat Rahasia',
                                        },
                                    ]"
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
                                            value: 'sangat_segera',
                                            label: 'Sangat Segera',
                                        },
                                    ]"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Dynamic fields based on letter type -->
                    <div
                        class="border-brand-slate-border space-y-4 border-t pt-4"
                    >
                        <h2
                            class="text-brand-navy-dark border-brand-slate-border border-b pb-2 font-heading text-base font-semibold"
                        >
                            2. Struktur & Rincian Konten ({{ form.type_code }})
                        </h2>

                        <!-- NOTA DINAS (ND) & MEMO (E) -->
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
                                        Kepada Yth.
                                        <span class="text-rose-500">*</span>
                                    </label>
                                    <AppInput
                                        v-model="kepada"
                                        placeholder="Contoh: Direktur Utama"
                                        required
                                    />
                                </div>

                                <div>
                                    <label
                                        class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                    >
                                        Dari (Jabatan Pembuat)
                                    </label>
                                    <AppInput
                                        v-model="dariJabatan"
                                        placeholder="Contoh: Kepala Departemen SDM"
                                    />
                                </div>
                            </div>

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
                                <AppRichText
                                    v-model="bodyHtml"
                                    label="Isi Naskah Dinas"
                                    placeholder="Tuliskan isi naskah dinas di sini..."
                                    required
                                />
                            </div>
                        </div>

                        <!-- SURAT TUGAS (ST) -->
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
                                    placeholder="Berdasarkan surat/kebutuhan dinas..."
                                />
                            </div>

                            <!-- Petugas rows -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label
                                        class="text-brand-slate-dark font-body text-xs font-semibold"
                                    >
                                        Daftar Pegawai Yang Ditugaskan
                                        <span class="text-rose-500">*</span>
                                    </label>
                                    <button
                                        type="button"
                                        class="text-brand-blue text-xs font-bold hover:underline"
                                        @click="addPetugas"
                                    >
                                        + Tambah Pegawai
                                    </button>
                                </div>

                                <div
                                    v-for="(pet, idx) in petugasList"
                                    :key="idx"
                                    class="border-brand-slate-border bg-brand-slate-light/5 grid grid-cols-12 items-center gap-3 rounded-lg border p-3"
                                >
                                    <div class="col-span-5">
                                        <label
                                            class="text-brand-slate-light mb-0.5 block text-[10px] font-semibold"
                                            >Nama Pegawai</label
                                        >
                                        <AppInput
                                            v-model="pet.nama"
                                            placeholder="Nama lengkap"
                                            required
                                        />
                                    </div>
                                    <div class="col-span-3">
                                        <label
                                            class="text-brand-slate-light mb-0.5 block text-[10px] font-semibold"
                                            >No. Register / NIP</label
                                        >
                                        <AppInput
                                            v-model="pet.nip"
                                            placeholder="NIP"
                                            required
                                        />
                                    </div>
                                    <div class="col-span-3">
                                        <label
                                            class="text-brand-slate-light mb-0.5 block text-[10px] font-semibold"
                                            >Jabatan</label
                                        >
                                        <AppInput
                                            v-model="pet.jabatan"
                                            placeholder="Jabatan pegawai"
                                            required
                                        />
                                    </div>
                                    <div class="col-span-1 pt-3 text-center">
                                        <button
                                            v-if="petugasList.length > 1"
                                            type="button"
                                            class="font-bold text-rose-500 hover:text-rose-700"
                                            @click="removePetugas(idx)"
                                        >
                                            &times;
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Maksud / Keperluan Tugas
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppTextarea
                                    v-model="stTujuan"
                                    placeholder="Untuk melakukan pemeriksaan / menghadiri rapat..."
                                    :rows="2"
                                    required
                                />
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div>
                                    <label
                                        class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                    >
                                        Lokasi Penugasan
                                    </label>
                                    <AppInput
                                        v-model="stLokasi"
                                        placeholder="Contoh: Kantor Pusat Bea Cukai Jakarta"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                    >
                                        Tanggal Mulai
                                    </label>
                                    <AppInput
                                        v-model="stTglMulai"
                                        type="date"
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
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- SURAT KEPUTUSAN (KPTS) -->
                        <div
                            v-else-if="form.type_code === 'KPTS'"
                            class="space-y-4"
                        >
                            <!-- Menimbang rows -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label
                                        class="text-brand-slate-dark font-body text-xs font-semibold"
                                    >
                                        Poin Menimbang
                                    </label>
                                    <button
                                        type="button"
                                        class="text-brand-blue text-xs font-bold hover:underline"
                                        @click="addMenimbang"
                                    >
                                        + Tambah Poin
                                    </button>
                                </div>
                                <div
                                    v-for="(m, idx) in menimbangList"
                                    :key="idx"
                                    class="flex items-center gap-2"
                                >
                                    <span
                                        class="text-xs font-bold text-slate-500"
                                        >{{
                                            String.fromCharCode(97 + idx)
                                        }}.</span
                                    >
                                    <AppInput
                                        v-model="menimbangList[idx]"
                                        class="flex-1"
                                        placeholder="Poin pertimbangan..."
                                    />
                                    <button
                                        v-if="menimbangList.length > 1"
                                        type="button"
                                        class="font-bold text-rose-500"
                                        @click="removeMenimbang(idx)"
                                    >
                                        &times;
                                    </button>
                                </div>
                            </div>

                            <!-- Mengingat rows -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label
                                        class="text-brand-slate-dark font-body text-xs font-semibold"
                                    >
                                        Poin Mengingat
                                    </label>
                                    <button
                                        type="button"
                                        class="text-brand-blue text-xs font-bold hover:underline"
                                        @click="addMengingat"
                                    >
                                        + Tambah Poin
                                    </button>
                                </div>
                                <div
                                    v-for="(mg, idx) in mengingatList"
                                    :key="idx"
                                    class="flex items-center gap-2"
                                >
                                    <span
                                        class="text-xs font-bold text-slate-500"
                                        >{{ idx + 1 }}.</span
                                    >
                                    <AppInput
                                        v-model="mengingatList[idx]"
                                        class="flex-1"
                                        placeholder="Dasar hukum / peraturan..."
                                    />
                                    <button
                                        v-if="mengingatList.length > 1"
                                        type="button"
                                        class="font-bold text-rose-500"
                                        @click="removeMengingat(idx)"
                                    >
                                        &times;
                                    </button>
                                </div>
                            </div>

                            <div>
                                <AppRichText
                                    v-model="bodyHtml"
                                    label="Diktum Putusan (Menetapkan)"
                                    placeholder="PERTAMA: ..., KEDUA: ..."
                                    required
                                />
                            </div>
                        </div>

                        <!-- PENGUMUMAN (PENG) -->
                        <div v-else class="space-y-4">
                            <AppRichText
                                v-model="bodyHtml"
                                label="Isi Pengumuman"
                                placeholder="Tuliskan isi pengumuman kepada seluruh karyawan di sini..."
                                required
                            />
                        </div>

                        <!-- Tembusan universal -->
                        <div>
                            <label
                                class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                            >
                                Tembusan (Satu baris per pihak)
                            </label>
                            <AppTextarea
                                v-model="tembusanRaw"
                                placeholder="1. Arsip"
                                :rows="2"
                            />
                        </div>
                    </div>

                    <!-- Section Upload Berkas: HIDDEN via v-show="false" per permintaan user -->
                    <div
                        v-show="false"
                        class="border-brand-slate-border space-y-4 border-t pt-4"
                    >
                        <h2
                            class="text-brand-navy-dark border-brand-slate-border border-b pb-2 font-heading text-base font-semibold"
                        >
                            Berkas Lampiran Tambahan (Disembunyikan)
                        </h2>
                        <input
                            type="file"
                            class="text-brand-slate-dark block w-full text-xs"
                            accept=".pdf,.docx,.doc"
                            @change="handleFileChange"
                        />
                    </div>

                    <div class="border-brand-slate-border border-t pt-4">
                        <div
                            class="bg-brand-slate-light/5 border-brand-slate-border rounded-lg border p-4"
                        >
                            <label
                                class="flex cursor-pointer items-center gap-2"
                            >
                                <input
                                    v-model="form.submit_now"
                                    type="checkbox"
                                    class="border-brand-slate-border text-brand-navy-dark focus:ring-brand-blue rounded"
                                />
                                <span
                                    class="text-brand-slate-dark font-body text-xs font-medium"
                                >
                                    Langsung ajukan naskah ke pejabat
                                    penandatangan setelah disimpan
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div
                        class="border-brand-slate-border flex items-center justify-end gap-3 border-t pt-4"
                    >
                        <Link :href="route('arsip.naskah-dinas.index')">
                            <AppButton variant="secondary" type="button">
                                Batal
                            </AppButton>
                        </Link>
                        <AppButton
                            variant="primary"
                            type="submit"
                            :disabled="form.processing"
                        >
                            {{
                                form.processing
                                    ? 'Menyimpan & Generate PDF...'
                                    : 'Simpan Naskah Dinas'
                            }}
                        </AppButton>
                    </div>
                </AppCard>
            </form>
        </div>
    </AppShell>
</template>
