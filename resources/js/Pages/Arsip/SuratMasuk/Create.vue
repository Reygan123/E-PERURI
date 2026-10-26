<script setup lang="ts">
import AppButton from '@/Components/AppButton.vue';
import AppCard from '@/Components/AppCard.vue';
import AppInput from '@/Components/AppInput.vue';
import AppSelect from '@/Components/AppSelect.vue';
import InputError from '@/Components/InputError.vue';
import AppShell from '@/Layouts/AppShell.vue';
import { Link, useForm } from '@inertiajs/vue3';

interface OrgUnitOption {
    id: string;
    code: string;
    name: string;
    level: number;
}

const props = defineProps<{
    type: { id: string; name: string; code: string };
    orgUnits: OrgUnitOption[];
}>();

const form = useForm({
    document_number: '',
    sender_organization: '',
    origin_unit_id: props.orgUnits[0]?.id || '',
    subject: '',
    letter_date: new Date().toISOString().split('T')[0],
    received_date: new Date().toISOString().split('T')[0],
    confidentiality: 'biasa',
    urgency: 'biasa',
    scanned_file: null as File | null,
});

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.scanned_file = target.files[0];
    }
};

const submit = () => {
    form.post(route('arsip.surat-masuk.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <AppShell title="Catat Surat Masuk - E-Arsip PTKP">
        <div class="mx-auto max-w-4xl space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-brand-navy-dark font-heading text-2xl font-bold tracking-tight"
                    >
                        Pencatatan Surat Masuk
                    </h1>
                    <p class="text-brand-slate-light mt-1 font-body text-sm">
                        Digitalisasi surat fisik dari pihak luar, pengunggahan
                        scan PDF, dan persiapan lembar disposisi.
                    </p>
                </div>

                <Link :href="route('arsip.surat-masuk.index')">
                    <AppButton variant="secondary"> &larr; Kembali </AppButton>
                </Link>
            </div>

            <!-- Form Card -->
            <form @submit.prevent="submit">
                <AppCard class="space-y-6 p-6">
                    <div class="space-y-4">
                        <h2
                            class="text-brand-navy-dark border-brand-slate-border border-b pb-2 font-heading text-base font-semibold"
                        >
                            1. Identitas Surat Pengirim
                        </h2>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Nomor Surat Pihak Luar
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppInput
                                    v-model="form.document_number"
                                    placeholder="Contoh: 120/DIR-IND/IV/2026"
                                    required
                                />
                                <InputError
                                    :message="form.errors.document_number"
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Instansi / Perusahaan Pengirim
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppInput
                                    v-model="form.sender_organization"
                                    placeholder="Contoh: PT Peruri Nusantara / Kementerian Keuangan"
                                    required
                                />
                                <InputError
                                    :message="form.errors.sender_organization"
                                />
                            </div>
                        </div>

                        <div>
                            <label
                                class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                            >
                                Perihal Surat
                                <span class="text-rose-500">*</span>
                            </label>
                            <AppInput
                                v-model="form.subject"
                                placeholder="Contoh: Undangan Koordinasi Standardisasi Kertas Berharga Nasional"
                                required
                            />
                            <InputError :message="form.errors.subject" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Tanggal Surat Pengirim
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppInput
                                    v-model="form.letter_date"
                                    type="date"
                                    required
                                />
                                <InputError
                                    :message="form.errors.letter_date"
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Tanggal Diterima di PTKP
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppInput
                                    v-model="form.received_date"
                                    type="date"
                                    required
                                />
                                <InputError
                                    :message="form.errors.received_date"
                                />
                            </div>
                        </div>
                    </div>

                    <div
                        class="border-brand-slate-border space-y-4 border-t pt-4"
                    >
                        <h2
                            class="text-brand-navy-dark border-brand-slate-border border-b pb-2 font-heading text-base font-semibold"
                        >
                            2. Unit Penerima & Sifat Dokumen
                        </h2>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Unit Penerima di PTKP
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppSelect
                                    v-model="form.origin_unit_id"
                                    :options="
                                        orgUnits.map((u) => ({
                                            value: u.id,
                                            label: `${u.code} - ${u.name}`,
                                        }))
                                    "
                                    required
                                />
                                <InputError
                                    :message="form.errors.origin_unit_id"
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Sifat Keamanan
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppSelect
                                    v-model="form.confidentiality"
                                    :options="[
                                        { value: 'biasa', label: 'Biasa' },
                                        {
                                            value: 'terbatas',
                                            label: 'Terbatas',
                                        },
                                        { value: 'rahasia', label: 'Rahasia' },
                                        {
                                            value: 'sangat_rahasia',
                                            label: 'Sangat Rahasia',
                                        },
                                    ]"
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Tingkat Urgensi
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppSelect
                                    v-model="form.urgency"
                                    :options="[
                                        { value: 'biasa', label: 'Biasa' },
                                        { value: 'segera', label: 'Segera' },
                                        {
                                            value: 'sangat_segera',
                                            label: 'Sangat Segera',
                                        },
                                    ]"
                                />
                            </div>
                        </div>
                    </div>

                    <div
                        class="border-brand-slate-border space-y-4 border-t pt-4"
                    >
                        <h2
                            class="text-brand-navy-dark border-brand-slate-border border-b pb-2 font-heading text-base font-semibold"
                        >
                            3. Berkas Scan Surat Masuk (PDF)
                        </h2>

                        <div>
                            <label
                                class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                            >
                                Unggah Scan Surat Fisik
                                <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="file"
                                class="text-brand-slate-dark file:bg-brand-navy-dark hover:file:bg-brand-blue border-brand-slate-border block w-full rounded-lg border p-2 text-xs file:mr-4 file:rounded-lg file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-white"
                                accept=".pdf"
                                @change="handleFileChange"
                                required
                            />
                            <p
                                class="text-brand-slate-light mt-1 font-body text-[11px]"
                            >
                                Format PDF, maksimal 15MB. Pastikan berkas scan
                                jelas dan terbaca untuk proses disposisi.
                            </p>
                            <InputError :message="form.errors.scanned_file" />
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div
                        class="border-brand-slate-border flex items-center justify-end gap-3 border-t pt-4"
                    >
                        <Link :href="route('arsip.surat-masuk.index')">
                            <AppButton variant="secondary" type="button">
                                Batal
                            </AppButton>
                        </Link>
                        <AppButton
                            variant="primary"
                            type="submit"
                            :disabled="form.processing"
                        >
                            {{
                                form.processing
                                    ? 'Menyimpan...'
                                    : 'Simpan & Catat Surat Masuk'
                            }}
                        </AppButton>
                    </div>
                </AppCard>
            </form>
        </div>
    </AppShell>
</template>
