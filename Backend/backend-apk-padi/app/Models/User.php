<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'verification_status',
        'last_login_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function farmerProfile(): HasOne
    {
        return $this->hasOne(FarmerProfile::class);
    }

    public function publicProfile(): HasOne
    {
        return $this->hasOne(FarmerPublicProfile::class, 'farmer_id');
    }


    public function farms(): HasMany
    {
        return $this->hasMany(Farm::class, 'farmer_user_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function permissionRoleName(): ?string
    {
        return match ($this->role) {
            'ppl' => UserRole::ExtensionOfficer->value,
            'partner' => UserRole::Buyer->value,
            UserRole::Admin->value,
            UserRole::ExtensionOfficer->value,
            UserRole::Farmer->value,
            UserRole::Buyer->value => $this->role,
            default => null,
        };
    }

    public function syncPermissionRoleFromColumn(): void
    {
        $roleName = $this->permissionRoleName();

        if (
            ! $roleName
            || ! Schema::hasTable('roles')
            || ! Schema::hasTable('model_has_roles')
            || ! Role::query()->where('name', $roleName)->exists()
        ) {
            return;
        }

        if ($this->hasRole($roleName) && $this->roles()->count() === 1) {
            return;
        }

        $this->syncRoles([$roleName]);
    }

    protected static function booted(): void
    {
        static::created(function (User $user): void {
            $user->syncPermissionRoleFromColumn();
        });

        static::updated(function (User $user): void {
            if ($user->wasChanged('role')) {
                $user->syncPermissionRoleFromColumn();
            }
        });
    }

}
