<script setup lang="ts">
import AppButton from '@/Components/AppButton.vue';
import AppCard from '@/Components/AppCard.vue';
import AppInput from '@/Components/AppInput.vue';
import AppSelect from '@/Components/AppSelect.vue';
import AppTextarea from '@/Components/AppTextarea.vue';
import InputError from '@/Components/InputError.vue';
import AppShell from '@/Layouts/AppShell.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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
        content_body: Record<string, any> | null;
        status: string;
        type: { id: string; name: string; code: string };
    };
    types: Array<{ id: string; name: string; code: string }>;
    orgUnits: OrgUnitOption[];
    revisionNote?: string | null;
}>();

const selectedType = ref(props.letter.type.code);

// 1. SPPD Payload
const sppdData = ref({
    pemberi_perintah:
        props.letter.content_body?.pemberi_perintah || 'Dani Jatnika',
    destination_city: props.letter.content_body?.destination_city || '',
    departure_date:
        props.letter.content_body?.departure_date ||
        new Date().toISOString().split('T')[0],
    return_date:
        props.letter.content_body?.return_date ||
        new Date().toISOString().split('T')[0],
    transport_mode:
        props.letter.content_body?.transport_mode || 'Kendaraan Dinas',
    purpose: props.letter.content_body?.purpose || '',
    kode_anggaran: props.letter.content_body?.kode_anggaran || '',
    kode_unit_kerja: props.letter.content_body?.kode_unit_kerja || '',
    terbilang: props.letter.content_body?.terbilang || '',
});

const sppdPegawai = ref<
    Array<{ nama: string; nip: string; unit: string; jabatan: string }>
>(
    Array.isArray(props.letter.content_body?.pegawai) &&
        props.letter.content_body.pegawai.length > 0
        ? props.letter.content_body.pegawai
        : [{ nama: '', nip: '', unit: '', jabatan: '' }],
);

const sppdBiaya = ref<
    Array<{
        nama: string;
        nip: string;
        jabatan: string;
        dept: string;
        lama_dinas: string;
        uang_harian: number;
        uang_perlengkapan: number;
        lain_lain: number;
    }>
>(
    Array.isArray(props.letter.content_body?.rincian_biaya) &&
        props.letter.content_body.rincian_biaya.length > 0
        ? props.letter.content_body.rincian_biaya
        : [
              {
                  nama: '',
                  nip: '',
                  jabatan: '',
                  dept: '',
                  lama_dinas: '1 Hari',
                  uang_harian: 125000,
                  uang_perlengkapan: 0,
                  lain_lain: 0,
              },
          ],
);

const addSppdPegawai = () => {
    sppdPegawai.value.push({ nama: '', nip: '', unit: '', jabatan: '' });
    sppdBiaya.value.push({
        nama: '',
        nip: '',
        jabatan: '',
        dept: '',
        lama_dinas: '1 Hari',
        uang_harian: 100000,
        uang_perlengkapan: 0,
        lain_lain: 0,
    });
};

const removeSppdPegawai = (index: number) => {
    if (sppdPegawai.value.length > 1) {
        sppdPegawai.value.splice(index, 1);
        sppdBiaya.value.splice(index, 1);
    }
};

const totalEstimatedBiaya = computed(() => {
    return sppdBiaya.value.reduce((acc, row) => {
        return (
            acc +
            Number(row.uang_harian || 0) +
            Number(row.uang_perlengkapan || 0) +
            Number(row.lain_lain || 0)
        );
    }, 0);
});

// 2. SPB Payload
const spbData = ref({
    lampiran: props.letter.content_body?.lampiran || '1 Berkas',
    kategori:
        props.letter.content_body?.kategori ||
        'Investasi / Non-Investasi / Jasa',
    tujuan_departemen:
        props.letter.content_body?.tujuan_departemen || 'Departemen Dafasum',
    tujuan_seksi: props.letter.content_body?.tujuan_seksi || 'Up. Seksi Gudang',
});

const spbItems = ref<
    Array<{
        item_code: string;
        nama_barang: string;
        spesifikasi: string;
        jumlah: number;
        satuan: string;
        keterangan: string;
    }>
>(
    Array.isArray(props.letter.content_body?.items) &&
        props.letter.content_body.items.length > 0
        ? props.letter.content_body.items
        : [
              {
                  item_code: '',
                  nama_barang: '',
                  spesifikasi: '',
                  jumlah: 1,
                  satuan: 'Unit',
                  keterangan: '',
              },
          ],
);

const addSpbItem = () => {
    spbItems.value.push({
        item_code: '',
        nama_barang: '',
        spesifikasi: '',
        jumlah: 1,
        satuan: 'Unit',
        keterangan: '',
    });
};

const removeSpbItem = (index: number) => {
    if (spbItems.value.length > 1) {
        spbItems.value.splice(index, 1);
    }
};

// 3. BP Payload
const bpData = ref({
    patient_name: props.letter.content_body?.patient_name || '',
    patient_nip: props.letter.content_body?.patient_nip || '',
    clinic_name:
        props.letter.content_body?.clinic_name || 'Klinik Pratama PTKP',
    treatment_date:
        props.letter.content_body?.treatment_date ||
        new Date().toISOString().split('T')[0],
    diagnosis:
        props.letter.content_body?.diagnosis ||
        'Rawat jalan dan pembelian obat resep dokter',
    total_cost: props.letter.content_body?.total_cost || 0,
    terbilang: props.letter.content_body?.terbilang || '',
});

const form = useForm({
    _method: 'PUT',
    type_code: selectedType.value,
    subject: props.letter.subject || '',
    origin_unit_id: props.letter.origin_unit_id || props.orgUnits[0]?.id || '',
    letter_date: props.letter.letter_date
        ? props.letter.letter_date.split('T')[0]
        : new Date().toISOString().split('T')[0],
    content_body: {} as Record<string, any>,
    submit_now: true,
});

const submit = () => {
    form.type_code = selectedType.value;

    if (selectedType.value === 'SPPD') {
        sppdBiaya.value.forEach((b, idx) => {
            const p = sppdPegawai.value[idx];
            if (p) {
                if (!b.nama) b.nama = p.nama;
                if (!b.nip) b.nip = p.nip;
                if (!b.jabatan) b.jabatan = p.jabatan;
                if (!b.dept) b.dept = p.unit;
            }
        });

        form.subject = `Surat Perintah Perjalanan Dinas ke ${sppdData.value.destination_city}: ${sppdData.value.purpose}`;
        form.content_body = {
            ...sppdData.value,
            pegawai: sppdPegawai.value,
            rincian_biaya: sppdBiaya.value,
            estimated_cost: totalEstimatedBiaya.value,
        };
    } else if (selectedType.value === 'SPB') {
        form.subject = `Permintaan Barang Gudang: ${spbItems.value[0]?.nama_barang || 'Barang Operasional'} (${spbItems.value.length} Item)`;
        form.content_body = {
            ...spbData.value,
            items: spbItems.value,
        };
    } else if (selectedType.value === 'BP') {
        form.subject = `Klaim Biaya Pengobatan: ${bpData.value.patient_name} (${bpData.value.clinic_name})`;
        form.content_body = bpData.value;
    }

    form.post(route('arsip.khusus.update', props.letter.id));
};
</script>

<template>
    <AppShell
        :title="`Perbaiki Formulir - ${letter.document_number || 'Draf'}`"
    >
        <div class="mx-auto max-w-4xl space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-brand-navy-dark font-heading text-2xl font-bold tracking-tight"
                    >
                        Perbaiki Formulir Khusus Digital
                    </h1>
                    <p class="text-brand-slate-light mt-1 font-body text-sm">
                        Sesuaikan data formulir khusus berdasarkan catatan
                        revisi dari pimpinan atau penelaah.
                    </p>
                </div>

                <Link :href="route('arsip.khusus.show', letter.id)">
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
                        'Harap periksa dan sesuaikan kembali draf formulir ini.'
                    }}"
                </p>
            </div>

            <!-- Type Badge (Fixed for editing) -->
            <div
                class="border-brand-slate-border flex items-center gap-2 border-b pb-3"
            >
                <div
                    class="bg-brand-navy-dark shadow-xs rounded-lg px-4 py-2 font-heading text-xs font-bold text-white"
                >
                    Jenis Formulir: {{ letter.type.code }} -
                    {{ letter.type.name }}
                </div>
            </div>

            <form @submit.prevent="submit">
                <AppCard class="space-y-6 p-6">
                    <!-- Common Metadata -->
                    <div class="space-y-4">
                        <h2
                            class="text-brand-navy-dark border-brand-slate-border border-b pb-2 font-heading text-base font-semibold"
                        >
                            1. Unit Asal & Tanggal Formulir
                        </h2>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Unit Kerja Pemohon
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

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Tanggal Pengajuan
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
                        </div>
                    </div>

                    <!-- Section 2: FORM TYPE SPECIFIC CONTENT -->

                    <!-- 2A. SPPD Section -->
                    <div
                        v-if="selectedType === 'SPPD'"
                        class="border-brand-slate-border space-y-6 border-t pt-4"
                    >
                        <h2
                            class="text-brand-navy-dark border-brand-slate-border border-b pb-2 font-heading text-base font-semibold"
                        >
                            2. Rincian Surat Perintah Perjalanan Dinas (SPPD)
                        </h2>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Pemberi Perintah (Atasan / Direksi)
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppInput
                                    v-model="sppdData.pemberi_perintah"
                                    placeholder="Nama Pejabat Pemberi Perintah"
                                    required
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Kota / Tempat Tujuan Dinas
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppInput
                                    v-model="sppdData.destination_city"
                                    placeholder="Contoh: Jakarta / Peruri Karawang"
                                    required
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Tanggal Berangkat
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppInput
                                    v-model="sppdData.departure_date"
                                    type="date"
                                    required
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Tanggal Kembali
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppInput
                                    v-model="sppdData.return_date"
                                    type="date"
                                    required
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Moda Angkutan / Kendaraan
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppSelect
                                    v-model="sppdData.transport_mode"
                                    :options="[
                                        {
                                            value: 'Kendaraan Dinas',
                                            label: 'Kendaraan Dinas Operasional',
                                        },
                                        {
                                            value: 'Kereta Cepat (Whoosh)',
                                            label: 'Kereta Cepat Whoosh',
                                        },
                                        {
                                            value: 'Kereta Api',
                                            label: 'Kereta Api Indonesia',
                                        },
                                        {
                                            value: 'Pesawat Terbang',
                                            label: 'Pesawat Terbang',
                                        },
                                        {
                                            value: 'Kendaraan Pribadi',
                                            label: 'Kendaraan Pribadi',
                                        },
                                        {
                                            value: 'Bus / Travel',
                                            label: 'Bus / Travel Umum',
                                        },
                                    ]"
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Maksud / Tujuan Perjalanan Dinas
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppInput
                                    v-model="sppdData.purpose"
                                    placeholder="Contoh: Koordinasi teknis pencetakan kertas pita cukai"
                                    required
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Mata Anggaran / Akun Pembebanan
                                </label>
                                <AppInput
                                    v-model="sppdData.kode_anggaran"
                                    placeholder="Contoh: 512.01 (Beban Perjalanan Dinas)"
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Unit Kerja Pembebanan
                                </label>
                                <AppInput
                                    v-model="sppdData.kode_unit_kerja"
                                    placeholder="Contoh: Departemen Produksi"
                                />
                            </div>
                        </div>

                        <!-- Tabel Pegawai yang Diperintahkan -->
                        <div class="space-y-3 pt-3">
                            <div class="flex items-center justify-between">
                                <h3
                                    class="text-brand-navy-dark font-heading text-xs font-bold uppercase tracking-wider"
                                >
                                    Daftar Pegawai yang Menjalankan Dinas
                                </h3>
                                <AppButton
                                    variant="secondary"
                                    size="sm"
                                    type="button"
                                    @click="addSppdPegawai"
                                >
                                    + Tambah Pegawai
                                </AppButton>
                            </div>

                            <div class="space-y-3">
                                <div
                                    v-for="(p, idx) in sppdPegawai"
                                    :key="idx"
                                    class="bg-brand-slate-light/5 border-brand-slate-border relative grid grid-cols-1 gap-3 rounded-lg border p-4 sm:grid-cols-4"
                                >
                                    <div>
                                        <label
                                            class="text-brand-slate-dark mb-1 block font-body text-[11px] font-semibold"
                                        >
                                            Nama Pegawai
                                            <span class="text-rose-500">*</span>
                                        </label>
                                        <AppInput
                                            v-model="p.nama"
                                            placeholder="Nama Lengkap"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="text-brand-slate-dark mb-1 block font-body text-[11px] font-semibold"
                                        >
                                            NIP Pegawai
                                            <span class="text-rose-500">*</span>
                                        </label>
                                        <AppInput
                                            v-model="p.nip"
                                            placeholder="NIP"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="text-brand-slate-dark mb-1 block font-body text-[11px] font-semibold"
                                        >
                                            Jabatan
                                            <span class="text-rose-500">*</span>
                                        </label>
                                        <AppInput
                                            v-model="p.jabatan"
                                            placeholder="Jabatan"
                                            required
                                        />
                                    </div>
                                    <div class="flex items-end gap-2">
                                        <div class="flex-1">
                                            <label
                                                class="text-brand-slate-dark mb-1 block font-body text-[11px] font-semibold"
                                            >
                                                Departemen / Unit
                                                <span class="text-rose-500"
                                                    >*</span
                                                >
                                            </label>
                                            <AppInput
                                                v-model="p.unit"
                                                placeholder="Unit Kerja"
                                                required
                                            />
                                        </div>
                                        <button
                                            v-if="sppdPegawai.length > 1"
                                            type="button"
                                            class="mb-1 rounded-lg p-2 text-xs text-rose-600 hover:bg-rose-50"
                                            title="Hapus Pegawai"
                                            @click="removeSppdPegawai(idx)"
                                        >
                                            &times;
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabel Rincian Biaya Perjalanan Dinas -->
                        <div class="space-y-3 pt-3">
                            <h3
                                class="text-brand-navy-dark font-heading text-xs font-bold uppercase tracking-wider"
                            >
                                Rincian Estimasi Biaya Perjalanan Dinas (Harian
                                & Transportasi)
                            </h3>

                            <div class="space-y-3">
                                <div
                                    v-for="(b, idx) in sppdBiaya"
                                    :key="idx"
                                    class="bg-brand-blue/5 border-brand-blue/20 grid grid-cols-1 gap-3 rounded-lg border p-4 sm:grid-cols-5"
                                >
                                    <div>
                                        <label
                                            class="text-brand-slate-dark mb-1 block font-body text-[11px] font-semibold"
                                        >
                                            Pegawai #{{ idx + 1 }}
                                        </label>
                                        <div
                                            class="text-brand-navy-dark pt-1 text-xs font-bold"
                                        >
                                            {{
                                                sppdPegawai[idx]?.nama ||
                                                `Pegawai ${idx + 1}`
                                            }}
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            class="text-brand-slate-dark mb-1 block font-body text-[11px] font-semibold"
                                        >
                                            Lama Dinas
                                        </label>
                                        <AppInput
                                            v-model="b.lama_dinas"
                                            placeholder="Contoh: 1 Hari"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="text-brand-slate-dark mb-1 block font-body text-[11px] font-semibold"
                                        >
                                            Uang Harian (Rp)
                                        </label>
                                        <AppInput
                                            v-model.number="b.uang_harian"
                                            type="number"
                                            min="0"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="text-brand-slate-dark mb-1 block font-body text-[11px] font-semibold"
                                        >
                                            Uang Perlengkapan (Rp)
                                        </label>
                                        <AppInput
                                            v-model.number="b.uang_perlengkapan"
                                            type="number"
                                            min="0"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="text-brand-slate-dark mb-1 block font-body text-[11px] font-semibold"
                                        >
                                            Biaya Lain-lain (Rp)
                                        </label>
                                        <AppInput
                                            v-model.number="b.lain_lain"
                                            type="number"
                                            min="0"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div
                                class="bg-brand-navy-dark flex items-center justify-between rounded-lg p-4 text-white"
                            >
                                <span
                                    class="font-heading text-xs font-bold uppercase tracking-wider"
                                >
                                    Total Estimasi Biaya SPPD
                                </span>
                                <span
                                    class="font-heading text-lg font-extrabold text-amber-300"
                                >
                                    Rp
                                    {{
                                        totalEstimatedBiaya.toLocaleString(
                                            'id-ID',
                                        )
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 2B. SPB Section (Surat Permintaan Barang) -->
                    <div
                        v-if="selectedType === 'SPB'"
                        class="border-brand-slate-border space-y-6 border-t pt-4"
                    >
                        <h2
                            class="text-brand-navy-dark border-brand-slate-border border-b pb-2 font-heading text-base font-semibold"
                        >
                            2. Rincian Surat Permintaan Barang (SPB Gudang)
                        </h2>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Kategori Pengadaan / Permintaan
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppSelect
                                    v-model="spbData.kategori"
                                    :options="[
                                        {
                                            value: 'Investasi / Non-Investasi / Jasa',
                                            label: 'Investasi / Non-Investasi / Jasa',
                                        },
                                        {
                                            value: 'Barang Habis Pakai / ATK',
                                            label: 'Barang Habis Pakai / ATK',
                                        },
                                        {
                                            value: 'Bahan Baku & Penolong Produksi',
                                            label: 'Bahan Baku & Penolong Produksi',
                                        },
                                        {
                                            value: 'Suku Cadang & Pemeliharaan',
                                            label: 'Suku Cadang & Pemeliharaan',
                                        },
                                    ]"
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Lampiran
                                </label>
                                <AppInput
                                    v-model="spbData.lampiran"
                                    placeholder="Contoh: 1 Berkas / Spesifikasi Teknis"
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Tujuan Departemen
                                </label>
                                <AppInput
                                    v-model="spbData.tujuan_departemen"
                                    placeholder="Departemen Dafasum"
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Tujuan Seksi / Up.
                                </label>
                                <AppInput
                                    v-model="spbData.tujuan_seksi"
                                    placeholder="Up. Seksi Gudang"
                                />
                            </div>
                        </div>

                        <!-- Tabel Daftar Barang -->
                        <div class="space-y-3 pt-3">
                            <div class="flex items-center justify-between">
                                <h3
                                    class="text-brand-navy-dark font-heading text-xs font-bold uppercase tracking-wider"
                                >
                                    Daftar Barang yang Diminta
                                </h3>
                                <AppButton
                                    variant="secondary"
                                    size="sm"
                                    type="button"
                                    @click="addSpbItem"
                                >
                                    + Tambah Barang
                                </AppButton>
                            </div>

                            <div class="space-y-3">
                                <div
                                    v-for="(item, idx) in spbItems"
                                    :key="idx"
                                    class="bg-brand-slate-light/5 border-brand-slate-border grid grid-cols-1 gap-3 rounded-lg border p-4 sm:grid-cols-6"
                                >
                                    <div>
                                        <label
                                            class="text-brand-slate-dark mb-1 block font-body text-[11px] font-semibold"
                                        >
                                            Kode Barang (Bila ada)
                                        </label>
                                        <AppInput
                                            v-model="item.item_code"
                                            placeholder="Kode SKU"
                                        />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label
                                            class="text-brand-slate-dark mb-1 block font-body text-[11px] font-semibold"
                                        >
                                            Nama Barang
                                            <span class="text-rose-500">*</span>
                                        </label>
                                        <AppInput
                                            v-model="item.nama_barang"
                                            placeholder="Nama Barang"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="text-brand-slate-dark mb-1 block font-body text-[11px] font-semibold"
                                        >
                                            Spesifikasi / Merk
                                        </label>
                                        <AppInput
                                            v-model="item.spesifikasi"
                                            placeholder="Merk / Tipe"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="text-brand-slate-dark mb-1 block font-body text-[11px] font-semibold"
                                        >
                                            Jumlah & Satuan
                                            <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="flex gap-1">
                                            <AppInput
                                                v-model.number="item.jumlah"
                                                type="number"
                                                min="1"
                                                class="w-20"
                                                required
                                            />
                                            <AppInput
                                                v-model="item.satuan"
                                                placeholder="Unit/Rim"
                                                required
                                            />
                                        </div>
                                    </div>
                                    <div class="flex items-end gap-2">
                                        <div class="flex-1">
                                            <label
                                                class="text-brand-slate-dark mb-1 block font-body text-[11px] font-semibold"
                                            >
                                                Keterangan
                                            </label>
                                            <AppInput
                                                v-model="item.keterangan"
                                                placeholder="Kebutuhan"
                                            />
                                        </div>
                                        <button
                                            v-if="spbItems.length > 1"
                                            type="button"
                                            class="mb-1 rounded-lg p-2 text-xs text-rose-600 hover:bg-rose-50"
                                            title="Hapus Barang"
                                            @click="removeSpbItem(idx)"
                                        >
                                            &times;
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2C. BP Section (Biaya Pengobatan) -->
                    <div
                        v-if="selectedType === 'BP'"
                        class="border-brand-slate-border space-y-6 border-t pt-4"
                    >
                        <h2
                            class="text-brand-navy-dark border-brand-slate-border border-b pb-2 font-heading text-base font-semibold"
                        >
                            2. Rincian Surat Biaya Pengobatan (BP)
                        </h2>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Nama Pasien (Pegawai / Keluarga)
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppInput
                                    v-model="bpData.patient_name"
                                    placeholder="Nama Pasien yang Diobati"
                                    required
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    NIP Pegawai Penanggung Jawab
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppInput
                                    v-model="bpData.patient_nip"
                                    placeholder="NIP Pegawai"
                                    required
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Klinik / Rumah Sakit / Apotek Rujukan
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppInput
                                    v-model="bpData.clinic_name"
                                    placeholder="Contoh: RS Cahya Kawaluyan / Klinik Pratama PTKP"
                                    required
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Tanggal Berobat / Tindakan
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppInput
                                    v-model="bpData.treatment_date"
                                    type="date"
                                    required
                                />
                            </div>

                            <div class="sm:col-span-2">
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Diagnosa / Jenis Tindakan Medis
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppTextarea
                                    v-model="bpData.diagnosis"
                                    :rows="2"
                                    placeholder="Diagnosa dokter atau resep obat"
                                    required
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Total Biaya Pengobatan (Rp)
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppInput
                                    v-model.number="bpData.total_cost"
                                    type="number"
                                    min="0"
                                    required
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Terbilang Jumlah Biaya
                                </label>
                                <AppInput
                                    v-model="bpData.terbilang"
                                    placeholder="Contoh: Tiga Ratus Lima Puluh Ribu Rupiah"
                                />
                            </div>
                        </div>
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
                                    Langsung ajukan ulang draf formulir hasil
                                    revisi ke alur persetujuan pejabat
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Action -->
                    <div
                        class="border-brand-slate-border flex items-center justify-end gap-3 border-t pt-4"
                    >
                        <Link :href="route('arsip.khusus.show', letter.id)">
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
                                    ? 'Menyimpan & Memperbarui PDF...'
                                    : 'Simpan Revisi & Ajukan Ulang'
                            }}
                        </AppButton>
                    </div>
                </AppCard>
            </form>
        </div>
    </AppShell>
</template>
