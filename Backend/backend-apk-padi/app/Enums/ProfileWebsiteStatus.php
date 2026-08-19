<?php

namespace App\Enums;

enum ProfileWebsiteStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Published = 'published';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Review => 'Menunggu Review',
            self::Published => 'Tayang',
            self::Suspended => 'Ditangguhkan',
        };
    }

    public function isPubliclyVisible(): bool
    {
        return $this === self::Published;
    }
}
