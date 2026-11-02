<?php

return [
    'token_encryption_key' => env('TOKEN_ENCRYPTION_KEY', env('APP_KEY')),
    'app_folder_name' => env('LUNADRIVE_FOLDER_NAME', 'lunadrive'),
    'max_upload_bytes' => (int) env('MAX_UPLOAD_BYTES', 5 * 1024 * 1024 * 1024),
    'quota_stale_seconds' => (int) env('QUOTA_STALE_SECONDS', 300),
    'google_scopes' => [
        'https://www.googleapis.com/auth/drive',
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/userinfo.profile',
    ],
];
