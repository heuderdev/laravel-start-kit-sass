<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use DomainException;
use Illuminate\Http\Request;

class StartTenantSubscriptionCheckoutService
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @throws DomainException
     */
    public function handle(Request $request, User $user): string
    {
        $tenant = $this->tenantContext->get();

        $isOwner = $user->tenants()
            ->wherePivot('tenant_id', $tenant->id)
            ->wherePivot('role', 'owner')
            ->wherePivot('status', 'active')
            ->exists();

        if (!$isOwner) {
            throw new DomainException('Apenas o proprietário pode gerenciar a assinatura.', 403);
        }

        if ($tenant->subscribed('default')) {
            throw new DomainException('Você já possui uma assinatura ativa.', 422);
        }

        $successUrl = route('subscription.success', absolute: true) . '?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = route('subscription.cancel', absolute: true);

        $checkout = $tenant->newSubscription('default', config('services.stripe.price_id'))
            ->trialDays(3)
            ->checkout([
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);

        return $checkout->url;
    }
}
