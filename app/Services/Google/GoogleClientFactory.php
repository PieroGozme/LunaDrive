<?php

namespace App\Services\Google;

use App\Models\ConnectedAccount;
use App\Models\ProviderConfig;
use App\Models\StorageAccount;
use Google\Client as GoogleClient;
use Google\Service\Drive;
use Google\Service\Oauth2;
use RuntimeException;

class GoogleClientFactory
{
    public function forConfig(ProviderConfig $config): GoogleClient
    {
        $client = new GoogleClient;
        $client->setClientId($config->client_id);
        $client->setClientSecret($config->client_secret);
        $client->setRedirectUri($config->redirect_uri);
        $client->setAccessType('offline');
        $client->setPrompt('consent select_account');
        $client->setScopes($config->scopes ?: config('lunadrive.google_scopes'));

        return $client;
    }

    public function forAccount(ConnectedAccount $account): GoogleClient
    {
        $config = $account->providerConfig ?: ProviderConfig::globalGoogle();
        if (! $config) {
            throw new RuntimeException('Google OAuth is not configured.');
        }

        $client = $this->forConfig($config);
        $token = [
            'access_token' => $account->access_token,
            'refresh_token' => $account->refresh_token,
            'expires_in' => max(0, optional($account->token_expires_at)?->diffInSeconds(now()) ?? 0),
            'created' => optional($account->token_expires_at)?->subHour()->timestamp ?? time(),
        ];
        $client->setAccessToken($token);

        if ($client->isAccessTokenExpired() && $account->refresh_token) {
            $fresh = $client->fetchAccessTokenWithRefreshToken($account->refresh_token);
            if (isset($fresh['error'])) {
                $account->update([
                    'status' => 'error',
                    'last_error' => $fresh['error_description'] ?? $fresh['error'],
                ]);
                throw new RuntimeException('Failed to refresh Google token.');
            }

            $account->access_token = $fresh['access_token'] ?? $account->access_token;
            if (! empty($fresh['refresh_token'])) {
                $account->refresh_token = $fresh['refresh_token'];
            }
            $account->token_expires_at = now()->addSeconds((int) ($fresh['expires_in'] ?? 3600));
            $account->status = 'connected';
            $account->last_error = null;
            $account->save();
            $client->setAccessToken($fresh);
        }

        return $client;
    }

    public function drive(ConnectedAccount $account): Drive
    {
        return new Drive($this->forAccount($account));
    }

    public function oauth2(ConnectedAccount $account): Oauth2
    {
        return new Oauth2($this->forAccount($account));
    }

    public function syncQuota(ConnectedAccount $account): StorageAccount
    {
        $about = $this->drive($account)->about->get(['fields' => 'storageQuota']);
        $quota = $about->getStorageQuota();

        return StorageAccount::query()->updateOrCreate(
            ['connected_account_id' => $account->id],
            [
                'quota_bytes' => $quota?->getLimit() !== null ? (int) $quota->getLimit() : null,
                'used_bytes' => $quota?->getUsage() !== null ? (int) $quota->getUsage() : null,
                'last_synced_at' => now(),
            ],
        );
    }

    public function ensureAppFolder(ConnectedAccount $account): string
    {
        if ($account->app_folder_id) {
            return $account->app_folder_id;
        }

        $drive = $this->drive($account);
        $name = config('lunadrive.app_folder_name', 'lunadrive');
        $existing = $drive->files->listFiles([
            'q' => sprintf("mimeType = 'application/vnd.google-apps.folder' and name = '%s' and trashed = false and 'root' in parents", addslashes($name)),
            'spaces' => 'drive',
            'fields' => 'files(id,name)',
            'pageSize' => 1,
        ]);

        $files = $existing->getFiles();
        if ($files && count($files) > 0) {
            $folderId = $files[0]->getId();
        } else {
            $meta = new Drive\DriveFile([
                'name' => $name,
                'mimeType' => 'application/vnd.google-apps.folder',
            ]);
            $created = $drive->files->create($meta, ['fields' => 'id']);
            $folderId = $created->getId();
        }

        $account->update(['app_folder_id' => $folderId]);

        return $folderId;
    }
}
