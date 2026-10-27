<script setup lang="ts">
import AppButton from '@/Components/AppButton.vue';
import AppShell from '@/Layouts/AppShell.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface DeviceType {
    id: string;
    name: string;
    code: string;
}

interface RequesterInfo {
    name: string;
    nip: string;
    email: string;
    org_unit_name: string;
    org_unit_code: string;
}

interface Props {
    deviceTypes: DeviceType[];
    requester: RequesterInfo;
}

const props = defineProps<Props>();

const photoPreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const isDragging = ref(false);

const form = useForm({
    section_name: '',
    device_type_id: '',
    device_name: '',
    it_inventory_code: '',
    issue_description: '',
    reference_memo_number: '',
    photo: null as File | null,
});

const locationPresets = [
    'Seksi Pengendalian Mutu',
    'Ruang Rapat Gedung Administrasi',
    'Seksi Tata Usaha & Kearsipan',
    'Laboratorium Kimia & Fisika',
    'Seksi Produksi Kertas Unit I',
    'Seksi Pemeliharaan Mesin & Listrik',
];

const symptomPresets = [
    'Mati total & tidak menyala',
    'Layar biru (BSOD Memory)',
    'Hasil cetakan bergaris hitam',
    'Kertas macet (paper jam)',
    'Koneksi Wi-Fi sering putus',
    'Komputer hang / not responding',
    'Monitor tidak ada tampilan',
];

const onFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        processFile(target.files[0]);
    }
};

const handleDrop = (e: DragEvent) => {
    isDragging.value = false;
    if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
        processFile(e.dataTransfer.files[0]);
    }
};

const processFile = (file: File) => {
    form.photo = file;
    const reader = new FileReader();
    reader.onload = (re) => {
        photoPreview.value = re.target?.result as string;
    };
    reader.readAsDataURL(file);
};

const removePhoto = () => {
    form.photo = null;
    photoPreview.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const selectCategory = (id: string) => {
    form.device_type_id = id;
};

const applyLocationPreset = (loc: string) => {
    form.section_name = loc;
};

const applySymptomPreset = (symptom: string) => {
    if (!form.issue_description.trim()) {
        form.issue_description = symptom;
    } else if (!form.issue_description.includes(symptom)) {
        form.issue_description += `, ${symptom}`;
    }
};

const selectedDeviceType = computed(() => {
    return props.deviceTypes.find((d) => d.id === form.device_type_id);
});

const isFormValid = computed(() => {
    return (
        form.section_name.trim().length > 0 &&
        form.device_type_id.length > 0 &&
        form.device_name.trim().length > 0 &&
        form.issue_description.trim().length > 0
    );
});

const submit = () => {
    form.post('/layanan/work-orders', {
        forceFormData: true,
    });
};
</script>

<template>
    <AppShell
        title="Ajukan Permohonan Perbaikan IT (Form IT-FR-01) - PT Kertas Padalarang"
        :breadcrumbs="[
            { label: 'Portal', href: '/dashboard' },
            { label: 'Layanan IT', href: '/layanan/work-orders' },
            { label: 'Ajukan Permohonan' },
        ]"
    >
        <div class="mx-auto max-w-7xl space-y-8 animate-fadeIn">
            <!-- Hero Corporate Banner -->
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-ptkp-deep via-ptkp-navy to-[#2631a3] p-6 text-white shadow-xl sm:p-8 transition-all duration-300"
            >
                <div class="absolute -right-12 -top-12 h-64 w-64 rounded-full bg-white/5 blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-8 right-24 h-48 w-48 rounded-full bg-ptkp-violet/15 blur-2xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col justify-between gap-6 md:flex-row md:items-center">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 font-mono text-xs font-semibold tracking-wider text-emerald-300 backdrop-blur-md">
                                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                FORMULIR STANDAR IT-FR-01/REV00
                            </span>
                            <span class="rounded-full bg-white/10 px-3 py-1 font-heading text-xs font-medium text-slate-200">
                                Unit Teknologi Informasi & SMT
                            </span>
                        </div>
                        <h1 class="mt-3 font-heading text-2xl font-bold tracking-tight text-white sm:text-3xl">
                            Permohonan Perbaikan & Pemeliharaan IT
                        </h1>
                        <p class="mt-2 max-w-2xl font-body text-sm text-slate-200">
                            Pusat pelaporan gangguan perangkat komputer, pencetakan sekuriti, infrastruktur jaringan pabrik, dan aplikasi operasional PT Kertas Padalarang.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link
                            href="/layanan/work-orders"
                            class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 font-heading text-sm font-semibold text-white shadow-xs backdrop-blur-md transition-all duration-200 hover:bg-white/20 hover:-translate-y-0.5"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                            </svg>
                            <span>Kembali ke Antrean</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- 2-Column Responsive Layout: Left (Form Workflow) + Right (Sticky Live Ticket Preview) -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12 items-start">
                <!-- Left Column: Form Workflow Steps (8 Cols) -->
                <div class="lg:col-span-8 space-y-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Step 1: Lokasi Kerja & Identitas Pemohon -->
                        <div class="rounded-2xl border border-ptkp-border bg-white p-6 shadow-card transition-all duration-300 hover:shadow-raised">
                            <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-ptkp-navy text-sm font-bold text-white shadow-xs">
                                        1
                                    </div>
                                    <div>
                                        <h2 class="font-heading text-base font-bold text-ptkp-deep">
                                            Identitas Pemohon & Lokasi Ruangan
                                        </h2>
                                        <p class="font-body text-xs text-slate-500">
                                            Data unit kerja penanggung jawab aset yang mengajukan perbaikan
                                        </p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 font-heading text-xs font-semibold text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Terverifikasi Otomatis
                                </span>
                            </div>

                            <!-- User Info Badge Box -->
                            <div class="mb-5 rounded-xl border border-slate-200/80 bg-slate-50/70 p-4">
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                    <div>
                                        <span class="block font-heading text-[11px] font-medium text-slate-500 uppercase tracking-wider">Nama Pemohon</span>
                                        <span class="font-heading text-sm font-bold text-slate-900">{{ requester.name }}</span>
                                    </div>
                                    <div>
                                        <span class="block font-heading text-[11px] font-medium text-slate-500 uppercase tracking-wider">NIP Pegawai</span>
                                        <span class="font-mono text-sm font-semibold text-ptkp-navy">{{ requester.nip }}</span>
                                    </div>
                                    <div>
                                        <span class="block font-heading text-[11px] font-medium text-slate-500 uppercase tracking-wider">Departemen / Divisi</span>
                                        <span class="font-heading text-sm font-semibold text-slate-800">{{ requester.org_unit_name }}</span>
                                    </div>
                                    <div>
                                        <span class="block font-heading text-[11px] font-medium text-slate-500 uppercase tracking-wider">Email Akun</span>
                                        <span class="font-body text-xs text-slate-600 truncate block" :title="requester.email">{{ requester.email }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Input Seksi / Lokasi Spesifik -->
                            <div class="space-y-2">
                                <label for="section_name" class="block font-heading text-xs font-semibold text-slate-900">
                                    Nama Seksi / Lokasi Ruangan Kerja <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                        </svg>
                                    </div>
                                    <input
                                        id="section_name"
                                        v-model="form.section_name"
                                        type="text"
                                        placeholder="Ketik lokasi spesifik ruangan (misal: Ruang Rapat Lt. 1, Lab Kimia, Seksi Gudang Bahan Baku)..."
                                        class="w-full rounded-xl border border-slate-300 pl-10 pr-4 py-2.5 font-body text-sm text-slate-900 placeholder-slate-400 transition-colors focus:border-ptkp-navy focus:outline-none focus:ring-2 focus:ring-ptkp-navy/20"
                                        :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.section_name }"
                                    />
                                </div>
                                <p v-if="form.errors.section_name" class="font-body text-xs text-red-600">
                                    {{ form.errors.section_name }}
                                </p>

                                <!-- Location Quick Presets -->
                                <div class="pt-2">
                                    <span class="block font-heading text-[11px] font-medium text-slate-500 mb-1.5">
                                        Pilihan Cepat Lokasi Populer:
                                    </span>
                                    <div class="flex flex-wrap gap-1.5">
                                        <button
                                            v-for="loc in locationPresets"
                                            :key="loc"
                                            type="button"
                                            @click="applyLocationPreset(loc)"
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 font-body text-xs text-slate-700 transition-all duration-150 hover:border-ptkp-navy/40 hover:bg-ptkp-mist hover:text-ptkp-navy hover:-translate-y-0.5 active:scale-95 cursor-pointer"
                                        >
                                            <span>+</span>
                                            <span>{{ loc }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Spesifikasi Perangkat & Kategori Aset IT -->
                        <div class="rounded-2xl border border-ptkp-border bg-white p-6 shadow-card transition-all duration-300 hover:shadow-raised">
                            <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-ptkp-navy text-sm font-bold text-white shadow-xs">
                                        2
                                    </div>
                                    <div>
                                        <h2 class="font-heading text-base font-bold text-ptkp-deep">
                                            Rincian Perangkat & Identitas Aset IT
                                        </h2>
                                        <p class="font-body text-xs text-slate-500">
                                            Pilih kategori alat dan masukkan nama tipe serta kode inventaris
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-5">
                                <!-- Interactive Category Selection Cards -->
                                <div>
                                    <label class="block font-heading text-xs font-semibold text-slate-900 mb-2">
                                        Kategori Peralatan IT <span class="text-red-500">*</span>
                                    </label>
                                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                        <button
                                            v-for="dev in deviceTypes"
                                            :key="dev.id"
                                            type="button"
                                            @click="selectCategory(dev.id)"
                                            class="flex flex-col items-center justify-center rounded-xl border p-4 text-center transition-all duration-200 cursor-pointer active:scale-95"
                                            :class="[
                                                form.device_type_id === dev.id
                                                    ? 'border-ptkp-navy bg-ptkp-mist/70 ring-2 ring-ptkp-navy/20 shadow-xs'
                                                    : 'border-slate-200 bg-white hover:border-ptkp-navy/40 hover:bg-slate-50',
                                            ]"
                                        >
                                            <div
                                                class="flex h-11 w-11 items-center justify-center rounded-xl mb-2 transition-transform duration-200"
                                                :class="[
                                                    form.device_type_id === dev.id
                                                        ? 'bg-ptkp-navy text-white scale-110 shadow-xs'
                                                        : 'bg-slate-100 text-slate-600',
                                                ]"
                                            >
                                                <!-- Category Icons -->
                                                <svg v-if="dev.code === 'PRINTER'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                </svg>
                                                <svg v-else-if="dev.code === 'NETWORK'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z" />
                                                </svg>
                                                <svg v-else-if="dev.code === 'SOFTWARE'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9.75L16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" />
                                                </svg>
                                                <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3" />
                                                </svg>
                                            </div>
                                            <span class="font-heading text-xs font-semibold text-slate-800 line-clamp-1">
                                                {{ dev.name }}
                                            </span>
                                            <span v-if="form.device_type_id === dev.id" class="mt-1 flex items-center gap-1 font-mono text-[10px] font-bold text-ptkp-navy">
                                                <span class="h-1.5 w-1.5 rounded-full bg-ptkp-navy"></span> Dipilih
                                            </span>
                                        </button>
                                    </div>
                                    <p v-if="form.errors.device_type_id" class="mt-1 font-body text-xs text-red-600">
                                        {{ form.errors.device_type_id }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <!-- Nama / Merek Peralatan -->
                                    <div class="space-y-1.5">
                                        <label for="device_name" class="block font-heading text-xs font-semibold text-slate-900">
                                            Nama / Tipe / Merek Alat <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            id="device_name"
                                            v-model="form.device_name"
                                            type="text"
                                            placeholder="Contoh: PC Lenovo ThinkCentre M70q, Printer Epson L3110..."
                                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 font-body text-sm text-slate-900 placeholder-slate-400 transition-colors focus:border-ptkp-navy focus:outline-none focus:ring-2 focus:ring-ptkp-navy/20"
                                            :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.device_name }"
                                        />
                                        <p v-if="form.errors.device_name" class="font-body text-xs text-red-600">
                                            {{ form.errors.device_name }}
                                        </p>
                                    </div>

                                    <!-- Barcode Label Aset IT -->
                                    <div class="space-y-1.5">
                                        <label for="it_inventory_code" class="block font-heading text-xs font-semibold text-slate-900">
                                            Kode Label Barcode Inventaris IT <span class="text-slate-400 font-normal">(Opsional)</span>
                                        </label>
                                        <div class="relative">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                                                </svg>
                                            </div>
                                            <input
                                                id="it_inventory_code"
                                                v-model="form.it_inventory_code"
                                                type="text"
                                                placeholder="Contoh: IT-PC-PROD-012 atau nomor stiker"
                                                class="w-full rounded-xl border border-slate-300 pl-10 pr-3.5 py-2.5 font-mono text-xs uppercase text-slate-900 placeholder-slate-400 transition-colors focus:border-ptkp-navy focus:outline-none focus:ring-2 focus:ring-ptkp-navy/20"
                                            />
                                        </div>
                                    </div>

                                    <!-- Nomor Memo Pengantar -->
                                    <div class="sm:col-span-2 space-y-1.5">
                                        <label for="reference_memo_number" class="block font-heading text-xs font-semibold text-slate-900">
                                            Nomor Memo / Nota Dinas Pengantar <span class="text-slate-400 font-normal">(Opsional)</span>
                                        </label>
                                        <input
                                            id="reference_memo_number"
                                            v-model="form.reference_memo_number"
                                            type="text"
                                            placeholder="Contoh: 048/ND/PROD-K1/X/2026 (Isi jika ada pengantar dinas tertulis)"
                                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 font-body text-sm text-slate-900 placeholder-slate-400 transition-colors focus:border-ptkp-navy focus:outline-none focus:ring-2 focus:ring-ptkp-navy/20"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Gejala Kerusakan & Bukti Foto Fisik -->
                        <div class="rounded-2xl border border-ptkp-border bg-white p-6 shadow-card transition-all duration-300 hover:shadow-raised">
                            <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-ptkp-navy text-sm font-bold text-white shadow-xs">
                                        3
                                    </div>
                                    <div>
                                        <h2 class="font-heading text-base font-bold text-ptkp-deep">
                                            Uraian Gejala Kerusakan & Lampiran Foto
                                        </h2>
                                        <p class="font-body text-xs text-slate-500">
                                            Jelaskan kendala secara rinci dan lampirkan foto fisik perangkat bila ada
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <!-- Uraian Kerusakan -->
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <label for="issue_description" class="block font-heading text-xs font-semibold text-slate-900">
                                            Uraian Lengkap Gangguan / Kerusakan <span class="text-red-500">*</span>
                                        </label>
                                        <span class="font-mono text-[11px] text-slate-400">
                                            {{ form.issue_description.length }} karakter
                                        </span>
                                    </div>
                                    <textarea
                                        id="issue_description"
                                        v-model="form.issue_description"
                                        rows="4"
                                        placeholder="Ceritakan kronologi gangguan, pesan error yang muncul, atau kondisi fisik perangkat..."
                                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 font-body text-sm text-slate-900 placeholder-slate-400 transition-colors focus:border-ptkp-navy focus:outline-none focus:ring-2 focus:ring-ptkp-navy/20"
                                        :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.issue_description }"
                                    ></textarea>
                                    <p v-if="form.errors.issue_description" class="font-body text-xs text-red-600">
                                        {{ form.errors.issue_description }}
                                    </p>

                                    <!-- Quick Symptom Tags -->
                                    <div class="pt-2">
                                        <span class="block font-heading text-[11px] font-medium text-slate-500 mb-1.5">
                                            Pilihan Cepat Gejala Umum:
                                        </span>
                                        <div class="flex flex-wrap gap-1.5">
                                            <button
                                                v-for="sym in symptomPresets"
                                                :key="sym"
                                                type="button"
                                                @click="applySymptomPreset(sym)"
                                                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 font-body text-xs text-slate-700 transition-all duration-150 hover:border-ptkp-navy/40 hover:bg-ptkp-mist hover:text-ptkp-navy hover:-translate-y-0.5 active:scale-95 cursor-pointer"
                                            >
                                                <span>+</span>
                                                <span>{{ sym }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Drag & Drop Modern File Upload -->
                                <div class="pt-3">
                                    <label class="block font-heading text-xs font-semibold text-slate-900 mb-2">
                                        Lampiran Foto Kerusakan Fisik <span class="text-slate-400 font-normal">(Opsional, maks 5 MB)</span>
                                    </label>

                                    <!-- If photo selected -->
                                    <div v-if="photoPreview" class="relative overflow-hidden rounded-2xl border-2 border-ptkp-navy/30 bg-ptkp-mist/40 p-4 transition-all duration-200">
                                        <div class="flex flex-col sm:flex-row items-center gap-4">
                                            <img
                                                :src="photoPreview"
                                                alt="Pratinjau Foto"
                                                class="h-32 w-48 rounded-xl object-cover shadow-xs border border-slate-200"
                                            />
                                            <div class="flex-1 text-center sm:text-left">
                                                <div class="flex items-center gap-2 justify-center sm:justify-start">
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                                                        ✓ Foto Terpilih
                                                    </span>
                                                    <span class="font-mono text-xs text-slate-500">
                                                        {{ form.photo?.name }}
                                                    </span>
                                                </div>
                                                <p class="mt-1 font-body text-xs text-slate-600">
                                                    Foto akan diunggah aman ke server platform PT Kertas Padalarang saat form dikirimkan.
                                                </p>
                                                <button
                                                    type="button"
                                                    @click="removePhoto"
                                                    class="mt-3 inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-1.5 font-heading text-xs font-semibold text-red-600 shadow-xs hover:bg-red-50 hover:text-red-700 transition-colors cursor-pointer"
                                                >
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                    <span>Hapus & Ganti Foto</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Upload Dropzone -->
                                    <div
                                        v-else
                                        @dragover.prevent="isDragging = true"
                                        @dragleave.prevent="isDragging = false"
                                        @drop.prevent="handleDrop"
                                        @click="fileInput?.click()"
                                        class="group relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed p-6 text-center transition-all duration-200 cursor-pointer"
                                        :class="[
                                            isDragging
                                                ? 'border-ptkp-navy bg-ptkp-mist/60 scale-[1.01]'
                                                : 'border-slate-300 bg-slate-50/50 hover:border-ptkp-navy/60 hover:bg-slate-50',
                                        ]"
                                    >
                                        <input
                                            ref="fileInput"
                                            type="file"
                                            accept="image/*"
                                            class="hidden"
                                            @change="onFileChange"
                                        />
                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-ptkp-navy/10 text-ptkp-navy mb-3 transition-transform duration-200 group-hover:scale-110">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                            </svg>
                                        </div>
                                        <span class="font-heading text-sm font-semibold text-slate-800">
                                            Klik untuk memilih foto atau seret ke sini
                                        </span>
                                        <span class="mt-1 font-body text-xs text-slate-500">
                                            Format yang didukung: JPG, PNG, WEBP (Maksimal 5 MB)
                                        </span>
                                    </div>
                                    <p v-if="form.errors.photo" class="mt-1 font-body text-xs text-red-600">
                                        {{ form.errors.photo }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Submit Button Bar (Mobile & Form Bottom) -->
                        <div class="flex items-center justify-end gap-3 pt-4">
                            <Link
                                href="/layanan/work-orders"
                                class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 font-heading text-sm font-semibold text-slate-700 shadow-xs hover:bg-slate-50 hover:text-slate-900 transition-colors"
                            >
                                Batalkan
                            </Link>

                            <button
                                type="submit"
                                :disabled="form.processing || !isFormValid"
                                class="inline-flex items-center gap-2 rounded-xl bg-ptkp-navy px-6 py-2.5 font-heading text-sm font-bold text-white shadow-md transition-all duration-200 hover:bg-ptkp-deep hover:-translate-y-0.5 hover:shadow-lg active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 cursor-pointer"
                            >
                                <svg v-if="form.processing" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                </svg>
                                <span>{{ form.processing ? 'Menyimpan Tiket...' : 'Kirim Permohonan Perbaikan IT' }}</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Sticky Live Ticket Preview Card (4 Cols) -->
                <div class="lg:col-span-4 sticky top-6 space-y-6">
                    <!-- Ticket Live Preview Card -->
                    <div class="rounded-2xl border border-ptkp-border bg-white p-6 shadow-card overflow-hidden relative">
                        <div class="absolute -right-10 -bottom-10 h-32 w-32 rounded-full bg-ptkp-navy/5 blur-xl pointer-events-none"></div>

                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <span class="font-heading text-xs font-bold uppercase tracking-wider text-ptkp-navy">
                                Pratinjau Permohonan
                            </span>
                            <span class="rounded-full bg-amber-50 px-2 py-0.5 font-mono text-[10px] font-bold text-amber-700">
                                DRAFT TIKET
                            </span>
                        </div>

                        <!-- Ticket Header Visual -->
                        <div class="rounded-xl bg-gradient-to-br from-slate-900 to-ptkp-deep p-4 text-white mb-4 shadow-xs">
                            <span class="block font-mono text-[10px] text-slate-300 uppercase tracking-widest">Nomor Tiket Antrean</span>
                            <div class="mt-1 flex items-baseline justify-between">
                                <span class="font-mono text-xl font-bold tracking-tight text-white">
                                    IT### <span class="text-xs font-normal text-slate-400 font-sans">(Otomatis)</span>
                                </span>
                                <span class="rounded bg-white/20 px-2 py-0.5 font-mono text-[10px] font-semibold text-emerald-300">
                                    IT-FR-01
                                </span>
                            </div>
                        </div>

                        <!-- Live Details List -->
                        <div class="space-y-3 font-body text-xs">
                            <div class="flex justify-between border-b border-slate-100 pb-2">
                                <span class="text-slate-500">Pemohon:</span>
                                <span class="font-heading font-semibold text-slate-900 text-right">{{ requester.name }}</span>
                            </div>

                            <div class="flex justify-between border-b border-slate-100 pb-2">
                                <span class="text-slate-500">Lokasi / Seksi:</span>
                                <span class="font-heading font-semibold text-ptkp-navy text-right truncate max-w-[160px]">
                                    {{ form.section_name || '-' }}
                                </span>
                            </div>

                            <div class="flex justify-between border-b border-slate-100 pb-2">
                                <span class="text-slate-500">Kategori:</span>
                                <span class="font-heading font-semibold text-slate-900 text-right">
                                    {{ selectedDeviceType?.name || 'Belum dipilih' }}
                                </span>
                            </div>

                            <div class="flex justify-between border-b border-slate-100 pb-2">
                                <span class="text-slate-500">Nama Perangkat:</span>
                                <span class="font-heading font-semibold text-slate-900 text-right truncate max-w-[160px]">
                                    {{ form.device_name || '-' }}
                                </span>
                            </div>

                            <div v-if="form.it_inventory_code" class="flex justify-between border-b border-slate-100 pb-2">
                                <span class="text-slate-500">Barcode IT:</span>
                                <span class="font-mono text-xs font-semibold text-slate-800 text-right">
                                    {{ form.it_inventory_code }}
                                </span>
                            </div>

                            <div class="pt-1">
                                <span class="block text-slate-500 mb-1">Ringkasan Gejala:</span>
                                <p class="rounded-lg bg-slate-50 p-2 text-slate-700 italic border border-slate-200/80 line-clamp-3">
                                    {{ form.issue_description || 'Uraian keluhan akan muncul di sini...' }}
                                </p>
                            </div>
                        </div>

                        <!-- SLA Quick Info -->
                        <div class="mt-5 rounded-xl border border-blue-200/70 bg-blue-50/60 p-3.5">
                            <div class="flex items-start gap-2.5">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-ptkp-navy text-white text-xs font-bold">
                                    ⏱
                                </div>
                                <div>
                                    <span class="block font-heading text-xs font-bold text-slate-900">
                                        Komitmen Respon Cepat (SLA)
                                    </span>
                                    <p class="font-body text-[11px] text-slate-600 mt-0.5">
                                        Tim Teknisi IT merespons tiket dalam tempo &lt; 2 jam kerja sejak permohonan disubmit ke sistem.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
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
