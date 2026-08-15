<?php

namespace App\Domain\Auth\Actions;

use App\Models\User;
use App\Services\AuthSessionService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;

class ChangePasswordAction
{
    public function __construct(private readonly AuthSessionService $authSessionService)
    {
    }

    /**
     * @param array{current_password: string, password: string} $data
     */
    public function execute(User $user, array $data): void
    {
        if (! Hash::check($data['current_password'], $user->password)) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Password saat ini tidak sesuai.',
                'errors' => [
                    'current_password' => ['Password saat ini tidak sesuai.'],
                ],
            ], 422));
        }

        $user->forceFill([
            'password' => $data['password'],
        ])->save();

        $this->authSessionService->revokeOtherTokens($user);
    }
}
