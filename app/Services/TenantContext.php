<?php

namespace App\Services;

use App\Models\Tenant;
use App\Exceptions\TenantNotFoundException;

class TenantContext
{
    private ?Tenant $current = null;

    public function set(Tenant $tenant): void
    {
        $this->current = $tenant;

        // Sincroniza Spatie team scope
        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app(\Spatie\Permission\PermissionRegistrar::class)
                ->setPermissionsTeamId($tenant->id);
        }
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
    }
}
