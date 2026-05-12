<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\TenantAccessDeniedException;
use App\Models\Tenant;
use App\Models\User;

class SwitchTenantService
{
    public function __construct(
        private readonly TenantMembershipService $membershipService,
    ) {}

    /**
     * @throws TenantAccessDeniedException
     */
    public function handle(User $user, int $tenantId): Tenant
    {
        $tenant = $user->tenants()
            ->wherePivot('status', 'active')
            ->find($tenantId);

        if (!$tenant) {
            throw new TenantAccessDeniedException(
                "User does not belong to tenant [{$tenantId}]."
            );
        }

        $this->membershipService->setDefaultTenant($user, $tenant);

        return $tenant;
    }
}
