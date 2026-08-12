<?php

use Illuminate\Support\Facades\Crypt;

if (! function_exists('encryptId')) {
    function encryptId(int|string $id): string
    {
        return Crypt::encryptString((string) $id);
    }
}

if (! function_exists('decryptId')) {
    function decryptId(string $encryptedId): int
    {
        return (int) Crypt::decryptString($encryptedId);
    }
}