<?php

namespace App\Http\Controllers\Drive;

use App\Http\Controllers\Controller;
use App\Jobs\SyncDriveQuota;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuotaController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $policy = $user->ensureRoutingPolicy();
        $accounts = $user->connectedAccounts()
            ->with('storageAccount')
            ->orderBy('email')
            ->get()
            ->map(function ($account) {
                $quota = $account->storageAccount?->quota_bytes;
                $used = $account->storageAccount?->used_bytes;

                return [
                    'id' => $account->id,
                    'email' => $account->email,
                    'display_name' => $account->display_name,
                    'status' => $account->status,
                    'quota_bytes' => $quota,
                    'used_bytes' => $used,
                    'available_bytes' => $account->availableBytes(),
                    'last_synced_at' => optional($account->storageAccount?->last_synced_at)->toIso8601String(),
                ];
            });

        $totalQuota = $accounts->sum(fn ($a) => $a['quota_bytes'] ?? 0);
        $totalUsed = $accounts->sum(fn ($a) => $a['used_bytes'] ?? 0);

        return Inertia::render('Drive/QuotaTracker', [
            'accounts' => $accounts,
            'policy' => [
                'mode' => $policy->mode,
                'priority_account_ids' => $policy->priority_account_ids ?? [],
            ],
            'summary' => [
                'total_quota_bytes' => $totalQuota,
                'total_used_bytes' => $totalUsed,
                'total_available_bytes' => max(0, $totalQuota - $totalUsed),
            ],
        ]);
    }

    public function updatePolicy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mode' => ['required', 'in:most_available,round_robin,priority'],
            'priority_account_ids' => ['nullable', 'array'],
            'priority_account_ids.*' => ['uuid'],
        ]);

        $policy = $request->user()->ensureRoutingPolicy();
        $policy->update([
            'mode' => $data['mode'],
            'priority_account_ids' => $data['priority_account_ids'] ?? [],
        ]);

        return back()->with('success', 'Upload routing updated.');
    }

    public function sync(Request $request): RedirectResponse
    {
        $accounts = $request->user()->connectedAccounts()->where('status', '!=', 'revoked')->get();
        foreach ($accounts as $account) {
            SyncDriveQuota::dispatch($account);
        }

        return back()->with('success', 'Quota sync queued.');
    }
}
