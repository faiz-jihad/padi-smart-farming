<?php

namespace App\Enums;

enum ProfileTemplateStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Inactive => 'Nonaktif',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }
}
