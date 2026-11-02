<script setup>
import { Link } from '@inertiajs/vue3';
import { Cloud } from 'lucide-vue-next';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-[radial-gradient(circle_at_top,_#dbeafe_0%,_#f8fafc_45%,_#eef2ff_100%)] px-4 py-10">
        <Link href="/" class="mb-8 flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-luna-600 text-white shadow-lg shadow-luna-600/30">
                <Cloud class="h-5 w-5" />
            </span>
            <div>
                <div class="text-xl font-semibold tracking-tight text-ink-950">LunaDrive</div>
                <div class="text-xs text-ink-500">All your free cloud storage, in one drive</div>
            </div>
        </Link>

        <div class="w-full max-w-md rounded-3xl border border-ink-200/80 bg-white/90 p-6 shadow-xl shadow-ink-900/5 backdrop-blur">
            <slot />
        </div>
    </div>
</template>
