<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import {
    ChevronsLeft,
    ChevronsRight,
    Cloud,
    Files,
    Gauge,
    LogOut,
    Menu,
    Settings,
    User as UserIcon,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash || {});

const collapsed = ref(false);
const mobileOpen = ref(false);

const nav = [
    { name: 'Todos los archivos', href: route('files.index'), icon: Files, match: 'files.*' },
    { name: 'Cuota', href: route('quota.index'), icon: Gauge, match: 'quota.*' },
    { name: 'Ajustes', href: route('settings.index'), icon: Settings, match: 'settings.*' },
];

const isActive = (match) => {
    const current = route().current();
    if (!current) return false;
    if (match.endsWith('.*')) {
        return current.startsWith(match.slice(0, -2));
    }
    return current === match;
};

onMounted(() => {
    collapsed.value = localStorage.getItem('lunadrive.sidebarCollapsed') === '1';
});

watch(collapsed, (value) => {
    localStorage.setItem('lunadrive.sidebarCollapsed', value ? '1' : '0');
});

const toggleCollapsed = () => {
    collapsed.value = !collapsed.value;
};

const closeMobile = () => {
    mobileOpen.value = false;
};
</script>

<template>
    <div class="flex min-h-screen w-full bg-[radial-gradient(circle_at_top_left,_#dbeafe_0%,_#f6f7f9_40%,_#eef2ff_100%)]">
        <div
            v-if="mobileOpen"
            class="fixed inset-0 z-30 bg-ink-950/40 md:hidden"
            @click="closeMobile"
        />

        <button
            type="button"
            class="fixed left-4 top-4 z-20 inline-flex h-10 w-10 items-center justify-center rounded-xl border border-ink-200 bg-white text-ink-700 shadow-sm md:hidden"
            @click="mobileOpen = true"
        >
            <Menu class="h-5 w-5" />
        </button>

        <aside
            class="fixed inset-y-0 left-0 z-40 flex h-screen flex-col overflow-visible border-r border-ink-200/70 bg-white/95 backdrop-blur transition-all duration-200 md:sticky md:translate-x-0"
            :class="[
                collapsed ? 'w-[4.5rem] p-3' : 'w-64 p-4',
                mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
            ]"
        >
            <div
                class="mb-6 flex items-start gap-2"
                :class="collapsed ? 'flex-col items-center' : 'justify-between'"
            >
                <Link
                    :href="route('files.index')"
                    class="flex min-w-0 items-center gap-3"
                    :class="collapsed ? 'justify-center' : ''"
                    @click="closeMobile"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-luna-600 text-white shadow-lg shadow-luna-600/30">
                        <Cloud class="h-5 w-5" />
                    </span>
                    <div v-if="!collapsed" class="min-w-0">
                        <div class="truncate text-lg font-semibold tracking-tight text-ink-950">LunaDrive</div>
                        <div class="truncate text-xs text-ink-500">Un pool. Varias unidades.</div>
                    </div>
                </Link>

                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        class="hidden h-8 w-8 items-center justify-center rounded-lg text-ink-500 hover:bg-ink-100 hover:text-ink-800 md:inline-flex"
                        :title="collapsed ? 'Expandir menú' : 'Colapsar menú'"
                        @click="toggleCollapsed"
                    >
                        <ChevronsLeft v-if="!collapsed" class="h-4 w-4" />
                        <ChevronsRight v-else class="h-4 w-4" />
                    </button>

                    <button
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-ink-500 hover:bg-ink-100 hover:text-ink-800 md:hidden"
                        @click="closeMobile"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <nav class="space-y-1">
                <Link
                    v-for="item in nav"
                    :key="item.name"
                    :href="item.href"
                    :title="item.name"
                    class="flex items-center rounded-xl text-sm font-medium transition"
                    :class="[
                        collapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2.5',
                        isActive(item.match)
                            ? 'bg-luna-600 text-white shadow-md shadow-luna-600/25'
                            : 'text-ink-600 hover:bg-ink-100 hover:text-ink-900',
                    ]"
                    @click="closeMobile"
                >
                    <component :is="item.icon" class="h-4 w-4 shrink-0" />
                    <span v-if="!collapsed" class="truncate">{{ item.name }}</span>
                </Link>
            </nav>

            <div class="mt-auto pt-4">
                <Dropdown
                    :placement="collapsed ? 'right' : 'top'"
                    align="left"
                    width="48"
                    content-classes="py-1 bg-white"
                >
                    <template #trigger>
                        <button
                            type="button"
                            class="flex w-full items-center rounded-2xl border border-ink-200 bg-white text-left text-sm text-ink-700 shadow-sm hover:bg-ink-50"
                            :class="collapsed ? 'justify-center p-2' : 'gap-3 px-3 py-2.5'"
                            :title="user?.name"
                        >
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-luna-100 text-luna-700">
                                <UserIcon class="h-4 w-4" />
                            </span>
                            <span v-if="!collapsed" class="min-w-0 flex-1">
                                <span class="block truncate font-medium text-ink-900">{{ user?.name }}</span>
                                <span class="block truncate text-xs text-ink-500">{{ user?.email }}</span>
                            </span>
                        </button>
                    </template>
                    <template #content>
                        <DropdownLink :href="route('profile.edit')">Perfil</DropdownLink>
                        <DropdownLink :href="route('settings.index')">Ajustes</DropdownLink>
                        <DropdownLink :href="route('logout')" method="post" as="button">
                            <span class="inline-flex items-center gap-2">
                                <LogOut class="h-3.5 w-3.5" />
                                Cerrar sesión
                            </span>
                        </DropdownLink>
                    </template>
                </Dropdown>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <main class="w-full flex-1 px-4 py-4 pt-16 sm:px-6 sm:py-6 md:pt-6 lg:px-8">
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
                <div class="w-full">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
