<script setup lang="ts">
import AppButton from '@/Components/AppButton.vue';
import AppCard from '@/Components/AppCard.vue';
import AppInput from '@/Components/AppInput.vue';
import AppRichText from '@/Components/AppRichText.vue';
import AppSelect from '@/Components/AppSelect.vue';
import AppTextarea from '@/Components/AppTextarea.vue';
import InputError from '@/Components/InputError.vue';
import AppShell from '@/Layouts/AppShell.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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

const bodyHtml = ref('');
const destinationAddress = ref('');
const lampiran = ref('-');
const tembusanRaw = ref('');

const form = useForm({
    subject: '',
    origin_unit_id: props.orgUnits[0]?.id || '',
    destination_type: 'external_text',
    destination_text: '',
    destination_unit_ids: [] as string[],
    letter_date: new Date().toISOString().split('T')[0],
    confidentiality: 'biasa',
    urgency: 'biasa',
    content_body: {} as Record<string, any>,
    draft_file: null as File | null,
    submit_now: false,
});

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.draft_file = target.files[0];
    }
};

const submit = () => {
    const tembusanList = tembusanRaw.value
        .split('\n')
        .map((t) => t.trim())
        .filter((t) => t.length > 0);

    form.content_body = {
        destination_name: form.destination_text,
        destination_address: destinationAddress.value,
        lampiran: lampiran.value,
        body_html: bodyHtml.value,
        tembusan: tembusanList.length > 0 ? tembusanList : ['Arsip'],
    };

    form.post(route('arsip.surat-keluar.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <AppShell title="Buat Surat Keluar - E-Arsip PTKP">
        <div class="mx-auto max-w-4xl space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-brand-navy-dark font-heading text-2xl font-bold tracking-tight"
                    >
                        Buat Draf Surat Keluar
                    </h1>
                    <p class="text-brand-slate-light mt-1 font-body text-sm">
                        Surat Keluar digital resmi PT Kertas Padalarang dengan
                        penomoran otomatis dan template baku.
                    </p>
                </div>

                <Link :href="route('arsip.surat-keluar.index')">
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
                            1. Informasi Pokok Surat
                        </h2>

                        <div>
                            <label
                                class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                            >
                                Perihal Surat
                                <span class="text-rose-500">*</span>
                            </label>
                            <AppInput
                                v-model="form.subject"
                                placeholder="Contoh: Permohonan Pengujian Kertas Sekuriti SP-45"
                                required
                            />
                            <InputError :message="form.errors.subject" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Unit Asal / Pembuat
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
                                    Tanggal Surat
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
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Lampiran
                                </label>
                                <AppInput
                                    v-model="lampiran"
                                    placeholder="Contoh: 1 (satu) Berkas / -"
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
                            2. Pihak Tujuan Surat
                        </h2>

                        <div>
                            <label
                                class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                            >
                                Kategori Tujuan
                                <span class="text-rose-500">*</span>
                            </label>
                            <AppSelect
                                v-model="form.destination_type"
                                :options="[
                                    {
                                        value: 'external_text',
                                        label: 'Pihak Eksternal (Instansi / Perusahaan Luar)',
                                    },
                                    {
                                        value: 'internal_units',
                                        label: 'Unit Internal PTKP',
                                    },
                                    {
                                        value: 'all_units',
                                        label: 'Seluruh Unit / Departemen',
                                    },
                                ]"
                            />
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Nama Penerima / Instansi Tujuan
                                    <span class="text-rose-500">*</span>
                                </label>
                                <AppInput
                                    v-model="form.destination_text"
                                    placeholder="Contoh: Direktur Industri Hasil Hutan, Kementerian Perindustrian RI"
                                    required
                                />
                                <InputError
                                    :message="form.errors.destination_text"
                                />
                            </div>

                            <div>
                                <label
                                    class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                                >
                                    Alamat Lengkap Tujuan
                                </label>
                                <AppInput
                                    v-model="destinationAddress"
                                    placeholder="Contoh: Jl. Gatot Subroto Kav. 52-53, Jakarta Selatan"
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
                            3. Isi Surat Keluar
                        </h2>

                        <div>
                            <AppRichText
                                v-model="bodyHtml"
                                label="Isi Surat (Editor Digital)"
                                placeholder="Tuliskan isi surat resmi di sini..."
                                required
                            />
                            <InputError
                                :message="form.errors['content_body.body_html']"
                            />
                        </div>

                        <div>
                            <label
                                class="text-brand-slate-dark mb-1 block font-body text-xs font-semibold"
                            >
                                Tembusan (Satu baris per pihak)
                            </label>
                            <AppTextarea
                                v-model="tembusanRaw"
                                placeholder="1. Kepala Departemen SDM&#10;2. Arsip"
                                :rows="2"
                            />
                        </div>
                    </div>

                    <!-- Section Upload Berkas: HIDDEN via v-show="false" per permintaan user -->
                    <div
                        v-show="false"
                        class="border-brand-slate-border space-y-4 border-t pt-4"
                    >
                        <h2
                            class="text-brand-navy-dark border-brand-slate-border border-b pb-2 font-heading text-base font-semibold"
                        >
                            4. Berkas Scan / Lampiran Manual (Disembunyikan)
                        </h2>

                        <div>
                            <input
                                type="file"
                                class="text-brand-slate-dark block w-full text-xs"
                                accept=".pdf,.docx,.doc"
                                @change="handleFileChange"
                            />
                        </div>
                    </div>

                    <div class="border-brand-slate-border border-t pt-4">
                        <div
                            class="bg-brand-slate-light/5 border-brand-slate-border rounded-lg border p-4"
                        >
                            <label
                                class="flex cursor-pointer items-center gap-2"
                            >
                                <input
                                    v-model="form.submit_now"
                                    type="checkbox"
                                    class="border-brand-slate-border text-brand-navy-dark focus:ring-brand-blue rounded"
                                />
                                <span
                                    class="text-brand-slate-dark font-body text-xs font-medium"
                                >
                                    Langsung ajukan draf ke alur persetujuan
                                    pejabat setelah disimpan
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Action -->
                    <div
                        class="border-brand-slate-border flex items-center justify-end gap-3 border-t pt-4"
                    >
                        <Link :href="route('arsip.surat-keluar.index')">
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
                                    ? 'Menyimpan & Generate PDF...'
                                    : 'Simpan Draf Surat Keluar'
                            }}
                        </AppButton>
                    </div>
                </AppCard>
            </form>
        </div>
    </AppShell>
</template>
