<script setup lang="ts">
import AppBadge from '@/Components/AppBadge.vue';
import AppLogo from '@/Components/AppLogo.vue';
import NotificationDropdown from '@/Components/NotificationDropdown.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

interface Stats {
    orgUnitsCount?: number;
    positionsCount?: number;
    employeesCount?: number;
    usersCount?: number;
}

withDefaults(defineProps<{ stats?: Stats }>(), {
    stats: () => ({
        orgUnitsCount: 37,
        positionsCount: 12,
        employeesCount: 5,
        usersCount: 4,
    }),
});

const page = usePage();
const user = computed(
    () => page.props.auth?.user as Record<string, any> | undefined,
);
const canAccessPlatform = computed(() =>
    Boolean((page.props.auth as any)?.can_access_platform),
);

// Tanggal lokal Indonesia realtime
const currentDate = ref('');
const updateDate = () => {
    currentDate.value = new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date());
};

onMounted(() => {
    updateDate();
});

// Dropdown Profil User
const userDropdownOpen = ref(false);
const toggleUserDropdown = () => {
    userDropdownOpen.value = !userDropdownOpen.value;
};
const closeUserDropdown = () => {
    userDropdownOpen.value = false;
};

// Modal SIM NEO & Modal Roadmap
const simNeoModalOpen = ref(false);
const selectedRoadmapApp = ref<{
    name: string;
    desc: string;
    stage: string;
} | null>(null);

const openSimNeoModal = () => {
    simNeoModalOpen.value = true;
};
const closeSimNeoModal = () => {
    simNeoModalOpen.value = false;
};

const openRoadmapModal = (name: string, desc: string, stage: string) => {
    selectedRoadmapApp.value = { name, desc, stage };
};
const closeRoadmapModal = () => {
    selectedRoadmapApp.value = null;
};

const logout = () => {
    router.post('/logout');
};

// Daftar Aplikasi & Modul Ekosistem PTKP
const apps = [
    {
        id: 'arsip',
        name: 'E-Arsip (Persuratan)',
        subtitle: 'PT. Kertas Padalarang',
        category: 'Tata Naskah & Dokumen',
        desc: 'Surat Masuk, Surat Keluar resmi, Naskah Dinas (Nota Dinas, Surat Tugas, Pengumuman), dan Persetujuan berjenjang.',
        status: 'active',
        statusLabel: 'Aktif',
        colorHeader: 'bg-ptkp-deep text-white',
        colorBottom: 'text-ptkp-navy border-ptkp-navy/20 hover:bg-ptkp-mist',
        buttonLabel: 'Buka Aplikasi',
        iconType: 'arsip',
        href: '/arsip/surat-masuk',
        isExternal: false,
        quickLinks: [
            { label: 'Surat Masuk', href: '/arsip/surat-masuk' },
            { label: 'Surat Keluar', href: '/arsip/surat-keluar' },
            { label: 'Naskah Dinas', href: '/arsip/naskah-dinas' },
            { label: 'Persetujuan', href: '/arsip/persetujuan' },
        ],
    },
    {
        id: 'platform',
        name: 'Platform Core',
        subtitle: 'Master Data & Tata Kelola',
        category: 'Administrasi Sistem',
        desc: 'Master Unit Organisasi SK 009/2026, Master Jabatan, Data Pegawai terenkripsi, Pengguna & Peran, dan Penomoran Dokumen.',
        status: 'active',
        statusLabel: 'Aktif',
        colorHeader: 'bg-ptkp-navy text-white',
        colorBottom: 'text-ptkp-navy border-ptkp-navy/20 hover:bg-ptkp-mist',
        buttonLabel: 'Buka Master Data',
        iconType: 'platform',
        href: '/platform/org-units',
        isExternal: false,
        quickLinks: [
            { label: 'Unit Organisasi', href: '/platform/org-units' },
            { label: 'Data Pegawai', href: '/platform/employees' },
            { label: 'Pengguna & Peran', href: '/platform/users' },
            { label: 'Audit Log', href: '/platform/audit-logs' },
        ],
    },
    {
        id: 'sdm_jobdesk',
        name: 'Digital Jobdesk PTKP',
        subtitle: 'Uraian Jabatan & Kompetensi',
        category: 'Manajemen SDM & Organisasi',
        desc: 'Direktori profil jabatan SK 009/2026, tugas pokok, wewenang, hubungan kerja "Dalam Hal", dan matriks standar kompetensi PL 1–5.',
        status: 'active',
        statusLabel: 'Aktif',
        colorHeader: 'bg-ptkp-deep text-white',
        colorBottom: 'text-ptkp-navy border-ptkp-navy/20 hover:bg-ptkp-mist',
        buttonLabel: 'Buka Jobdesk',
        iconType: 'briefcase',
        href: '/sdm/jobdesk',
        isExternal: false,
        quickLinks: [
            { label: 'Katalog Jabatan', href: '/sdm/jobdesk' },
            { label: 'Susun Jobdesk Baru', href: '/sdm/jobdesk/create' },
        ],
    },
    {
        id: 'simneo_local',
        name: 'SIM NEO (Local)',
        subtitle: 'Intranet PTKP',
        category: 'ERP & Operasional',
        desc: 'Sistem Informasi Manajemen Terpadu untuk operasional harian via jaringan intranet lokal pabrik Padalarang.',
        status: 'external',
        statusLabel: 'Operasional',
        colorHeader: 'bg-[#00897B] text-white',
        colorBottom:
            'text-[#00897B] border-[#00897B]/20 hover:bg-emerald-50/50',
        buttonLabel: 'Buka New Tab',
        iconType: 'network',
        href: '#',
        isExternal: true,
    },
    {
        id: 'simneo_public',
        name: 'SIM NEO (Public)',
        subtitle: 'Akses Publik / VPN',
        category: 'ERP & Operasional',
        desc: 'Akses eksternal dan jarak jauh ke sistem SIM NEO untuk manajemen dan pegawai dinas luar.',
        status: 'external',
        statusLabel: 'Operasional',
        colorHeader: 'bg-[#E65100] text-white',
        colorBottom: 'text-[#E65100] border-[#E65100]/20 hover:bg-amber-50/50',
        buttonLabel: 'Buka New Tab',
        iconType: 'network',
        href: '#',
        isExternal: true,
    },
    {
        id: 'layanan_wo',
        name: 'Work Order IT (SI-WOPPIT)',
        subtitle: 'Layanan Umum & IT Support',
        category: 'Layanan Fasilitas',
        desc: 'Pengajuan tiket gangguan perangkat, pemeliharaan IT, serta pengelolaan fasilitas operasional perusahaan.',
        status: 'active',
        statusLabel: 'Aktif',
        colorHeader: 'bg-ptkp-deep text-white',
        colorBottom: 'text-ptkp-navy border-ptkp-navy/20 hover:bg-ptkp-mist',
        buttonLabel: 'Buka Work Order',
        iconType: 'desktop',
        href: '/layanan/work-orders',
        isExternal: false,
        quickLinks: [
            { label: 'Papan Antrean', href: '/layanan/work-orders' },
            { label: 'Ajukan Tiket Baru', href: '/layanan/work-orders/create' },
        ],
    },
    {
        id: 'lms',
        name: 'Pembelajaran & Pengembangan',
        subtitle: 'LMS PTKP',
        category: 'Diklat & Kompetensi',
        desc: 'Platform pelatihan daring, materi kompetensi pencetakan kertas pengaman, dan riwayat pelatihan pegawai.',
        status: 'roadmap',
        statusLabel: 'Roadmap',
        stageLabel: 'Tahap 2',
        colorHeader: 'bg-[#334155] text-white',
        colorBottom: 'text-[#334155] border-[#334155]/20 hover:bg-slate-50',
        buttonLabel: 'Segera Hadir',
        iconType: 'book',
        href: '#',
        isExternal: false,
    },
    {
        id: 'sdm_hris',
        name: 'Penilaian Kinerja (NKU & NKI)',
        subtitle: 'SDM (HRIS)',
        category: 'Manajemen SDM',
        desc: 'Evaluasi kinerja triwulan, penilaian sasaran kerja individu (NKI) dan unit (NKU) pegawai PTKP.',
        status: 'roadmap',
        statusLabel: 'Roadmap',
        stageLabel: 'Tahap 3',
        colorHeader: 'bg-[#059669] text-white',
        colorBottom:
            'text-[#059669] border-[#059669]/20 hover:bg-emerald-50/50',
        buttonLabel: 'Segera Hadir',
        iconType: 'chart',
        href: '#',
        isExternal: false,
    },
    {
        id: 'mes',
        name: 'Manufaktur & MES',
        subtitle: 'Security Paper Mill',
        category: 'Produksi Kertas Pengaman',
        desc: 'Perencanaan mesin cetak Fourdrinier, kontrol bahan baku pulp, watermarking, dan pengujian laboratorium mutu.',
        status: 'roadmap',
        statusLabel: 'Roadmap',
        stageLabel: 'Tahap 4',
        colorHeader: 'bg-[#4338CA] text-white',
        colorBottom: 'text-[#4338CA] border-[#4338CA]/20 hover:bg-indigo-50/50',
        buttonLabel: 'Segera Hadir',
        iconType: 'factory',
        href: '#',
        isExternal: false,
    },
];

const visibleApps = computed(() => {
    return apps.filter((app) => {
        if (app.id === 'platform') {
            return canAccessPlatform.value;
        }
        return true;
    });
});

const handleAppClick = (app: (typeof apps)[0]) => {
    if (app.status === 'active') {
        router.visit(app.href);
    } else if (app.status === 'external') {
        openSimNeoModal();
    } else {
        openRoadmapModal(app.name, app.desc, app.stageLabel || 'Segera Hadir');
    }
};
</script>

<template>
    <div class="flex min-h-screen select-none flex-col bg-ptkp-mist font-sans">
        <Head title="Portal Aplikasi - PT Kertas Padalarang" />

        <!-- 1. Top Strip Header (Deep Navy) -->
        <header
            class="shrink-0 border-b border-white/10 bg-ptkp-deep px-4 py-2.5 text-white/90 sm:px-8"
        >
            <div
                class="mx-auto flex max-w-[1360px] items-center justify-between text-xs"
            >
                <!-- Date display -->
                <div class="flex items-center gap-2">
                    <svg
                        class="h-4 w-4 text-ptkp-violet"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5"
                        />
                    </svg>
                    <span
                        class="font-medium capitalize tracking-wide text-white/95"
                        >{{ currentDate }}</span
                    >
                </div>

                <!-- User profile & actions -->
                <div class="relative flex items-center gap-3">
                    <NotificationDropdown />

                    <button
                        type="button"
                        class="flex cursor-pointer items-center gap-2 rounded-lg px-2.5 py-1 text-white transition-colors hover:bg-white/10"
                        :aria-expanded="userDropdownOpen"
                        @click="toggleUserDropdown"
                    >
                        <div
                            class="flex h-6 w-6 items-center justify-center rounded-md border border-white/20 bg-ptkp-navy font-heading text-xs font-bold text-white"
                        >
                            {{
                                user?.name
                                    ? user.name.charAt(0).toUpperCase()
                                    : 'U'
                            }}
                        </div>
                        <span
                            class="hidden max-w-[150px] truncate text-xs font-medium sm:inline"
                        >
                            {{ user?.name || user?.username || 'Pegawai PTKP' }}
                        </span>
                        <svg
                            class="h-3.5 w-3.5 text-white/70"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>

                    <!-- Dropdown menu -->
                    <div
                        v-if="userDropdownOpen"
                        class="absolute right-0 top-full z-50 mt-2 w-56 rounded-xl border border-slate-200 bg-white p-1.5 text-ptkp-body shadow-raised"
                    >
                        <div class="border-b border-slate-100 px-3 py-2">
                            <p
                                class="m-0 font-body text-xs font-bold text-ptkp-navy"
                            >
                                {{ user?.name || 'Pegawai' }}
                            </p>
                            <p
                                class="mb-0 mt-0.5 truncate font-body text-[11px] text-slate-500"
                            >
                                {{ user?.email || user?.username }}
                            </p>
                        </div>

                        <Link
                            href="/profile"
                            class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 font-body text-xs text-ptkp-body transition-colors hover:bg-ptkp-mist hover:text-ptkp-navy"
                            @click="closeUserDropdown"
                        >
                            <svg
                                class="h-4 w-4 text-slate-500"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21c-2.676 0-5.216-.584-7.499-1.632Z"
                                />
                            </svg>
                            Profil Akun
                        </Link>

                        <Link
                            href="/mandatory-password-change"
                            class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 font-body text-xs text-ptkp-body transition-colors hover:bg-ptkp-mist hover:text-ptkp-navy"
                            @click="closeUserDropdown"
                        >
                            <svg
                                class="h-4 w-4 text-slate-500"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"
                                />
                            </svg>
                            Ganti Password
                        </Link>

                        <div class="my-1 h-px bg-slate-100"></div>

                        <button
                            type="button"
                            class="flex w-full cursor-pointer items-center gap-2.5 rounded-lg px-3 py-2 text-left font-body text-xs text-ptkp-error transition-colors hover:bg-ptkp-error-bg"
                            @click="logout"
                        >
                            <svg
                                class="h-4 w-4 text-ptkp-error"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"
                                />
                            </svg>
                            Keluar Sistem
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- 2. Brand Banner Header (White) -->
        <div class="shadow-xs shrink-0 border-b border-ptkp-border bg-white">
            <div
                class="mx-auto flex max-w-[1360px] flex-col items-center justify-between gap-4 px-4 py-4 sm:px-8 sm:py-5 md:flex-row"
            >
                <div class="flex w-full items-center gap-4 sm:gap-6 md:w-auto">
                    <!-- Logo PERURI on light background -->
                    <div
                        class="shadow-xs shrink-0 rounded-lg border border-ptkp-border bg-white p-1.5"
                    >
                        <AppLogo width="160px" theme="on-light" />
                    </div>

                    <div
                        class="hidden h-10 w-px shrink-0 bg-slate-200 sm:block"
                    ></div>

                    <div>
                        <h1
                            class="m-0 font-heading text-lg font-bold leading-snug tracking-tight text-ptkp-navy sm:text-2xl"
                        >
                            Portal Aplikasi PT. Kertas Padalarang
                        </h1>
                        <p
                            class="m-0 mt-0.5 font-body text-xs text-slate-500 sm:text-sm"
                        >
                            Ekosistem Internal Digital Terpadu • Peruri Security
                            Paper Mill
                        </p>
                    </div>
                </div>

                <!-- Quick Info / Status Pill -->
                <div
                    class="hidden items-center gap-3 rounded-xl border border-ptkp-border bg-ptkp-mist px-4 py-2.5 lg:flex"
                >
                    <div
                        class="h-2.5 w-2.5 animate-pulse rounded-full bg-emerald-500"
                    ></div>
                    <div class="flex flex-col text-left">
                        <span
                            class="font-heading text-xs font-bold text-ptkp-navy"
                            >Single Sign-On Aktif</span
                        >
                        <span class="font-body text-[11px] text-slate-500"
                            >Sesi terautentikasi otomatis ke seluruh modul</span
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Main Portal Body -->
        <main
            class="mx-auto flex w-full max-w-[1360px] flex-1 flex-col gap-6 px-4 py-6 sm:px-8 sm:py-8"
        >
            <!-- Welcome Greeting Card -->
            <div
                class="flex flex-col items-start justify-between gap-5 rounded-xl border border-ptkp-border bg-white p-5 shadow-card sm:p-6 md:flex-row md:items-center"
            >
                <div class="flex items-start gap-4 sm:items-center">
                    <div
                        class="shadow-xs flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-ptkp-deep font-heading text-lg font-bold text-white"
                    >
                        {{
                            user?.name ? user.name.charAt(0).toUpperCase() : 'P'
                        }}
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2.5">
                            <h2
                                class="m-0 font-heading text-base font-bold text-ptkp-navy sm:text-lg"
                            >
                                Selamat Datang,
                                {{
                                    user?.name ||
                                    user?.username ||
                                    'Pegawai PTKP'
                                }}
                            </h2>
                            <AppBadge variant="violet" size="sm"
                                >Terverifikasi</AppBadge
                            >
                        </div>
                        <p
                            class="m-0 mt-1 max-w-2xl font-body text-xs leading-relaxed text-slate-600 sm:text-sm"
                        >
                            Klik salah satu aplikasi di bawah untuk langsung
                            masuk tanpa perlu login ulang. Hak akses Anda
                            disesuaikan secara otomatis dengan unit organisasi
                            dan penugasan resmi.
                        </p>
                    </div>
                </div>

                <div
                    class="flex shrink-0 items-center justify-end gap-2 self-stretch sm:self-auto"
                >
                    <Link
                        href="/arsip/surat-masuk"
                        class="shadow-xs inline-flex cursor-pointer items-center justify-center gap-2 rounded-lg bg-ptkp-navy px-4 py-2.5 font-heading text-xs font-bold text-white transition-all hover:bg-ptkp-deep"
                    >
                        Masuk E-Arsip
                        <svg
                            class="h-4 w-4"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"
                            />
                        </svg>
                    </Link>
                </div>
            </div>

            <!-- Application Grid (4 Columns on Desktop, exactly matching Image 2 layout) -->
            <div>
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <h3
                            class="m-0 font-heading text-sm font-bold text-ptkp-navy sm:text-base"
                        >
                            Daftar Aplikasi & Layanan Tersedia
                        </h3>
                        <span class="font-body text-xs text-slate-500"
                            >({{ apps.length }} Modul Terdaftar)</span
                        >
                    </div>
                </div>

                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-4"
                >
                    <div
                        v-for="app in visibleApps"
                        :key="app.id"
                        class="group flex cursor-pointer flex-col overflow-hidden rounded-xl border border-ptkp-border bg-white shadow-card transition-all duration-200 hover:-translate-y-0.5 hover:shadow-raised"
                        @click="handleAppClick(app)"
                    >
                        <!-- Card Upper Section (Colored Banner) -->
                        <div
                            :class="[
                                'relative flex min-h-[110px] items-start justify-between p-5',
                                app.colorHeader,
                            ]"
                        >
                            <!-- Large Left Icon -->
                            <div
                                class="backdrop-blur-xs shadow-xs flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-white/20 bg-white/15 text-white"
                            >
                                <!-- E-Arsip Icon -->
                                <svg
                                    v-if="app.iconType === 'arsip'"
                                    class="h-7 w-7"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.8"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"
                                    />
                                </svg>

                                <!-- Platform Core Icon -->
                                <svg
                                    v-else-if="app.iconType === 'platform'"
                                    class="h-7 w-7"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.8"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"
                                    />
                                </svg>

                                <!-- Network / ERP Icon (SIM NEO) -->
                                <svg
                                    v-else-if="app.iconType === 'network'"
                                    class="h-7 w-7"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.8"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z"
                                    />
                                </svg>

                                <!-- Desktop / Work Order Icon -->
                                <svg
                                    v-else-if="app.iconType === 'desktop'"
                                    class="h-7 w-7"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.8"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25"
                                    />
                                </svg>

                                <!-- Book / LMS Icon -->
                                <svg
                                    v-else-if="app.iconType === 'book'"
                                    class="h-7 w-7"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.8"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"
                                    />
                                </svg>

                                <!-- Chart / Performance Icon -->
                                <svg
                                    v-else-if="app.iconType === 'chart'"
                                    class="h-7 w-7"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.8"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"
                                    />
                                </svg>

                                <!-- Briefcase / Jobdesk Icon -->
                                <svg
                                    v-else-if="app.iconType === 'briefcase'"
                                    class="h-7 w-7"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.8"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0"
                                    />
                                </svg>

                                <!-- Factory / MES Icon -->
                                <svg
                                    v-else
                                    class="h-7 w-7"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.8"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M11.42 15.17 17.25 21A2.67 2.67 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.07a4.5 4.5 0 0 0 4.486-6.32l-3.27 3.27a1.5 1.5 0 0 1-2.122-2.122l3.27-3.27a4.5 4.5 0 0 0-6.32 4.486c.118.58.094 1.193-.07 1.743"
                                    />
                                </svg>
                            </div>

                            <!-- Title & Category Info on the Right -->
                            <div class="flex flex-col pl-3 text-right">
                                <span
                                    class="font-body text-[11px] font-medium uppercase tracking-wider text-white/70"
                                >
                                    {{ app.category }}
                                </span>
                                <span
                                    class="mt-0.5 font-heading text-sm font-bold leading-tight text-white sm:text-base"
                                >
                                    {{ app.name }}
                                </span>
                                <span
                                    class="mt-1 font-body text-[11px] text-white/80"
                                >
                                    {{ app.subtitle }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Middle Description -->
                        <div
                            class="flex flex-1 flex-col justify-between bg-white p-4 text-ptkp-body"
                        >
                            <p
                                class="m-0 font-body text-xs leading-relaxed text-slate-600"
                            >
                                {{ app.desc }}
                            </p>

                            <!-- Optional Sub-Links for E-Arsip and Platform -->
                            <div
                                v-if="app.quickLinks"
                                class="mt-3 flex flex-wrap gap-1.5 border-t border-slate-100 pt-3"
                                @click.stop
                            >
                                <Link
                                    v-for="sub in app.quickLinks"
                                    :key="sub.label"
                                    :href="sub.href"
                                    class="rounded bg-ptkp-mist px-2 py-0.5 font-body text-[11px] font-medium text-ptkp-navy transition-colors hover:bg-ptkp-navy hover:text-white"
                                >
                                    {{ sub.label }}
                                </Link>
                            </div>
                        </div>

                        <!-- Card Bottom Bar (Action Row exactly like Image 2) -->
                        <div
                            :class="[
                                'flex items-center justify-between border-t px-4 py-2.5 font-body text-xs font-semibold transition-colors',
                                app.colorBottom,
                            ]"
                        >
                            <span class="flex items-center gap-1.5">
                                {{ app.buttonLabel }}
                                <span
                                    v-if="app.stageLabel"
                                    class="text-[10px] font-normal text-slate-400"
                                >
                                    ({{ app.stageLabel }})
                                </span>
                            </span>

                            <div
                                class="bg-current/10 flex h-6 w-6 items-center justify-center rounded-full transition-transform group-hover:translate-x-1"
                            >
                                <svg
                                    class="h-3.5 w-3.5 text-current"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m8.25 4.5 7.5 7.5-7.5 7.5"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- 4. Footer Bar (Deep Navy, exactly matching Image 2 style) -->
        <footer
            class="mt-auto shrink-0 border-t border-white/10 bg-ptkp-deep px-4 py-4 text-white/70 sm:px-8"
        >
            <div
                class="mx-auto flex max-w-[1360px] flex-col items-center justify-between gap-2 text-center font-body text-xs sm:flex-row sm:text-left"
            >
                <div>All Rights Reserved | © PT. Kertas Padalarang - 2026</div>
                <div class="text-[11px] text-white/50">
                    Peruri Security Paper Mill • Ekosistem Terpadu v2.0
                </div>
            </div>
        </footer>

        <!-- SIM NEO Modal Dialog -->
        <div
            v-if="simNeoModalOpen"
            class="backdrop-blur-xs fixed inset-0 z-50 flex items-center justify-center bg-ptkp-deep/70 p-4"
            @click.self="closeSimNeoModal"
        >
            <div
                class="animate-in fade-in zoom-in-95 flex w-full max-w-md flex-col gap-4 rounded-xl border border-ptkp-border bg-white p-6 shadow-2xl duration-150"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-100 pb-3"
                >
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#00897B] text-white"
                        >
                            <svg
                                class="h-5 w-5"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z"
                                />
                            </svg>
                        </div>
                        <div>
                            <h4
                                class="m-0 font-heading text-sm font-bold text-ptkp-navy"
                            >
                                Tautan Sistem SIM NEO
                            </h4>
                            <p class="m-0 font-body text-xs text-slate-500">
                                Pilih rute akses ke server ERP
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="cursor-pointer rounded p-1 text-slate-400 hover:text-slate-600"
                        @click="closeSimNeoModal"
                    >
                        ✕
                    </button>
                </div>

                <p class="m-0 font-body text-xs leading-relaxed text-slate-600">
                    Sistem SIM NEO berjalan pada infrastruktur ERP operasional.
                    Silakan pilih rute koneksi yang sesuai dengan posisi
                    jaringan Anda:
                </p>

                <div class="flex flex-col gap-2.5">
                    <a
                        href="http://simneo.ptkp.local"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group flex cursor-pointer items-center justify-between rounded-lg border border-[#00897B]/30 bg-emerald-50/50 p-3 text-ptkp-navy transition-colors hover:bg-emerald-50"
                    >
                        <div class="flex flex-col text-left">
                            <span
                                class="font-heading text-xs font-bold text-[#00897B]"
                                >SIM NEO Local (Intranet)</span
                            >
                            <span class="font-body text-[11px] text-slate-500"
                                >Gunakan jika terhubung Wi-Fi / LAN Pabrik
                                PTKP</span
                            >
                        </div>
                        <svg
                            class="h-4 w-4 text-[#00897B] transition-transform group-hover:translate-x-1"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"
                            />
                        </svg>
                    </a>

                    <a
                        href="https://simneo.ptkp.co.id"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group flex cursor-pointer items-center justify-between rounded-lg border border-[#E65100]/30 bg-amber-50/50 p-3 text-ptkp-navy transition-colors hover:bg-amber-50"
                    >
                        <div class="flex flex-col text-left">
                            <span
                                class="font-heading text-xs font-bold text-[#E65100]"
                                >SIM NEO Public / VPN</span
                            >
                            <span class="font-body text-[11px] text-slate-500"
                                >Gunakan jika berada di luar jaringan
                                kantor</span
                            >
                        </div>
                        <svg
                            class="h-4 w-4 text-[#E65100] transition-transform group-hover:translate-x-1"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"
                            />
                        </svg>
                    </a>
                </div>

                <div class="flex justify-end pt-2">
                    <button
                        type="button"
                        class="cursor-pointer rounded-lg bg-slate-100 px-4 py-2 font-body text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-200"
                        @click="closeSimNeoModal"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- Roadmap Modal Dialog -->
        <div
            v-if="selectedRoadmapApp"
            class="backdrop-blur-xs fixed inset-0 z-50 flex items-center justify-center bg-ptkp-deep/70 p-4"
            @click.self="closeRoadmapModal"
        >
            <div
                class="animate-in fade-in zoom-in-95 flex w-full max-w-md flex-col gap-4 rounded-xl border border-ptkp-border bg-white p-6 shadow-2xl duration-150"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-100 pb-3"
                >
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-ptkp-mist text-sm font-bold text-ptkp-navy"
                        >
                            🔜
                        </div>
                        <div>
                            <h4
                                class="m-0 font-heading text-sm font-bold text-ptkp-navy"
                            >
                                {{ selectedRoadmapApp.name }}
                            </h4>
                            <AppBadge variant="neutral" size="sm">{{
                                selectedRoadmapApp.stage
                            }}</AppBadge>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="cursor-pointer rounded p-1 text-slate-400 hover:text-slate-600"
                        @click="closeRoadmapModal"
                    >
                        ✕
                    </button>
                </div>

                <p class="m-0 font-body text-xs leading-relaxed text-slate-600">
                    {{ selectedRoadmapApp.desc }}
                </p>

                <div
                    class="rounded-lg border border-ptkp-border bg-ptkp-mist p-3.5 font-body text-xs text-ptkp-navy"
                >
                    <strong>Informasi Pengembangan:</strong> Modul ini terjadwal
                    pada roadmap ekosistem digital internal PTKP. Saat ini fokus
                    implementasi berada pada Modul E-Arsip & Tata Naskah Dinas.
                </div>

                <div class="flex justify-end pt-2">
                    <button
                        type="button"
                        class="cursor-pointer rounded-lg bg-ptkp-navy px-4 py-2 font-body text-xs font-semibold text-white transition-colors hover:bg-ptkp-deep"
                        @click="closeRoadmapModal"
                    >
                        Mengerti
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
