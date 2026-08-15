<?php

namespace App\Helpers;

class PhoneNormalizer
{
    public static function normalize(?string $phone): string
    {
        return preg_replace('/[^\d+]/', '', (string) $phone) ?? '';
    }
}
