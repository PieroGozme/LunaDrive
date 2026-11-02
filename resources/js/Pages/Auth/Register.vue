<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Registro" />

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
                <InputLabel for="name" value="Nombre" />
                <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required autofocus autocomplete="name" />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Correo" />
                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Contraseña" />
                <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirmar contraseña" />
                <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="mt-6 flex items-center justify-end gap-4">
                <Link :href="route('login')" class="text-sm font-medium text-ink-700 underline hover:text-ink-950">
                    ¿Ya tienes cuenta?
                </Link>
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Registrarse
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
