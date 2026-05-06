<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;
    protected $guarded = ['id'];
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_recovery_codes' => 'array',
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
            ->wherePivot('tenant_id', $tenantId)
            ->wherePivot('status', 'active')
            ->exists();
    }
}
