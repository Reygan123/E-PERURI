<script setup lang="ts">
import AppButton from '@/Components/AppButton.vue';
import AppLogo from '@/Components/AppLogo.vue';
import { Head, Link } from '@inertiajs/vue3';

interface Props {
    status?: number;
    message?: string;
}

withDefaults(defineProps<Props>(), {
    status: 403,
    message:
        'Anda tidak memiliki hak akses yang memadai untuk membuka halaman atau fitur ini.',
});

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = '/dashboard';
    }
};
</script>

<template>
    <div
        class="flex min-h-screen flex-col justify-between bg-ptkp-mist p-4 sm:p-6 lg:p-8"
    >
        <Head title="Akses Ditolak (403) - PT Kertas Padalarang" />

        <!-- Top Bar with Official Logo -->
        <header
            class="mx-auto flex w-full max-w-4xl items-center justify-between pb-6"
        >
            <div
                class="rounded-lg border border-ptkp-border/30 bg-white px-4 py-2 shadow-sm"
            >
                <AppLogo width="160px" theme="on-light" />
            </div>
            <div
                class="text-ptkp-text-body/70 hidden font-body text-xs sm:block"
            >
                Sistem Informasi Internal Terintegrasi
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="mx-auto my-auto w-full max-w-xl py-8">
            <div
                class="space-y-6 rounded-xl border border-ptkp-border bg-white p-8 text-center shadow-card sm:p-10"
            >
                <!-- Security Shield Icon -->
                <div
                    class="inline-flex h-20 w-20 items-center justify-center rounded-full border border-rose-200 bg-rose-50 text-rose-600 shadow-sm"
                >
                    <svg
                        class="h-10 w-10"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.75"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"
                        />
                    </svg>
                </div>

                <!-- Headings -->
                <div class="space-y-2">
                    <div
                        class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 px-3 py-1 font-body text-xs font-semibold text-rose-800"
                    >
                        <span
                            class="h-2 w-2 animate-pulse rounded-full bg-rose-600"
                        ></span>
                        Status HTTP 403 &bull; Terlarang
                    </div>
                    <h1
                        class="font-heading text-2xl font-bold tracking-tight text-ptkp-navy sm:text-3xl"
                    >
                        Akses Ditolak
                    </h1>
                    <p
                        class="text-ptkp-text-body/80 mx-auto max-w-md pt-1 font-body text-sm leading-relaxed"
                    >
                        {{ message }}
                    </p>
                </div>

                <!-- Information Callout Box -->
                <div
                    class="text-ptkp-text-body space-y-1.5 rounded-lg border border-ptkp-border/60 bg-ptkp-mist/70 p-4 text-left font-body text-xs"
                >
                    <div
                        class="flex items-center gap-1.5 font-semibold text-ptkp-navy"
                    >
                        <svg
                            class="h-4 w-4 text-ptkp-navy"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        Petunjuk Keamanan & Otorisasi:
                    </div>
                    <p class="text-ptkp-text-body/75">
                        Halaman ini khusus diperuntukkan bagi peran otorisasi
                        tertentu (misalnya Administrator Platform atau Pimpinan
                        Berwenang). Jika Anda memerlukan akses kedinasan ke
                        fitur ini, silakan koordinasikan dengan Departemen SDM
                        atau Tim IT PTKP.
                    </p>
                </div>

                <!-- Action CTA Buttons -->
                <div
                    class="flex flex-col items-center justify-center gap-3 pt-2 sm:flex-row"
                >
                    <Link href="/dashboard" class="w-full sm:w-auto">
                        <AppButton
                            variant="primary"
                            class="w-full px-6 py-2.5 sm:w-auto"
                        >
                            <svg
                                class="mr-2 h-4 w-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"
                                />
                            </svg>
                            Kembali ke Portal Aplikasi
                        </AppButton>
                    </Link>

                    <button
                        type="button"
                        @click="goBack"
                        class="w-full rounded-lg border border-ptkp-navy px-5 py-2.5 font-heading text-xs font-bold text-ptkp-navy transition-colors hover:bg-ptkp-mist/50 sm:w-auto"
                    >
                        &larr; Halaman Sebelumnya
                    </button>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer
            class="text-ptkp-text-body/60 mx-auto w-full max-w-4xl border-t border-ptkp-border/40 pt-6 text-center font-body text-xs"
        >
            PT Kertas Padalarang &bull; Peruri Security Paper Mill &copy;
            {{ new Date().getFullYear() }}
        </footer>
    </div>
</template>
