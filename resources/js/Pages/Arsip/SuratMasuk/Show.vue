<script setup lang="ts">
import AppBadge from '@/Components/AppBadge.vue';
import AppButton from '@/Components/AppButton.vue';
import AppCard from '@/Components/AppCard.vue';
import AppInput from '@/Components/AppInput.vue';
import AppSelect from '@/Components/AppSelect.vue';
import AppTextarea from '@/Components/AppTextarea.vue';
import AppShell from '@/Layouts/AppShell.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface LetterData {
    id: string;
    document_number: string;
    subject: string;
    letter_date: string;
    destination_text: string | null;
    confidentiality: string;
    urgency: string;
    content_body?: { received_date?: string; sender_organization?: string };
    dispositions: Array<{
        id: string;
        instruction: string;
        notes: string | null;
        deadline: string | null;
        status: string;
        created_at: string;
        completed_at: string | null;
    }>;
}

const props = defineProps<{
    letter: LetterData;
    recipientUnit?: { id: string; name: string; code: string } | null;
    orgUnits: Array<{ id: string; name: string; code: string; level: number }>;
}>();

const showDispositionModal = ref(false);

const dispositionForm = useForm({
    recipient_unit_id: props.orgUnits[0]?.id || '',
    instruction: 'Mohon untuk dipelajari dan ditindaklanjuti sesuai ketentuan.',
    notes: '',
    deadline: '',
});

const submitDisposition = () => {
    dispositionForm.post(
        route('arsip.surat-masuk.disposisi.store', props.letter.id),
        {
            onSuccess: () => {
                showDispositionModal.value = false;
                dispositionForm.reset();
            },
        },
    );
};
</script>

<template>
    <AppShell :title="`Surat Masuk: ${letter.document_number} - E-Arsip PTKP`">
        <div class="mx-auto max-w-5xl space-y-6">
            <!-- Breadcrumb & Top Bar -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <div
                        class="text-brand-slate-light mb-1 flex items-center gap-2 font-body text-xs"
                    >
                        <Link
                            :href="route('arsip.surat-masuk.index')"
                            class="hover:underline"
                            >Surat Masuk</Link
                        >
                        <span>/</span>
                        <span>Detail & Lembar Disposisi</span>
                    </div>
                    <h1
                        class="text-brand-navy-dark font-heading text-2xl font-bold tracking-tight"
                    >
                        {{ letter.document_number }}
                    </h1>
                    <p class="text-brand-slate-dark mt-0.5 font-body text-sm">
                        {{ letter.subject }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Link :href="route('arsip.surat-masuk.index')">
                        <AppButton variant="secondary">
                            &larr; Daftar Surat
                        </AppButton>
                    </Link>
                    <AppButton
                        variant="primary"
                        @click="showDispositionModal = true"
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
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                            />
                        </svg>
                        Terbitkan Disposisi
                    </AppButton>
                </div>
            </div>

            <!-- Main Overview Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Letter Metadata -->
                <div class="space-y-6">
                    <AppCard class="space-y-4 p-6">
                        <h2
                            class="text-brand-navy-dark border-brand-slate-border border-b pb-2 font-heading text-base font-bold"
                        >
                            Data Surat Masuk
                        </h2>

                        <dl class="space-y-3 font-body text-xs">
                            <div>
                                <dt class="text-brand-slate-light font-medium">
                                    Instansi Pengirim
                                </dt>
                                <dd
                                    class="text-brand-navy-dark mt-0.5 text-sm font-semibold"
                                >
                                    {{ letter.destination_text || '-' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-brand-slate-light font-medium">
                                    Nomor Surat Asal
                                </dt>
                                <dd
                                    class="text-brand-slate-dark mt-0.5 font-medium"
                                >
                                    {{ letter.document_number }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-brand-slate-light font-medium">
                                    Unit Penerima PTKP
                                </dt>
                                <dd
                                    class="text-brand-slate-dark mt-0.5 font-medium"
                                >
                                    {{
                                        recipientUnit
                                            ? `${recipientUnit.code} - ${recipientUnit.name}`
                                            : '-'
                                    }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-brand-slate-light font-medium">
                                    Tanggal Surat
                                </dt>
                                <dd class="text-brand-slate-dark mt-0.5">
                                    {{
                                        new Date(
                                            letter.letter_date,
                                        ).toLocaleDateString('id-ID', {
                                            dateStyle: 'full',
                                        })
                                    }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-brand-slate-light font-medium">
                                    Tanggal Diterima
                                </dt>
                                <dd class="text-brand-slate-dark mt-0.5">
                                    {{
                                        letter.content_body?.received_date
                                            ? new Date(
                                                  letter.content_body
                                                      .received_date,
                                              ).toLocaleDateString('id-ID', {
                                                  dateStyle: 'full',
                                              })
                                            : '-'
                                    }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-brand-slate-light font-medium">
                                    Sifat & Urgensi
                                </dt>
                                <dd class="mt-1 flex items-center gap-2">
                                    <AppBadge variant="neutral" size="sm">{{
                                        letter.confidentiality
                                    }}</AppBadge>
                                    <AppBadge variant="navy" size="sm">{{
                                        letter.urgency
                                    }}</AppBadge>
                                </dd>
                            </div>
                        </dl>
                    </AppCard>
                </div>

                <!-- Dispositions Section -->
                <div class="space-y-6 lg:col-span-2">
                    <AppCard class="space-y-4 p-6">
                        <div
                            class="border-brand-slate-border flex items-center justify-between border-b pb-2"
                        >
                            <h2
                                class="text-brand-navy-dark font-heading text-base font-bold"
                            >
                                Lembar Disposisi Pejabat
                            </h2>
                            <span
                                class="text-brand-slate-light font-body text-xs font-semibold"
                            >
                                Total:
                                {{ letter.dispositions?.length || 0 }} Lembar
                            </span>
                        </div>

                        <div
                            v-if="
                                letter.dispositions &&
                                letter.dispositions.length > 0
                            "
                            class="space-y-4"
                        >
                            <div
                                v-for="disp in letter.dispositions"
                                :key="disp.id"
                                class="border-brand-slate-border/80 bg-brand-slate-light/5 space-y-2 rounded-xl border p-4"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-brand-navy-dark font-heading text-xs font-bold"
                                        >
                                            Instruksi Pejabat
                                        </span>
                                        <AppBadge
                                            :variant="
                                                disp.status === 'completed'
                                                    ? 'success'
                                                    : disp.status === 'read'
                                                      ? 'navy'
                                                      : 'warning'
                                            "
                                            size="sm"
                                        >
                                            {{
                                                disp.status === 'completed'
                                                    ? 'Selesai'
                                                    : disp.status === 'read'
                                                      ? 'Dibaca'
                                                      : 'Belum Dibaca'
                                            }}
                                        </AppBadge>
                                    </div>
                                    <span
                                        class="text-brand-slate-light font-body text-[11px]"
                                    >
                                        {{
                                            new Date(
                                                disp.created_at,
                                            ).toLocaleString('id-ID')
                                        }}
                                    </span>
                                </div>

                                <p
                                    class="text-brand-slate-dark font-body text-xs font-medium"
                                >
                                    {{ disp.instruction }}
                                </p>

                                <div
                                    v-if="disp.notes"
                                    class="border-brand-slate-border text-brand-slate-dark whitespace-pre-line rounded-lg border bg-white p-2.5 font-body text-[11px]"
                                >
                                    {{ disp.notes }}
                                </div>

                                <div
                                    v-if="disp.deadline"
                                    class="flex items-center gap-1 font-body text-[11px] font-semibold text-rose-600"
                                >
                                    <svg
                                        class="h-3.5 w-3.5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                    Tenggat Waktu:
                                    {{
                                        new Date(
                                            disp.deadline,
                                        ).toLocaleDateString('id-ID')
                                    }}
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="text-brand-slate-light py-8 text-center font-body text-xs italic"
                        >
                            Belum ada lembar disposisi yang diterbitkan untuk
                            surat ini.
                        </div>
                    </AppCard>
                </div>
            </div>

            <!-- Modal Terbitkan Disposisi -->
            <div
                v-if="showDispositionModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
            >
                <div
                    class="w-full max-w-lg space-y-4 rounded-xl bg-white p-6 shadow-xl"
                >
                    <div
                        class="border-brand-slate-border flex items-center justify-between border-b pb-2"
                    >
                        <h3
                            class="text-brand-navy-dark font-heading text-lg font-bold"
                        >
                            Terbitkan Lembar Disposisi
                        </h3>
                        <button
                            class="text-brand-slate-light hover:text-brand-slate-dark"
                            @click="showDispositionModal = false"
                        >
                            &times;
                        </button>
                    </div>

                    <form @submit.prevent="submitDisposition" class="space-y-4">
                        <div>
                            <label
                                class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                            >
                                Tujuan Unit Disposisi
                                <span class="text-rose-500">*</span>
                            </label>
                            <AppSelect
                                v-model="dispositionForm.recipient_unit_id"
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
                                Instruksi Pokok
                                <span class="text-rose-500">*</span>
                            </label>
                            <AppSelect
                                v-model="dispositionForm.instruction"
                                :options="[
                                    {
                                        value: 'Mohon untuk dipelajari dan ditindaklanjuti sesuai ketentuan.',
                                        label: 'Pelajari & Tindaklanjuti',
                                    },
                                    {
                                        value: 'Tolong siapkan bahan rapat / koordinasi terkait.',
                                        label: 'Siapkan Bahan Koordinasi',
                                    },
                                    {
                                        value: 'Harap hadir mewakili Direksi / Perusahaan.',
                                        label: 'Hadir Mewakili',
                                    },
                                    {
                                        value: 'Untuk diketahui dan diarsipkan.',
                                        label: 'Untuk Diketahui & Arsipkan',
                                    },
                                    {
                                        value: 'Mohon berikan kajian analisis dan rekomendasi.',
                                        label: 'Buat Kajian & Rekomendasi',
                                    },
                                ]"
                            />
                        </div>

                        <div>
                            <label
                                class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                            >
                                Catatan Tambahan Pejabat
                            </label>
                            <AppTextarea
                                v-model="dispositionForm.notes"
                                placeholder="Tuliskan catatan arahan spesifik bagi penerima disposisi..."
                                :rows="3"
                            />
                        </div>

                        <div>
                            <label
                                class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                            >
                                Batas Waktu Penyelesaian (Deadline)
                            </label>
                            <AppInput
                                v-model="dispositionForm.deadline"
                                type="date"
                            />
                        </div>

                        <div
                            class="border-brand-slate-border flex items-center justify-end gap-3 border-t pt-3"
                        >
                            <AppButton
                                variant="secondary"
                                type="button"
                                @click="showDispositionModal = false"
                            >
                                Batal
                            </AppButton>
                            <AppButton
                                variant="primary"
                                type="submit"
                                :disabled="dispositionForm.processing"
                            >
                                {{
                                    dispositionForm.processing
                                        ? 'Menerbitkan...'
                                        : 'Terbitkan Disposisi'
                                }}
                            </AppButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppShell>
</template>
