<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriveFile extends Model
{
    use HasUuids;

    protected $table = 'files';

    protected $fillable = [
        'user_id',
        'folder_id',
        'connected_account_id',
        'name',
        'mime_type',
        'size_bytes',
        'provider_file_id',
        'status',
        'starred',
        'deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'starred' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function connectedAccount(): BelongsTo
    {
        return $this->belongsTo(ConnectedAccount::class);
    }
}
