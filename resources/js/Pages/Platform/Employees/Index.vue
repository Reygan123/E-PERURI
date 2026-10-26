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
import { Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface OrgUnitRef {
    id: string;
    code: string;
    name: string;
}

interface PositionRef {
    id: string;
    code: string;
    name: string;
    org_unit_id: string;
}

interface EmployeeItem {
    id: string;
    nip: string;
    name: string;
    email: string;
    phone: string | null;
    gender: string | null;
    is_active: boolean;
    active_assignment?: {
        id: string;
        assignment_type: string;
        org_unit?: OrgUnitRef;
        position?: PositionRef;
    } | null;
    user?: {
        id: string;
        username: string;
        is_active: boolean;
        must_change_pwd: boolean;
    } | null;
}

interface PaginatedEmployees {
    data: EmployeeItem[];
    links: any[];
    from?: number;
    to?: number;
    total?: number;
}

const props = defineProps<{
    employees: PaginatedEmployees;
    orgUnits: OrgUnitRef[];
    positions: PositionRef[];
    filters: { search?: string; org_unit_id?: string; is_active?: string };
}>();

const search = ref(props.filters.search || '');
const orgUnitFilter = ref(props.filters.org_unit_id || '');
const statusFilter = ref(props.filters.is_active || '');

const handleFilter = () => {
    router.get(
        route('platform.employees.index'),
        {
            search: search.value,
            org_unit_id: orgUnitFilter.value,
            is_active: statusFilter.value,
        },
        { preserveState: true, replace: true },
    );
};

// Modal State
const isModalOpen = ref(false);
const editingEmployee = ref<EmployeeItem | null>(null);

const form = useForm({
    name: '',
    nip: '',
    email: '',
    phone: '',
    gender: 'male',
    birth_date: '',
    nik: '',
    org_unit_id: '',
    position_id: '',
    assignment_type: 'permanent',
    username: '',
    initial_password: 'PTKP2026!',
    is_active: true,
});

const openCreateModal = () => {
    editingEmployee.value = null;
    form.reset();
    form.clearErrors();
    form.gender = 'male';
    form.assignment_type = 'permanent';
    form.initial_password = 'PTKP2026!';
    form.is_active = true;
    isModalOpen.value = true;
};

const openEditModal = (emp: EmployeeItem) => {
    editingEmployee.value = emp;
    form.clearErrors();
    form.name = emp.name;
    form.nip = emp.nip;
    form.email = emp.email;
    form.phone = emp.phone || '';
    form.gender = emp.gender || 'male';
    form.is_active = emp.is_active;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    editingEmployee.value = null;
};

const saveEmployee = () => {
    if (editingEmployee.value) {
        form.put(route('platform.employees.update', editingEmployee.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('platform.employees.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const orgUnitOptions = computed(() => {
    return props.orgUnits.map((u) => ({
        value: u.id,
        label: `${u.name} (${u.code})`,
    }));
});

const filteredPositionOptions = computed(() => {
    if (!form.org_unit_id) {
        return props.positions.map((p) => ({
            value: p.id,
            label: p.name,
        }));
    }
    return props.positions
        .filter((p) => p.org_unit_id === form.org_unit_id)
        .map((p) => ({
            value: p.id,
            label: p.name,
        }));
});

const columns = [
    { key: 'nip', label: 'NIP', width: '130px' },
    { key: 'name', label: 'Nama Lengkap & Kontak' },
    { key: 'unit', label: 'Unit Organisasi' },
    { key: 'position', label: 'Jabatan Aktif' },
    { key: 'user', label: 'Akun Portal', width: '140px' },
    { key: 'status', label: 'Status', width: '90px' },
    { key: 'actions', label: 'Aksi', align: 'right' as const, width: '130px' },
];
</script>

<template>
    <AppShell
        title="Data Pegawai"
        :breadcrumbs="[
            { label: 'Platform', href: '/dashboard' },
            { label: 'Data Pegawai' },
        ]"
    >
        <AppCard
            title="Direktori Pegawai PTKP"
            subtitle="Manajemen data induk pegawai, penugasan resmi, dan akun portal terintegrasi"
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
                    Tambah Pegawai Baru
                </AppButton>
            </template>

            <!-- Filter Bar -->
            <div
                class="mb-5 flex flex-col justify-between gap-3 lg:flex-row lg:items-center"
            >
                <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-3">
                    <AppInput
                        placeholder="Cari NIP, nama, atau email..."
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
                        :options="[
                            { value: 'true', label: 'Hanya Aktif' },
                            { value: 'false', label: 'Hanya Nonaktif' },
                        ]"
                        placeholder="-- Semua Status --"
                        v-model="statusFilter"
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
                    :items="employees.data"
                    dark-header
                >
                    <template #row="{ item }">
                        <td class="px-4 py-3 align-middle">
                            <span
                                class="rounded-md bg-slate-100 px-2 py-1 font-mono text-xs font-bold text-ptkp-navy"
                            >
                                {{ item.nip }}
                            </span>
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <div class="flex flex-col">
                                <span class="font-semibold text-ptkp-body">{{
                                    item.name
                                }}</span>
                                <span class="text-xs text-slate-500">{{
                                    item.email
                                }}</span>
                            </div>
                        </td>
                        <td
                            class="px-4 py-3 align-middle text-xs text-slate-600"
                        >
                            <span v-if="item.active_assignment?.org_unit">
                                {{ item.active_assignment.org_unit.name }}
                            </span>
                            <span v-else class="italic text-slate-400"
                                >- Belum Ditempatkan -</span
                            >
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <div
                                v-if="item.active_assignment?.position"
                                class="flex items-center gap-1.5"
                            >
                                <span
                                    class="text-sm font-medium text-ptkp-body"
                                    >{{
                                        item.active_assignment.position.name
                                    }}</span
                                >
                                <AppBadge
                                    v-if="
                                        item.active_assignment
                                            .assignment_type === 'acting'
                                    "
                                    variant="warning"
                                    size="sm"
                                >
                                    Plt
                                </AppBadge>
                            </div>
                            <span v-else class="text-xs italic text-slate-400"
                                >-</span
                            >
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <div v-if="item.user" class="flex flex-col">
                                <span
                                    class="font-mono text-xs font-semibold text-ptkp-navy"
                                    >@{{ item.user.username }}</span
                                >
                                <span
                                    v-if="item.user.must_change_pwd"
                                    class="text-[10px] text-ptkp-warning"
                                >
                                    Wajib Ganti Password
                                </span>
                            </div>
                            <span v-else class="text-xs italic text-slate-400"
                                >Belum ada akun</span
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
                            <div class="inline-flex items-center gap-2">
                                <Link
                                    :href="
                                        route(
                                            'platform.employees.show',
                                            item.id,
                                        )
                                    "
                                    class="shadow-xs rounded-lg bg-ptkp-mist px-3 py-1.5 font-body text-xs font-semibold text-ptkp-navy no-underline transition-all hover:bg-ptkp-navy hover:text-white"
                                >
                                    Detail
                                </Link>
                                <AppButton
                                    variant="text"
                                    size="sm"
                                    @click="openEditModal(item)"
                                >
                                    Edit
                                </AppButton>
                            </div>
                        </td>
                    </template>

                    <template #footer>
                        <AppPagination
                            :links="employees.links"
                            :from="employees.from"
                            :to="employees.to"
                            :total="employees.total"
                        />
                    </template>
                </AppTable>
            </div>
        </AppCard>

        <!-- Create / Edit Modal -->
        <AppModal
            :show="isModalOpen"
            :title="
                editingEmployee
                    ? 'Edit Data Pegawai'
                    : 'Pendaftaran Pegawai Baru & Akun Portal'
            "
            max-width="lg"
            @close="closeModal"
        >
            <form @submit.prevent="saveEmployee" class="flex flex-col gap-3">
                <!-- Section: Identitas Pegawai -->
                <div
                    class="mt-2 border-b border-slate-100 pb-1 font-heading text-xs font-bold tracking-wide text-ptkp-navy"
                >
                    1. Data Induk Pegawai
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <AppInput
                        label="Nomor Induk Pegawai (NIP)"
                        placeholder="Contoh: PTKP-2026-001"
                        v-model="form.nip"
                        :error="form.errors.nip"
                        required
                    />
                    <AppInput
                        label="Nama Lengkap"
                        placeholder="Nama pegawai lengkap beserta gelar"
                        v-model="form.name"
                        :error="form.errors.name"
                        required
                    />
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <AppInput
                        type="email"
                        label="Email Dinas / Resmi"
                        placeholder="nama.pegawai@ptkp.co.id"
                        v-model="form.email"
                        :error="form.errors.email"
                        required
                    />
                    <AppInput
                        label="Nomor Telepon / WhatsApp"
                        placeholder="081234567890"
                        v-model="form.phone"
                        :error="form.errors.phone"
                    />
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <AppSelect
                        label="Jenis Kelamin"
                        :options="[
                            { value: 'male', label: 'Laki-Laki' },
                            { value: 'female', label: 'Perempuan' },
                        ]"
                        v-model="form.gender"
                        :error="form.errors.gender"
                    />
                    <AppInput
                        v-if="!editingEmployee"
                        label="Nomor KTP / NIK (Akan Dienkripsi)"
                        placeholder="16 Digit NIK KTP"
                        v-model="form.nik"
                        :error="form.errors.nik"
                    />
                </div>

                <!-- Section: Penugasan Awal (Hanya saat create) -->
                <template v-if="!editingEmployee">
                    <div
                        class="mt-2 border-b border-slate-100 pb-1 font-heading text-xs font-bold tracking-wide text-ptkp-navy"
                    >
                        2. Penempatan & Formasi Jabatan
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <AppSelect
                            label="Unit Organisasi"
                            :options="orgUnitOptions"
                            v-model="form.org_unit_id"
                            :error="form.errors.org_unit_id"
                            required
                        />
                        <AppSelect
                            label="Formasi Jabatan"
                            :options="filteredPositionOptions"
                            v-model="form.position_id"
                            :error="form.errors.position_id"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <AppSelect
                            label="Jenis Penugasan"
                            :options="[
                                {
                                    value: 'permanent',
                                    label: 'Tetap (Definitif)',
                                },
                                {
                                    value: 'temporary',
                                    label: 'Sementara / Kontrak',
                                },
                                {
                                    value: 'acting',
                                    label: 'Pelaksana Tugas (Plt)',
                                },
                            ]"
                            v-model="form.assignment_type"
                            :error="form.errors.assignment_type"
                            required
                        />
                    </div>

                    <!-- Section: Akun Portal -->
                    <div
                        class="mt-2 border-b border-slate-100 pb-1 font-heading text-xs font-bold tracking-wide text-ptkp-navy"
                    >
                        3. Akun Pengguna Portal Internal
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <AppInput
                            label="Nama Pengguna (Username)"
                            placeholder="Contoh: andi.suherman"
                            v-model="form.username"
                            :error="form.errors.username"
                            required
                        />
                        <AppInput
                            type="text"
                            label="Password Sementara"
                            v-model="form.initial_password"
                            :error="form.errors.initial_password"
                            hint="Pegawai wajib mengganti password ini saat pertama kali login"
                            required
                        />
                    </div>
                </template>

                <div class="mt-2">
                    <label
                        class="flex cursor-pointer items-center gap-2 text-sm text-ptkp-body"
                    >
                        <input
                            type="checkbox"
                            v-model="form.is_active"
                            class="rounded-md border-slate-300 text-ptkp-navy focus:ring-ptkp-navy"
                        />
                        <span
                            >Pegawai berstatus aktif dalam organisasi PTKP</span
                        >
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
                    @click="saveEmployee"
                >
                    {{
                        editingEmployee
                            ? 'Perbarui Pegawai'
                            : 'Daftarkan Pegawai'
                    }}
                </AppButton>
            </template>
        </AppModal>
    </AppShell>
</template>
