<?php

declare(strict_types=1);

namespace App\Services;

class GetTenantBillingPortalUrlService
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    public function handle(bool $expectsJson): array
    {
        $tenant = $this->tenantContext->get();

        $returnUrl = $expectsJson
            ? route('api.tenants.index', absolute: true)
            : route('dashboard', absolute: true);

        $portalUrl = $tenant->billingPortalUrl($returnUrl);

        return [
            'tenant_id' => $tenant->id,
            'url' => $portalUrl,
        ];
    }
}
