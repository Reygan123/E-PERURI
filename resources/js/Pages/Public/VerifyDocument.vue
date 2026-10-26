<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface VerificationData {
    is_valid: boolean;
    integrity_verified: boolean;
    letter_id: string;
    document_number: string;
    letter_type_code: string;
    letter_type_name: string;
    subject: string;
    letter_date: string;
    origin_unit_name: string;
    signer_name: string;
    signer_nip: string;
    signer_position: string;
    signed_at: string;
    issuer: string;
    certificate_serial: string;
    algorithm: string;
    signature_hash: string;
    verification_url: string;
    has_signed_file: boolean;
    signed_file_id: string | null;
}

const props = defineProps<{
    verification: VerificationData | null;
    hash?: string;
}>();

const isValid = computed(() => Boolean(props.verification?.is_valid));
</script>

<template>
    <Head title="Verifikasi Dokumen Elektronik - PT Kertas Padalarang" />

    <div
        class="flex min-h-screen flex-col justify-between bg-slate-50 font-sans text-slate-800"
    >
        <!-- Official Navbar Header -->
        <header
            class="border-b-2 border-[#20288E] bg-[#141A5C] text-white shadow-md"
        >
            <div
                class="mx-auto flex max-w-4xl items-center justify-between px-4 py-4 sm:px-6"
            >
                <div class="flex items-center space-x-3">
                    <div
                        class="flex items-center justify-center rounded-lg bg-white p-1.5 shadow-sm"
                    >
                        <img
                            src="/assets/logo.png"
                            alt="Logo PTKP"
                            class="h-8 w-auto sm:h-9"
                        />
                    </div>
                    <div>
                        <div
                            class="text-sm font-bold leading-tight tracking-wide sm:text-base"
                        >
                            PT KERTAS PADALARANG
                        </div>
                        <div
                            class="text-[11px] font-medium text-slate-300 sm:text-xs"
                        >
                            Peruri Security Paper Mill • Portal Verifikasi
                            Publik
                        </div>
                    </div>
                </div>

                <div
                    class="hidden items-center space-x-2 rounded-full border border-blue-400/30 bg-[#20288E]/60 px-3 py-1.5 text-xs font-medium text-blue-100 sm:flex"
                >
                    <svg
                        class="h-4 w-4 text-emerald-400"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <span>Layanan TTE Tersertifikasi BSrE</span>
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="mx-auto w-full max-w-4xl flex-grow px-4 py-8 sm:px-6">
            <!-- 1. VERIFIED STATE -->
            <div v-if="isValid && verification" class="space-y-6">
                <!-- Status Banner -->
                <div
                    class="relative overflow-hidden rounded-2xl border border-emerald-100 bg-white p-6 text-center shadow-sm ring-1 ring-emerald-500/20 sm:p-8"
                >
                    <div
                        class="pointer-events-none absolute -right-12 -top-12 h-36 w-36 rounded-full bg-emerald-50 blur-2xl"
                    />

                    <div
                        class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 shadow-inner sm:h-20 sm:w-20"
                    >
                        <svg
                            class="h-10 w-10 sm:h-12 sm:w-12"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2.5"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                            />
                        </svg>
                    </div>

                    <h1
                        class="text-xl font-extrabold tracking-tight text-[#141A5C] sm:text-2xl"
                    >
                        KEABSAHAN NASKAH DINAS TERVERIFIKASI
                    </h1>
                    <p
                        class="mx-auto mt-2 max-w-2xl text-sm text-slate-600 sm:text-base"
                    >
                        Dokumen elektronik ini resmi, sah secara hukum, dan
                        telah ditandatangani menggunakan sertifikat digital
                        tersertifikasi oleh
                        <strong
                            >Balai Sertifikasi Elektronik (BSrE - BSSN)</strong
                        >.
                    </p>

                    <div
                        class="mt-4 inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700"
                    >
                        <span
                            class="mr-2 h-2 w-2 animate-pulse rounded-full bg-emerald-500"
                        />
                        Integritas Dokumen Utuh & Terjaga (SHA-256 Matched)
                    </div>
                </div>

                <!-- Document Details Card -->
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 bg-[#EEF0FB] px-6 py-4"
                    >
                        <div
                            class="flex items-center space-x-2 text-sm font-bold text-[#141A5C] sm:text-base"
                        >
                            <svg
                                class="h-5 w-5 text-[#20288E]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                />
                            </svg>
                            <span>Informasi Naskah Dinas</span>
                        </div>
                        <span
                            class="rounded-full bg-[#141A5C] px-2.5 py-0.5 text-xs font-medium text-white"
                        >
                            {{ verification.letter_type_name }}
                        </span>
                    </div>

                    <div class="divide-y divide-slate-100 p-6 text-sm">
                        <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-slate-500">
                                Nomor Naskah
                            </dt>
                            <dd
                                class="mt-1 font-mono text-base font-bold text-[#141A5C] sm:col-span-2 sm:mt-0"
                            >
                                {{ verification.document_number }}
                            </dd>
                        </div>

                        <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-slate-500">Perihal</dt>
                            <dd
                                class="mt-1 font-semibold text-slate-900 sm:col-span-2 sm:mt-0"
                            >
                                {{ verification.subject }}
                            </dd>
                        </div>

                        <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-slate-500">
                                Unit Penerbit
                            </dt>
                            <dd
                                class="mt-1 text-slate-800 sm:col-span-2 sm:mt-0"
                            >
                                {{ verification.origin_unit_name }}
                            </dd>
                        </div>

                        <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-slate-500">
                                Tanggal Naskah
                            </dt>
                            <dd
                                class="mt-1 text-slate-800 sm:col-span-2 sm:mt-0"
                            >
                                {{ verification.letter_date }}
                            </dd>
                        </div>
                    </div>
                </div>

                <!-- Signer & Certificate Metadata Card -->
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 bg-[#EEF0FB] px-6 py-4"
                    >
                        <div
                            class="flex items-center space-x-2 text-sm font-bold text-[#141A5C] sm:text-base"
                        >
                            <svg
                                class="h-5 w-5 text-[#20288E]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                />
                            </svg>
                            <span
                                >Informasi Penandatangan & Sertifikat
                                Digital</span
                            >
                        </div>
                        <span
                            class="rounded-full bg-emerald-600 px-2.5 py-0.5 text-xs font-medium text-white"
                        >
                            VALID
                        </span>
                    </div>

                    <div class="divide-y divide-slate-100 p-6 text-sm">
                        <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-slate-500">
                                Nama Pejabat
                            </dt>
                            <dd
                                class="mt-1 font-bold text-slate-900 sm:col-span-2 sm:mt-0"
                            >
                                {{ verification.signer_name }}
                            </dd>
                        </div>

                        <div
                            v-if="
                                verification.signer_nip &&
                                verification.signer_nip !== '-'
                            "
                            class="py-3 sm:grid sm:grid-cols-3 sm:gap-4"
                        >
                            <dt class="font-medium text-slate-500">
                                NIP / NRP
                            </dt>
                            <dd
                                class="mt-1 font-mono text-slate-800 sm:col-span-2 sm:mt-0"
                            >
                                {{ verification.signer_nip }}
                            </dd>
                        </div>

                        <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-slate-500">
                                Jabatan Struktural
                            </dt>
                            <dd
                                class="mt-1 text-slate-800 sm:col-span-2 sm:mt-0"
                            >
                                {{ verification.signer_position }}
                            </dd>
                        </div>

                        <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-slate-500">
                                Waktu Penandatanganan
                            </dt>
                            <dd
                                class="mt-1 font-medium text-slate-800 sm:col-span-2 sm:mt-0"
                            >
                                {{ verification.signed_at }}
                            </dd>
                        </div>

                        <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-slate-500">
                                Penerbit Sertifikat
                            </dt>
                            <dd
                                class="mt-1 text-slate-800 sm:col-span-2 sm:mt-0"
                            >
                                {{ verification.issuer }}
                            </dd>
                        </div>

                        <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-slate-500">
                                Nomor Seri Sertifikat
                            </dt>
                            <dd
                                class="mt-1 rounded border border-slate-200 bg-slate-50 p-2 font-mono text-xs text-slate-700 sm:col-span-2 sm:mt-0"
                            >
                                {{ verification.certificate_serial }}
                            </dd>
                        </div>

                        <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-slate-500">
                                Algoritma TTE
                            </dt>
                            <dd
                                class="mt-1 font-mono text-xs text-slate-700 sm:col-span-2 sm:mt-0"
                            >
                                {{ verification.algorithm }}
                            </dd>
                        </div>

                        <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-slate-500">
                                Kode Hash Verifikasi
                            </dt>
                            <dd
                                class="mt-1 break-all rounded border border-slate-200 bg-slate-50 p-2 font-mono text-xs text-slate-600 sm:col-span-2 sm:mt-0"
                            >
                                {{ verification.signature_hash }}
                            </dd>
                        </div>
                    </div>
                </div>

                <!-- Legal Clause -->
                <div
                    class="rounded-xl border border-blue-200/60 bg-blue-50/60 p-4 text-xs leading-relaxed text-blue-900"
                >
                    <p class="mb-1 font-semibold text-blue-950">
                        Dasar Hukum & Kekuatan Pembuktian:
                    </p>
                    <p>
                        Sesuai dengan Undang-Undang Republik Indonesia Nomor 11
                        Tahun 2008 tentang Informasi dan Transaksi Elektronik
                        (UU ITE) Pasal 5 dan Pasal 11, serta Peraturan
                        Pemerintah Nomor 71 Tahun 2019, Tanda Tangan Elektronik
                        yang tersertifikasi memiliki kekuatan hukum dan akibat
                        hukum yang sah serta mengikat setara dengan tanda tangan
                        basah di atas kertas.
                    </p>
                </div>
            </div>

            <!-- 2. INVALID / NOT FOUND STATE -->
            <div v-else class="space-y-6">
                <div
                    class="relative overflow-hidden rounded-2xl border border-rose-100 bg-white p-8 text-center shadow-sm ring-1 ring-rose-500/20"
                >
                    <div
                        class="mb-4 inline-flex h-20 w-20 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 shadow-inner"
                    >
                        <svg
                            class="h-12 w-12"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2.5"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                            />
                        </svg>
                    </div>

                    <h1
                        class="text-xl font-extrabold tracking-tight text-rose-950 sm:text-2xl"
                    >
                        DOKUMEN TIDAK TERDAFTAR ATAU TIDAK VALID
                    </h1>
                    <p
                        class="mx-auto mt-2 max-w-xl text-sm leading-relaxed text-slate-600 sm:text-base"
                    >
                        Kode hash verifikasi yang Anda masukkan tidak ditemukan
                        dalam basis data naskah dinas resmi PT Kertas Padalarang
                        atau telah dibatalkan.
                    </p>

                    <div
                        v-if="hash"
                        class="mt-4 inline-block max-w-full break-all rounded-lg border border-slate-200 bg-slate-50 p-3 font-mono text-xs text-slate-500"
                    >
                        Kode Hash: {{ hash }}
                    </div>

                    <div class="mt-6">
                        <p class="text-xs text-slate-500">
                            Harap pastikan Anda memindai QR Code resmi dari
                            dokumen asli atau hubungi Bagian Sekretariat PT
                            Kertas Padalarang untuk konfirmasi keabsahan fisik
                            dokumen.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div
                class="mt-8 flex flex-col items-center justify-center gap-3 text-center sm:flex-row"
            >
                <Link
                    href="/"
                    class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 sm:w-auto"
                >
                    &larr; Kembali ke Beranda
                </Link>
                <a
                    href="mailto:office@kertas-padalarang.co.id"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-[#141A5C] px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#20288E] sm:w-auto"
                >
                    Hubungi Sekretariat PTKP
                </a>
            </div>
        </main>

        <!-- Official Footer -->
        <footer
            class="mt-12 border-t border-slate-200 bg-white py-6 text-center text-xs text-slate-500"
        >
            <div class="mx-auto max-w-4xl px-4">
                <div class="text-sm font-bold text-[#141A5C]">
                    PT Kertas Padalarang • Peruri Security Paper Mill
                </div>
                <div class="mt-1 text-slate-500">
                    Jl. Cihaliwung No. 181 Padalarang, Kabupaten Bandung Barat,
                    Jawa Barat 40553
                </div>
                <div class="mt-2 text-[11px] text-slate-400">
                    © {{ new Date().getFullYear() }} E-Arsip PT Kertas
                    Padalarang. Dilindungi Undang-Undang.
                </div>
            </div>
        </footer>
    </div>
</template>
