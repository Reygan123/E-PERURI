<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import AppBadge from '@/Components/AppBadge.vue';
import AppModal from '@/Components/AppModal.vue';
import AppInput from '@/Components/AppInput.vue';
import AppSelect from '@/Components/AppSelect.vue';
import AppTable from '@/Components/AppTable.vue';
import AppPagination from '@/Components/AppPagination.vue';

interface RoleRef {
  id: string;
  name: string;
  label: string;
}

interface OrgUnitRef {
  id: string;
  code: string;
  name: string;
}

interface UserRoleItem {
  id: string;
  role_id: string;
  org_unit_id: string | null;
  valid_from: string;
  valid_until: string | null;
  role: RoleRef;
  org_unit?: OrgUnitRef | null;
}

interface UserItem {
  id: string;
  employee_id: string | null;
  username: string;
  name: string;
  email: string;
  is_active: boolean;
  must_change_pwd: boolean;
  last_login_at: string | null;
  employee?: {
    id: string;
    nip: string;
    name: string;
    active_assignment?: {
      org_unit?: OrgUnitRef;
    };
  } | null;
  user_roles?: UserRoleItem[];
}

interface PaginatedUsers {
  data: UserItem[];
  links: any[];
  from?: number;
  to?: number;
  total?: number;
}

const props = defineProps<{
  users: PaginatedUsers;
  roles: RoleRef[];
  orgUnits: OrgUnitRef[];
  filters: { search?: string; role_id?: string; is_active?: string };
}>();

const search = ref(props.filters.search || '');
const roleFilter = ref(props.filters.role_id || '');
const statusFilter = ref(props.filters.is_active || '');

const handleFilter = () => {
  router.get(
    route('platform.users.index'),
    {
      search: search.value,
      role_id: roleFilter.value,
      is_active: statusFilter.value,
    },
    { preserveState: true, replace: true }
  );
};

// Assign Role Modal
const isAssignModalOpen = ref(false);
const targetUser = ref<UserItem | null>(null);

const assignForm = useForm({
  role_id: '',
  org_unit_id: '',
  valid_from: new Date().toISOString().substring(0, 10),
  valid_until: '',
});

const openAssignModal = (user: UserItem) => {
  targetUser.value = user;
  assignForm.reset();
  assignForm.clearErrors();
  assignForm.valid_from = new Date().toISOString().substring(0, 10);
  isAssignModalOpen.value = true;
};

const closeAssignModal = () => {
  isAssignModalOpen.value = false;
  targetUser.value = null;
};

const submitAssignRole = () => {
  if (!targetUser.value) return;

  assignForm.post(route('platform.users.roles.assign', targetUser.value.id), {
    onSuccess: () => closeAssignModal(),
  });
};

const revokeRole = (ur: UserRoleItem) => {
  if (confirm(`Yakin ingin mencabut peran ${ur.role.label}?`)) {
    router.delete(route('platform.users.roles.revoke', ur.id));
  }
};

const toggleUserStatus = (user: UserItem) => {
  const actionText = user.is_active ? 'menonaktifkan' : 'mengaktifkan';
  if (confirm(`Yakin ingin ${actionText} akun ${user.username}?`)) {
    router.post(route('platform.users.toggle-status', user.id));
  }
};

const resetPassword = (user: UserItem) => {
  if (confirm(`Reset password untuk ${user.username}? Password baru akan digenerate otomatis.`)) {
    router.post(route('platform.users.reset-password', user.id));
  }
};

const roleOptions = computed(() => {
  return props.roles.map((r) => ({
    value: r.id,
    label: `${r.label} (${r.name})`,
  }));
});

const orgUnitOptions = computed(() => {
  return props.orgUnits.map((u) => ({
    value: u.id,
    label: `${u.name} (${u.code})`,
  }));
});

const columns = [
  { key: 'username', label: 'Nama Pengguna', width: '150px' },
  { key: 'identity', label: 'Nama & Email' },
  { key: 'employee', label: 'Pegawai Terkait' },
  { key: 'roles', label: 'Peran & Scope Hak Akses' },
  { key: 'status', label: 'Status Akun', width: '130px' },
  { key: 'actions', label: 'Aksi Otorisasi', align: 'right' as const, width: '180px' },
];
</script>

<template>
  <AppShell
    title="Pengguna & Peran"
    :breadcrumbs="[{ label: 'Platform', href: '/dashboard' }, { label: 'Pengguna & Peran' }]"
  >
    <AppCard
      title="Manajemen Akun Pengguna & Otorisasi Peran"
      subtitle="Pengaturan otorisasi berbasis peran (RBAC) dengan cakupan scope unit di lingkungan PTKP"
    >
      <!-- Filter Bar -->
      <div class="flex items-center justify-between gap-4 mb-4 flex-wrap">
        <div class="flex gap-3 flex-1 max-w-3xl flex-wrap sm:flex-nowrap">
          <AppInput
            placeholder="Cari username, nama, atau email..."
            v-model="search"
            @keyup.enter="handleFilter"
          />
          <AppSelect
            :options="roleOptions"
            placeholder="-- Semua Peran --"
            v-model="roleFilter"
            @change="handleFilter"
          />
          <AppSelect
            :options="[
              { value: 'true', label: 'Hanya Aktif' },
              { value: 'false', label: 'Hanya Nonaktif' }
            ]"
            placeholder="-- Semua Status --"
            v-model="statusFilter"
            @change="handleFilter"
          />
        </div>
        <AppButton variant="secondary" size="md" @click="handleFilter">
          Filter
        </AppButton>
      </div>

      <!-- Table View -->
      <div class="mt-2">
        <AppTable :columns="columns" :items="users.data" dark-header>
          <template #row="{ item }">
            <td class="px-4 py-3 align-middle">
              <span class="font-mono text-xs font-bold text-ptkp-navy bg-ptkp-mist px-2 py-0.5 rounded-sm border border-ptkp-border">
                @{{ item.username }}
              </span>
            </td>
            <td class="px-4 py-3 align-middle">
              <div class="flex flex-col">
                <span class="font-semibold text-ptkp-body">{{ item.name }}</span>
                <span class="text-xs text-slate-500">{{ item.email }}</span>
              </div>
            </td>
            <td class="px-4 py-3 align-middle text-xs">
              <span v-if="item.employee" class="text-ptkp-body">
                {{ item.employee.name }} ({{ item.employee.nip }})
              </span>
              <span v-else class="text-slate-400 italic">Akun Sistem / Admin</span>
            </td>
            <td class="px-4 py-3 align-middle">
              <div class="flex flex-wrap gap-1.5 items-center">
                <template v-if="item.user_roles && item.user_roles.length > 0">
                  <div
                    v-for="ur in item.user_roles"
                    :key="ur.id"
                    class="inline-flex items-center gap-1 bg-ptkp-mist px-2 py-0.5 rounded-sm border border-ptkp-border"
                  >
                    <span class="font-medium text-xs text-ptkp-navy">{{ ur.role.label }}</span>
                    <span v-if="ur.org_unit" class="text-[10px] text-slate-500 font-mono">
                      [{{ ur.org_unit.code }}]
                    </span>
                    <button
                      type="button"
                      class="text-slate-400 hover:text-ptkp-error p-0.5 cursor-pointer bg-transparent border-none"
                      title="Cabut peran"
                      @click="revokeRole(ur)"
                    >
                      &times;
                    </button>
                  </div>
                </template>
                <span v-else class="text-xs text-slate-400 italic">Belum ada peran</span>

                <button
                  type="button"
                  class="text-xs font-semibold text-ptkp-navy hover:underline bg-transparent border-none cursor-pointer ml-1"
                  @click="openAssignModal(item)"
                >
                  + Tambah
                </button>
              </div>
            </td>
            <td class="px-4 py-3 align-middle">
              <div class="flex flex-col gap-1">
                <div>
                  <AppBadge v-if="item.is_active" variant="success" size="sm">Aktif</AppBadge>
                  <AppBadge v-else variant="error" size="sm">Nonaktif</AppBadge>
                </div>
                <span v-if="item.must_change_pwd" class="text-[10px] text-ptkp-warning">
                  Wajib Ganti Password
                </span>
              </div>
            </td>
            <td class="px-4 py-3 align-middle text-right">
              <div class="inline-flex items-center gap-1.5">
                <AppButton
                  variant="text"
                  size="sm"
                  title="Reset password ke default acak"
                  @click="resetPassword(item)"
                >
                  Reset Sandi
                </AppButton>
                <AppButton
                  variant="text"
                  size="sm"
                  :class="item.is_active ? 'text-ptkp-error hover:text-red-800' : 'text-ptkp-success hover:text-green-800'"
                  @click="toggleUserStatus(item)"
                >
                  {{ item.is_active ? 'Kunci' : 'Aktifkan' }}
                </AppButton>
              </div>
            </td>
          </template>

          <template #footer>
            <AppPagination
              :links="users.links"
              :from="users.from"
              :to="users.to"
              :total="users.total"
            />
          </template>
        </AppTable>
      </div>
    </AppCard>

    <!-- Assign Role Modal -->
    <AppModal
      :show="isAssignModalOpen"
      :title="`Tetapkan Peran Pengguna: @${targetUser?.username}`"
      max-width="md"
      @close="closeAssignModal"
    >
      <form @submit.prevent="submitAssignRole" class="flex flex-col gap-3">
        <AppSelect
          label="Pilih Peran"
          :options="roleOptions"
          v-model="assignForm.role_id"
          :error="assignForm.errors.role_id"
          required
        />

        <AppSelect
          label="Scope Unit Organisasi (Opsional)"
          placeholder="-- Seluruh Organisasi / Default Scope --"
          :options="orgUnitOptions"
          v-model="assignForm.org_unit_id"
          :error="assignForm.errors.org_unit_id"
          hint="Kosongkan jika peran ini berlaku global (misal: super-admin)"
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <AppInput
            type="date"
            label="Mulai Berlaku"
            v-model="assignForm.valid_from"
            :error="assignForm.errors.valid_from"
            required
          />
          <AppInput
            type="date"
            label="Berlaku Sampai (Opsional)"
            v-model="assignForm.valid_until"
            :error="assignForm.errors.valid_until"
            hint="Kosongkan jika berlaku permanen"
          />
        </div>
      </form>

      <template #footer>
        <AppButton variant="secondary" size="md" @click="closeAssignModal">
          Batal
        </AppButton>
        <AppButton variant="primary" size="md" :loading="assignForm.processing" @click="submitAssignRole">
          Simpan Peran
        </AppButton>
      </template>
    </AppModal>
  </AppShell>
</template>
