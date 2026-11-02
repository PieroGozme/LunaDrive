<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Iniciar sesión" />

        <div v-if="status" class="mb-4 text-sm font-medium text-emerald-600">
            {{ status }}
        </div>

        <a
            :href="route('auth.google')"
            class="mb-5 flex w-full items-center justify-center gap-2 rounded-xl border border-ink-200 bg-white px-4 py-2.5 text-sm font-medium text-ink-800 hover:bg-ink-50"
        >
            Continuar con Google
        </a>

        <div class="mb-5 flex items-center gap-3 text-xs font-semibold uppercase tracking-wide text-ink-600">
            <div class="h-px flex-1 bg-ink-300" />
            o con correo
            <div class="h-px flex-1 bg-ink-300" />
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Correo" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Contraseña" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="current-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-sm text-ink-600">Recordarme</span>
                </label>
            </div>

            <div class="mt-4 flex items-center justify-between">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm font-medium text-ink-700 underline hover:text-ink-950"
                >
                    ¿Olvidaste tu contraseña?
                </Link>
                <PrimaryButton class="ms-auto" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Entrar
                </PrimaryButton>
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-ink-600">
            ¿No tienes cuenta?
            <Link :href="route('register')" class="font-medium text-luna-700 hover:underline">Regístrate</Link>
        </p>
    </GuestLayout>
</template>
