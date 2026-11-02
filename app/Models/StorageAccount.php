<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorageAccount extends Model
{
    use HasUuids;

    protected $fillable = [
        'connected_account_id',
        'quota_bytes',
        'used_bytes',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'quota_bytes' => 'integer',
            'used_bytes' => 'integer',
            'last_synced_at' => 'datetime',
        ];
    }

    public function connectedAccount(): BelongsTo
    {
        return $this->belongsTo(ConnectedAccount::class);
    }

    public function availableBytes(): ?int
    {
        if ($this->quota_bytes === null || $this->used_bytes === null) {
            return null;
        }

        return max(0, (int) $this->quota_bytes - (int) $this->used_bytes);
    }
}
