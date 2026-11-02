<?php

namespace App\Models;

use App\Support\TokenCrypt;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ConnectedAccount extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'provider_config_id',
        'provider',
        'provider_account_id',
        'email',
        'display_name',
        'avatar_url',
        'access_token_encrypted',
        'refresh_token_encrypted',
        'token_expires_at',
        'scopes',
        'status',
        'last_error',
        'app_folder_id',
    ];

    protected function casts(): array
    {
        return [
            'scopes' => 'array',
            'token_expires_at' => 'datetime',
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

    public function storageAccount(): HasOne
    {
        return $this->hasOne(StorageAccount::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(DriveFile::class, 'connected_account_id');
    }

    public function getAccessTokenAttribute(): ?string
    {
        return TokenCrypt::decrypt($this->attributes['access_token_encrypted'] ?? null);
    }

    public function setAccessTokenAttribute(?string $value): void
    {
        $this->attributes['access_token_encrypted'] = TokenCrypt::encrypt($value);
    }

    public function getRefreshTokenAttribute(): ?string
    {
        return TokenCrypt::decrypt($this->attributes['refresh_token_encrypted'] ?? null);
    }

    public function setRefreshTokenAttribute(?string $value): void
    {
        $this->attributes['refresh_token_encrypted'] = TokenCrypt::encrypt($value);
    }

    public function availableBytes(): ?int
    {
        $storage = $this->storageAccount;
        if (! $storage || $storage->quota_bytes === null || $storage->used_bytes === null) {
            return null;
        }

        return max(0, (int) $storage->quota_bytes - (int) $storage->used_bytes);
    }
}
