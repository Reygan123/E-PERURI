<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import AppInput from '@/Components/AppInput.vue';
import AppSelect from '@/Components/AppSelect.vue';
import AppTextarea from '@/Components/AppTextarea.vue';
import InputError from '@/Components/InputError.vue';

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
  subject: '',
  origin_unit_id: props.orgUnits[0]?.id || '',
  destination_type: 'external_text',
  destination_text: '',
  destination_unit_ids: [] as string[],
  letter_date: new Date().toISOString().split('T')[0],
  confidentiality: 'biasa',
  urgency: 'biasa',
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
  form.post(route('arsip.surat-keluar.store'), {
    forceFormData: true,
  });
};
</script>

<template>
  <AppShell title="Buat Surat Keluar - E-Arsip PTKP">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="font-heading text-2xl font-bold text-brand-navy-dark tracking-tight">
            Buat Draf Surat Keluar
          </h1>
          <p class="font-body text-sm text-brand-slate-light mt-1">
            Surat Keluar bertanda tangan manual dengan format baku {SEQ}/{UNIT}/PTKP/{BULAN}/{TAHUN}.
          </p>
        </div>

        <Link :href="route('arsip.surat-keluar.index')">
          <AppButton variant="secondary">
            &larr; Kembali
          </AppButton>
        </Link>
      </div>

      <!-- Form Card -->
      <form @submit.prevent="submit">
        <AppCard class="p-6 space-y-6">
          <div class="space-y-4">
            <h2 class="font-heading text-base font-semibold text-brand-navy-dark pb-2 border-b border-brand-slate-border">
              1. Informasi Pokok Surat
            </h2>

            <div>
              <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                Perihal Surat <span class="text-rose-500">*</span>
              </label>
              <AppInput
                v-model="form.subject"
                placeholder="Contoh: Permohonan Pengujian Kertas Sekuriti SP-45"
                required
              />
              <InputError :message="form.errors.subject" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                  Unit Asal / Pembuat <span class="text-rose-500">*</span>
                </label>
                <AppSelect
                  v-model="form.origin_unit_id"
                  :options="orgUnits.map(u => ({ value: u.id, label: `${u.code} - ${u.name}` }))"
                  required
                />
                <InputError :message="form.errors.origin_unit_id" />
              </div>

              <div>
                <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                  Tanggal Surat <span class="text-rose-500">*</span>
                </label>
                <AppInput
                  v-model="form.letter_date"
                  type="date"
                  required
                />
                <InputError :message="form.errors.letter_date" />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                  Sifat Keamanan <span class="text-rose-500">*</span>
                </label>
                <AppSelect
                  v-model="form.confidentiality"
                  :options="[
                    { value: 'biasa', label: 'Biasa' },
                    { value: 'terbatas', label: 'Terbatas' },
                    { value: 'rahasia', label: 'Rahasia' },
                    { value: 'sangat_rahasia', label: 'Sangat Rahasia' },
                  ]"
                />
              </div>

              <div>
                <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                  Tingkat Urgensi <span class="text-rose-500">*</span>
                </label>
                <AppSelect
                  v-model="form.urgency"
                  :options="[
                    { value: 'biasa', label: 'Biasa' },
                    { value: 'segera', label: 'Segera' },
                    { value: 'sangat_segera', label: 'Sangat Segera' },
                  ]"
                />
              </div>
            </div>
          </div>

          <div class="space-y-4 pt-4 border-t border-brand-slate-border">
            <h2 class="font-heading text-base font-semibold text-brand-navy-dark pb-2 border-b border-brand-slate-border">
              2. Pihak Tujuan Surat
            </h2>

            <div>
              <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                Kategori Tujuan <span class="text-rose-500">*</span>
              </label>
              <AppSelect
                v-model="form.destination_type"
                :options="[
                  { value: 'external_text', label: 'Pihak Eksternal (Instansi / Perusahaan Luar)' },
                  { value: 'internal_units', label: 'Unit Internal PTKP' },
                  { value: 'all_units', label: 'Seluruh Unit / Departemen' },
                ]"
              />
            </div>

            <div v-if="form.destination_type === 'external_text'">
              <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                Nama Instansi / Alamat Tujuan Eksternal <span class="text-rose-500">*</span>
              </label>
              <AppInput
                v-model="form.destination_text"
                placeholder="Contoh: Direktur Industri Hasil Hutan, Kementerian Perindustrian RI"
                required
              />
              <InputError :message="form.errors.destination_text" />
            </div>
          </div>

          <div class="space-y-4 pt-4 border-t border-brand-slate-border">
            <h2 class="font-heading text-base font-semibold text-brand-navy-dark pb-2 border-b border-brand-slate-border">
              3. Berkas Draf Surat
            </h2>

            <div>
              <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                Unggah Draf (PDF/DOCX)
              </label>
              <input
                type="file"
                class="block w-full text-xs text-brand-slate-dark file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-navy-dark file:text-white hover:file:bg-brand-blue border border-brand-slate-border rounded-lg p-2"
                accept=".pdf,.docx,.doc"
                @change="handleFileChange"
              />
              <p class="font-body text-[11px] text-brand-slate-light mt-1">
                Maksimal 10MB. Setelah draf disetujui para pejabat dan dicetak bertanda tangan basah, Anda wajib mengunggah hasil scan basah final.
              </p>
              <InputError :message="form.errors.draft_file" />
            </div>

            <div class="p-4 bg-brand-slate-light/5 rounded-lg border border-brand-slate-border">
              <label class="flex items-center gap-2 cursor-pointer">
                <input
                  v-model="form.submit_now"
                  type="checkbox"
                  class="rounded border-brand-slate-border text-brand-navy-dark focus:ring-brand-blue"
                />
                <span class="font-body text-xs font-medium text-brand-slate-dark">
                  Langsung ajukan draf ke alur persetujuan pejabat setelah disimpan
                </span>
              </label>
            </div>
          </div>

          <!-- Submit Action -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-brand-slate-border">
            <Link :href="route('arsip.surat-keluar.index')">
              <AppButton variant="secondary" type="button">
                Batal
              </AppButton>
            </Link>
            <AppButton variant="primary" type="submit" :disabled="form.processing">
              {{ form.processing ? 'Menyimpan...' : 'Simpan Draf Surat Keluar' }}
            </AppButton>
          </div>
        </AppCard>
      </form>
    </div>
  </AppShell>
</template>
