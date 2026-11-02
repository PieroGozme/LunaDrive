<?php

namespace App\Http\Controllers\Drive;

use App\Http\Controllers\Controller;
use App\Models\DriveFile;
use App\Models\Folder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FileManagerController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $folderId = $request->query('folder');
        $view = $request->query('view', 'list');
        $q = trim((string) $request->query('q', ''));

        $foldersQuery = Folder::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereNull('deleted_at');

        $filesQuery = DriveFile::query()
            ->with('connectedAccount:id,email,display_name')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereNull('deleted_at');

        if ($q !== '') {
            $foldersQuery->where('name', 'ilike', "%{$q}%");
            $filesQuery->where('name', 'ilike', "%{$q}%");
        } else {
            $foldersQuery->where('parent_id', $folderId);
            $filesQuery->where('folder_id', $folderId);
        }

        $currentFolder = $folderId
            ? Folder::query()->where('user_id', $user->id)->where('id', $folderId)->first()
            : null;

        $breadcrumbs = $this->breadcrumbs($currentFolder);

        return Inertia::render('Drive/AllFiles', [
            'folders' => $foldersQuery->orderBy('name')->get(),
            'files' => $filesQuery->orderByDesc('updated_at')->get(),
            'currentFolder' => $currentFolder,
            'breadcrumbs' => $breadcrumbs,
            'viewMode' => in_array($view, ['list', 'grid'], true) ? $view : 'list',
            'query' => $q,
            'accountsCount' => $user->connectedAccounts()->where('status', 'connected')->count(),
        ]);
    }

    private function breadcrumbs(?Folder $folder): array
    {
        $crumbs = [];
        $cursor = $folder;
        while ($cursor) {
            array_unshift($crumbs, [
                'id' => $cursor->id,
                'name' => $cursor->name,
            ]);
            $cursor = $cursor->parent;
        }

        return $crumbs;
    }
}
