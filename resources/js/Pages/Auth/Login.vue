<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import AppInput from '@/Components/AppInput.vue';
import AppButton from '@/Components/AppButton.vue';
import AppAlert from '@/Components/AppAlert.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
  canResetPassword?: boolean;
  status?: string;
}>();

const form = useForm({
  login: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post(route('login'), {
    onFinish: () => {
      form.reset('password');
    },
  });
};
</script>

<template>
  <GuestLayout>
    <Head title="Masuk ke Sistem" />

    <div class="login-header">
      <h2 class="login-title">Masuk ke Portal Internal</h2>
      <p class="login-subtitle">Silakan masukkan nama pengguna atau email dinas Anda</p>
    </div>

    <div v-if="status" class="status-alert">
      <AppAlert variant="success" dismissible>
        {{ status }}
      </AppAlert>
    </div>

    <form @submit.prevent="submit" class="login-form">
      <AppInput
        id="login"
        label="Nama Pengguna atau Email"
        placeholder="Contoh: superadmin atau email@peruri.co.id"
        v-model="form.login"
        :error="form.errors.login || (form.errors as any).email"
        required
        autofocus
      />

      <AppInput
        id="password"
        type="password"
        label="Kata Sandi"
        placeholder="Masukkan kata sandi akun Anda"
        v-model="form.password"
        :error="form.errors.password"
        required
      />

      <div class="remember-row">
        <label class="remember-label">
          <input
            type="checkbox"
            v-model="form.remember"
            class="remember-checkbox"
          />
          <span>Ingat saya di perangkat ini</span>
        </label>

        <Link
          v-if="canResetPassword"
          :href="route('password.request')"
          class="forgot-link"
        >
          Lupa kata sandi?
        </Link>
      </div>

      <div class="actions-row">
        <AppButton
          type="submit"
          variant="primary"
          size="lg"
          :loading="form.processing"
          class="submit-button"
        >
          Masuk ke Sistem
        </AppButton>
      </div>
    </form>
  </GuestLayout>
</template>

<style scoped>
.login-header {
  margin-bottom: var(--space-5);
  text-align: center;
}

.login-title {
  margin: 0;
  font-family: var(--font-heading);
  font-size: var(--text-xl);
  font-weight: 700;
  color: var(--color-navy);
}

.login-subtitle {
  margin: var(--space-1) 0 0 0;
  font-family: var(--font-body);
  font-size: var(--text-sm);
  color: #64748b;
}

.status-alert {
  margin-bottom: var(--space-4);
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.remember-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: var(--text-xs);
}

.remember-label {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  cursor: pointer;
  color: var(--color-text-body);
}

.remember-checkbox {
  accent-color: var(--color-navy);
  border-radius: 2px;
}

.forgot-link {
  color: var(--color-navy);
  text-decoration: none;
  font-weight: 500;
}
.forgot-link:hover {
  text-decoration: underline;
}

.actions-row {
  margin-top: var(--space-2);
}

.submit-button {
  width: 100%;
}
</style>
