<script setup lang="ts">
import AppBadge from '@/Components/AppBadge.vue';
import AppButton from '@/Components/AppButton.vue';
import AppCard from '@/Components/AppCard.vue';
import AppInput from '@/Components/AppInput.vue';
import AppModal from '@/Components/AppModal.vue';
import AppPagination from '@/Components/AppPagination.vue';
import AppSelect from '@/Components/AppSelect.vue';
import AppTable from '@/Components/AppTable.vue';
import AppShell from '@/Layouts/AppShell.vue';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface OrgUnitRef {
    id: string;
    code: string;
    name: string;
}

interface PositionItem {
    id: string;
    org_unit_id: string;
    code: string;
    name: string;
    type: string;
    level: number | null;
    is_active: boolean;
    org_unit?: OrgUnitRef | null;
}

interface PaginatedPositions {
    data: PositionItem[];
    links: any[];
    from?: number;
    to?: number;
    total?: number;
}

const props = defineProps<{
    positions: PaginatedPositions;
    orgUnits: OrgUnitRef[];
    filters: { search?: string; org_unit_id?: string; type?: string };
}>();

const search = ref(props.filters.search || '');
const orgUnitFilter = ref(props.filters.org_unit_id || '');
const typeFilter = ref(props.filters.type || '');

const handleFilter = () => {
    router.get(
        route('platform.positions.index'),
        {
            search: search.value,
            org_unit_id: orgUnitFilter.value,
            type: typeFilter.value,
        },
        { preserveState: true, replace: true },
    );
};

// Modal State
const isModalOpen = ref(false);
const editingPosition = ref<PositionItem | null>(null);

const form = useForm({
    name: '',
    code: '',
    org_unit_id: '',
    type: 'structural',
    level: 1,
    is_active: true,
});

const openCreateModal = () => {
    editingPosition.value = null;
    form.reset();
    form.clearErrors();
    form.type = 'structural';
    form.level = 1;
    form.is_active = true;
    isModalOpen.value = true;
};

const openEditModal = (pos: PositionItem) => {
    editingPosition.value = pos;
    form.clearErrors();
    form.name = pos.name;
    form.code = pos.code;
    form.org_unit_id = pos.org_unit_id;
    form.type = pos.type;
    form.level = pos.level || 1;
    form.is_active = pos.is_active;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    editingPosition.value = null;
};

const savePosition = () => {
    if (editingPosition.value) {
        form.put(route('platform.positions.update', editingPosition.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('platform.positions.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deactivatePosition = (pos: PositionItem) => {
    if (confirm(`Yakin ingin menonaktifkan jabatan ${pos.name}?`)) {
        router.delete(route('platform.positions.destroy', pos.id));
    }
};

const orgUnitOptions = computed(() => {
    return props.orgUnits.map((u) => ({
        value: u.id,
        label: `${u.name} (${u.code})`,
    }));
});

const typeOptions = [
    { value: 'structural', label: 'Struktural (Pimpinan / Kepala)' },
    { value: 'functional', label: 'Fungsional (Staff / Pelaksana / Operator)' },
];

const columns = [
    { key: 'code', label: 'Kode Jabatan', width: '130px' },
    { key: 'name', label: 'Nama Jabatan' },
    { key: 'org_unit', label: 'Unit Organisasi' },
    { key: 'type', label: 'Jenis Jabatan', width: '150px' },
    { key: 'status', label: 'Status', width: '100px' },
    { key: 'actions', label: 'Aksi', align: 'right' as const, width: '140px' },
];
</script>

<template>
    <AppShell
        title="Master Jabatan"
        :breadcrumbs="[
            { label: 'Platform', href: '/dashboard' },
            { label: 'Master Jabatan' },
        ]"
    >
        <AppCard
            title="Daftar Master Jabatan"
            subtitle="Pengelolaan formasi jabatan struktural dan fungsional di lingkungan PTKP"
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
                    Tambah Jabatan
                </AppButton>
            </template>

            <!-- Filter Bar -->
            <div
                class="mb-5 flex flex-col justify-between gap-3 lg:flex-row lg:items-center"
            >
                <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-3">
                    <AppInput
                        placeholder="Cari kode atau nama jabatan..."
                        v-model="search"
                        @keyup.enter="handleFilter"
                    />
                    <AppSelect
                        :options="orgUnitOptions"
                        placeholder="-- Semua Unit Organisasi --"
                        v-model="orgUnitFilter"
                        @change="handleFilter"
                    />
                    <AppSelect
                        :options="typeOptions"
                        placeholder="-- Semua Jenis --"
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
                <AppTable
                    :columns="columns"
                    :items="positions.data"
                    dark-header
                >
                    <template #row="{ item }">
                        <td class="px-4 py-3 align-middle">
                            <span
                                class="rounded-md bg-slate-100 px-2 py-1 font-mono text-xs font-bold text-ptkp-navy"
                            >
                                {{ item.code }}
                            </span>
                        </td>
                        <td
                            class="px-4 py-3 align-middle font-semibold text-ptkp-body"
                        >
                            {{ item.name }}
                        </td>
                        <td
                            class="px-4 py-3 align-middle text-xs text-slate-600"
                        >
                            <span v-if="item.org_unit">
                                {{ item.org_unit.name }}
                            </span>
                            <span v-else class="italic text-slate-400"
                                >- Belum Diatur -</span
                            >
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <AppBadge
                                v-if="item.type === 'structural'"
                                variant="navy"
                                size="sm"
                            >
                                Struktural
                            </AppBadge>
                            <AppBadge v-else variant="neutral" size="sm">
                                Fungsional
                            </AppBadge>
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
                                    @click="deactivatePosition(item)"
                                >
                                    Nonaktifkan
                                </AppButton>
                            </div>
                        </td>
                    </template>

                    <template #footer>
                        <AppPagination
                            :links="positions.links"
                            :from="positions.from"
                            :to="positions.to"
                            :total="positions.total"
                        />
                    </template>
                </AppTable>
            </div>
        </AppCard>

        <!-- Create / Edit Modal -->
        <AppModal
            :show="isModalOpen"
            :title="
                editingPosition ? 'Edit Jabatan' : 'Tambah Formasi Jabatan Baru'
            "
            max-width="md"
            @close="closeModal"
        >
            <form @submit.prevent="savePosition" class="flex flex-col gap-3">
                <AppInput
                    label="Kode Jabatan"
                    placeholder="Contoh: KASIE-PK1, STF-SDM, OP-ROLL"
                    v-model="form.code"
                    :error="form.errors.code"
                    required
                />

                <AppInput
                    label="Nama Jabatan"
                    placeholder="Contoh: Kepala Seksi Pembuatan Kertas I"
                    v-model="form.name"
                    :error="form.errors.name"
                    required
                />

                <AppSelect
                    label="Unit Organisasi Penempatan"
                    :options="orgUnitOptions"
                    v-model="form.org_unit_id"
                    :error="form.errors.org_unit_id"
                    required
                />

                <AppSelect
                    label="Jenis Jabatan"
                    :options="typeOptions"
                    v-model="form.type"
                    :error="form.errors.type"
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
                        <span>Jabatan aktif dan dapat diisi oleh pegawai</span>
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
                    @click="savePosition"
                >
                    {{
                        editingPosition ? 'Perbarui Jabatan' : 'Simpan Jabatan'
                    }}
                </AppButton>
            </template>
        </AppModal>
    </AppShell>
</template>
