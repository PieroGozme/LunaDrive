<?php

namespace App\Services\Google;

use App\Models\ConnectedAccount;
use App\Models\DriveFile;
use Google\Http\MediaFileUpload;
use Google\Service\Drive;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class GoogleDriveUploader
{
    public function __construct(private readonly GoogleClientFactory $clients) {}

    public function upload(ConnectedAccount $account, UploadedFile $file, ?string $folderId = null): DriveFile
    {
        $client = $this->clients->forAccount($account);
        $drive = new Drive($client);
        $parent = $this->clients->ensureAppFolder($account);

        $meta = new Drive\DriveFile([
            'name' => $file->getClientOriginalName(),
            'parents' => [$parent],
        ]);

        $client->setDefer(true);
        $request = $drive->files->create($meta, [
            'fields' => 'id,name,mimeType,size',
        ]);

        $chunkSize = 5 * 1024 * 1024;
        $media = new MediaFileUpload(
            $client,
            $request,
            $file->getMimeType() ?: 'application/octet-stream',
            null,
            true,
            $chunkSize,
        );
        $media->setFileSize($file->getSize());

        $handle = fopen($file->getRealPath(), 'rb');
        if ($handle === false) {
            throw new RuntimeException('Unable to open uploaded file stream.');
        }

        $status = false;
        while (! $status && ! feof($handle)) {
            $chunk = fread($handle, $chunkSize);
            $status = $media->nextChunk($chunk);
        }
        fclose($handle);
        $client->setDefer(false);

        if (! $status || ! $status->getId()) {
            throw new RuntimeException('Google Drive upload failed.');
        }

        $this->clients->syncQuota($account);

        return DriveFile::query()->create([
            'user_id' => $account->user_id,
            'folder_id' => $folderId,
            'connected_account_id' => $account->id,
            'name' => $status->getName() ?: $file->getClientOriginalName(),
            'mime_type' => $status->getMimeType() ?: $file->getMimeType(),
            'size_bytes' => (int) ($status->getSize() ?: $file->getSize()),
            'provider_file_id' => $status->getId(),
            'status' => 'active',
        ]);
    }

    public function streamDownload(ConnectedAccount $account, string $providerFileId)
    {
        $drive = $this->clients->drive($account);

        return $drive->files->get($providerFileId, ['alt' => 'media']);
    }

    public function deleteRemote(ConnectedAccount $account, string $providerFileId): void
    {
        $this->clients->drive($account)->files->delete($providerFileId);
        $this->clients->syncQuota($account);
    }
}
