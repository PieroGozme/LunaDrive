<?php

namespace App\Services\Storage;

use App\Jobs\SyncDriveQuota;
use App\Models\ConnectedAccount;
use App\Models\User;
use RuntimeException;

class UploadRouter
{
    public function selectAccount(User $user, int $sizeBytes): ConnectedAccount
    {
        $accounts = $user->connectedAccounts()
            ->with('storageAccount')
            ->where('provider', 'google_drive')
            ->where('status', 'connected')
            ->get();

        if ($accounts->isEmpty()) {
            throw new RuntimeException('Connect a Google Drive account before uploading.');
        }

        $staleSeconds = config('lunadrive.quota_stale_seconds', 300);
        foreach ($accounts as $account) {
            $last = $account->storageAccount?->last_synced_at;
            if (! $last || $last->diffInSeconds(now()) > $staleSeconds) {
                SyncDriveQuota::dispatchSync($account);
                $account->load('storageAccount');
            }
        }

        $policy = $user->ensureRoutingPolicy();
        $eligible = $accounts->filter(function (ConnectedAccount $account) use ($sizeBytes) {
            $available = $account->availableBytes();

            return $available === null || $available >= $sizeBytes;
        })->values();

        if ($eligible->isEmpty()) {
            throw new RuntimeException('No connected account has enough free space for this file.');
        }

        return match ($policy->mode) {
            'round_robin' => $this->roundRobin($eligible, $policy),
            'priority' => $this->priority($eligible, $policy),
            default => $eligible->sortByDesc(fn (ConnectedAccount $a) => $a->availableBytes() ?? PHP_INT_MAX)->first(),
        };
    }

    private function roundRobin($eligible, $policy): ConnectedAccount
    {
        $index = $policy->round_robin_cursor % max(1, $eligible->count());
        $selected = $eligible[$index];
        $policy->update(['round_robin_cursor' => $index + 1]);

        return $selected;
    }

    private function priority($eligible, $policy): ConnectedAccount
    {
        $priority = collect($policy->priority_account_ids ?? []);
        foreach ($priority as $id) {
            $match = $eligible->firstWhere('id', $id);
            if ($match) {
                return $match;
            }
        }

        return $eligible->sortByDesc(fn (ConnectedAccount $a) => $a->availableBytes() ?? PHP_INT_MAX)->first();
    }
}
