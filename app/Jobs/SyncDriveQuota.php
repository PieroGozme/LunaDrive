<?php

namespace App\Jobs;

use App\Models\ConnectedAccount;
use App\Services\Google\GoogleClientFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncDriveQuota implements ShouldQueue
{
    use Queueable;

    public function __construct(public ConnectedAccount $account) {}

    public function handle(GoogleClientFactory $clients): void
    {
        try {
            $clients->syncQuota($this->account);
            $this->account->update(['status' => 'connected', 'last_error' => null]);
        } catch (\Throwable $e) {
            Log::warning('Quota sync failed', [
                'account_id' => $this->account->id,
                'error' => $e->getMessage(),
            ]);
            $this->account->update([
                'status' => 'error',
                'last_error' => $e->getMessage(),
            ]);
        }
    }
}
