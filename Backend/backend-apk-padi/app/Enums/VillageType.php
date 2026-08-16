<?php

namespace App\Enums;

enum VillageType: string
{
    case Village      = 'village';
    case UrbanVillage = 'urban_village';

    public function label(): string
    {
        return match ($this) {
            self::Village      => 'Desa',
            self::UrbanVillage => 'Kelurahan',
        };
    }
}
