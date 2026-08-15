<?php

namespace App\Enums;

enum UserRole: string
{
    case Farmer = 'farmer';
    case Buyer = 'buyer';
    case ExtensionOfficer = 'extension_officer';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Farmer => 'Petani',
            self::Buyer => 'Pembeli',
            self::ExtensionOfficer => 'Penyuluh',
            self::Admin => 'Administrator',
        };
    }

    /**
     * @return list<string>
     */
    public static function publicValues(): array
    {
        return [
            self::Farmer->value,
            self::Buyer->value,
        ];
    }
}
