<?php

namespace App\Support;

use Illuminate\Support\Facades\Crypt;
use RuntimeException;

class TokenCrypt
{
    public static function encrypt(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Crypt::encryptString($value);
    }

    public static function decrypt(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            throw new RuntimeException('Unable to decrypt stored credential.', 0, $e);
        }
    }
}
