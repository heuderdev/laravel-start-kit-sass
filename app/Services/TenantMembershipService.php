<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TenantMembershipService
{
    private const ALLOWED_ROLES = [
        'owner',
        'admin',
        'member',
        'client',
    ];

    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly PermissionRegistrar $permissionRegistrar,
    ) {}

    public function attachUserToTenant(
        User $user,
        Tenant $tenant,
        string $role,
        bool $isDefault = false,
        string $status = 'active',
    ): void {
        $this->ensureValidRole($role);

        DB::transaction(function () use ($user, $tenant, $role, $isDefault, $status): void {
            $membership = $user->tenants()
                ->where('tenant_id', $tenant->id)
                ->first();

            if (!$membership) {
                if ($isDefault) {
                    $this->unsetDefaultTenant($user);
                }

                $user->tenants()->attach($tenant->id, [
                    'role' => $role, // compatibilidade temporária
                    'is_default' => $isDefault,
                    'status' => $status,
                    'joined_at' => now(),
                ]);
            } else {
                if ($isDefault) {
                    $this->unsetDefaultTenant($user);
                }

                $user->tenants()->updateExistingPivot($tenant->id, [
                    'role' => $role, // compatibilidade temporária
                    'is_default' => $isDefault,
                    'status' => $status,
                ]);
            }

            $this->tenantContext->set($tenant);
            $this->ensureTenantRolesExist();

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            $user->syncRoles([$role]);

            $this->permissionRegistrar->forgetCachedPermissions();
        });
    }

    public function syncUserRoleInTenant(
        User $user,
        Tenant $tenant,
        string $role,
    ): void {
        $this->ensureValidRole($role);

        DB::transaction(function () use ($user, $tenant, $role): void {
            $belongsToTenant = $user->tenants()
                ->where('tenant_id', $tenant->id)
                ->exists();

            if (!$belongsToTenant) {
                throw new InvalidArgumentException('O usuário não pertence ao tenant informado.');
            }

            $user->tenants()->updateExistingPivot($tenant->id, [
                'role' => $role, // compatibilidade temporária
            ]);

            $this->tenantContext->set($tenant);
            $this->ensureTenantRolesExist();

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            $user->syncRoles([$role]);

            $this->permissionRegistrar->forgetCachedPermissions();
        });
    }

    public function detachUserFromTenant(User $user, Tenant $tenant): void
    {
        DB::transaction(function () use ($user, $tenant): void {
            $belongsToTenant = $user->tenants()
                ->where('tenant_id', $tenant->id)
                ->exists();

            if (!$belongsToTenant) {
                return;
            }

            $this->tenantContext->set($tenant);

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            $user->syncRoles([]);

            $user->tenants()->detach($tenant->id);

            $this->permissionRegistrar->forgetCachedPermissions();
        });
    }

    public function setDefaultTenant(User $user, Tenant $tenant): void
    {
        DB::transaction(function () use ($user, $tenant): void {
            $belongsToTenant = $user->tenants()
                ->where('tenant_id', $tenant->id)
                ->exists();

            if (!$belongsToTenant) {
                throw new InvalidArgumentException('O usuário não pertence ao tenant informado.');
            }

            $this->unsetDefaultTenant($user);

            $user->tenants()->updateExistingPivot($tenant->id, [
                'is_default' => true,
            ]);
        });
    }

    private function ensureTenantRolesExist(): void
    {
        foreach (self::ALLOWED_ROLES as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    private function unsetDefaultTenant(User $user): void
    {
        $user->tenants()
            ->newPivotStatement()
            ->where('user_id', $user->id)
            ->update([
                'is_default' => false,
            ]);
    }

    private function ensureValidRole(string $role): void
    {
        if (!in_array($role, self::ALLOWED_ROLES, true)) {
            throw new InvalidArgumentException("Role inválido: {$role}");
        }
    }
}
