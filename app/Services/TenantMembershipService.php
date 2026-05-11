<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TenantMembershipService
{
    private const ALLOWED_ROLES = [
        'owner',
        'admin',
        'member',
        'client',
    ];

    public function attachUserToTenant(
        User $user,
        Tenant $tenant,
        string $role,
        bool $isDefault = false,
        string $status = 'active',
    ): void {
        $this->ensureValidRole($role);

        DB::transaction(function () use ($user, $tenant, $role, $isDefault, $status): void {
            $exists = $user->tenants()
                ->where('tenant_id', $tenant->id)
                ->exists();

            if ($isDefault) {
                $this->unsetDefaultTenant($user);
            }

            if (!$exists) {
                $user->tenants()->attach($tenant->id, [
                    'role'       => $role,
                    'is_default' => $isDefault,
                    'status'     => $status,
                    'joined_at'  => now(),
                ]);
            } else {
                $user->tenants()->updateExistingPivot($tenant->id, [
                    'role'       => $role,
                    'is_default' => $isDefault,
                    'status'     => $status,
                ]);
            }
        });
    }

    public function syncUserRoleInTenant(
        User $user,
        Tenant $tenant,
        string $role,
    ): void {
        $this->ensureValidRole($role);

        DB::transaction(function () use ($user, $tenant, $role): void {
            if (!$user->tenants()->where('tenant_id', $tenant->id)->exists()) {
                throw new InvalidArgumentException('O usuário não pertence ao tenant informado.');
            }

            $user->tenants()->updateExistingPivot($tenant->id, [
                'role' => $role,
            ]);
        });
    }

    public function detachUserFromTenant(User $user, Tenant $tenant): void
    {
        DB::transaction(function () use ($user, $tenant): void {
            if (!$user->tenants()->where('tenant_id', $tenant->id)->exists()) {
                return;
            }

            $user->tenants()->detach($tenant->id);
        });
    }

    public function setDefaultTenant(User $user, Tenant $tenant): void
    {
        DB::transaction(function () use ($user, $tenant): void {
            if (!$user->tenants()->where('tenant_id', $tenant->id)->exists()) {
                throw new InvalidArgumentException('O usuário não pertence ao tenant informado.');
            }

            $this->unsetDefaultTenant($user);

            $user->tenants()->updateExistingPivot($tenant->id, [
                'is_default' => true,
            ]);
        });
    }

    private function unsetDefaultTenant(User $user): void
    {
        $user->tenants()
            ->newPivotStatement()
            ->where('user_id', $user->id)
            ->update(['is_default' => false]);
    }

    private function ensureValidRole(string $role): void
    {
        if (!in_array($role, self::ALLOWED_ROLES, true)) {
            throw new InvalidArgumentException("Role inválido: {$role}");
        }
    }
}
