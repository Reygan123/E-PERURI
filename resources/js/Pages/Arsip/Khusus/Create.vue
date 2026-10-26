<script setup lang="ts">
import AppButton from '@/Components/AppButton.vue';
import AppCard from '@/Components/AppCard.vue';
import AppInput from '@/Components/AppInput.vue';
import AppSelect from '@/Components/AppSelect.vue';
import AppTextarea from '@/Components/AppTextarea.vue';
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
    types: Array<{ id: string; name: string; code: string }>;
    orgUnits: OrgUnitOption[];
    selectedTypeCode?: string;
}>();

const selectedType = ref(props.selectedTypeCode || 'SPPD');

// 1. SPPD Payload
const sppdData = ref({
    pemberi_perintah: 'Dani Jatnika',
    destination_city: '',
    departure_date: new Date().toISOString().split('T')[0],
    return_date: new Date().toISOString().split('T')[0],
    transport_mode: 'Kendaraan Dinas',
    purpose: '',
    kode_anggaran: '',
    kode_unit_kerja: '',
    terbilang: '',
});

const sppdPegawai = ref<
    Array<{ nama: string; nip: string; unit: string; jabatan: string }>
>([{ nama: '', nip: '', unit: '', jabatan: '' }]);

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
>([
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
]);

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
    lampiran: '1 Berkas',
    kategori: 'Investasi / Non-Investasi / Jasa',
    tujuan_departemen: 'Departemen Dafasum',
    tujuan_seksi: 'Up. Seksi Gudang',
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
>([
    {
        item_code: '',
        nama_barang: '',
        spesifikasi: '',
        jumlah: 1,
        satuan: 'Unit',
        keterangan: '',
    },
]);

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
    patient_name: '',
    patient_nip: '',
    clinic_name: 'Klinik Pratama PTKP',
    treatment_date: new Date().toISOString().split('T')[0],
    diagnosis: 'Rawat jalan dan pembelian obat resep dokter',
    total_cost: 0,
    terbilang: '',
});

const form = useForm({
    type_code: selectedType.value,
    subject: '',
    origin_unit_id: props.orgUnits[0]?.id || '',
    letter_date: new Date().toISOString().split('T')[0],
    content_body: {} as Record<string, any>,
});

const submit = () => {
    form.type_code = selectedType.value;

    if (selectedType.value === 'SPPD') {
        // sync names to biaya table if empty
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

    form.post(route('arsip.khusus.store'));
};
</script>

<template>
    <AppShell title="Buat Formulir Khusus - E-Arsip PTKP">
        <div class="mx-auto max-w-4xl space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-brand-navy-dark font-heading text-2xl font-bold tracking-tight"
                    >
                        Buat Formulir Khusus Digital
                    </h1>
                    <p class="text-brand-slate-light mt-1 font-body text-sm">
                        Pengisian formulir operasional terstruktur PTKP (SPPD,
                        Permintaan Gudang SPB, Biaya Pengobatan BP).
                    </p>
                </div>

                <Link :href="route('arsip.khusus.index')">
                    <AppButton variant="secondary"> &larr; Kembali </AppButton>
                </Link>
            </div>

            <!-- Type Selector Tabs -->
            <div
                class="border-brand-slate-border flex items-center gap-2 border-b pb-3"
            >
                <button
                    v-for="t in types"
                    :key="t.code"
                    type="button"
                    class="rounded-lg px-4 py-2 font-heading text-xs font-bold transition-all"
                    :class="
                        selectedType === t.code
                            ? 'bg-brand-navy-dark text-white shadow-sm'
                            : 'border-brand-slate-border text-brand-slate-dark hover:bg-brand-slate-light/10 border bg-white'
                    "
                    @click="selectedType = t.code"
                >
                    {{ t.code }} - {{ t.name }}
                </button>
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
                                    Unit Asal / Departemen Pemohon
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
                            </div>
                        </div>
                    </div>

                    <!-- Section: SPPD Form -->
                    <div
                        v-if="selectedType === 'SPPD'"
                        class="border-brand-slate-border space-y-6 border-t pt-4"
                    >
                        <div
                            class="border-brand-slate-border flex items-center justify-between border-b pb-2"
                        >
                            <h2
                                class="text-brand-navy-dark font-heading text-base font-semibold"
                            >
                                2. Rincian Surat Perintah Dinas (SPPD)
                            </h2>
                            <span class="text-brand-blue text-xs font-semibold"
                                >Nomor Reset Bulanan</span
                            >
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Pejabat Pemberi Perintah Dinas
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppInput
                                    v-model="sppdData.pemberi_perintah"
                                    placeholder="Contoh: Dani Jatnika / Puteri Zulieni"
                                    required
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Kota / Instansi Tujuan Dinas
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppInput
                                    v-model="sppdData.destination_city"
                                    placeholder="Contoh: Perum Peruri - Karawang / Jakarta"
                                    required
                                />
                            </div>
                        </div>

                        <!-- Daftar Pegawai Yang Diperintah -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label
                                    class="text-brand-slate-dark font-body text-xs font-semibold"
                                >
                                    Daftar Pegawai Yang Diperintah (Butir 2
                                    SPPD) <span class="text-rose-500">*</span>
                                </label>
                                <button
                                    type="button"
                                    class="text-brand-blue text-xs font-bold hover:underline"
                                    @click="addSppdPegawai"
                                >
                                    + Tambah Pegawai
                                </button>
                            </div>

                            <div
                                v-for="(peg, idx) in sppdPegawai"
                                :key="idx"
                                class="border-brand-slate-border bg-brand-slate-light/5 grid grid-cols-12 items-center gap-2 rounded-lg border p-3"
                            >
                                <div class="col-span-4">
                                    <label
                                        class="text-brand-slate-light mb-0.5 block text-[10px] font-semibold"
                                        >Nama Pegawai</label
                                    >
                                    <AppInput
                                        v-model="peg.nama"
                                        placeholder="Nama pegawai"
                                        required
                                    />
                                </div>
                                <div class="col-span-2">
                                    <label
                                        class="text-brand-slate-light mb-0.5 block text-[10px] font-semibold"
                                        >No. Reg / NIP</label
                                    >
                                    <AppInput
                                        v-model="peg.nip"
                                        placeholder="1883"
                                        required
                                    />
                                </div>
                                <div class="col-span-3">
                                    <label
                                        class="text-brand-slate-light mb-0.5 block text-[10px] font-semibold"
                                        >Departemen/Seksi</label
                                    >
                                    <AppInput
                                        v-model="peg.unit"
                                        placeholder="SDM"
                                        required
                                    />
                                </div>
                                <div class="col-span-2">
                                    <label
                                        class="text-brand-slate-light mb-0.5 block text-[10px] font-semibold"
                                        >Jabatan</label
                                    >
                                    <AppInput
                                        v-model="peg.jabatan"
                                        placeholder="Kasek / Kaun"
                                        required
                                    />
                                </div>
                                <div class="col-span-1 pt-3 text-center">
                                    <button
                                        v-if="sppdPegawai.length > 1"
                                        type="button"
                                        class="font-bold text-rose-500 hover:text-rose-700"
                                        @click="removeSppdPegawai(idx)"
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
                                    Kode Anggaran & Kode Unit Kerja
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <AppInput
                                        v-model="sppdData.kode_anggaran"
                                        placeholder="Kode Anggaran"
                                    />
                                    <AppInput
                                        v-model="sppdData.kode_unit_kerja"
                                        placeholder="Kode Unit"
                                    />
                                </div>
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Moda Transportasi
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppSelect
                                    v-model="sppdData.transport_mode"
                                    :options="[
                                        {
                                            value: 'Kendaraan Dinas',
                                            label: 'Kendaraan Dinas',
                                        },
                                        {
                                            value: 'Kereta Api',
                                            label: 'Kereta Api (KAI)',
                                        },
                                        {
                                            value: 'Pesawat Udara',
                                            label: 'Pesawat Udara',
                                        },
                                        {
                                            value: 'Kendaraan Pribadi',
                                            label: 'Kendaraan Pribadi',
                                        },
                                        {
                                            value: 'Kendaraan Umum / Travel',
                                            label: 'Kendaraan Umum / Travel',
                                        },
                                    ]"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
                        </div>

                        <div>
                            <label
                                class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                            >
                                Maksud & Tujuan Perjalanan Dinas
                                <span class="text-rose-500">*</span>
                            </label>
                            <AppTextarea
                                v-model="sppdData.purpose"
                                placeholder="Contoh: Pelaksanaan Bimbingan Teknis Pembekalan Calon Asesi di Perum Peruri Karawang"
                                :rows="2"
                                required
                            />
                        </div>

                        <!-- Rincian Biaya Perjalanan Dinas (Halaman 2) -->
                        <div
                            class="border-brand-slate-border space-y-3 border-t pt-4"
                        >
                            <h3
                                class="text-brand-navy-dark font-heading text-sm font-semibold"
                            >
                                Rincian Biaya Perjalanan Dinas (Lampiran Tabel
                                Biaya)
                            </h3>

                            <div
                                v-for="(row, idx) in sppdBiaya"
                                :key="idx"
                                class="border-brand-slate-border grid grid-cols-12 items-center gap-2 rounded-lg border bg-white p-3 text-xs"
                            >
                                <div class="col-span-3">
                                    <label
                                        class="mb-0.5 block text-[10px] text-slate-500"
                                        >Pegawai</label
                                    >
                                    <div
                                        class="truncate font-bold text-slate-800"
                                    >
                                        {{
                                            sppdPegawai[idx]?.nama ||
                                            `Pegawai #${idx + 1}`
                                        }}
                                    </div>
                                </div>
                                <div class="col-span-2">
                                    <label
                                        class="mb-0.5 block text-[10px] text-slate-500"
                                        >Lama Dinas</label
                                    >
                                    <AppInput
                                        v-model="row.lama_dinas"
                                        placeholder="1 Hari"
                                    />
                                </div>
                                <div class="col-span-2">
                                    <label
                                        class="mb-0.5 block text-[10px] text-slate-500"
                                        >Uang Harian (Rp)</label
                                    >
                                    <AppInput
                                        v-model="row.uang_harian"
                                        type="number"
                                        min="0"
                                    />
                                </div>
                                <div class="col-span-2">
                                    <label
                                        class="mb-0.5 block text-[10px] text-slate-500"
                                        >Perlengkapan (Rp)</label
                                    >
                                    <AppInput
                                        v-model="row.uang_perlengkapan"
                                        type="number"
                                        min="0"
                                    />
                                </div>
                                <div class="col-span-3">
                                    <label
                                        class="mb-0.5 block text-[10px] text-slate-500"
                                        >Lain-lain (Rp)</label
                                    >
                                    <AppInput
                                        v-model="row.lain_lain"
                                        type="number"
                                        min="0"
                                    />
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-between rounded-lg bg-[#EEF0FB] p-3 text-sm font-bold text-[#20288E]"
                            >
                                <span>Total Estimasi Biaya:</span>
                                <span
                                    >Rp
                                    {{
                                        totalEstimatedBiaya.toLocaleString(
                                            'id-ID',
                                        )
                                    }}</span
                                >
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Terbilang (Teks)
                                </label>
                                <AppInput
                                    v-model="sppdData.terbilang"
                                    placeholder="Contoh: Tiga Ratus Tiga Puluh Tujuh Ribu Lima Ratus Rupiah"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Section: SPB Form -->
                    <div
                        v-else-if="selectedType === 'SPB'"
                        class="border-brand-slate-border space-y-4 border-t pt-4"
                    >
                        <div
                            class="border-brand-slate-border flex items-center justify-between border-b pb-2"
                        >
                            <h2
                                class="text-brand-navy-dark font-heading text-base font-semibold"
                            >
                                2. Permintaan Barang/Jasa (SPB / SPJ)
                            </h2>
                            <button
                                type="button"
                                class="text-brand-blue text-xs font-bold hover:underline"
                                @click="addSpbItem"
                            >
                                + Tambah Baris Barang
                            </button>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Kategori Permintaan
                                </label>
                                <AppSelect
                                    v-model="spbData.kategori"
                                    :options="[
                                        {
                                            value: 'Investasi',
                                            label: 'Investasi',
                                        },
                                        {
                                            value: 'Non-Investasi',
                                            label: 'Non-Investasi',
                                        },
                                        { value: 'Jasa', label: 'Jasa' },
                                        {
                                            value: 'Investasi / Non-Investasi / Jasa',
                                            label: 'Campuran',
                                        },
                                    ]"
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Kepada Yth. Departemen
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
                                    Up. Seksi
                                </label>
                                <AppInput
                                    v-model="spbData.tujuan_seksi"
                                    placeholder="Up. Seksi Gudang"
                                />
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="(item, idx) in spbItems"
                                :key="idx"
                                class="border-brand-slate-border bg-brand-slate-light/5 space-y-3 rounded-lg border p-4"
                            >
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-xs font-bold text-slate-700"
                                        >Barang #{{ idx + 1 }}</span
                                    >
                                    <button
                                        v-if="spbItems.length > 1"
                                        type="button"
                                        class="text-xs font-bold text-rose-500 hover:text-rose-700"
                                        @click="removeSpbItem(idx)"
                                    >
                                        Hapus Baris
                                    </button>
                                </div>

                                <div class="grid grid-cols-12 gap-3">
                                    <div class="col-span-3">
                                        <label
                                            class="text-brand-slate-light mb-0.5 block text-[10px] font-semibold"
                                            >Item Code (ERP SAFT)</label
                                        >
                                        <AppInput
                                            v-model="item.item_code"
                                            placeholder="127-018-008"
                                        />
                                    </div>
                                    <div class="col-span-5">
                                        <label
                                            class="text-brand-slate-light mb-0.5 block text-[10px] font-semibold"
                                            >Nama Barang
                                            <span class="text-rose-500"
                                                >*</span
                                            ></label
                                        >
                                        <AppInput
                                            v-model="item.nama_barang"
                                            placeholder="Contoh: LAPTOP ASUS Expertbook"
                                            required
                                        />
                                    </div>
                                    <div class="col-span-2">
                                        <label
                                            class="text-brand-slate-light mb-0.5 block text-[10px] font-semibold"
                                            >Jumlah
                                            <span class="text-rose-500"
                                                >*</span
                                            ></label
                                        >
                                        <AppInput
                                            v-model="item.jumlah"
                                            type="number"
                                            min="1"
                                            required
                                        />
                                    </div>
                                    <div class="col-span-2">
                                        <label
                                            class="text-brand-slate-light mb-0.5 block text-[10px] font-semibold"
                                            >Satuan
                                            <span class="text-rose-500"
                                                >*</span
                                            ></label
                                        >
                                        <AppInput
                                            v-model="item.satuan"
                                            placeholder="Unit/Rim/Kg"
                                            required
                                        />
                                    </div>
                                </div>

                                <div
                                    class="grid grid-cols-1 gap-3 sm:grid-cols-2"
                                >
                                    <div>
                                        <label
                                            class="text-brand-slate-light mb-0.5 block text-[10px] font-semibold"
                                            >Type / Ukuran / Merk / Spesifikasi
                                            Lengkap</label
                                        >
                                        <AppTextarea
                                            v-model="item.spesifikasi"
                                            placeholder="CPU, RAM, Storage, Merk..."
                                            :rows="2"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="text-brand-slate-light mb-0.5 block text-[10px] font-semibold"
                                            >Keterangan / Keperluan</label
                                        >
                                        <AppTextarea
                                            v-model="item.keterangan"
                                            placeholder="Pengadaan untuk Departemen..."
                                            :rows="2"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: BP Form -->
                    <div
                        v-else-if="selectedType === 'BP'"
                        class="border-brand-slate-border space-y-4 border-t pt-4"
                    >
                        <h2
                            class="text-brand-navy-dark border-brand-slate-border border-b pb-2 font-heading text-base font-semibold"
                        >
                            2. Data Pasien & Klaim Biaya Pengobatan (BP)
                        </h2>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Nama Pasien / Pegawai
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppInput
                                    v-model="bpData.patient_name"
                                    placeholder="Nama pegawai yang berobat"
                                    required
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    No. Register / NIP Pasien
                                </label>
                                <AppInput
                                    v-model="bpData.patient_nip"
                                    placeholder="Contoh: 1731"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Nama Klinik / Rumah Sakit
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppInput
                                    v-model="bpData.clinic_name"
                                    placeholder="Klinik Pratama PTKP / RSUD Cibabat"
                                    required
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Tanggal Berobat
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppInput
                                    v-model="bpData.treatment_date"
                                    type="date"
                                    required
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Total Biaya Pengobatan (Rp)
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppInput
                                    v-model="bpData.total_cost"
                                    type="number"
                                    min="0"
                                    required
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Terbilang
                                </label>
                                <AppInput
                                    v-model="bpData.terbilang"
                                    placeholder="Contoh: Seratus Lima Puluh Ribu Rupiah"
                                />
                            </div>
                        </div>

                        <div>
                            <label
                                class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                            >
                                Keterangan / Diagnosa Singkat
                            </label>
                            <AppTextarea
                                v-model="bpData.diagnosis"
                                placeholder="Pemeriksaan rawat jalan dan pembelian resep..."
                                :rows="2"
                            />
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div
                        class="border-brand-slate-border flex items-center justify-end gap-3 border-t pt-4"
                    >
                        <Link :href="route('arsip.khusus.index')">
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
                                    : 'Simpan & Generate Formulir'
                            }}
                        </AppButton>
                    </div>
                </AppCard>
            </form>
        </div>
    </AppShell>
</template>
