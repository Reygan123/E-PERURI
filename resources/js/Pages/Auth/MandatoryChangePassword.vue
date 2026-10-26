<script setup lang="ts">
import AppAlert from '@/Components/AppAlert.vue';
import AppButton from '@/Components/AppButton.vue';
import AppInput from '@/Components/AppInput.vue';
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

        <div class="mb-4 text-center">
            <h2 class="m-0 font-heading text-xl font-bold text-ptkp-navy">
                Wajib Perbarui Kata Sandi
            </h2>
            <p class="mb-0 mt-1 font-body text-sm text-slate-500">
                Akun Anda memerlukan pembaruan kata sandi sebelum dapat
                mengakses fitur sistem.
            </p>
        </div>

        <div class="mb-4">
            <AppAlert variant="warning">
                Demi keamanan akun internal PT Kertas Padalarang, Anda wajib
                mengganti password default Anda.
            </AppAlert>
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-4">
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

            <div class="mt-2">
                <AppButton
                    type="submit"
                    variant="primary"
                    size="lg"
                    :loading="form.processing"
                    class="w-full"
                >
                    Simpan & Masuk ke Sistem
                </AppButton>
            </div>
        </form>
    </GuestLayout>
</template>
