<script setup lang="ts">
import AppBadge from '@/Components/AppBadge.vue';
import AppButton from '@/Components/AppButton.vue';
import AppCard from '@/Components/AppCard.vue';
import AppInput from '@/Components/AppInput.vue';
import AppModal from '@/Components/AppModal.vue';
import AppSelect from '@/Components/AppSelect.vue';
import AppTable from '@/Components/AppTable.vue';
import AppShell from '@/Layouts/AppShell.vue';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface OrgUnitItem {
    id: string;
    parent_id: string | null;
    code: string;
    name: string;
    type: string;
    level: number;
    is_active: boolean;
    parent?: { id: string; name: string; code: string } | null;
}

const props = defineProps<{
    orgUnits: OrgUnitItem[];
    parentOptions: Array<{
        id: string;
        code: string;
        name: string;
        level: number;
    }>;
    filters: { search?: string; type?: string };
}>();

const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || '');

const handleFilter = () => {
    router.get(
        route('platform.org-units.index'),
        { search: search.value, type: typeFilter.value },
        { preserveState: true, replace: true },
    );
};

// Modal State
const isModalOpen = ref(false);
const editingUnit = ref<OrgUnitItem | null>(null);

const form = useForm({
    name: '',
    code: '',
    parent_id: '',
    type: 'seksi',
    level: 4,
    is_active: true,
});

const openCreateModal = () => {
    editingUnit.value = null;
    form.reset();
    form.clearErrors();
    form.type = 'seksi';
    form.level = 4;
    form.is_active = true;
    isModalOpen.value = true;
};

const openEditModal = (unit: OrgUnitItem) => {
    editingUnit.value = unit;
    form.clearErrors();
    form.name = unit.name;
    form.code = unit.code;
    form.parent_id = unit.parent_id || '';
    form.type = unit.type;
    form.level = unit.level;
    form.is_active = unit.is_active;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    editingUnit.value = null;
};

const saveOrgUnit = () => {
    if (editingUnit.value) {
        form.put(route('platform.org-units.update', editingUnit.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('platform.org-units.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deactivateUnit = (unit: OrgUnitItem) => {
    if (confirm(`Yakin ingin menonaktifkan unit ${unit.name}?`)) {
        router.delete(route('platform.org-units.destroy', unit.id));
    }
};

const typeOptions = [
    { value: 'direksi', label: 'Direksi (Level 1)' },
    { value: 'divisi', label: 'Divisi (Level 2)' },
    { value: 'departemen', label: 'Departemen (Level 3)' },
    { value: 'seksi', label: 'Seksi (Level 4)' },
    { value: 'lainnya', label: 'Lainnya / Unit Khusus' },
];

const parentSelectOptions = computed(() => {
    return props.parentOptions.map((p) => ({
        value: p.id,
        label: `[Lvl ${p.level}] ${p.name} (${p.code})`,
    }));
});

const columns = [
    { key: 'code', label: 'Kode Unit', width: '130px' },
    { key: 'name', label: 'Nama Unit Organisasi' },
    { key: 'level', label: 'Tingkat', width: '120px' },
    { key: 'parent', label: 'Unit Induk (Parent)' },
    { key: 'status', label: 'Status', width: '100px' },
    { key: 'actions', label: 'Aksi', align: 'right' as const, width: '140px' },
];
</script>

<template>
    <AppShell
        title="Unit Organisasi"
        :breadcrumbs="[
            { label: 'Platform', href: '/dashboard' },
            { label: 'Unit Organisasi' },
        ]"
    >
        <AppCard
            title="Bagan Struktur Organisasi PTKP"
            subtitle="Pengelolaan hierarki 37 unit organisasi resmi berdasarkan SK 009/KPTS/PTKP/IV/2026"
        >
            <template #headerActions>
                <AppButton variant="primary" size="md" @click="openCreateModal">
                    <svg
                        class="mr-1 h-4 w-4"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"
                        />
                    </svg>
                    Tambah Unit
                </AppButton>
            </template>

            <!-- Search & Filters -->
            <div
                class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
            >
                <div
                    class="grid max-w-xl flex-1 grid-cols-1 gap-3 sm:grid-cols-2"
                >
                    <AppInput
                        placeholder="Cari kode atau nama unit..."
                        v-model="search"
                        @keyup.enter="handleFilter"
                    />
                    <AppSelect
                        :options="typeOptions"
                        placeholder="-- Semua Tipe --"
                        v-model="typeFilter"
                        @change="handleFilter"
                    />
                </div>
                <AppButton
                    variant="secondary"
                    size="md"
                    class="shrink-0"
                    @click="handleFilter"
                >
                    Filter
                </AppButton>
            </div>

            <!-- Table View -->
            <div class="mt-2">
                <AppTable :columns="columns" :items="orgUnits" dark-header>
                    <template #row="{ item }">
                        <td class="px-4 py-3 align-middle">
                            <span
                                class="rounded-md bg-slate-100 px-2 py-1 font-mono text-xs font-bold text-ptkp-navy"
                            >
                                {{ item.code }}
                            </span>
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <div
                                class="flex items-center gap-2"
                                :style="{
                                    paddingLeft: `${(item.level - 1) * 16}px`,
                                }"
                            >
                                <span
                                    v-if="item.level > 1"
                                    class="font-mono text-slate-400"
                                    aria-hidden="true"
                                    >└─</span
                                >
                                <span class="font-semibold text-ptkp-body">{{
                                    item.name
                                }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <AppBadge
                                v-if="item.level === 1"
                                variant="navy"
                                size="sm"
                                >Level 1 (Direksi)</AppBadge
                            >
                            <AppBadge
                                v-else-if="item.level === 2"
                                variant="navy"
                                size="sm"
                                >Level 2 (Divisi)</AppBadge
                            >
                            <AppBadge
                                v-else-if="item.level === 3"
                                variant="neutral"
                                size="sm"
                                >Level 3 (Dept)</AppBadge
                            >
                            <AppBadge v-else variant="neutral" size="sm"
                                >Level 4 (Seksi)</AppBadge
                            >
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <span
                                v-if="item.parent"
                                class="text-xs text-slate-500"
                            >
                                {{ item.parent.name }} ({{ item.parent.code }})
                            </span>
                            <span v-else class="text-xs italic text-slate-400"
                                >- Puncak Struktur -</span
                            >
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <AppBadge
                                v-if="item.is_active"
                                variant="success"
                                size="sm"
                                >Aktif</AppBadge
                            >
                            <AppBadge v-else variant="error" size="sm"
                                >Nonaktif</AppBadge
                            >
                        </td>
                        <td class="px-4 py-3 text-right align-middle">
                            <div class="inline-flex gap-2">
                                <AppButton
                                    variant="text"
                                    size="sm"
                                    @click="openEditModal(item)"
                                >
                                    Edit
                                </AppButton>
                                <AppButton
                                    v-if="item.is_active"
                                    variant="text"
                                    size="sm"
                                    class="text-ptkp-error hover:text-red-800"
                                    @click="deactivateUnit(item)"
                                >
                                    Nonaktifkan
                                </AppButton>
                            </div>
                        </td>
                    </template>
                </AppTable>
            </div>
        </AppCard>

        <!-- Create / Edit Modal -->
        <AppModal
            :show="isModalOpen"
            :title="
                editingUnit
                    ? 'Edit Unit Organisasi'
                    : 'Tambah Unit Organisasi Baru'
            "
            max-width="md"
            @close="closeModal"
        >
            <form @submit.prevent="saveOrgUnit" class="flex flex-col gap-3">
                <AppInput
                    label="Kode Unit"
                    placeholder="Contoh: DIR, DIV-PROD, SEC-PK1"
                    v-model="form.code"
                    :error="form.errors.code"
                    required
                />

                <AppInput
                    label="Nama Unit Organisasi"
                    placeholder="Contoh: Seksi Pembuatan Kertas I"
                    v-model="form.name"
                    :error="form.errors.name"
                    required
                />

                <AppSelect
                    label="Tipe Unit"
                    :options="typeOptions"
                    v-model="form.type"
                    :error="form.errors.type"
                    required
                />

                <AppSelect
                    label="Unit Induk (Parent)"
                    placeholder="-- Tidak ada (Puncak Struktur) --"
                    :options="parentSelectOptions"
                    v-model="form.parent_id"
                    :error="form.errors.parent_id"
                />

                <AppSelect
                    label="Tingkat Hirarki (Level)"
                    :options="[
                        { value: 1, label: 'Level 1 — Direksi' },
                        { value: 2, label: 'Level 2 — Divisi' },
                        { value: 3, label: 'Level 3 — Departemen' },
                        { value: 4, label: 'Level 4 — Seksi' },
                    ]"
                    v-model="form.level"
                    :error="form.errors.level"
                    required
                />

                <div class="mt-1">
                    <label
                        class="flex cursor-pointer items-center gap-2 text-sm text-ptkp-body"
                    >
                        <input
                            type="checkbox"
                            v-model="form.is_active"
                            class="rounded-md border-slate-300 text-ptkp-navy focus:ring-ptkp-navy"
                        />
                        <span>Unit aktif dan dapat menerima penugasan</span>
                    </label>
                </div>
            </form>

            <template #footer>
                <AppButton variant="secondary" size="md" @click="closeModal">
                    Batal
                </AppButton>
                <AppButton
                    variant="primary"
                    size="md"
                    :loading="form.processing"
                    @click="saveOrgUnit"
                >
                    {{ editingUnit ? 'Perbarui Unit' : 'Simpan Unit' }}
                </AppButton>
            </template>
        </AppModal>
    </AppShell>
</template>
