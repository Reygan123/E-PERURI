<script setup lang="ts">
import AppBadge from '@/Components/AppBadge.vue';
import AppButton from '@/Components/AppButton.vue';
import AppCard from '@/Components/AppCard.vue';
import AppInput from '@/Components/AppInput.vue';
import AppModal from '@/Components/AppModal.vue';
import AppSelect from '@/Components/AppSelect.vue';
import AppTable from '@/Components/AppTable.vue';
import AppTextarea from '@/Components/AppTextarea.vue';
import AppShell from '@/Layouts/AppShell.vue';
import { Link, useForm } from '@inertiajs/vue3';
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

interface AssignmentItem {
    id: string;
    started_at: string;
    ended_at: string | null;
    assignment_type: string;
    notes: string | null;
    org_unit?: OrgUnitRef | null;
    position?: PositionRef | null;
}

interface EmployeeDetail {
    id: string;
    nip: string;
    name: string;
    email: string;
    phone: string | null;
    gender: string | null;
    birth_date: string | null;
    is_active: boolean;
    assignments?: AssignmentItem[];
    user?: {
        id: string;
        username: string;
        email: string;
        is_active: boolean;
        must_change_pwd: boolean;
        last_login_at: string | null;
        user_roles?: Array<{
            id: string;
            valid_from: string;
            valid_until: string | null;
            role: { name: string; label: string };
            org_unit?: OrgUnitRef | null;
        }>;
    } | null;
}

const props = defineProps<{
    employee: EmployeeDetail;
    orgUnits: OrgUnitRef[];
    positions: PositionRef[];
}>();

// Assign / Mutation Modal
const isAssignModalOpen = ref(false);

const form = useForm({
    org_unit_id: '',
    position_id: '',
    started_at: new Date().toISOString().substring(0, 10),
    assignment_type: 'permanent',
    notes: '',
});

const openAssignModal = () => {
    form.reset();
    form.clearErrors();
    form.started_at = new Date().toISOString().substring(0, 10);
    form.assignment_type = 'permanent';
    isAssignModalOpen.value = true;
};

const closeAssignModal = () => {
    isAssignModalOpen.value = false;
};

const submitMutation = () => {
    form.post(route('platform.employees.assign', props.employee.id), {
        onSuccess: () => closeAssignModal(),
    });
};

const orgUnitOptions = computed(() => {
    return props.orgUnits.map((u) => ({
        value: u.id,
        label: `${u.name} (${u.code})`,
    }));
});

const filteredPositions = computed(() => {
    if (!form.org_unit_id) {
        return props.positions.map((p) => ({ value: p.id, label: p.name }));
    }
    return props.positions
        .filter((p) => p.org_unit_id === form.org_unit_id)
        .map((p) => ({ value: p.id, label: p.name }));
});

const assignmentColumns = [
    { key: 'period', label: 'Periode Penugasan', width: '220px' },
    { key: 'position', label: 'Jabatan' },
    { key: 'unit', label: 'Unit Organisasi' },
    { key: 'type', label: 'Jenis Status', width: '130px' },
    { key: 'notes', label: 'Catatan SK / Mutasi' },
];
</script>

<template>
    <AppShell
        :title="`Profil Pegawai - ${employee.name}`"
        :breadcrumbs="[
            { label: 'Platform', href: '/dashboard' },
            { label: 'Data Pegawai', href: route('platform.employees.index') },
            { label: employee.name },
        ]"
    >
        <!-- Top Action / Back Link -->
        <div class="mb-2">
            <Link
                :href="route('platform.employees.index')"
                class="font-body text-sm font-semibold text-ptkp-navy no-underline hover:underline"
            >
                &larr; Kembali ke Daftar Pegawai
            </Link>
        </div>

        <!-- Profile & Account Info Grid -->
        <div class="mb-4 grid grid-cols-1 gap-5 md:grid-cols-2">
            <!-- Card: Biodata Pegawai -->
            <AppCard
                title="Identitas Induk Pegawai"
                subtitle="Informasi kepegawaian resmi tercatat"
            >
                <div class="flex flex-col gap-3">
                    <div
                        class="flex items-center justify-between border-b border-slate-100 pb-2"
                    >
                        <span class="font-body text-xs text-slate-500"
                            >Nomor Induk Pegawai (NIP)</span
                        >
                        <span class="font-mono text-sm text-ptkp-body">{{
                            employee.nip
                        }}</span>
                    </div>
                    <div
                        class="flex items-center justify-between border-b border-slate-100 pb-2"
                    >
                        <span class="font-body text-xs text-slate-500"
                            >Nama Lengkap</span
                        >
                        <span
                            class="font-body text-sm font-bold text-ptkp-body"
                            >{{ employee.name }}</span
                        >
                    </div>
                    <div
                        class="flex items-center justify-between border-b border-slate-100 pb-2"
                    >
                        <span class="font-body text-xs text-slate-500"
                            >Email Dinas</span
                        >
                        <span class="font-body text-sm text-ptkp-body">{{
                            employee.email
                        }}</span>
                    </div>
                    <div
                        class="flex items-center justify-between border-b border-slate-100 pb-2"
                    >
                        <span class="font-body text-xs text-slate-500"
                            >Nomor Telepon</span
                        >
                        <span class="font-body text-sm text-ptkp-body">{{
                            employee.phone || '-'
                        }}</span>
                    </div>
                    <div
                        class="flex items-center justify-between border-b border-slate-100 pb-2"
                    >
                        <span class="font-body text-xs text-slate-500"
                            >Jenis Kelamin</span
                        >
                        <span class="font-body text-sm text-ptkp-body">{{
                            employee.gender === 'female'
                                ? 'Perempuan'
                                : 'Laki-Laki'
                        }}</span>
                    </div>
                    <div
                        class="flex items-center justify-between border-b border-slate-100 pb-2"
                    >
                        <span class="font-body text-xs text-slate-500"
                            >Status Kepegawaian</span
                        >
                        <span class="font-body text-sm text-ptkp-body">
                            <AppBadge
                                v-if="employee.is_active"
                                variant="success"
                                size="sm"
                                >Aktif</AppBadge
                            >
                            <AppBadge v-else variant="error" size="sm"
                                >Nonaktif</AppBadge
                            >
                        </span>
                    </div>
                </div>
            </AppCard>

            <!-- Card: Akun Portal & Peran -->
            <AppCard
                title="Akun Portal Terhubung"
                subtitle="Kredensial dan otorisasi hak akses modul"
            >
                <template v-if="employee.user">
                    <div class="flex flex-col gap-3">
                        <div
                            class="flex items-center justify-between border-b border-slate-100 pb-2"
                        >
                            <span class="font-body text-xs text-slate-500"
                                >Nama Pengguna (Username)</span
                            >
                            <span
                                class="font-mono text-sm font-semibold text-ptkp-navy"
                                >@{{ employee.user.username }}</span
                            >
                        </div>
                        <div
                            class="flex items-center justify-between border-b border-slate-100 pb-2"
                        >
                            <span class="font-body text-xs text-slate-500"
                                >Status Akun</span
                            >
                            <span class="font-body text-sm text-ptkp-body">
                                <AppBadge
                                    v-if="employee.user.is_active"
                                    variant="success"
                                    size="sm"
                                    >Aktif</AppBadge
                                >
                                <AppBadge v-else variant="error" size="sm"
                                    >Terkunci / Nonaktif</AppBadge
                                >
                            </span>
                        </div>
                        <div
                            class="flex items-center justify-between border-b border-slate-100 pb-2"
                        >
                            <span class="font-body text-xs text-slate-500"
                                >Keamanan Kata Sandi</span
                            >
                            <span class="font-body text-sm text-ptkp-body">
                                <AppBadge
                                    v-if="employee.user.must_change_pwd"
                                    variant="warning"
                                    size="sm"
                                >
                                    Wajib Ganti Password
                                </AppBadge>
                                <AppBadge v-else variant="neutral" size="sm"
                                    >Aman & Terverifikasi</AppBadge
                                >
                            </span>
                        </div>
                        <div
                            class="flex items-center justify-between border-b border-slate-100 pb-2"
                        >
                            <span class="font-body text-xs text-slate-500"
                                >Login Terakhir</span
                            >
                            <span class="font-body text-sm text-ptkp-body">{{
                                employee.user.last_login_at ||
                                'Belum pernah login'
                            }}</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <span
                            class="mb-2 block font-heading text-xs font-bold text-ptkp-navy"
                        >
                            Peran yang Diberikan:
                        </span>
                        <div class="flex flex-wrap gap-2">
                            <template
                                v-if="
                                    employee.user.user_roles &&
                                    employee.user.user_roles.length > 0
                                "
                            >
                                <div
                                    v-for="ur in employee.user.user_roles"
                                    :key="ur.id"
                                    class="flex items-center gap-1.5"
                                >
                                    <AppBadge variant="navy" size="md">{{
                                        ur.role.label
                                    }}</AppBadge>
                                    <span
                                        v-if="ur.org_unit"
                                        class="text-[11px] italic text-slate-500"
                                    >
                                        Scope: {{ ur.org_unit.code }}
                                    </span>
                                </div>
                            </template>
                            <span v-else class="text-xs italic text-slate-400"
                                >Belum ada peran tambahan</span
                            >
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div
                        class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500"
                    >
                        <p class="m-0">
                            Pegawai ini belum memiliki akun portal pengguna.
                        </p>
                    </div>
                </template>
            </AppCard>
        </div>

        <!-- Assignment Mutation History -->
        <AppCard
            title="Riwayat Penugasan & Mutasi Jabatan"
            subtitle="Pola riwayat bertanggal resmi mutasi formasi jabatan di PT Kertas Padalarang"
        >
            <template #headerActions>
                <AppButton variant="primary" size="md" @click="openAssignModal">
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
                    Catat Mutasi / Penugasan Baru
                </AppButton>
            </template>

            <div class="mt-2">
                <AppTable
                    :columns="assignmentColumns"
                    :items="employee.assignments || []"
                    dark-header
                >
                    <template #row="{ item }">
                        <td class="px-4 py-3 align-middle">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs">
                                    {{ item.started_at }} s/d
                                    {{ item.ended_at || 'Sekarang' }}
                                </span>
                                <AppBadge
                                    v-if="!item.ended_at"
                                    variant="success"
                                    size="sm"
                                >
                                    Aktif Saat Ini
                                </AppBadge>
                            </div>
                        </td>
                        <td
                            class="px-4 py-3 align-middle font-bold text-ptkp-body"
                        >
                            {{ item.position?.name || '-' }}
                        </td>
                        <td
                            class="px-4 py-3 align-middle text-sm text-ptkp-body"
                        >
                            {{ item.org_unit?.name || '-' }} ({{
                                item.org_unit?.code || '-'
                            }})
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <AppBadge
                                v-if="item.assignment_type === 'permanent'"
                                variant="navy"
                                size="sm"
                            >
                                Tetap
                            </AppBadge>
                            <AppBadge
                                v-else-if="item.assignment_type === 'acting'"
                                variant="warning"
                                size="sm"
                            >
                                Plt / Acting
                            </AppBadge>
                            <AppBadge v-else variant="neutral" size="sm">
                                Kontrak / Temp
                            </AppBadge>
                        </td>
                        <td
                            class="px-4 py-3 align-middle text-xs text-slate-500"
                        >
                            {{ item.notes || '-' }}
                        </td>
                    </template>
                </AppTable>
            </div>
        </AppCard>

        <!-- New Assignment Modal -->
        <AppModal
            :show="isAssignModalOpen"
            title="Catat Mutasi / Penugasan Baru"
            max-width="md"
            @close="closeAssignModal"
        >
            <form @submit.prevent="submitMutation" class="flex flex-col gap-3">
                <p
                    class="m-0 rounded-xl bg-slate-50 p-3.5 text-xs leading-relaxed text-slate-600"
                >
                    Mencatat penugasan baru akan secara otomatis mengakhiri
                    penugasan aktif sebelumnya pada H-1 tanggal mulai yang
                    dipilih (pola riwayat bertanggal).
                </p>

                <AppSelect
                    label="Unit Organisasi Baru"
                    :options="orgUnitOptions"
                    v-model="form.org_unit_id"
                    :error="form.errors.org_unit_id"
                    required
                />

                <AppSelect
                    label="Jabatan Baru"
                    :options="filteredPositions"
                    v-model="form.position_id"
                    :error="form.errors.position_id"
                    required
                />

                <AppInput
                    type="date"
                    label="Tanggal Mulai Berlaku"
                    v-model="form.started_at"
                    :error="form.errors.started_at"
                    required
                />

                <AppSelect
                    label="Jenis Penugasan"
                    :options="[
                        { value: 'permanent', label: 'Tetap (Definitif)' },
                        { value: 'temporary', label: 'Sementara / Kontrak' },
                        { value: 'acting', label: 'Pelaksana Tugas (Plt)' },
                    ]"
                    v-model="form.assignment_type"
                    :error="form.errors.assignment_type"
                    required
                />

                <AppTextarea
                    label="Catatan SK / Referensi Mutasi"
                    placeholder="Nomor SK Direksi atau alasan mutasi jabatan"
                    v-model="form.notes"
                    :error="form.errors.notes"
                />
            </form>

            <template #footer>
                <AppButton
                    variant="secondary"
                    size="md"
                    @click="closeAssignModal"
                >
                    Batal
                </AppButton>
                <AppButton
                    variant="primary"
                    size="md"
                    :loading="form.processing"
                    @click="submitMutation"
                >
                    Simpan Mutasi
                </AppButton>
            </template>
        </AppModal>
    </AppShell>
</template>
