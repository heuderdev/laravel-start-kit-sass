<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class SyncTenantPlanFromStripeWebhookService
{
    public function syncFromSubscriptionPayload(array $payload): void
    {
        $data = $payload['data']['object'] ?? [];
        $stripeId = $data['customer'] ?? null;
        $status = $data['status'] ?? null;

        $tenant = $this->findTenantByStripeId($stripeId);

        Log::info('Webhook sync tenant plan', [
            'stripe_id' => $stripeId,
            'status' => $status,
            'tenant_id' => $tenant?->id,
        ]);

        if (!$tenant || !$status) {
            return;
        }

        $plan = in_array($status, ['active', 'trialing'], true) ? 'pro' : 'free';

        $this->updateTenantPlan($tenant, $plan);
    }

    public function markProFromInvoicePaymentSucceeded(array $payload): void
    {
        $data = $payload['data']['object'] ?? [];
        $stripeId = $data['customer'] ?? null;

        $tenant = $this->findTenantByStripeId($stripeId);

        Log::info('Webhook invoice payment succeeded', [
            'stripe_id' => $stripeId,
            'tenant_id' => $tenant?->id,
            'amount' => $data['amount_paid'] ?? null,
        ]);

        if (!$tenant) {
            return;
        }

        $this->updateTenantPlan($tenant, 'pro');
    }

    public function logInvoicePaymentFailed(array $payload): void
    {
        $data = $payload['data']['object'] ?? [];
        $stripeId = $data['customer'] ?? null;

        $tenant = $this->findTenantByStripeId($stripeId);

        Log::warning('Webhook invoice payment failed', [
            'stripe_id' => $stripeId,
            'tenant_id' => $tenant?->id,
            'attempt_count' => $data['attempt_count'] ?? null,
        ]);
    }

    public function downgradeFromPayload(array $payload, string $reason): void
    {
        $data = $payload['data']['object'] ?? [];
        $stripeId = $data['customer'] ?? null;

        $tenant = $this->findTenantByStripeId($stripeId);

        Log::info('Webhook tenant downgraded', [
            'stripe_id' => $stripeId,
            'tenant_id' => $tenant?->id,
            'reason' => $reason,
        ]);

        if (!$tenant) {
            return;
        }

        $this->updateTenantPlan($tenant, 'free');
    }

    private function findTenantByStripeId(?string $stripeId): ?Tenant
    {
        if (!$stripeId) {
            return null;
        }

        return Tenant::query()
            ->where('stripe_id', $stripeId)
            ->first();
    }

    private function updateTenantPlan(Tenant $tenant, string $plan): void
    {
        if ($tenant->plan === $plan) {
            return;
        }

        $tenant->update([
            'plan' => $plan,
        ]);
    }
}
