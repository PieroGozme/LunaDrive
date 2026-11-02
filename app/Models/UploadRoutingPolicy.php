<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UploadRoutingPolicy extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'mode',
        'priority_account_ids',
        'round_robin_cursor',
    ];

    protected function casts(): array
    {
        return [
            'priority_account_ids' => 'array',
            'round_robin_cursor' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
