<script setup lang="ts">
import AppInput from '@/Components/AppInput.vue';
import AppButton from '@/Components/AppButton.vue';
import AppAlert from '@/Components/AppAlert.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post(route('password.mandatory.update'), {
    onFinish: () => {
      form.reset('current_password', 'password', 'password_confirmation');
    },
  });
};
</script>

<template>
  <GuestLayout>
    <Head title="Wajib Perbarui Password" />

    <div class="mandatory-header">
      <h2 class="mandatory-title">Wajib Perbarui Kata Sandi</h2>
      <p class="mandatory-subtitle">
        Akun Anda memerlukan pembaruan kata sandi sebelum dapat mengakses fitur sistem.
      </p>
    </div>

    <div class="alert-box">
      <AppAlert variant="warning">
        Demi keamanan akun internal PT Kertas Padalarang, Anda wajib mengganti password default Anda.
      </AppAlert>
    </div>

    <form @submit.prevent="submit" class="mandatory-form">
      <AppInput
        id="current_password"
        type="password"
        label="Kata Sandi Saat Ini"
        placeholder="Masukkan kata sandi lama Anda"
        v-model="form.current_password"
        :error="form.errors.current_password"
        required
        autofocus
      />

      <AppInput
        id="password"
        type="password"
        label="Kata Sandi Baru"
        placeholder="Minimal 8 karakter aman"
        v-model="form.password"
        :error="form.errors.password"
        required
      />

      <AppInput
        id="password_confirmation"
        type="password"
        label="Konfirmasi Kata Sandi Baru"
        placeholder="Ulangi kata sandi baru"
        v-model="form.password_confirmation"
        :error="form.errors.password_confirmation"
        required
      />

      <div class="actions-row">
        <AppButton
          type="submit"
          variant="primary"
          size="lg"
          :loading="form.processing"
          class="submit-button"
        >
          Simpan & Masuk ke Sistem
        </AppButton>
      </div>
    </form>
  </GuestLayout>
</template>

<style scoped>
.mandatory-header {
  margin-bottom: var(--space-4);
  text-align: center;
}

.mandatory-title {
  margin: 0;
  font-family: var(--font-heading);
  font-size: var(--text-xl);
  font-weight: 700;
  color: var(--color-navy);
}

.mandatory-subtitle {
  margin: var(--space-1) 0 0 0;
  font-family: var(--font-body);
  font-size: var(--text-sm);
  color: #64748b;
}

.alert-box {
  margin-bottom: var(--space-4);
}

.mandatory-form {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.actions-row {
  margin-top: var(--space-2);
}

.submit-button {
  width: 100%;
}
</style>
