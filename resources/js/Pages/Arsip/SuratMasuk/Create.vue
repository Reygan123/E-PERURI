<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import AppInput from '@/Components/AppInput.vue';
import AppSelect from '@/Components/AppSelect.vue';
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
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="font-heading text-2xl font-bold text-brand-navy-dark tracking-tight">
            Pencatatan Surat Masuk
          </h1>
          <p class="font-body text-sm text-brand-slate-light mt-1">
            Digitalisasi surat fisik dari pihak luar, pengunggahan scan PDF, dan persiapan lembar disposisi.
          </p>
        </div>

        <Link :href="route('arsip.surat-masuk.index')">
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
              1. Identitas Surat Pengirim
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                  Nomor Surat Pihak Luar <span class="text-rose-500">*</span>
                </label>
                <AppInput
                  v-model="form.document_number"
                  placeholder="Contoh: 120/DIR-IND/IV/2026"
                  required
                />
                <InputError :message="form.errors.document_number" />
              </div>

              <div>
                <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                  Instansi / Perusahaan Pengirim <span class="text-rose-500">*</span>
                </label>
                <AppInput
                  v-model="form.sender_organization"
                  placeholder="Contoh: PT Peruri Nusantara / Kementerian Keuangan"
                  required
                />
                <InputError :message="form.errors.sender_organization" />
              </div>
            </div>

            <div>
              <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                Perihal Surat <span class="text-rose-500">*</span>
              </label>
              <AppInput
                v-model="form.subject"
                placeholder="Contoh: Undangan Koordinasi Standardisasi Kertas Berharga Nasional"
                required
              />
              <InputError :message="form.errors.subject" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                  Tanggal Surat Pengirim <span class="text-rose-500">*</span>
                </label>
                <AppInput
                  v-model="form.letter_date"
                  type="date"
                  required
                />
                <InputError :message="form.errors.letter_date" />
              </div>

              <div>
                <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                  Tanggal Diterima di PTKP <span class="text-rose-500">*</span>
                </label>
                <AppInput
                  v-model="form.received_date"
                  type="date"
                  required
                />
                <InputError :message="form.errors.received_date" />
              </div>
            </div>
          </div>

          <div class="space-y-4 pt-4 border-t border-brand-slate-border">
            <h2 class="font-heading text-base font-semibold text-brand-navy-dark pb-2 border-b border-brand-slate-border">
              2. Unit Penerima & Sifat Dokumen
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                  Unit Penerima di PTKP <span class="text-rose-500">*</span>
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
              3. Berkas Scan Surat Masuk (PDF)
            </h2>

            <div>
              <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                Unggah Scan Surat Fisik <span class="text-rose-500">*</span>
              </label>
              <input
                type="file"
                class="block w-full text-xs text-brand-slate-dark file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-navy-dark file:text-white hover:file:bg-brand-blue border border-brand-slate-border rounded-lg p-2"
                accept=".pdf"
                @change="handleFileChange"
                required
              />
              <p class="font-body text-[11px] text-brand-slate-light mt-1">
                Format PDF, maksimal 15MB. Pastikan berkas scan jelas dan terbaca untuk proses disposisi.
              </p>
              <InputError :message="form.errors.scanned_file" />
            </div>
          </div>

          <!-- Submit Buttons -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-brand-slate-border">
            <Link :href="route('arsip.surat-masuk.index')">
              <AppButton variant="secondary" type="button">
                Batal
              </AppButton>
            </Link>
            <AppButton variant="primary" type="submit" :disabled="form.processing">
              {{ form.processing ? 'Menyimpan...' : 'Simpan & Catat Surat Masuk' }}
            </AppButton>
          </div>
        </AppCard>
      </form>
    </div>
  </AppShell>
</template>
