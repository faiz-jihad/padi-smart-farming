<?php

namespace App\Enums;

enum ProfileVerificationStatus: string
{
    case Unverified = 'unverified';
    case Verified = 'verified';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Unverified => 'Belum Diverifikasi',
            self::Verified => 'Terverifikasi P.A.D.I.',
            self::Rejected => 'Ditolak',
        };
    }

    public function isVerified(): bool
    {
        return $this === self::Verified;
    }
}
