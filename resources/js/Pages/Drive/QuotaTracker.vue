<script setup>
import DriveLayout from '@/Layouts/DriveLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { RefreshCw } from 'lucide-vue-next';

const props = defineProps({
    accounts: Array,
    policy: Object,
    summary: Object,
});

const form = useForm({
    mode: props.policy.mode,
    priority_account_ids: props.policy.priority_account_ids || [],
});

const syncForm = useForm({});

const formatBytes = (bytes) => {
    if (bytes === null || bytes === undefined) return 'Unknown';
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    let value = Number(bytes);
    let i = 0;
    while (value >= 1024 && i < units.length - 1) {
        value /= 1024;
        i += 1;
    }
    return `${value.toFixed(i === 0 ? 0 : 1)} ${units[i]}`;
};

const percent = (used, quota) => {
    if (!quota) return 0;
    return Math.min(100, Math.round((used / quota) * 100));
};

const savePolicy = () => form.patch(route('quota.policy'));
const sync = () => syncForm.post(route('quota.sync'));
</script>

<template>
    <Head title="Quota Tracker" />

    <DriveLayout>
        <template #header>Quota Tracker</template>

        <div class="space-y-5">
            <div class="flex flex-col gap-4 rounded-3xl border border-ink-200/80 bg-white/80 p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-ink-950">Quota Tracker</h1>
                    <p class="mt-1 text-sm text-ink-500">Combined storage across every connected Google Drive.</p>
                </div>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl border border-ink-200 bg-white px-3 py-2 text-sm font-medium text-ink-700"
                    :disabled="syncForm.processing"
                    @click="sync"
                >
                    <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': syncForm.processing }" />
                    Sync quotas
                </button>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-3xl border border-ink-200 bg-white p-5 shadow-sm">
                    <div class="text-xs uppercase tracking-wide text-ink-500">Total quota</div>
                    <div class="mt-2 text-2xl font-semibold text-ink-950">{{ formatBytes(summary.total_quota_bytes) }}</div>
                </div>
                <div class="rounded-3xl border border-ink-200 bg-white p-5 shadow-sm">
                    <div class="text-xs uppercase tracking-wide text-ink-500">Used</div>
                    <div class="mt-2 text-2xl font-semibold text-ink-950">{{ formatBytes(summary.total_used_bytes) }}</div>
                </div>
                <div class="rounded-3xl border border-ink-200 bg-white p-5 shadow-sm">
                    <div class="text-xs uppercase tracking-wide text-ink-500">Available</div>
                    <div class="mt-2 text-2xl font-semibold text-luna-700">{{ formatBytes(summary.total_available_bytes) }}</div>
                </div>
            </div>

            <div class="grid gap-5 lg:grid-cols-[1.4fr_1fr]">
                <div class="space-y-3 rounded-3xl border border-ink-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-ink-950">Accounts</h2>
                    <div v-if="accounts.length === 0" class="text-sm text-ink-500">No Drive accounts connected yet.</div>
                    <div v-for="account in accounts" :key="account.id" class="rounded-2xl border border-ink-100 bg-ink-50/50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-medium text-ink-900">{{ account.display_name || account.email }}</div>
                                <div class="text-xs text-ink-500">{{ account.email }} · {{ account.status }}</div>
                            </div>
                            <div class="text-right text-sm text-ink-600">
                                {{ formatBytes(account.used_bytes) }} / {{ formatBytes(account.quota_bytes) }}
                            </div>
                        </div>
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-ink-200">
                            <div
                                class="h-full rounded-full bg-luna-600"
                                :style="{ width: percent(account.used_bytes, account.quota_bytes) + '%' }"
                            />
                        </div>
                    </div>
                </div>

                <form class="rounded-3xl border border-ink-200 bg-white p-5 shadow-sm" @submit.prevent="savePolicy">
                    <h2 class="text-lg font-semibold text-ink-950">Upload routing</h2>
                    <p class="mt-1 text-sm text-ink-500">Choose how LunaDrive picks a Drive account for each upload.</p>

                    <div class="mt-4 space-y-3">
                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-ink-200 p-3" :class="form.mode === 'most_available' ? 'border-luna-400 bg-luna-50' : ''">
                            <input v-model="form.mode" type="radio" value="most_available" class="mt-1" />
                            <span>
                                <span class="block font-medium text-ink-900">Most available</span>
                                <span class="text-xs text-ink-500">Prefer the account with the most free space.</span>
                            </span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-ink-200 p-3" :class="form.mode === 'round_robin' ? 'border-luna-400 bg-luna-50' : ''">
                            <input v-model="form.mode" type="radio" value="round_robin" class="mt-1" />
                            <span>
                                <span class="block font-medium text-ink-900">Round robin</span>
                                <span class="text-xs text-ink-500">Rotate across accounts that fit the file.</span>
                            </span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-ink-200 p-3" :class="form.mode === 'priority' ? 'border-luna-400 bg-luna-50' : ''">
                            <input v-model="form.mode" type="radio" value="priority" class="mt-1" />
                            <span>
                                <span class="block font-medium text-ink-900">Priority order</span>
                                <span class="text-xs text-ink-500">Use the first priority account that has room.</span>
                            </span>
                        </label>
                    </div>

                    <div v-if="form.mode === 'priority'" class="mt-4 space-y-2">
                        <div class="text-sm font-medium text-ink-800">Priority accounts</div>
                        <label v-for="account in accounts" :key="account.id" class="flex items-center gap-2 text-sm text-ink-700">
                            <input v-model="form.priority_account_ids" type="checkbox" :value="account.id" />
                            {{ account.email }}
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="mt-5 rounded-xl bg-luna-600 px-4 py-2 text-sm font-medium text-white"
                        :disabled="form.processing"
                    >
                        Save routing
                    </button>
                </form>
            </div>
        </div>
    </DriveLayout>
</template>
