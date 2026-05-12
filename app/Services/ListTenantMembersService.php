<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Collection;

class ListTenantMembersService
{
    /**
     * @return Collection<int, User>
     */
    public function handle(Tenant $tenant): Collection
    {
        return $tenant->activeNonOwnerUsers()
            ->get()
            ->each(function (User $member) use ($tenant): void {
                $member->setAttribute('tenant_role', $member->roleInTenant($tenant));
            });
    }
}
