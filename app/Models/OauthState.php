<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OauthState extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'provider_config_id',
        'flow',
        'state_hash',
        'expires_at',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function providerConfig(): BelongsTo
    {
        return $this->belongsTo(ProviderConfig::class);
    }
}
