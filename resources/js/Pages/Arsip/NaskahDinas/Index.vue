<script setup lang="ts">
import AppBadge from '@/Components/AppBadge.vue';
import AppButton from '@/Components/AppButton.vue';
import AppCard from '@/Components/AppCard.vue';
import AppEmptyState from '@/Components/AppEmptyState.vue';
import AppInput from '@/Components/AppInput.vue';
import AppSelect from '@/Components/AppSelect.vue';
import AppShell from '@/Layouts/AppShell.vue';
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface LetterItem {
    id: string;
    document_number: string | null;
    subject: string;
    letter_date: string;
    status: string;
    type: { name: string; code: string };
    created_at: string;
}

const props = defineProps<{
    letters: {
        data: LetterItem[];
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: { search?: string; type_code?: string };
    typeOptions: Array<{ id: string; name: string; code: string }>;
}>();

const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type_code || '');

const handleFilter = () => {
    router.get(
        route('arsip.naskah-dinas.index'),
        { search: search.value, type_code: typeFilter.value },
        { preserveState: true, replace: true },
    );
};

const getStatusBadge = (
    status: string,
): {
    variant: 'navy' | 'violet' | 'success' | 'warning' | 'error' | 'neutral';
    label: string;
} => {
    switch (status) {
        case 'draft':
            return { variant: 'neutral', label: 'Draf' };
        case 'in_review':
            return { variant: 'warning', label: 'Dalam Review' };
        case 'revision_requested':
            return { variant: 'warning', label: 'Perlu Revisi' };
        case 'approved':
            return { variant: 'navy', label: 'Disetujui' };
        case 'published':
            return { variant: 'success', label: 'Diterbitkan (Sah)' };
        case 'rejected':
            return { variant: 'error', label: 'Ditolak' };
        default:
            return { variant: 'neutral', label: status };
    }
};

const pageTitle = computed(() => {
    switch (typeFilter.value) {
        case 'ND':
            return 'Nota Dinas (ND)';
        case 'E':
            return 'Surat Memo Direksi (E)';
        case 'ST':
            return 'Surat Tugas (ST)';
        case 'PENG':
            return 'Surat Pengumuman (PENG)';
        case 'KPTS':
            return 'Surat Keputusan (KPTS)';
        default:
            return 'Naskah Dinas Internal';
    }
});

const pageSubtitle = computed(() => {
    switch (typeFilter.value) {
        case 'ND':
            return 'Daftar dan pembuatan Nota Dinas resmi antar unit kerja dan kepada Direksi.';
        case 'E':
            return 'Surat Memo Direksi dengan persetujuan berjenjang dan tanda tangan digital.';
        case 'ST':
            return 'Surat Tugas penugasan kedinasan karyawan dengan daftar personel dan rincian tugas.';
        case 'PENG':
            return 'Surat Pengumuman kedinasan resmi PT Kertas Padalarang.';
        case 'KPTS':
            return 'Surat Keputusan penetapan kebijakan pimpinan dan Direksi PT Kertas Padalarang.';
        default:
            return 'Pengelolaan Nota Dinas (ND), Surat Memo Direksi (E), Surat Tugas (ST), Pengumuman (PENG), dan SK.';
    }
});

const createButtonText = computed(() => {
    switch (typeFilter.value) {
        case 'ND':
            return 'Buat Nota Dinas';
        case 'E':
            return 'Buat Surat Memo';
        case 'ST':
            return 'Buat Surat Tugas';
        case 'PENG':
            return 'Buat Pengumuman';
        case 'KPTS':
            return 'Buat Surat Keputusan';
        default:
            return 'Buat Naskah Dinas';
    }
});
</script>

<template>
    <AppShell :title="`${pageTitle} - E-Arsip PTKP`">
        <div class="space-y-6">
            <!-- Header -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1
                        class="text-brand-navy-dark font-heading text-2xl font-bold tracking-tight"
                    >
                        {{ pageTitle }}
                    </h1>
                    <p class="text-brand-slate-light mt-1 font-body text-sm">
                        {{ pageSubtitle }}
                    </p>
                </div>

                <Link
                    :href="
                        route(
                            'arsip.naskah-dinas.create',
                            typeFilter ? { type_code: typeFilter } : {},
                        )
                    "
                >
                    <AppButton variant="primary">
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
                                d="M12 4v16m8-8H4"
                            />
                        </svg>
                        {{ createButtonText }}
                    </AppButton>
                </Link>
            </div>

            <!-- Filter Controls -->
            <AppCard class="p-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <AppInput
                        v-model="search"
                        placeholder="Cari perihal atau nomor naskah dinas..."
                        @keyup.enter="handleFilter"
                    />
                    <AppSelect
                        v-model="typeFilter"
                        :options="[
                            { value: '', label: 'Semua Jenis Naskah' },
                            ...typeOptions.map((t) => ({
                                value: t.code,
                                label: `${t.code} - ${t.name}`,
                            })),
                        ]"
                        @change="handleFilter"
                    />
                    <div class="flex items-center gap-2">
                        <AppButton variant="secondary" @click="handleFilter">
                            Terapkan Filter
                        </AppButton>
                        <AppButton
                            v-if="search || typeFilter"
                            variant="text"
                            @click="
                                search = '';
                                typeFilter = '';
                                handleFilter();
                            "
                        >
                            Reset
                        </AppButton>
                    </div>
                </div>
            </AppCard>

            <!-- Table Card -->
            <AppCard class="overflow-hidden">
                <div v-if="letters.data.length > 0" class="overflow-x-auto">
                    <table
                        class="w-full border-collapse text-left font-body text-sm"
                    >
                        <thead>
                            <tr
                                class="border-brand-slate-border bg-brand-slate-light/5 text-brand-slate-dark border-b text-xs uppercase tracking-wider"
                            >
                                <th class="px-4 py-3.5 font-semibold">Jenis</th>
                                <th class="px-4 py-3.5 font-semibold">
                                    Nomor Naskah
                                </th>
                                <th class="px-4 py-3.5 font-semibold">
                                    Perihal
                                </th>
                                <th class="px-4 py-3.5 font-semibold">
                                    Tanggal
                                </th>
                                <th class="px-4 py-3.5 font-semibold">
                                    Status
                                </th>
                                <th
                                    class="px-4 py-3.5 text-right font-semibold"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-brand-slate-border/50 divide-y">
                            <tr
                                v-for="letter in letters.data"
                                :key="letter.id"
                                class="hover:bg-brand-slate-light/5 transition-colors"
                            >
                                <td class="px-4 py-3.5">
                                    <AppBadge variant="navy" size="sm">
                                        {{ letter.type.code }}
                                    </AppBadge>
                                </td>
                                <td
                                    class="text-brand-navy-dark px-4 py-3.5 font-semibold"
                                >
                                    {{ letter.document_number || 'Draf' }}
                                </td>
                                <td
                                    class="text-brand-slate-dark max-w-sm truncate px-4 py-3.5"
                                    :title="letter.subject"
                                >
                                    {{ letter.subject }}
                                </td>
                                <td
                                    class="text-brand-slate-light whitespace-nowrap px-4 py-3.5"
                                >
                                    {{
                                        new Date(
                                            letter.letter_date,
                                        ).toLocaleDateString('id-ID')
                                    }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <AppBadge
                                        :variant="
                                            getStatusBadge(letter.status)
                                                .variant as any
                                        "
                                        size="sm"
                                    >
                                        {{
                                            getStatusBadge(letter.status).label
                                        }}
                                    </AppBadge>
                                </td>
                                <td
                                    class="space-x-2 whitespace-nowrap px-4 py-3.5 text-right"
                                >
                                    <a
                                        :href="
                                            route(
                                                'arsip.letters.preview',
                                                letter.id,
                                            )
                                        "
                                        target="_blank"
                                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-2.5 py-1.5 font-body text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                        title="Pratinjau PDF di tab baru"
                                    >
                                        <svg
                                            class="mr-1 h-3.5 w-3.5 text-[#20288E]"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                            />
                                        </svg>
                                        Pratinjau
                                    </a>
                                    <Link
                                        :href="
                                            route(
                                                'arsip.naskah-dinas.show',
                                                letter.id,
                                            )
                                        "
                                    >
                                        <AppButton
                                            variant="secondary"
                                            size="sm"
                                        >
                                            Detail
                                        </AppButton>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="p-8">
                    <AppEmptyState
                        title="Belum Ada Naskah Dinas"
                        description="Tidak ada dokumen naskah dinas internal yang tercatat pada sistem."
                    />
                </div>
            </AppCard>
        </div>
    </AppShell>
</template>
