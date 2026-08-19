<?php

namespace App\Enums;

enum RegencyType: string
{
    case Regency = 'regency';
    case City = 'city';

    public function label(): string
    {
        return match ($this) {
            self::Regency => 'Kabupaten',
            self::City    => 'Kota',
        };
    }
}
