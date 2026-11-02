<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import {
    Cloud,
    Files,
    Gauge,
    LogOut,
    Settings,
    User as UserIcon,
} from 'lucide-vue-next';
import { computed } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash || {});

const nav = [
    { name: 'All Files', href: route('files.index'), icon: Files, match: 'files.*' },
    { name: 'Quota Tracker', href: route('quota.index'), icon: Gauge, match: 'quota.*' },
    { name: 'Settings', href: route('settings.index'), icon: Settings, match: 'settings.*' },
];

const isActive = (match) => {
    const current = route().current();
    if (!current) return false;
    if (match.endsWith('.*')) {
        return current.startsWith(match.slice(0, -2));
    }
    return current === match;
};
</script>

<template>
    <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_#dbeafe_0%,_#f6f7f9_40%,_#eef2ff_100%)]">
        <div class="mx-auto flex min-h-screen max-w-[1400px]">
            <aside class="hidden w-64 shrink-0 border-r border-ink-200/70 bg-white/70 p-5 backdrop-blur md:flex md:flex-col">
                <Link :href="route('files.index')" class="mb-8 flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-luna-600 text-white shadow-lg shadow-luna-600/30">
                        <Cloud class="h-5 w-5" />
                    </span>
                    <div>
                        <div class="text-lg font-semibold tracking-tight text-ink-950">LunaDrive</div>
                        <div class="text-xs text-ink-500">One pool. Many drives.</div>
                    </div>
                </Link>

                <nav class="space-y-1">
                    <Link
                        v-for="item in nav"
                        :key="item.name"
                        :href="item.href"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition"
                        :class="
                            isActive(item.match)
                                ? 'bg-luna-600 text-white shadow-md shadow-luna-600/25'
                                : 'text-ink-600 hover:bg-ink-100 hover:text-ink-900'
                        "
                    >
                        <component :is="item.icon" class="h-4 w-4" />
                        {{ item.name }}
                    </Link>
                </nav>

                <div class="mt-auto rounded-2xl border border-ink-200 bg-ink-50 p-4 text-xs text-ink-600">
                    Uploads route to the Drive account with enough free space. Files stay in Google; folders are virtual.
                </div>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="flex items-center justify-between gap-4 border-b border-ink-200/70 bg-white/60 px-4 py-3 backdrop-blur sm:px-6">
                    <div class="md:hidden">
                        <Link :href="route('files.index')" class="flex items-center gap-2 font-semibold text-ink-950">
                            <Cloud class="h-5 w-5 text-luna-600" />
                            LunaDrive
                        </Link>
                    </div>
                    <div class="hidden text-sm text-ink-500 md:block">
                        <slot name="header" />
                    </div>
                    <div class="ml-auto flex items-center gap-3">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white px-3 py-1.5 text-sm text-ink-700 shadow-sm"
                                >
                                    <UserIcon class="h-4 w-4" />
                                    {{ user?.name }}
                                </button>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                                <DropdownLink :href="route('settings.index')">Settings</DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    <span class="inline-flex items-center gap-2"><LogOut class="h-3.5 w-3.5" /> Log out</span>
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </header>

                <main class="flex-1 p-4 sm:p-6">
                    <div
                        v-if="flash.success"
                        class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                    >
                        {{ flash.success }}
                    </div>
                    <div
                        v-if="flash.error"
                        class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"
                    >
                        {{ flash.error }}
                    </div>
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
