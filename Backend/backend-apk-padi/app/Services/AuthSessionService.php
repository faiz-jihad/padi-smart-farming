<?php

namespace App\Services;

use App\Models\User;

class AuthSessionService
{
    public function revokeCurrentToken(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    public function revokeOtherTokens(User $user): void
    {
        $currentToken = $user->currentAccessToken();

        if ($currentToken === null) {
            return;
        }

        $user->tokens()
            ->where('id', '!=', $currentToken->id)
            ->delete();
    }
}
