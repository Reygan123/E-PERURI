<script setup lang="ts">
import AppAlert from '@/Components/AppAlert.vue';
import AppButton from '@/Components/AppButton.vue';
import AppInput from '@/Components/AppInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
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

        <div class="mb-5 text-center">
            <h2 class="m-0 font-heading text-xl font-bold text-ptkp-navy">
                Masuk ke Portal Internal
            </h2>
            <p class="mb-0 mt-1 font-body text-sm text-slate-500">
                Silakan masukkan nama pengguna atau email dinas Anda
            </p>
        </div>

        <div v-if="status" class="mb-4">
            <AppAlert variant="success" dismissible>
                {{ status }}
            </AppAlert>
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-4">
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

            <div class="flex items-center justify-between text-xs">
                <label
                    class="flex cursor-pointer items-center gap-2 text-ptkp-body"
                >
                    <input
                        type="checkbox"
                        v-model="form.remember"
                        class="rounded-md border-slate-300 text-ptkp-navy focus:ring-ptkp-navy"
                    />
                    <span>Ingat saya di perangkat ini</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="font-medium text-ptkp-navy hover:underline"
                >
                    Lupa kata sandi?
                </Link>
            </div>

            <div class="mt-2">
                <AppButton
                    type="submit"
                    variant="primary"
                    size="lg"
                    :loading="form.processing"
                    class="w-full"
                >
                    Masuk ke Sistem
                </AppButton>
            </div>
        </form>
    </GuestLayout>
</template>
