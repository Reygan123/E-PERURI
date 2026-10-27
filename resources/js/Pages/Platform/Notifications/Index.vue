<script setup lang="ts">
import AppButton from '@/Components/AppButton.vue';
import AppPagination from '@/Components/AppPagination.vue';
import AppShell from '@/Layouts/AppShell.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

interface NotificationItem {
    id: string;
    type: string;
    channel: string;
    title: string;
    message: string;
    module: string;
    action_url: string | null;
    is_read: boolean;
    read_at: string | null;
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    notifications: {
        data: NotificationItem[];
        current_page: number;
        last_page: number;
        total: number;
        from: number | null;
        to: number | null;
        links: PaginationLink[];
    };
    unreadCount: number;
    filters: {
        module?: string;
        search?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const selectedModule = ref(props.filters.module || 'all');

const applyFilter = (moduleName: string) => {
    selectedModule.value = moduleName;
    router.get(
        '/notifications',
        {
            module: moduleName !== 'all' ? moduleName : undefined,
            search: search.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const applySearch = () => {
    router.get(
        '/notifications',
        {
            module: selectedModule.value !== 'all' ? selectedModule.value : undefined,
            search: search.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const markAsRead = async (item: NotificationItem) => {
    if (!item.is_read) {
        item.is_read = true;
        try {
            await axios.post(`/notifications/${item.id}/read`);
        } catch (e) {
            console.error('Failed marking notification as read:', e);
        }
    }

    if (item.action_url) {
        router.visit(item.action_url);
    }
};

const markAllAsRead = async () => {
    try {
        const response = await axios.post('/notifications/read-all', {
            module: selectedModule.value !== 'all' ? selectedModule.value : null,
        });

        if (response.status === 200) {
            router.reload({ only: ['notifications', 'unreadCount'] });
        }
    } catch (e) {
        console.error('Failed marking all as read:', e);
    }
};

const formatDate = (dateStr: string): string => {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getModuleBadge = (mod: string) => {
    switch (mod?.toLowerCase()) {
        case 'layanan':
            return {
                label: 'Layanan IT',
                color: 'bg-blue-100 text-blue-800 border-blue-200',
                iconBg: 'bg-blue-600 text-white',
            };
        case 'arsip':
            return {
                label: 'E-Arsip',
                color: 'bg-amber-100 text-amber-900 border-amber-200',
                iconBg: 'bg-amber-500 text-white',
            };
        case 'sdm':
            return {
                label: 'SDM Jobdesk',
                color: 'bg-purple-100 text-purple-900 border-purple-200',
                iconBg: 'bg-purple-600 text-white',
            };
        default:
            return {
                label: 'Platform',
                color: 'bg-slate-100 text-slate-700 border-slate-200',
                iconBg: 'bg-ptkp-navy text-white',
            };
    }
};
</script>

<template>
    <AppShell
        title="Pusat Notifikasi Terpadu - PT Kertas Padalarang"
        :breadcrumbs="[
            { label: 'Portal', href: '/dashboard' },
            { label: 'Pusat Notifikasi' },
        ]"
    >
        <div class="space-y-6 animate-fadeIn">
            <!-- Hero Corporate Banner -->
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-ptkp-deep via-ptkp-navy to-[#2631a3] p-6 text-white shadow-xl sm:p-8"
            >
                <div class="absolute -right-12 -top-12 h-64 w-64 rounded-full bg-white/5 blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col justify-between gap-6 md:flex-row md:items-center">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 font-mono text-xs font-semibold tracking-wider text-emerald-300 backdrop-blur-md">
                                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                EKOSISTEM INTERNAL PTKP
                            </span>
                            <span class="rounded-full bg-white/10 px-3 py-1 font-heading text-xs font-medium text-slate-200">
                                Pusat Komunikasi & Notifikasi
                            </span>
                        </div>

                        <h1 class="mt-3 font-heading text-2xl font-bold tracking-tight text-white sm:text-3xl">
                            Pusat Notifikasi Terpadu
                        </h1>
                        <p class="mt-2 max-w-2xl font-body text-sm text-slate-200">
                            Pantau seluruh pembaruan tugas, disposisi surat masuk, penanganan tiket IT, dan verifikasi jobdesk dalam satu pusat kendali terpadu.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            v-if="unreadCount > 0"
                            type="button"
                            @click="markAllAsRead"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 font-heading text-xs font-bold text-ptkp-deep shadow-sm transition-all duration-200 hover:bg-slate-50 hover:-translate-y-0.5 active:scale-95 cursor-pointer"
                        >
                            <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span>Tandai Semua Dibaca</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filter Controls Bar -->
            <div class="rounded-2xl border border-ptkp-border bg-white p-5 shadow-card">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <!-- Segmented Module Buttons -->
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button
                            type="button"
                            @click="applyFilter('all')"
                            class="rounded-xl px-3.5 py-2 font-heading text-xs font-bold transition-all cursor-pointer"
                            :class="[
                                selectedModule === 'all'
                                    ? 'bg-ptkp-navy text-white shadow-xs'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                            ]"
                        >
                            Semua Notifikasi
                        </button>

                        <button
                            type="button"
                            @click="applyFilter('layanan')"
                            class="rounded-xl px-3.5 py-2 font-heading text-xs font-bold transition-all cursor-pointer"
                            :class="[
                                selectedModule === 'layanan'
                                    ? 'bg-blue-600 text-white shadow-xs'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                            ]"
                        >
                            Layanan IT (WOPPIT)
                        </button>

                        <button
                            type="button"
                            @click="applyFilter('arsip')"
                            class="rounded-xl px-3.5 py-2 font-heading text-xs font-bold transition-all cursor-pointer"
                            :class="[
                                selectedModule === 'arsip'
                                    ? 'bg-amber-600 text-white shadow-xs'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                            ]"
                        >
                            E-Arsip Persuratan
                        </button>

                        <button
                            type="button"
                            @click="applyFilter('sdm')"
                            class="rounded-xl px-3.5 py-2 font-heading text-xs font-bold transition-all cursor-pointer"
                            :class="[
                                selectedModule === 'sdm'
                                    ? 'bg-purple-600 text-white shadow-xs'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                            ]"
                        >
                            SDM & Jobdesk
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="flex items-center gap-2">
                        <div class="relative w-full sm:w-64">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Cari isi notifikasi..."
                                class="w-full rounded-xl border border-slate-300 pl-9 pr-3.5 py-2 font-body text-xs text-slate-900 placeholder-slate-400 focus:border-ptkp-navy focus:outline-none focus:ring-1 focus:ring-ptkp-navy"
                                @keyup.enter="applySearch"
                            />
                            <svg class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <button
                            type="button"
                            @click="applySearch"
                            class="rounded-xl bg-ptkp-navy px-3.5 py-2 font-heading text-xs font-bold text-white shadow-xs hover:bg-ptkp-deep cursor-pointer"
                        >
                            Cari
                        </button>
                    </div>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="rounded-2xl border border-ptkp-border bg-white shadow-card overflow-hidden">
                <!-- Empty State -->
                <div v-if="notifications.data.length === 0" class="py-16 text-center">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 text-2xl">
                        🔔
                    </div>
                    <h3 class="font-heading text-sm font-bold text-slate-900">Tidak Ada Notifikasi Ditemukan</h3>
                    <p class="mt-1 font-body text-xs text-slate-500">
                        Belum ada riwayat notifikasi untuk filter yang dipilih.
                    </p>
                </div>

                <!-- List Items -->
                <div v-else class="divide-y divide-slate-100">
                    <div
                        v-for="item in notifications.data"
                        :key="item.id"
                        class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-5 transition-colors duration-150"
                        :class="[
                            !item.is_read
                                ? 'bg-ptkp-mist/40 hover:bg-ptkp-mist/70'
                                : 'bg-white hover:bg-slate-50/70',
                        ]"
                    >
                        <div class="flex items-start gap-4 flex-1 min-w-0">
                            <!-- Module Icon Badge -->
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl shadow-xs"
                                :class="getModuleBadge(item.module).iconBg"
                            >
                                <svg v-if="item.module === 'layanan'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.07a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091.49.07 1.002-.07 1.488" />
                                </svg>
                                <svg v-else-if="item.module === 'arsip'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                                <svg v-else-if="item.module === 'sdm'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                                <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                </svg>
                            </div>

                            <!-- Body Text -->
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="rounded-md border px-2 py-0.5 font-mono text-[10px] font-bold uppercase tracking-wider"
                                        :class="getModuleBadge(item.module).color"
                                    >
                                        {{ getModuleBadge(item.module).label }}
                                    </span>
                                    <span class="font-mono text-xs text-slate-500">
                                        {{ formatDate(item.created_at) }}
                                    </span>
                                    <span
                                        v-if="!item.is_read"
                                        class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-700"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> Belum Dibaca
                                    </span>
                                </div>

                                <h3 class="mt-1 font-heading text-sm font-bold text-slate-900">
                                    {{ item.title }}
                                </h3>

                                <p class="mt-1 font-body text-xs text-slate-600 leading-relaxed max-w-3xl">
                                    {{ item.message }}
                                </p>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="flex items-center gap-2 shrink-0">
                            <button
                                type="button"
                                @click="markAsRead(item)"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-ptkp-border bg-white px-3.5 py-2 font-heading text-xs font-bold text-ptkp-navy shadow-xs hover:bg-ptkp-mist hover:border-ptkp-navy transition-all duration-150 cursor-pointer"
                            >
                                <span>Buka Dokumen / Tiket</span>
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pagination Footer -->
                <div v-if="notifications.total > notifications.data.length" class="border-t border-slate-100 bg-slate-50/60 p-4">
                    <AppPagination :links="notifications.links" />
                </div>
            </div>
        </div>
    </AppShell>
</template>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fadeIn {
    animation: fadeIn 0.25s ease-out forwards;
}
</style>
