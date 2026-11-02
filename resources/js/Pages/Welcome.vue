<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Cloud, FolderTree, Gauge, Shield } from 'lucide-vue-next';

defineProps({
    canLogin: { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
});
</script>

<template>
    <Head title="LunaDrive" />

    <div class="min-h-screen bg-[radial-gradient(ellipse_at_top,_#bfdbfe_0%,_#f8fafc_42%,_#e0e7ff_100%)] text-ink-900">
        <header class="mx-auto flex max-w-6xl items-center justify-between px-6 py-6">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-luna-600 text-white shadow-lg shadow-luna-600/30">
                    <Cloud class="h-5 w-5" />
                </span>
                <span class="text-lg font-semibold tracking-tight">LunaDrive</span>
            </div>
            <div class="flex items-center gap-3">
                <Link :href="route('login')" class="text-sm font-medium text-ink-700 hover:text-ink-950">Iniciar sesión</Link>
                <Link
                    :href="route('register')"
                    class="rounded-xl bg-ink-950 px-4 py-2 text-sm font-medium text-white hover:bg-ink-800"
                >
                    Empezar
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-6 pb-20 pt-10">
            <section class="grid items-center gap-10 lg:grid-cols-2">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-luna-700">Gateway de almacenamiento self-hosted</p>
                    <h1 class="mt-4 text-5xl font-semibold tracking-tight text-ink-950 sm:text-6xl">
                        LunaDrive
                    </h1>
                    <p class="mt-5 max-w-xl text-lg text-ink-600">
                        Conecta varias cuentas de Google Drive y úsalas como una sola unidad virtual, con enrutado inteligente de subidas, carpetas virtuales y un gestor de archivos claro.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <Link
                            :href="route('register')"
                            class="rounded-2xl bg-luna-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-luna-600/30 hover:bg-luna-700"
                        >
                            Crear cuenta
                        </Link>
                        <Link
                            :href="route('login')"
                            class="rounded-2xl border border-ink-300 bg-white/80 px-5 py-3 text-sm font-semibold text-ink-800 backdrop-blur hover:bg-white"
                        >
                            Entrar
                        </Link>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-[2rem] border border-white/70 bg-white/70 p-6 shadow-2xl shadow-luna-900/10 backdrop-blur">
                    <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-luna-300/40 blur-3xl" />
                    <div class="relative space-y-4">
                        <div class="rounded-2xl bg-ink-950 p-4 text-white">
                            <div class="text-xs uppercase tracking-wide text-ink-300">Pool combinado</div>
                            <div class="mt-2 text-3xl font-semibold">45 GB libres</div>
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-ink-700">
                                <div class="h-full w-2/5 rounded-full bg-luna-400" />
                            </div>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl border border-ink-200 bg-white p-4">
                                <Gauge class="h-5 w-5 text-luna-600" />
                                <div class="mt-3 text-sm font-medium">Enrutado por cuota</div>
                            </div>
                            <div class="rounded-2xl border border-ink-200 bg-white p-4">
                                <FolderTree class="h-5 w-5 text-luna-600" />
                                <div class="mt-3 text-sm font-medium">Carpetas virtuales</div>
                            </div>
                            <div class="rounded-2xl border border-ink-200 bg-white p-4">
                                <Shield class="h-5 w-5 text-luna-600" />
                                <div class="mt-3 text-sm font-medium">Tokens cifrados</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
