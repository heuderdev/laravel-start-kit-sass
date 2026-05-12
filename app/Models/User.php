<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'email_verified_at',
        'deleted_at',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_recovery_codes' => 'array',
            'is_super_admin' => 'boolean',
        ];
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')
            ->withPivot(['role', 'is_default', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function defaultTenant(): ?Tenant
    {
        return $this->tenants()
            ->wherePivot('is_default', true)
            ->wherePivot('status', 'active')
            ->first();
    }

    public function belongsToTenant(int $tenantId): bool
    {
        return $this->tenants()
            ->where('tenants.id', $tenantId)
            ->wherePivot('status', 'active')
            ->exists();
    }

    public function roleInTenant(Tenant $tenant): ?string
    {
        return $this->tenants()
            ->where('tenants.id', $tenant->id)
            ->wherePivot('status', 'active')
            ->first()?->pivot?->role;
    }

    public function hasRoleInTenant(string $role, Tenant $tenant): bool
    {
        return $this->tenants()
            ->where('tenants.id', $tenant->id)
            ->wherePivot('status', 'active')
            ->wherePivot('role', $role)
            ->exists();
    }

    public function hasAnyRoleInTenant(array $roles, Tenant $tenant): bool
    {
        return $this->tenants()
            ->where('tenants.id', $tenant->id)
            ->wherePivot('status', 'active')
            ->wherePivotIn('role', $roles)
            ->exists();
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    public function isOwnerOfTenant(Tenant $tenant): bool
    {
        return $this->hasRoleInTenant('owner', $tenant);
    }
}
