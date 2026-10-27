<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

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

const page = usePage();
const initialUnreadCount = computed(() => {
    return Number((page.props.auth as any)?.unread_notifications_count || 0);
});

const isOpen = ref(false);
const activeTab = ref('all');
const notifications = ref<NotificationItem[]>([]);
const unreadCount = ref(initialUnreadCount.value);
const isLoading = ref(false);
let pollInterval: ReturnType<typeof setInterval> | null = null;

// Watch Inertia auth prop changes
watch(initialUnreadCount, (val) => {
    unreadCount.value = val;
});

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        fetchNotifications();
    }
};

const closeDropdown = () => {
    isOpen.value = false;
};

const fetchNotifications = async (moduleOverride?: string) => {
    isLoading.value = true;
    const targetModule = moduleOverride !== undefined ? moduleOverride : activeTab.value;
    const params = targetModule !== 'all' ? { module: targetModule } : {};

    try {
        const response = await axios.get('/notifications/feed', { params });
        notifications.value = response.data.notifications || [];
        unreadCount.value = response.data.unread_count ?? unreadCount.value;
    } catch (e) {
        console.error('Failed fetching notification feed:', e);
    } finally {
        isLoading.value = false;
    }
};

const switchTab = (tab: string) => {
    activeTab.value = tab;
    fetchNotifications(tab);
};

const markAsRead = async (item: NotificationItem) => {
    if (!item.is_read) {
        item.is_read = true;
        unreadCount.value = Math.max(0, unreadCount.value - 1);

        try {
            await axios.post(`/notifications/${item.id}/read`);
        } catch (e) {
            console.error('Failed marking notification as read:', e);
        }
    }

    closeDropdown();

    if (item.action_url) {
        router.visit(item.action_url);
    }
};

const markAllAsRead = async () => {
    try {
        const response = await axios.post('/notifications/read-all', {
            module: activeTab.value !== 'all' ? activeTab.value : null,
        });

        if (response.status === 200) {
            notifications.value.forEach((n) => {
                n.is_read = true;
            });
            unreadCount.value = response.data.unread_count ?? 0;
        }
    } catch (e) {
        console.error('Failed marking all notifications as read:', e);
    }
};

const formatRelativeTime = (dateStr: string): string => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    const now = new Date();
    const diffSec = Math.floor((now.getTime() - date.getTime()) / 1000);

    if (diffSec < 60) return 'Baru saja';
    if (diffSec < 3600) return `${Math.floor(diffSec / 60)} mnt lalu`;
    if (diffSec < 86400) return `${Math.floor(diffSec / 3600)} jam lalu`;
    if (diffSec < 172800) return 'Kemarin';
    return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
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

onMounted(() => {
    // Lightweight polling every 45s when tab is active
    pollInterval = setInterval(() => {
        if (document.visibilityState === 'visible') {
            fetchNotifications();
        }
    }, 45000);
});

onUnmounted(() => {
    if (pollInterval) {
        clearInterval(pollInterval);
    }
});
</script>

<template>
    <div class="relative">
        <!-- Bell Icon Button -->
        <button
            type="button"
            @click="toggleDropdown"
            class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-xs transition-all duration-200 hover:border-ptkp-navy/30 hover:bg-slate-50 hover:text-ptkp-navy active:scale-95 cursor-pointer"
            :class="{ 'ring-2 ring-ptkp-navy/20 border-ptkp-navy text-ptkp-navy': isOpen }"
            aria-label="Pusat Notifikasi"
            :title="`Pusat Notifikasi (${unreadCount} belum dibaca)`"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>

            <!-- Unread Pulse Badge -->
            <span
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-600 px-1 font-mono text-[10px] font-bold text-white shadow-xs ring-2 ring-white animate-pulse"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown Backdrop -->
        <div
            v-if="isOpen"
            class="fixed inset-0 z-40 bg-black/5"
            @click="closeDropdown"
        ></div>

        <!-- Notification Popover Flyout -->
        <div
            v-if="isOpen"
            class="absolute right-0 mt-2 z-50 w-80 sm:w-96 rounded-2xl border border-slate-200 bg-white shadow-2xl overflow-hidden animate-fadeIn"
        >
            <!-- Header Bar -->
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-4 py-3">
                <div class="flex items-center gap-2">
                    <span class="font-heading text-sm font-bold text-slate-900">
                        Notifikasi Terpadu
                    </span>
                    <span
                        v-if="unreadCount > 0"
                        class="rounded-full bg-red-100 px-2 py-0.5 font-mono text-[10px] font-bold text-red-700"
                    >
                        {{ unreadCount }} Baru
                    </span>
                </div>

                <button
                    v-if="unreadCount > 0"
                    type="button"
                    @click="markAllAsRead"
                    class="font-heading text-xs font-semibold text-ptkp-navy hover:underline cursor-pointer"
                >
                    Tandai Semua Dibaca
                </button>
            </div>

            <!-- Segmented Filter Tabs per Modul -->
            <div class="flex items-center gap-1 border-b border-slate-100 bg-white px-3 py-2 overflow-x-auto no-scrollbar">
                <button
                    type="button"
                    @click="switchTab('all')"
                    class="rounded-lg px-2.5 py-1 font-heading text-xs font-semibold transition-all cursor-pointer whitespace-nowrap"
                    :class="[
                        activeTab === 'all'
                            ? 'bg-ptkp-navy text-white shadow-xs'
                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                    ]"
                >
                    Semua
                </button>

                <button
                    type="button"
                    @click="switchTab('layanan')"
                    class="rounded-lg px-2.5 py-1 font-heading text-xs font-semibold transition-all cursor-pointer whitespace-nowrap"
                    :class="[
                        activeTab === 'layanan'
                            ? 'bg-blue-600 text-white shadow-xs'
                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                    ]"
                >
                    Layanan IT
                </button>

                <button
                    type="button"
                    @click="switchTab('arsip')"
                    class="rounded-lg px-2.5 py-1 font-heading text-xs font-semibold transition-all cursor-pointer whitespace-nowrap"
                    :class="[
                        activeTab === 'arsip'
                            ? 'bg-amber-600 text-white shadow-xs'
                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                    ]"
                >
                    E-Arsip
                </button>

                <button
                    type="button"
                    @click="switchTab('sdm')"
                    class="rounded-lg px-2.5 py-1 font-heading text-xs font-semibold transition-all cursor-pointer whitespace-nowrap"
                    :class="[
                        activeTab === 'sdm'
                            ? 'bg-purple-600 text-white shadow-xs'
                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                    ]"
                >
                    SDM Jobdesk
                </button>
            </div>

            <!-- Notification Items List -->
            <div class="max-h-[380px] overflow-y-auto divide-y divide-slate-100">
                <!-- Loading State -->
                <div v-if="isLoading" class="py-8 text-center">
                    <svg class="mx-auto h-6 w-6 animate-spin text-ptkp-navy" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-2 font-body text-xs text-slate-500">Memuat notifikasi...</p>
                </div>

                <!-- Empty State -->
                <div v-else-if="notifications.length === 0" class="py-10 text-center px-4">
                    <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                        🔔
                    </div>
                    <p class="font-heading text-xs font-bold text-slate-800">Tidak Ada Notifikasi</p>
                    <p class="mt-0.5 font-body text-xs text-slate-500">
                        {{ activeTab !== 'all' ? 'Belum ada notifikasi untuk kategori ini.' : 'Semua pekerjaan dan tugas Anda telah terpantau.' }}
                    </p>
                </div>

                <!-- Notification Row Items -->
                <div
                    v-else
                    v-for="item in notifications"
                    :key="item.id"
                    @click="markAsRead(item)"
                    class="group relative flex items-start gap-3 p-3.5 transition-colors duration-150 cursor-pointer"
                    :class="[
                        !item.is_read
                            ? 'bg-ptkp-mist/50 hover:bg-ptkp-mist/80'
                            : 'bg-white hover:bg-slate-50',
                    ]"
                >
                    <!-- Unread Indicator Dot -->
                    <span
                        v-if="!item.is_read"
                        class="absolute left-1.5 top-5 h-2 w-2 rounded-full bg-ptkp-navy"
                    ></span>

                    <!-- Module Icon Badge -->
                    <div
                        class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl shadow-xs transition-transform duration-150 group-hover:scale-105"
                        :class="getModuleBadge(item.module).iconBg"
                    >
                        <!-- IT Tool Icon -->
                        <svg v-if="item.module === 'layanan'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.07a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091.49.07 1.002-.07 1.488" />
                        </svg>
                        <!-- Arsip Envelope Icon -->
                        <svg v-else-if="item.module === 'arsip'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                        <!-- SDM Badge Icon -->
                        <svg v-else-if="item.module === 'sdm'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                        <!-- Bell Icon Default -->
                        <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1">
                            <span
                                class="rounded px-1.5 py-0.2 font-mono text-[9px] font-bold uppercase tracking-wider"
                                :class="getModuleBadge(item.module).color"
                            >
                                {{ getModuleBadge(item.module).label }}
                            </span>
                            <span class="font-mono text-[10px] text-slate-400">
                                {{ formatRelativeTime(item.created_at) }}
                            </span>
                        </div>

                        <h4
                            class="mt-1 font-heading text-xs font-bold truncate group-hover:text-ptkp-navy"
                            :class="!item.is_read ? 'text-slate-900' : 'text-slate-700'"
                        >
                            {{ item.title }}
                        </h4>

                        <p class="mt-0.5 font-body text-xs text-slate-500 line-clamp-2 leading-relaxed">
                            {{ item.message }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer Link to Full Notification Center -->
            <div class="border-t border-slate-100 bg-slate-50/80 p-2.5 text-center">
                <Link
                    href="/notifications"
                    @click="closeDropdown"
                    class="block rounded-lg py-1.5 font-heading text-xs font-bold text-ptkp-navy hover:bg-white transition-colors"
                >
                    Buka Pusat Notifikasi Lengkap &rarr;
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
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
    animation: fadeIn 0.2s ease-out forwards;
}
</style>
