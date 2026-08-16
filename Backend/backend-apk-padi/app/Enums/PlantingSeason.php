<?php

namespace App\Enums;

enum PlantingSeason: string
{
    case Rainy      = 'rainy';
    case Dry        = 'dry';
    case Transition = 'transition';

    public function label(): string
    {
        return match ($this) {
            self::Rainy      => 'Musim Hujan',
            self::Dry        => 'Musim Kemarau',
            self::Transition => 'Musim Peralihan',
        };
    }

    public function indonesian(): string
    {
        return match ($this) {
            self::Rainy      => 'MH (Musim Hujan)',
            self::Dry        => 'MK (Musim Kemarau)',
            self::Transition => 'MP (Musim Peralihan)',
        };
    }
}
