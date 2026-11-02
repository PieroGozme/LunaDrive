<?php

namespace App\Http\Controllers\Drive;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'parent_id' => ['nullable', 'uuid'],
            'color' => ['nullable', 'string', 'max:32'],
            'icon' => ['nullable', 'string', 'max:64'],
        ]);

        if (! empty($data['parent_id'])) {
            Folder::query()
                ->where('user_id', $request->user()->id)
                ->where('id', $data['parent_id'])
                ->where('status', 'active')
                ->firstOrFail();
        }

        Folder::query()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'color' => $data['color'] ?? '#0c8ce7',
            'icon' => $data['icon'] ?? 'folder',
            'status' => 'active',
        ]);

        return back()->with('success', 'Folder created.');
    }

    public function update(Request $request, Folder $folder): RedirectResponse
    {
        abort_unless($folder->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:191'],
            'parent_id' => ['nullable', 'uuid'],
            'color' => ['nullable', 'string', 'max:32'],
            'icon' => ['nullable', 'string', 'max:64'],
        ]);

        $folder->update($data);

        return back()->with('success', 'Folder updated.');
    }

    public function destroy(Request $request, Folder $folder): RedirectResponse
    {
        abort_unless($folder->user_id === $request->user()->id, 403);

        $folder->update([
            'status' => 'trashed',
            'deleted_at' => now(),
        ]);

        return back()->with('success', 'Folder moved to trash.');
    }
}
