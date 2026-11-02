<script setup>
import DriveLayout from '@/Layouts/DriveLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Download,
    File as FileIcon,
    FolderPlus,
    Grid3X3,
    List,
    MoreHorizontal,
    Pencil,
    Trash2,
    Upload,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    folders: Array,
    files: Array,
    currentFolder: Object,
    breadcrumbs: Array,
    viewMode: String,
    query: String,
    accountsCount: Number,
});

const search = ref(props.query || '');
const showNewFolder = ref(false);
const uploadInput = ref(null);
const renaming = ref(null);

const folderForm = useForm({
    name: '',
    parent_id: props.currentFolder?.id || null,
    color: '#0c8ce7',
});

const uploadForm = useForm({
    file: null,
    folder_id: props.currentFolder?.id || null,
});

const renameForm = useForm({
    name: '',
});

const formatBytes = (bytes) => {
    if (!bytes && bytes !== 0) return '—';
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    let value = Number(bytes);
    let i = 0;
    while (value >= 1024 && i < units.length - 1) {
        value /= 1024;
        i += 1;
    }
    return `${value.toFixed(i === 0 ? 0 : 1)} ${units[i]}`;
};

const setView = (mode) => {
    router.get(
        route('files.index'),
        { folder: props.currentFolder?.id || undefined, view: mode, q: search.value || undefined },
        { preserveState: true, replace: true },
    );
};

const runSearch = () => {
    router.get(
        route('files.index'),
        { q: search.value || undefined, view: props.viewMode },
        { preserveState: true, replace: true },
    );
};

const createFolder = () => {
    folderForm.post(route('folders.store'), {
        onSuccess: () => {
            folderForm.reset('name');
            showNewFolder.value = false;
        },
    });
};

const onFilePicked = (event) => {
    const file = event.target.files?.[0];
    if (!file) return;
    uploadForm.file = file;
    uploadForm.post(route('files.store'), {
        forceFormData: true,
        onFinish: () => {
            uploadForm.reset('file');
            if (uploadInput.value) uploadInput.value.value = '';
        },
    });
};

const startRename = (item, type) => {
    renaming.value = { id: item.id, type, name: item.name };
    renameForm.name = item.name;
};

const submitRename = () => {
    if (!renaming.value) return;
    const { id, type } = renaming.value;
    const url = type === 'folder' ? route('folders.update', id) : route('files.update', id);
    renameForm.patch(url, {
        onSuccess: () => {
            renaming.value = null;
        },
    });
};

const removeItem = (item, type) => {
    if (!confirm(`¿Mover "${item.name}" a la papelera?`)) return;
    const url = type === 'folder' ? route('folders.destroy', item.id) : route('files.destroy', item.id);
    router.delete(url);
};

const empty = computed(() => props.folders.length === 0 && props.files.length === 0);
</script>

<template>
    <Head title="Todos los archivos" />

    <DriveLayout>
        <template #header>
            <div class="flex items-center gap-2 text-ink-600">
                <Link :href="route('files.index')" class="hover:text-luna-700">Todos los archivos</Link>
                <template v-for="crumb in breadcrumbs" :key="crumb.id">
                    <span>/</span>
                    <Link :href="route('files.index', { folder: crumb.id })" class="hover:text-luna-700">
                        {{ crumb.name }}
                    </Link>
                </template>
            </div>
        </template>

        <div class="space-y-5">
            <div class="flex flex-col gap-4 rounded-3xl border border-ink-200/80 bg-white/80 p-5 shadow-sm backdrop-blur sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-ink-950">Todos los archivos</h1>
                    <p class="mt-1 text-sm text-ink-500">
                        Carpetas virtuales sobre tu pool de Google Drive conectado.
                        <span v-if="accountsCount === 0" class="text-amber-700"> Conecta un Drive en Ajustes para subir archivos.</span>
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-ink-200 bg-white px-3 py-2 text-sm font-medium text-ink-700 hover:bg-ink-50"
                        @click="showNewFolder = !showNewFolder"
                    >
                        <FolderPlus class="h-4 w-4" />
                        Nueva carpeta
                    </button>
                    <label
                        class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-luna-600 px-3 py-2 text-sm font-medium text-white shadow-md shadow-luna-600/25 hover:bg-luna-700"
                        :class="{ 'pointer-events-none opacity-60': uploadForm.processing || accountsCount === 0 }"
                    >
                        <Upload class="h-4 w-4" />
                        {{ uploadForm.processing ? 'Subiendo…' : 'Subir' }}
                        <input ref="uploadInput" type="file" class="hidden" @change="onFilePicked" />
                    </label>
                </div>
            </div>

            <div
                v-if="showNewFolder"
                class="rounded-2xl border border-luna-200 bg-luna-50/70 p-4"
            >
                <form class="flex flex-col gap-3 sm:flex-row" @submit.prevent="createFolder">
                    <input
                        v-model="folderForm.name"
                        type="text"
                        placeholder="Nombre de la carpeta"
                        class="flex-1 rounded-xl border-ink-200 text-sm"
                        required
                    />
                    <button
                        type="submit"
                        class="rounded-xl bg-luna-600 px-4 py-2 text-sm font-medium text-white"
                        :disabled="folderForm.processing"
                    >
                        Crear
                    </button>
                </form>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form class="flex w-full max-w-md gap-2" @submit.prevent="runSearch">
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Buscar archivos y carpetas"
                        class="w-full rounded-xl border-ink-200 text-sm"
                    />
                    <button type="submit" class="rounded-xl border border-ink-200 bg-white px-3 py-2 text-sm">
                        Buscar
                    </button>
                </form>
                <div class="inline-flex rounded-xl border border-ink-200 bg-white p-1">
                    <button
                        type="button"
                        class="rounded-lg px-2.5 py-1.5"
                        :class="viewMode === 'list' ? 'bg-ink-100 text-ink-900' : 'text-ink-500'"
                        @click="setView('list')"
                    >
                        <List class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        class="rounded-lg px-2.5 py-1.5"
                        :class="viewMode === 'grid' ? 'bg-ink-100 text-ink-900' : 'text-ink-500'"
                        @click="setView('grid')"
                    >
                        <Grid3X3 class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <div v-if="empty" class="rounded-3xl border border-dashed border-ink-300 bg-white/60 px-6 py-16 text-center">
                <p class="text-lg font-medium text-ink-800">Aún no hay nada</p>
                <p class="mt-2 text-sm text-ink-500">Crea una carpeta o sube tu primer archivo.</p>
            </div>

            <div v-else-if="viewMode === 'grid'" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Link
                    v-for="folder in folders"
                    :key="folder.id"
                    :href="route('files.index', { folder: folder.id, view: 'grid' })"
                    class="rounded-2xl border border-ink-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl" :style="{ background: folder.color || '#0c8ce7' }">
                        <FolderPlus class="h-5 w-5 text-white" />
                    </div>
                    <div class="truncate font-medium text-ink-900">{{ folder.name }}</div>
                    <div class="text-xs text-ink-500">Carpeta</div>
                </Link>
                <div
                    v-for="file in files"
                    :key="file.id"
                    class="rounded-2xl border border-ink-200 bg-white p-4 shadow-sm"
                >
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-ink-100">
                        <FileIcon class="h-5 w-5 text-ink-600" />
                    </div>
                    <div class="truncate font-medium text-ink-900">{{ file.name }}</div>
                    <div class="text-xs text-ink-500">{{ formatBytes(file.size_bytes) }}</div>
                    <div class="mt-3 flex gap-2">
                        <a :href="route('files.download', file.id)" class="rounded-lg border border-ink-200 px-2 py-1 text-xs">Descargar</a>
                        <button type="button" class="rounded-lg border border-ink-200 px-2 py-1 text-xs" @click="startRename(file, 'file')">Renombrar</button>
                        <button type="button" class="rounded-lg border border-rose-200 px-2 py-1 text-xs text-rose-700" @click="removeItem(file, 'file')">Eliminar</button>
                    </div>
                </div>
            </div>

            <div v-else class="overflow-hidden rounded-3xl border border-ink-200 bg-white/90 shadow-sm">
                <table class="min-w-full divide-y divide-ink-100 text-sm">
                    <thead class="bg-ink-50/80 text-left text-xs uppercase tracking-wide text-ink-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Nombre</th>
                            <th class="px-4 py-3 font-medium">Cuenta</th>
                            <th class="px-4 py-3 font-medium">Tamaño</th>
                            <th class="px-4 py-3 font-medium">Actualizado</th>
                            <th class="px-4 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        <tr v-for="folder in folders" :key="folder.id" class="hover:bg-ink-50/60">
                            <td class="px-4 py-3">
                                <Link :href="route('files.index', { folder: folder.id })" class="inline-flex items-center gap-2 font-medium text-ink-900">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg" :style="{ background: (folder.color || '#0c8ce7') + '22', color: folder.color || '#0c8ce7' }">
                                        <FolderPlus class="h-4 w-4" />
                                    </span>
                                    {{ folder.name }}
                                </Link>
                            </td>
                            <td class="px-4 py-3 text-ink-500">—</td>
                            <td class="px-4 py-3 text-ink-500">—</td>
                            <td class="px-4 py-3 text-ink-500">{{ new Date(folder.updated_at).toLocaleString() }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1">
                                    <button type="button" class="rounded-lg p-1.5 text-ink-500 hover:bg-ink-100" @click="startRename(folder, 'folder')"><Pencil class="h-4 w-4" /></button>
                                    <button type="button" class="rounded-lg p-1.5 text-rose-600 hover:bg-rose-50" @click="removeItem(folder, 'folder')"><Trash2 class="h-4 w-4" /></button>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="file in files" :key="file.id" class="hover:bg-ink-50/60">
                            <td class="px-4 py-3">
                                <div class="inline-flex items-center gap-2 font-medium text-ink-900">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-ink-100 text-ink-600">
                                        <FileIcon class="h-4 w-4" />
                                    </span>
                                    {{ file.name }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-ink-500">{{ file.connected_account?.email || '—' }}</td>
                            <td class="px-4 py-3 text-ink-500">{{ formatBytes(file.size_bytes) }}</td>
                            <td class="px-4 py-3 text-ink-500">{{ new Date(file.updated_at).toLocaleString() }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1">
                                    <a :href="route('files.preview', file.id)" target="_blank" class="rounded-lg p-1.5 text-ink-500 hover:bg-ink-100"><MoreHorizontal class="h-4 w-4" /></a>
                                    <a :href="route('files.download', file.id)" class="rounded-lg p-1.5 text-ink-500 hover:bg-ink-100"><Download class="h-4 w-4" /></a>
                                    <button type="button" class="rounded-lg p-1.5 text-ink-500 hover:bg-ink-100" @click="startRename(file, 'file')"><Pencil class="h-4 w-4" /></button>
                                    <button type="button" class="rounded-lg p-1.5 text-rose-600 hover:bg-rose-50" @click="removeItem(file, 'file')"><Trash2 class="h-4 w-4" /></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="renaming" class="fixed inset-0 z-50 flex items-center justify-center bg-ink-950/40 p-4">
            <form class="w-full max-w-md rounded-2xl bg-white p-5 shadow-xl" @submit.prevent="submitRename">
                <h3 class="text-lg font-semibold text-ink-950">Renombrar</h3>
                <input v-model="renameForm.name" type="text" class="mt-3 w-full rounded-xl border-ink-200 text-sm" required />
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" class="rounded-xl border border-ink-200 px-3 py-2 text-sm" @click="renaming = null">Cancelar</button>
                    <button type="submit" class="rounded-xl bg-luna-600 px-3 py-2 text-sm text-white">Guardar</button>
                </div>
            </form>
        </div>
    </DriveLayout>
</template>
