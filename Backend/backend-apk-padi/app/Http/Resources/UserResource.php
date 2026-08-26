<?php

namespace App\Http\Resources;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $role = (method_exists($this->resource, 'getRoleNames') && $this->getRoleNames()->first())
            ?: $this->role;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $role,
            'role_label' => UserRole::tryFrom($role)?->label() ?? $role,
            'status' => $this->status,
            'status_label' => UserStatus::tryFrom($this->status)?->label() ?? $this->status,
            'last_login_at' => $this->last_login_at ? (is_string($this->last_login_at) ? $this->last_login_at : $this->last_login_at->toIso8601String()) : null,
            'created_at' => $this->created_at ? (is_string($this->created_at) ? $this->created_at : $this->created_at->toIso8601String()) : now()->toIso8601String(),
        ];
    }
}
