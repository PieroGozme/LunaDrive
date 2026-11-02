<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UploadSession extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'connected_account_id',
        'folder_id',
        'file_name',
        'mime_type',
        'size_bytes',
        'bytes_received',
        'resumable_uri',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'bytes_received' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function connectedAccount(): BelongsTo
    {
        return $this->belongsTo(ConnectedAccount::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }
}
