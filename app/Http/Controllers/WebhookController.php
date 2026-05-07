<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;

class WebhookController extends CashierWebhookController
{
    protected function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        parent::handleCustomerSubscriptionUpdated($payload);

        $stripeId = $payload['data']['object']['customer'];
        $tenant   = Tenant::query()->where('stripe_id', $stripeId)->first();

        if ($tenant) {
            $tenant->update(['plan' => 'pro']);
        }

        return $this->successMethod();
    }

    protected function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        parent::handleCustomerSubscriptionDeleted($payload);

        $stripeId = $payload['data']['object']['customer'];
        $tenant   = Tenant::query()->where('stripe_id', $stripeId)->first();

        if ($tenant) {
            $tenant->update(['plan' => 'free']);
        }
        Log::error('Subscription cancelada no Stripe', [
            'stripe_id' => $stripeId,
            'tenant_id' => $tenant?->id,
        ]);
        return $this->successMethod();
    }
}
