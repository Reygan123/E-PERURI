<script setup lang="ts">
import NotificationDropdown from '@/Components/NotificationDropdown.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Props {
    title?: string;
    breadcrumbs?: Array<{ label: string; href?: string }>;
}

withDefaults(defineProps<Props>(), {
    title: '',
    breadcrumbs: () => [],
});

const emit = defineEmits<{
    (e: 'toggle-menu'): void;
}>();

const page = usePage();
const user = computed(
    () => page.props.auth?.user as Record<string, any> | undefined,
);

const dropdownOpen = ref(false);

const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
};

const closeDropdown = () => {
    dropdownOpen.value = false;
};

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <header
        class="shadow-xs sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-100 bg-white px-4 sm:px-6 lg:px-8"
    >
        <div class="flex min-w-0 items-center gap-2 sm:gap-3">
            <!-- Mobile hamburger toggle -->
            <button
                type="button"
                class="-ml-1 rounded-lg p-2 text-slate-600 transition-colors hover:bg-slate-100 hover:text-ptkp-navy focus:outline-none lg:hidden"
                aria-label="Buka Menu"
                @click="emit('toggle-menu')"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16"
                    />
                </svg>
            </button>

            <div class="flex min-w-0 flex-col justify-center">
                <!-- Breadcrumbs (hidden on small mobile) -->
                <nav
                    v-if="breadcrumbs.length > 0"
                    class="mb-0.5 hidden sm:block"
                    aria-label="Breadcrumb"
                >
                    <ol
                        class="m-0 flex list-none items-center gap-1.5 p-0 font-body text-xs"
                    >
                        <li
                            v-for="(crumb, idx) in breadcrumbs"
                            :key="idx"
                            class="flex items-center gap-1.5"
                        >
                            <Link
                                v-if="crumb.href"
                                :href="crumb.href"
                                class="text-slate-500 hover:text-ptkp-navy hover:underline"
                            >
                                {{ crumb.label }}
                            </Link>
                            <span v-else class="font-semibold text-ptkp-navy">{{
                                crumb.label
                            }}</span>
                            <span
                                v-if="idx < breadcrumbs.length - 1"
                                class="text-slate-300"
                                >/</span
                            >
                        </li>
                    </ol>
                </nav>
                <h1
                    v-if="title"
                    class="m-0 truncate font-heading text-sm font-bold text-ptkp-navy sm:text-base"
                >
                    {{ title }}
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            <!-- Notification Center -->
            <NotificationDropdown />

            <!-- User Menu -->
            <div class="relative">
                <button
                    type="button"
                    class="flex cursor-pointer items-center gap-2 rounded-lg bg-transparent px-2 py-1.5 transition-colors hover:bg-slate-100 sm:gap-3 sm:px-3"
                    :aria-expanded="dropdownOpen"
                    @click="toggleDropdown"
                >
                    <div
                        class="shadow-xs flex h-8 w-8 items-center justify-center rounded-lg bg-ptkp-navy font-heading text-xs font-bold text-white sm:text-sm"
                    >
                        {{
                            user?.name ? user.name.charAt(0).toUpperCase() : 'U'
                        }}
                    </div>
                    <div class="hidden flex-col text-left sm:flex">
                        <span
                            class="max-w-[140px] truncate font-body text-xs font-semibold text-ptkp-body"
                            >{{
                                user?.name || user?.username || 'Pegawai PTKP'
                            }}</span
                        >
                        <span class="font-body text-[11px] text-slate-500">{{
                            user?.username
                                ? `@${user.username}`
                                : 'Platform User'
                        }}</span>
                    </div>
                    <svg
                        class="h-4 w-4 text-slate-400"
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

                <!-- Dropdown Backdrop -->
                <div
                    v-if="dropdownOpen"
                    class="fixed inset-0 z-30"
                    @click="closeDropdown"
                />

                <!-- Dropdown Menu -->
                <div
                    v-if="dropdownOpen"
                    class="absolute right-0 top-full z-40 mt-2 w-56 rounded-xl border border-slate-100 bg-white p-1.5 shadow-raised"
                >
                    <div class="px-3 py-2">
                        <p
                            class="m-0 font-body text-sm font-semibold text-ptkp-body"
                        >
                            {{ user?.name || 'Pegawai' }}
                        </p>
                        <p
                            class="mb-0 mt-0.5 truncate font-body text-xs text-slate-500"
                        >
                            {{ user?.email || user?.username }}
                        </p>
                    </div>
                    <div class="my-1 h-px bg-slate-100"></div>

                    <Link
                        href="/profile"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2 font-body text-xs text-ptkp-body transition-colors hover:bg-ptkp-mist hover:text-ptkp-navy"
                        @click="closeDropdown"
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
                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
                            />
                        </svg>
                        Profil Akun
                    </Link>

                    <Link
                        href="/mandatory-password-change"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2 font-body text-xs text-ptkp-body transition-colors hover:bg-ptkp-mist hover:text-ptkp-navy"
                        @click="closeDropdown"
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
                        class="flex w-full cursor-pointer items-center gap-3 rounded-lg px-3 py-2 text-left font-body text-xs text-ptkp-error transition-colors hover:bg-ptkp-error-bg"
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
</template>
