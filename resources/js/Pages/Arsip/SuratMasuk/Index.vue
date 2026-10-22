<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import AppBadge from '@/Components/AppBadge.vue';
import AppInput from '@/Components/AppInput.vue';
import AppEmptyState from '@/Components/AppEmptyState.vue';

interface LetterItem {
  id: string;
  document_number: string;
  subject: string;
  letter_date: string;
  destination_text: string | null;
  confidentiality: string;
  urgency: string;
  dispositions_count?: number;
  dispositions?: Array<{ id: string; instruction: string; status: string }>;
  created_at: string;
}

const props = defineProps<{
  letters: {
    data: LetterItem[];
    current_page: number;
    last_page: number;
    total: number;
  };
  filters: { search?: string };
}>();

const search = ref(props.filters.search || '');

const handleFilter = () => {
  router.get(
    route('arsip.surat-masuk.index'),
    { search: search.value },
    { preserveState: true, replace: true }
  );
};
</script>

<template>
  <AppShell title="Surat Masuk (B) - E-Arsip PTKP">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="font-heading text-2xl font-bold text-brand-navy-dark tracking-tight">
            Surat Masuk (Kode B)
          </h1>
          <p class="font-body text-sm text-brand-slate-light mt-1">
            Pencatatan dan digitalisasi surat fisik dari pihak luar serta penerbitan lembar disposisi pejabat.
          </p>
        </div>

        <Link :href="route('arsip.surat-masuk.create')">
          <AppButton variant="primary">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Catat Surat Masuk
          </AppButton>
        </Link>
      </div>

      <!-- Filter Controls -->
      <AppCard class="p-4">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1">
            <AppInput
              v-model="search"
              placeholder="Cari perihal, nomor surat pengirim, atau instansi pengirim..."
              @keyup.enter="handleFilter"
            />
          </div>
          <div class="flex items-center gap-2">
            <AppButton variant="secondary" @click="handleFilter">
              Cari Surat
            </AppButton>
            <AppButton v-if="search" variant="secondary" @click="search = ''; handleFilter();">
              Reset
            </AppButton>
          </div>
        </div>
      </AppCard>

      <!-- Table Card -->
      <AppCard class="overflow-hidden">
        <div v-if="letters.data.length > 0" class="overflow-x-auto">
          <table class="w-full text-left font-body text-sm border-collapse">
            <thead>
              <tr class="border-b border-brand-slate-border bg-brand-slate-light/5 text-brand-slate-dark text-xs uppercase tracking-wider">
                <th class="py-3.5 px-4 font-semibold">Nomor Surat Fisik</th>
                <th class="py-3.5 px-4 font-semibold">Instansi Pengirim</th>
                <th class="py-3.5 px-4 font-semibold">Perihal</th>
                <th class="py-3.5 px-4 font-semibold">Tanggal Surat</th>
                <th class="py-3.5 px-4 font-semibold">Disposisi</th>
                <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-brand-slate-border/50">
              <tr
                v-for="letter in letters.data"
                :key="letter.id"
                class="hover:bg-brand-slate-light/5 transition-colors"
              >
                <td class="py-3.5 px-4 font-semibold text-brand-navy-dark">
                  {{ letter.document_number }}
                </td>
                <td class="py-3.5 px-4 font-medium text-brand-slate-dark max-w-xs truncate">
                  {{ letter.destination_text || '-' }}
                </td>
                <td class="py-3.5 px-4 text-brand-slate-dark max-w-sm truncate" :title="letter.subject">
                  {{ letter.subject }}
                </td>
                <td class="py-3.5 px-4 text-brand-slate-light whitespace-nowrap">
                  {{ new Date(letter.letter_date).toLocaleDateString('id-ID') }}
                </td>
                <td class="py-3.5 px-4">
                  <span v-if="letter.dispositions && letter.dispositions.length > 0" class="inline-flex items-center text-xs font-semibold text-brand-blue">
                    {{ letter.dispositions.length }} Lembar
                  </span>
                  <span v-else class="text-xs text-brand-slate-light italic">
                    Belum Ada
                  </span>
                </td>
                <td class="py-3.5 px-4 text-right">
                  <Link :href="route('arsip.surat-masuk.show', letter.id)">
                    <AppButton variant="secondary" size="sm">
                      Detail & Disposisi
                    </AppButton>
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="p-8">
          <AppEmptyState
            title="Belum Ada Surat Masuk"
            description="Tidak ada dokumen surat masuk yang tercatat pada sistem."
          />
        </div>
      </AppCard>
    </div>
  </AppShell>
</template>
