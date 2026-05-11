<?php

namespace App\Services;

use App\Exceptions\TenantNotFoundException;
use App\Models\Tenant;
use Spatie\Permission\PermissionRegistrar;

class TenantContext
{
    private ?Tenant $current = null;

    public function __construct(
        private readonly PermissionRegistrar $permissionRegistrar,
    ) {}

    public function set(Tenant $tenant): void
    {
        $this->current = $tenant;

        $this->permissionRegistrar->setPermissionsTeamId($tenant->id);
    }

    public function get(): Tenant
    {
        if ($this->current === null) {
            throw new TenantNotFoundException('No active tenant in context.');
        }

        return $this->current;
    }

    public function id(): int
    {
        return $this->get()->id;
    }

    public function isSet(): bool
    {
        return $this->current !== null;
    }

    public function clear(): void
    {
        $this->current = null;

        $this->permissionRegistrar->setPermissionsTeamId(null);
    }
}
