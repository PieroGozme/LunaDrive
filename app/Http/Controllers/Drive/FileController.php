<?php

namespace App\Http\Controllers\Drive;

use App\Http\Controllers\Controller;
use App\Models\DriveFile;
use App\Services\Google\GoogleDriveUploader;
use App\Services\Storage\UploadRouter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function store(Request $request, UploadRouter $router, GoogleDriveUploader $uploader): RedirectResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'max:'.(int) (config('lunadrive.max_upload_bytes') / 1024)],
            'folder_id' => ['nullable', 'uuid'],
        ]);

        $uploaded = $request->file('file');
        $account = $router->selectAccount($request->user(), (int) $uploaded->getSize());
        $uploader->upload($account, $uploaded, $data['folder_id'] ?? null);

        return back()->with('success', 'File uploaded.');
    }

    public function update(Request $request, DriveFile $file): RedirectResponse
    {
        abort_unless($file->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:191'],
            'folder_id' => ['nullable', 'uuid'],
            'starred' => ['sometimes', 'boolean'],
        ]);

        $file->update($data);

        return back()->with('success', 'File updated.');
    }

    public function destroy(Request $request, DriveFile $file): RedirectResponse
    {
        abort_unless($file->user_id === $request->user()->id, 403);

        $file->update([
            'status' => 'trashed',
            'deleted_at' => now(),
        ]);

        return back()->with('success', 'File moved to trash.');
    }

    public function download(Request $request, DriveFile $file, GoogleDriveUploader $uploader): StreamedResponse
    {
        abort_unless($file->user_id === $request->user()->id, 403);

        $body = $uploader->streamDownload($file->connectedAccount, $file->provider_file_id);

        return response()->streamDownload(function () use ($body) {
            echo $body->getBody()->getContents();
        }, $file->name, [
            'Content-Type' => $file->mime_type ?: 'application/octet-stream',
        ]);
    }

    public function preview(Request $request, DriveFile $file, GoogleDriveUploader $uploader)
    {
        abort_unless($file->user_id === $request->user()->id, 403);

        $body = $uploader->streamDownload($file->connectedAccount, $file->provider_file_id);

        return response($body->getBody()->getContents(), 200, [
            'Content-Type' => $file->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.$file->name.'"',
        ]);
    }
}
