<script setup>
import DriveLayout from '@/Layouts/DriveLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Link2, Trash2 } from 'lucide-vue-next';

const props = defineProps({
    googleConfigured: Boolean,
    googleRedirectUri: String,
    accounts: Array,
});

const form = useForm({
    client_id: '',
    client_secret: '',
    redirect_uri: props.googleRedirectUri,
});

const formatBytes = (bytes) => {
    if (bytes === null || bytes === undefined) return '—';
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    let value = Number(bytes);
    let i = 0;
    while (value >= 1024 && i < units.length - 1) {
        value /= 1024;
        i += 1;
    }
    return `${value.toFixed(i === 0 ? 0 : 1)} ${units[i]}`;
};

const save = () => form.post(route('settings.google'));
const disconnect = (id) => {
    if (!confirm('Disconnect this Google Drive account?')) return;
    router.delete(route('settings.accounts.destroy', id));
};
</script>

<template>
    <Head title="Settings" />

    <DriveLayout>
        <template #header>Settings</template>

        <div class="space-y-5">
            <div class="rounded-3xl border border-ink-200/80 bg-white/80 p-5 shadow-sm">
                <h1 class="text-2xl font-semibold tracking-tight text-ink-950">Settings</h1>
                <p class="mt-1 text-sm text-ink-500">Google OAuth credentials and connected Drive accounts.</p>
            </div>

            <form class="rounded-3xl border border-ink-200 bg-white p-5 shadow-sm" @submit.prevent="save">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-ink-950">Google credentials</h2>
                        <p class="mt-1 text-sm text-ink-500">
                            Create an OAuth Web client in Google Cloud, enable Drive API, then paste the values here.
                        </p>
                    </div>
                    <span
                        class="rounded-full px-3 py-1 text-xs font-medium"
                        :class="googleConfigured ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                    >
                        {{ googleConfigured ? 'Configured' : 'Missing' }}
                    </span>
                </div>

                <div class="mt-5 grid gap-4">
                    <label class="block text-sm">
                        <span class="mb-1 block font-medium text-ink-700">Client ID</span>
                        <input v-model="form.client_id" type="text" class="w-full rounded-xl border-ink-200" required />
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block font-medium text-ink-700">Client Secret</span>
                        <input v-model="form.client_secret" type="password" class="w-full rounded-xl border-ink-200" required />
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block font-medium text-ink-700">Redirect URI</span>
                        <input v-model="form.redirect_uri" type="url" class="w-full rounded-xl border-ink-200" required />
                        <span class="mt-1 block text-xs text-ink-500">Must match Google Cloud authorized redirect URIs.</span>
                    </label>
                </div>

                <button type="submit" class="mt-5 rounded-xl bg-luna-600 px-4 py-2 text-sm font-medium text-white" :disabled="form.processing">
                    Save configuration
                </button>
            </form>

            <div class="rounded-3xl border border-ink-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-ink-950">Connected Drive accounts</h2>
                        <p class="mt-1 text-sm text-ink-500">Each account becomes part of your virtual storage pool.</p>
                    </div>
                    <a
                        v-if="googleConfigured"
                        :href="route('settings.google.connect')"
                        class="inline-flex items-center gap-2 rounded-xl bg-ink-900 px-3 py-2 text-sm font-medium text-white"
                    >
                        <Link2 class="h-4 w-4" />
                        Connect Google Drive
                    </a>
                    <span
                        v-else
                        class="inline-flex items-center gap-2 rounded-xl bg-ink-300 px-3 py-2 text-sm font-medium text-white"
                    >
                        <Link2 class="h-4 w-4" />
                        Connect Google Drive
                    </span>
                </div>

                <div v-if="accounts.length === 0" class="mt-6 rounded-2xl border border-dashed border-ink-300 px-4 py-10 text-center text-sm text-ink-500">
                    No accounts connected yet.
                </div>

                <div v-else class="mt-5 space-y-3">
                    <div
                        v-for="account in accounts"
                        :key="account.id"
                        class="flex flex-col gap-3 rounded-2xl border border-ink-100 bg-ink-50/60 p-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <img
                                v-if="account.avatar_url"
                                :src="account.avatar_url"
                                alt=""
                                class="h-10 w-10 rounded-full"
                            />
                            <div
                                v-else
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-luna-100 text-sm font-semibold text-luna-700"
                            >
                                {{ (account.email || '?').slice(0, 1).toUpperCase() }}
                            </div>
                            <div>
                                <div class="font-medium text-ink-900">{{ account.display_name || account.email }}</div>
                                <div class="text-xs text-ink-500">
                                    {{ account.email }} · {{ account.status }} ·
                                    {{ formatBytes(account.used_bytes) }} / {{ formatBytes(account.quota_bytes) }}
                                </div>
                                <div v-if="account.last_error" class="mt-1 text-xs text-rose-600">{{ account.last_error }}</div>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl border border-rose-200 px-3 py-2 text-sm text-rose-700"
                            @click="disconnect(account.id)"
                        >
                            <Trash2 class="h-4 w-4" />
                            Disconnect
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DriveLayout>
</template>
