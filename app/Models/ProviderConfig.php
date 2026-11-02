<?php

namespace App\Models;

use App\Support\TokenCrypt;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProviderConfig extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'provider',
        'client_id_encrypted',
        'client_secret_encrypted',
        'redirect_uri',
        'scopes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'scopes' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function connectedAccounts(): HasMany
    {
        return $this->hasMany(ConnectedAccount::class);
    }

    public function setClientIdAttribute(string $value): void
    {
        $this->attributes['client_id_encrypted'] = TokenCrypt::encrypt($value);
    }

    public function getClientIdAttribute(): ?string
    {
        return TokenCrypt::decrypt($this->attributes['client_id_encrypted'] ?? null);
    }

    public function setClientSecretAttribute(string $value): void
    {
        $this->attributes['client_secret_encrypted'] = TokenCrypt::encrypt($value);
    }

    public function getClientSecretAttribute(): ?string
    {
        return TokenCrypt::decrypt($this->attributes['client_secret_encrypted'] ?? null);
    }

    public static function globalGoogle(): ?self
    {
        return static::query()
            ->whereNull('user_id')
            ->where('provider', 'google_drive')
            ->where('status', 'active')
            ->latest()
            ->first();
    }
}
