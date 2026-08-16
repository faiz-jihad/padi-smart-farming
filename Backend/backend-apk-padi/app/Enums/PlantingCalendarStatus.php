<?php

namespace App\Enums;

enum PlantingCalendarStatus: string
{
    case Draft    = 'draft';
    case Active   = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Draft    => 'Draft',
            self::Active   => 'Aktif',
            self::Inactive => 'Tidak Aktif',
        };
    }
}
