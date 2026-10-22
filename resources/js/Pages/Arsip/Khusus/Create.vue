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
  types: Array<{ id: string; name: string; code: string }>;
  orgUnits: OrgUnitOption[];
  selectedTypeCode?: string;
}>();

const selectedType = ref(props.selectedTypeCode || 'SPPD');

// Dynamic payload fields
const sppdData = ref({
  destination_city: '',
  departure_date: new Date().toISOString().split('T')[0],
  return_date: new Date().toISOString().split('T')[0],
  transport_mode: 'Kendaraan Dinas',
  purpose: '',
  estimated_cost: 0,
});

const spbItems = ref<Array<{ item_name: string; qty: number; unit: string; purpose: string }>>([
  { item_name: '', qty: 1, unit: 'Pcs', purpose: '' },
]);

const bpData = ref({
  patient_name: '',
  clinic_name: '',
  treatment_date: new Date().toISOString().split('T')[0],
  total_cost: 0,
});

const form = useForm({
  type_code: selectedType.value,
  subject: '',
  origin_unit_id: props.orgUnits[0]?.id || '',
  letter_date: new Date().toISOString().split('T')[0],
  content_body: {} as Record<string, any>,
});

const addSpbItem = () => {
  spbItems.value.push({ item_name: '', qty: 1, unit: 'Pcs', purpose: '' });
};

const removeSpbItem = (index: number) => {
  if (spbItems.value.length > 1) {
    spbItems.value.splice(index, 1);
  }
};

const submit = () => {
  form.type_code = selectedType.value;

  if (selectedType.value === 'SPPD') {
    form.subject = `Surat Perintah Perjalanan Dinas ke ${sppdData.value.destination_city}: ${sppdData.value.purpose}`;
    form.content_body = sppdData.value;
  } else if (selectedType.value === 'SPB') {
    form.subject = `Permintaan Barang Gudang (${spbItems.value.length} Item)`;
    form.content_body = { items: spbItems.value };
  } else if (selectedType.value === 'BP') {
    form.subject = `Klaim Biaya Pengobatan: ${bpData.value.patient_name} (${bpData.value.clinic_name})`;
    form.content_body = bpData.value;
  }

  form.post(route('arsip.khusus.store'));
};
</script>

<template>
  <AppShell title="Buat Formulir Khusus - E-Arsip PTKP">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="font-heading text-2xl font-bold text-brand-navy-dark tracking-tight">
            Buat Formulir Khusus
          </h1>
          <p class="font-body text-sm text-brand-slate-light mt-1">
            Pengisian formulir terstruktur operasional PTKP (SPPD, Permintaan Gudang SPB, Biaya Pengobatan BP).
          </p>
        </div>

        <Link :href="route('arsip.khusus.index')">
          <AppButton variant="secondary">
            &larr; Kembali
          </AppButton>
        </Link>
      </div>

      <!-- Type Selector Tabs -->
      <div class="flex items-center gap-2 border-b border-brand-slate-border pb-3">
        <button
          v-for="t in types"
          :key="t.code"
          type="button"
          class="px-4 py-2 rounded-lg font-heading text-xs font-bold transition-all"
          :class="selectedType === t.code ? 'bg-brand-navy-dark text-white shadow-sm' : 'bg-white border border-brand-slate-border text-brand-slate-dark hover:bg-brand-slate-light/10'"
          @click="selectedType = t.code"
        >
          {{ t.code }} - {{ t.name }}
        </button>
      </div>

      <form @submit.prevent="submit">
        <AppCard class="p-6 space-y-6">
          <!-- Common Metadata -->
          <div class="space-y-4">
            <h2 class="font-heading text-base font-semibold text-brand-navy-dark pb-2 border-b border-brand-slate-border">
              1. Unit Asal & Tanggal
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                  Unit Asal / Departemen Pemohon <span class="text-rose-500">*</span>
                </label>
                <AppSelect
                  v-model="form.origin_unit_id"
                  :options="orgUnits.map(u => ({ value: u.id, label: `${u.code} - ${u.name}` }))"
                  required
                />
              </div>

              <div>
                <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                  Tanggal Pengajuan <span class="text-rose-500">*</span>
                </label>
                <AppInput
                  v-model="form.letter_date"
                  type="date"
                  required
                />
              </div>
            </div>
          </div>

          <!-- Section: SPPD Form -->
          <div v-if="selectedType === 'SPPD'" class="space-y-4 pt-4 border-t border-brand-slate-border">
            <div class="flex items-center justify-between pb-2 border-b border-brand-slate-border">
              <h2 class="font-heading text-base font-semibold text-brand-navy-dark">
                2. Rincian Surat Perintah Dinas (SPPD)
              </h2>
              <span class="text-xs text-brand-blue font-semibold">Reset Nomor: Bulanan</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                  Kota / Lokasi Tujuan Dinas <span class="text-rose-500">*</span>
                </label>
                <AppInput
                  v-model="sppdData.destination_city"
                  placeholder="Contoh: Jakarta / Karawang / Surabaya"
                  required
                />
              </div>

              <div>
                <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                  Moda Transportasi <span class="text-rose-500">*</span>
                </label>
                <AppSelect
                  v-model="sppdData.transport_mode"
                  :options="[
                    { value: 'Kendaraan Dinas', label: 'Kendaraan Operasional Dinas' },
                    { value: 'Kereta Api', label: 'Kereta Api (KAI)' },
                    { value: 'Pesawat Udara', label: 'Pesawat Udara' },
                    { value: 'Kendaraan Pribadi', label: 'Kendaraan Pribadi' },
                  ]"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                  Tanggal Berangkat <span class="text-rose-500">*</span>
                </label>
                <AppInput
                  v-model="sppdData.departure_date"
                  type="date"
                  required
                />
              </div>

              <div>
                <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                  Tanggal Kembali <span class="text-rose-500">*</span>
                </label>
                <AppInput
                  v-model="sppdData.return_date"
                  type="date"
                  required
                />
              </div>
            </div>

            <div>
              <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                Maksud & Tujuan Perjalanan Dinas <span class="text-rose-500">*</span>
              </label>
              <AppTextarea
                v-model="sppdData.purpose"
                placeholder="Jelaskan agenda kerja, rapat, atau kunjungan teknis..."
                rows="3"
                required
              />
            </div>
          </div>

          <!-- Section: SPB Form -->
          <div v-else-if="selectedType === 'SPB'" class="space-y-4 pt-4 border-t border-brand-slate-border">
            <div class="flex items-center justify-between pb-2 border-b border-brand-slate-border">
              <h2 class="font-heading text-base font-semibold text-brand-navy-dark">
                2. Daftar Permintaan Barang Gudang (SPB)
              </h2>
              <button
                type="button"
                class="text-xs font-bold text-brand-blue hover:underline"
                @click="addSpbItem"
              >
                + Tambah Baris Barang
              </button>
            </div>

            <div class="space-y-3">
              <div
                v-for="(item, idx) in spbItems"
                :key="idx"
                class="grid grid-cols-12 gap-3 items-center p-3 rounded-lg border border-brand-slate-border bg-brand-slate-light/5"
              >
                <div class="col-span-5">
                  <label class="block text-[10px] font-semibold text-brand-slate-light mb-0.5">Nama Barang / Material</label>
                  <AppInput v-model="item.item_name" placeholder="Nama barang" required />
                </div>
                <div class="col-span-2">
                  <label class="block text-[10px] font-semibold text-brand-slate-light mb-0.5">Jumlah</label>
                  <AppInput v-model="item.qty" type="number" min="1" required />
                </div>
                <div class="col-span-2">
                  <label class="block text-[10px] font-semibold text-brand-slate-light mb-0.5">Satuan</label>
                  <AppInput v-model="item.unit" placeholder="Pcs/Rim/Kg" required />
                </div>
                <div class="col-span-2">
                  <label class="block text-[10px] font-semibold text-brand-slate-light mb-0.5">Keperluan</label>
                  <AppInput v-model="item.purpose" placeholder="Alasan butuh" />
                </div>
                <div class="col-span-1 text-center pt-3">
                  <button
                    v-if="spbItems.length > 1"
                    type="button"
                    class="text-rose-500 hover:text-rose-700 font-bold"
                    @click="removeSpbItem(idx)"
                  >
                    &times;
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Section: BP Form -->
          <div v-else-if="selectedType === 'BP'" class="space-y-4 pt-4 border-t border-brand-slate-border">
            <h2 class="font-heading text-base font-semibold text-brand-navy-dark pb-2 border-b border-brand-slate-border">
              2. Data Pasien & Klaim Biaya Pengobatan (BP)
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                  Nama Pasien / Pegawai <span class="text-rose-500">*</span>
                </label>
                <AppInput
                  v-model="bpData.patient_name"
                  placeholder="Nama pegawai yang berobat"
                  required
                />
              </div>

              <div>
                <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                  Nama Klinik / Rumah Sakit <span class="text-rose-500">*</span>
                </label>
                <AppInput
                  v-model="bpData.clinic_name"
                  placeholder="Klinik Pratama PTKP / RSUD"
                  required
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                  Tanggal Berobat <span class="text-rose-500">*</span>
                </label>
                <AppInput
                  v-model="bpData.treatment_date"
                  type="date"
                  required
                />
              </div>

              <div>
                <label class="block font-body text-xs font-semibold text-brand-slate-dark mb-1">
                  Total Biaya Pengobatan (Rp) <span class="text-rose-500">*</span>
                </label>
                <AppInput
                  v-model="bpData.total_cost"
                  type="number"
                  min="0"
                  required
                />
              </div>
            </div>
          </div>

          <!-- Submit Buttons -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-brand-slate-border">
            <Link :href="route('arsip.khusus.index')">
              <AppButton variant="secondary" type="button">
                Batal
              </AppButton>
            </Link>
            <AppButton variant="primary" type="submit" :disabled="form.processing">
              {{ form.processing ? 'Menyimpan...' : 'Simpan & Ajukan Formulir' }}
            </AppButton>
          </div>
        </AppCard>
      </form>
    </div>
  </AppShell>
</template>
