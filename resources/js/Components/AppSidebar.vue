<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppBadge from './AppBadge.vue';
import AppLogo from './AppLogo.vue';
import AppNavItem from './AppNavItem.vue';

interface Props {
    mobileOpen?: boolean;
}

withDefaults(defineProps<Props>(), {
    mobileOpen: false,
});

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const page = usePage();
const currentUrl = computed(() => page.url);

const isArsipWorkspace = computed(() => currentUrl.value.startsWith('/arsip'));
const isPlatformWorkspace = computed(() =>
    currentUrl.value.startsWith('/platform'),
);
const isSdmWorkspace = computed(() => currentUrl.value.startsWith('/sdm'));
const isLayananWorkspace = computed(() =>
    currentUrl.value.startsWith('/layanan'),
);

const canAccessPlatform = computed(() =>
    Boolean((page.props.auth as any)?.can_access_platform),
);

const isItemActive = (href: string): boolean => {
    if (href.includes('?')) {
        return currentUrl.value.includes(href.split('?')[1]);
    }

    if (href === '/dashboard') {
        return currentUrl.value === '/dashboard';
    }

    // If viewing a general naskah-dinas without query and href is not specific
    if (
        currentUrl.value.startsWith('/arsip/naskah-dinas') &&
        href.includes('/arsip/naskah-dinas')
    ) {
        return currentUrl.value === href;
    }

    return currentUrl.value.startsWith(href);
};

const handleNavClick = () => {
    emit('close');
};
</script>

<template>
    <!-- Mobile Backdrop Overlay -->
    <Transition
        enter-active-class="transition-opacity duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="mobileOpen"
            class="fixed inset-0 z-40 bg-ptkp-deep/75 backdrop-blur-sm lg:hidden"
            aria-hidden="true"
            @click="emit('close')"
        />
    </Transition>

    <!-- Sidebar Component -->
    <aside
        :class="[
            'lg:w-68 fixed inset-y-0 left-0 z-50 flex w-72 select-none flex-col bg-ptkp-deep text-white shadow-2xl transition-transform duration-300 ease-in-out lg:static lg:z-20 lg:h-full lg:min-w-[272px] lg:shrink-0 lg:translate-x-0 lg:shadow-none',
            mobileOpen ? 'translate-x-0' : '-translate-x-full',
        ]"
    >
        <!-- Brand Header -->
        <div
            class="flex shrink-0 items-center justify-between gap-3 border-b border-white/10 bg-ptkp-deep p-4"
        >
            <div
                class="flex flex-1 items-center justify-center rounded-lg bg-white px-3.5 py-2 shadow-sm"
            >
                <AppLogo width="100%" theme="on-light" />
            </div>
            <!-- Mobile Close Button -->
            <button
                type="button"
                class="cursor-pointer rounded-lg p-2 text-white/70 transition-colors hover:bg-white/10 hover:text-white lg:hidden"
                aria-label="Tutup navigasi"
                @click="emit('close')"
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
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>

        <!-- Quick Return to Portal Link -->
        <div class="px-3 pb-1 pt-3">
            <Link
                href="/dashboard"
                class="shadow-xs flex w-full items-center justify-center gap-2 rounded-lg border border-white/15 bg-white/10 px-3 py-2 font-heading text-xs font-semibold tracking-wide text-white transition-all hover:bg-white/20"
                @click="handleNavClick"
            >
                <svg
                    class="h-4 w-4 text-ptkp-violet"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"
                    />
                </svg>
                <span>Portal Aplikasi</span>
            </Link>
        </div>

        <!-- Navigation Menu (Context Sensitive) -->
        <nav
            class="flex flex-1 flex-col gap-4 overflow-y-auto px-3 py-3 font-body text-sm"
        >
            <!-- ========================================== -->
            <!-- WORKSPACE 1: E-ARSIP (PERSURATAN)          -->
            <!-- ========================================== -->
            <template
                v-if="
                    isArsipWorkspace ||
                    (!isPlatformWorkspace && !isArsipWorkspace && !isSdmWorkspace && !isLayananWorkspace)
                "
            >
                <!-- App Title Badge -->
                <div
                    class="flex items-center justify-between rounded-md border border-white/10 bg-white/5 px-2 py-1.5"
                >
                    <div class="flex items-center gap-2">
                        <span
                            class="h-2 w-2 rounded-full bg-emerald-400"
                        ></span>
                        <span
                            class="font-heading text-xs font-bold tracking-wider text-white"
                            >E-ARSIP PTKP</span
                        >
                    </div>
                    <AppBadge variant="violet" size="sm">Persuratan</AppBadge>
                </div>

                <!-- Section: Persuratan Utama -->
                <div class="flex flex-col gap-1">
                    <span
                        class="px-2.5 pb-1 font-heading text-[11px] font-bold tracking-wider text-white/50"
                    >
                        PERSURATAN UTAMA
                    </span>

                    <!-- Surat Masuk -->
                    <AppNavItem
                        href="/arsip/surat-masuk"
                        :active="isItemActive('/arsip/surat-masuk')"
                        @click="handleNavClick"
                    >
                        <template #icon>
                            <svg
                                class="h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"
                                />
                            </svg>
                        </template>
                        Surat Masuk (B)
                    </AppNavItem>

                    <!-- Surat Keluar -->
                    <AppNavItem
                        href="/arsip/surat-keluar"
                        :active="isItemActive('/arsip/surat-keluar')"
                        @click="handleNavClick"
                    >
                        <template #icon>
                            <svg
                                class="h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"
                                />
                            </svg>
                        </template>
                        Surat Keluar (A)
                    </AppNavItem>

                    <!-- Nota Dinas -->
                    <AppNavItem
                        href="/arsip/naskah-dinas?type_code=ND"
                        :active="
                            isItemActive('/arsip/naskah-dinas?type_code=ND')
                        "
                        @click="handleNavClick"
                    >
                        <template #icon>
                            <svg
                                class="h-4 w-4"
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
                        </template>
                        Nota Dinas (ND)
                    </AppNavItem>

                    <!-- Surat Memo Direksi -->
                    <AppNavItem
                        href="/arsip/naskah-dinas?type_code=E"
                        :active="
                            isItemActive('/arsip/naskah-dinas?type_code=E')
                        "
                        @click="handleNavClick"
                    >
                        <template #icon>
                            <svg
                                class="h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"
                                />
                            </svg>
                        </template>
                        Surat Memo (E)
                    </AppNavItem>

                    <!-- Surat Tugas -->
                    <AppNavItem
                        href="/arsip/naskah-dinas?type_code=ST"
                        :active="
                            isItemActive('/arsip/naskah-dinas?type_code=ST')
                        "
                        @click="handleNavClick"
                    >
                        <template #icon>
                            <svg
                                class="h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"
                                />
                            </svg>
                        </template>
                        Surat Tugas (ST)
                    </AppNavItem>

                    <!-- Surat Pengumuman -->
                    <AppNavItem
                        href="/arsip/naskah-dinas?type_code=PENG"
                        :active="
                            isItemActive('/arsip/naskah-dinas?type_code=PENG')
                        "
                        @click="handleNavClick"
                    >
                        <template #icon>
                            <svg
                                class="h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.455a20.72 20.72 0 0 1-1.338-3.69m3-9.709c.253-.962.584-1.892.985-2.783.247-.55.06-1.21-.463-1.511l-.657-.38c-.551-.318-1.26-.117-1.527.455a20.72 20.72 0 0 0-1.338 3.69m3 9.709a20.89 20.89 0 0 0 5.41 0m-5.41-9.18a20.89 20.89 0 0 1 5.41 0m0 9.18a4.5 4.5 0 0 0 4.5-4.5v-0.18a4.5 4.5 0 0 0-4.5-4.5m0 9.18v-9.18"
                                />
                            </svg>
                        </template>
                        Pengumuman (PENG)
                    </AppNavItem>

                    <!-- Surat Keputusan -->
                    <AppNavItem
                        href="/arsip/naskah-dinas?type_code=KPTS"
                        :active="
                            isItemActive('/arsip/naskah-dinas?type_code=KPTS')
                        "
                        @click="handleNavClick"
                    >
                        <template #icon>
                            <svg
                                class="h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.155.095 2 1.058 2 2.192V8.25m-12 0h12m-12 0v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V8.25"
                                />
                            </svg>
                        </template>
                        Surat Keputusan (KPTS)
                    </AppNavItem>
                </div>

                <!-- Section: Formulir Khusus -->
                <div class="flex flex-col gap-1 pt-1">
                    <span
                        class="px-2.5 pb-1 font-heading text-[11px] font-bold tracking-wider text-white/50"
                    >
                        FORMULIR KHUSUS
                    </span>

                    <!-- SPPD -->
                    <AppNavItem
                        href="/arsip/khusus?type_code=SPPD"
                        :active="isItemActive('/arsip/khusus?type_code=SPPD')"
                        @click="handleNavClick"
                    >
                        <template #icon>
                            <svg
                                class="h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V3.75m0 3.75h3.75m-3.75 0H9.75M9.75 7.5V3.75M9.75 7.5H6m3.75 0v3.75m0 0H6m3.75 0h3.75m0 0v3.75"
                                />
                            </svg>
                        </template>
                        Perjalanan Dinas (SPPD)
                    </AppNavItem>

                    <!-- SPB -->
                    <AppNavItem
                        href="/arsip/khusus?type_code=SPB"
                        :active="isItemActive('/arsip/khusus?type_code=SPB')"
                        @click="handleNavClick"
                    >
                        <template #icon>
                            <svg
                                class="h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"
                                />
                            </svg>
                        </template>
                        Permintaan Barang (SPB)
                    </AppNavItem>

                    <!-- Biaya Pengobatan -->
                    <AppNavItem
                        href="/arsip/khusus?type_code=BP"
                        :active="isItemActive('/arsip/khusus?type_code=BP')"
                        @click="handleNavClick"
                    >
                        <template #icon>
                            <svg
                                class="h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                />
                            </svg>
                        </template>
                        Biaya Pengobatan (BP)
                    </AppNavItem>
                </div>

                <!-- Section: Meja Kerja Persetujuan -->
                <div class="flex flex-col gap-1 pt-1">
                    <span
                        class="px-2.5 pb-1 font-heading text-[11px] font-bold tracking-wider text-white/50"
                    >
                        ALUR & OTORISASI
                    </span>

                    <AppNavItem
                        href="/arsip/persetujuan"
                        :active="isItemActive('/arsip/persetujuan')"
                        @click="handleNavClick"
                    >
                        <template #icon>
                            <svg
                                class="h-4 w-4 text-emerald-400"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m4.5 12.75 6 6 9-13.5"
                                />
                            </svg>
                        </template>
                        <span class="font-semibold text-emerald-300"
                            >Meja Persetujuan</span
                        >
                    </AppNavItem>
                </div>

                <!-- Switchers to Jobdesk & Platform Core -->
                <div class="mt-auto border-t border-white/10 pt-2 flex flex-col gap-1.5">
                    <Link
                        href="/sdm/jobdesk"
                        class="flex items-center justify-between rounded-lg bg-white/5 px-3 py-2 font-body text-xs text-white/70 transition-colors hover:bg-white/10 hover:text-white"
                        @click="handleNavClick"
                    >
                        <span>Direktori Jobdesk (SDM)</span>
                        <span>&rarr;</span>
                    </Link>

                    <Link
                        v-if="canAccessPlatform"
                        href="/platform/org-units"
                        class="flex items-center justify-between rounded-lg bg-white/5 px-3 py-2 font-body text-xs text-white/70 transition-colors hover:bg-white/10 hover:text-white"
                        @click="handleNavClick"
                    >
                        <span>Beralih ke Platform Inti</span>
                        <span>&rarr;</span>
                    </Link>
                </div>
            </template>

            <!-- ========================================== -->
            <!-- WORKSPACE 2: PLATFORM CORE (ADMIN)         -->
            <!-- ========================================== -->
            <template v-else-if="isPlatformWorkspace">
                <!-- App Title Badge -->
                <div
                    class="flex items-center justify-between rounded-md border border-white/10 bg-white/5 px-2 py-1.5"
                >
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-blue-400"></span>
                        <span
                            class="font-heading text-xs font-bold tracking-wider text-white"
                            >PLATFORM CORE</span
                        >
                    </div>
                    <AppBadge variant="navy" size="sm">Admin</AppBadge>
                </div>

                <div class="flex flex-col gap-1">
                    <span
                        class="px-2.5 pb-1 font-heading text-[11px] font-bold tracking-wider text-white/50"
                    >
                        MASTER DATA & TATA KELOLA
                    </span>

                    <AppNavItem
                        href="/platform/org-units"
                        :active="isItemActive('/platform/org-units')"
                        @click="handleNavClick"
                    >
                        <template #icon>
                            <svg
                                class="h-4 w-4"
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
                        </template>
                        Unit Organisasi
                    </AppNavItem>

                    <AppNavItem
                        href="/platform/positions"
                        :active="isItemActive('/platform/positions')"
                        @click="handleNavClick"
                    >
                        <template #icon>
                            <svg
                                class="h-4 w-4"
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
                        </template>
                        Master Jabatan
                    </AppNavItem>

                    <AppNavItem
                        href="/platform/employees"
                        :active="isItemActive('/platform/employees')"
                        @click="handleNavClick"
                    >
                        <template #icon>
                            <svg
                                class="h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"
                                />
                            </svg>
                        </template>
                        Data Pegawai
                    </AppNavItem>

                    <AppNavItem
                        href="/platform/users"
                        :active="isItemActive('/platform/users')"
                        @click="handleNavClick"
                    >
                        <template #icon>
                            <svg
                                class="h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                />
                            </svg>
                        </template>
                        Pengguna & Peran
                    </AppNavItem>

                    <AppNavItem
                        href="/platform/numbering"
                        :active="isItemActive('/platform/numbering')"
                        @click="handleNavClick"
                    >
                        <template #icon>
                            <svg
                                class="h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5-3.9 19.5m-2.1-19.5-3.9 19.5"
                                />
                            </svg>
                        </template>
                        Penomoran Dokumen
                    </AppNavItem>

                    <AppNavItem
                        href="/platform/audit-logs"
                        :active="isItemActive('/platform/audit-logs')"
                        @click="handleNavClick"
                    >
                        <template #icon>
                            <svg
                                class="h-4 w-4"
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
                        </template>
                        Audit Log
                    </AppNavItem>
                </div>

                <!-- Switchers to E-Arsip & Jobdesk -->
                <div class="mt-auto border-t border-white/10 pt-2 flex flex-col gap-1.5">
                    <Link
                        href="/sdm/jobdesk"
                        class="flex items-center justify-between rounded-lg bg-white/5 px-3 py-2 font-body text-xs text-white/70 transition-colors hover:bg-white/10 hover:text-white"
                        @click="handleNavClick"
                    >
                        <span>Direktori Jobdesk (SDM)</span>
                        <span>&rarr;</span>
                    </Link>

                    <Link
                        href="/arsip/surat-masuk"
                        class="flex items-center justify-between rounded-lg bg-white/5 px-3 py-2 font-body text-xs text-white/70 transition-colors hover:bg-white/10 hover:text-white"
                        @click="handleNavClick"
                    >
                        <span>Beralih ke E-Arsip</span>
                        <span>&rarr;</span>
                    </Link>
                </div>
            </template>

            <!-- ========================================== -->
            <!-- WORKSPACE 3: SDM & DIGITAL JOBDESK         -->
            <!-- ========================================== -->
            <template v-else-if="isSdmWorkspace">
                <!-- App Title Badge -->
                <div
                    class="flex items-center justify-between rounded-md border border-white/10 bg-white/5 px-2 py-1.5"
                >
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        <span
                            class="font-heading text-xs font-bold tracking-wider text-white"
                            >SDM & ORGANISASI</span
                        >
                    </div>
                    <AppBadge variant="violet" size="sm">Jobdesk</AppBadge>
                </div>

                <div class="flex flex-col gap-1">
                    <span
                        class="px-2.5 pb-1 font-heading text-[11px] font-bold tracking-wider text-white/50"
                    >
                        PROFIL & URAIAN JABATAN
                    </span>

                    <AppNavItem
                        href="/sdm/jobdesk"
                        :active="isItemActive('/sdm/jobdesk') && currentUrl !== '/sdm/jobdesk/create'"
                        @click="handleNavClick"
                    >
                        <template #icon>
                            <svg
                                class="h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"
                                />
                            </svg>
                        </template>
                        Katalog Jobdesk
                    </AppNavItem>

                    <AppNavItem
                        href="/sdm/jobdesk/create"
                        :active="isItemActive('/sdm/jobdesk/create')"
                        @click="handleNavClick"
                    >
                        <template #icon>
                            <svg
                                class="h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 4.5v15m7.5-7.5h-15"
                                />
                            </svg>
                        </template>
                        Susun Jobdesk Baru
                    </AppNavItem>
                </div>

                <!-- Switchers to other workspaces -->
                <div class="mt-auto border-t border-white/10 pt-2 flex flex-col gap-1.5">
                    <Link
                        href="/arsip/surat-masuk"
                        class="flex items-center justify-between rounded-lg bg-white/5 px-3 py-2 font-body text-xs text-white/70 transition-colors hover:bg-white/10 hover:text-white"
                        @click="handleNavClick"
                    >
                        <span>Beralih ke E-Arsip</span>
                        <span>&rarr;</span>
                    </Link>

                    <Link
                        v-if="canAccessPlatform"
                        href="/platform/org-units"
                        class="flex items-center justify-between rounded-lg bg-white/5 px-3 py-2 font-body text-xs text-white/70 transition-colors hover:bg-white/10 hover:text-white"
                        @click="handleNavClick"
                    >
                        <span>Beralih ke Platform Inti</span>
                        <span>&rarr;</span>
                    </Link>
                </div>
            </template>

            <!-- ========================================== -->
            <!-- WORKSPACE 4: LAYANAN IT (SI-WOPPIT)        -->
            <!-- ========================================== -->
            <template v-else-if="isLayananWorkspace">
                <!-- App Title Badge -->
                <div
                    class="flex items-center justify-between rounded-md border border-white/10 bg-white/5 px-2 py-1.5"
                >
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-sky-400"></span>
                        <span
                            class="font-heading text-xs font-bold tracking-wider text-white"
                            >LAYANAN IT (WOPPIT)</span
                        >
                    </div>
                    <AppBadge variant="violet" size="sm">Work Order</AppBadge>
                </div>

                <div class="flex flex-col gap-1">
                    <span
                        class="px-2.5 pb-1 font-heading text-[11px] font-bold tracking-wider text-white/50"
                    >
                        ANTREAN & PENANGANAN IT
                    </span>

                    <AppNavItem
                        href="/layanan/work-orders"
                        :active="isItemActive('/layanan/work-orders') && currentUrl !== '/layanan/work-orders/create'"
                        @click="handleNavClick"
                    >
                        <template #icon>
                            <svg
                                class="h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                />
                            </svg>
                        </template>
                        Papan Antrean IT
                    </AppNavItem>

                    <AppNavItem
                        href="/layanan/work-orders/create"
                        :active="isItemActive('/layanan/work-orders/create')"
                        @click="handleNavClick"
                    >
                        <template #icon>
                            <svg
                                class="h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 4.5v15m7.5-7.5h-15"
                                />
                            </svg>
                        </template>
                        Ajukan Permohonan IT
                    </AppNavItem>
                </div>

                <!-- Switchers to other workspaces -->
                <div class="mt-auto border-t border-white/10 pt-2 flex flex-col gap-1.5">
                    <Link
                        href="/sdm/jobdesk"
                        class="flex items-center justify-between rounded-lg bg-white/5 px-3 py-2 font-body text-xs text-white/70 transition-colors hover:bg-white/10 hover:text-white"
                        @click="handleNavClick"
                    >
                        <span>Direktori Jobdesk (SDM)</span>
                        <span>&rarr;</span>
                    </Link>

                    <Link
                        href="/arsip/surat-masuk"
                        class="flex items-center justify-between rounded-lg bg-white/5 px-3 py-2 font-body text-xs text-white/70 transition-colors hover:bg-white/10 hover:text-white"
                        @click="handleNavClick"
                    >
                        <span>Beralih ke E-Arsip</span>
                        <span>&rarr;</span>
                    </Link>

                    <Link
                        v-if="canAccessPlatform"
                        href="/platform/org-units"
                        class="flex items-center justify-between rounded-lg bg-white/5 px-3 py-2 font-body text-xs text-white/70 transition-colors hover:bg-white/10 hover:text-white"
                        @click="handleNavClick"
                    >
                        <span>Beralih ke Platform Inti</span>
                        <span>&rarr;</span>
                    </Link>
                </div>
            </template>
        </nav>

        <!-- Sidebar Footer -->
        <div
            class="shrink-0 border-t border-white/10 bg-black/20 p-3.5 font-body"
        >
            <div class="text-xs font-semibold text-white/90">
                PT Kertas Padalarang
            </div>
            <div class="text-[11px] text-white/50">
                Ekosistem Internal &bull; v2.0
            </div>
        </div>
    </aside>
</template>
