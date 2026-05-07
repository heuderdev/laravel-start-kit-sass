<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;

class WebhookController extends CashierWebhookController
{
    // Assinatura criada (primeiro pagamento / checkout)
    protected function handleCustomerSubscriptionCreated(array $payload): Response
    {
        parent::handleCustomerSubscriptionCreated($payload);
        return $this->syncTenantPlan($payload);
    }

    // Assinatura atualizada (renovação mensal, mudança de plano)
    protected function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        parent::handleCustomerSubscriptionUpdated($payload);
        return $this->syncTenantPlan($payload);
    }

    // Assinatura cancelada
    protected function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        parent::handleCustomerSubscriptionDeleted($payload);
        return $this->downgradeTenant($payload, 'canceled');
    }

    // Pagamento da fatura aprovado (renovação mensal confirmada)
    protected function handleInvoicePaymentSucceeded(array $payload): Response
    {
        $data     = $payload['data']['object'];
        $stripeId = $data['customer'];
        $tenant   = Tenant::query()->where('stripe_id', $stripeId)->first();

        Log::info('Webhook invoice payment succeeded', [
            'stripe_id' => $stripeId,
            'tenant_id' => $tenant?->id,
            'amount'    => $data['amount_paid'],
        ]);

        if ($tenant) {
            $tenant->update(['plan' => 'pro']);
        }

        return $this->successMethod();
    }

    // Pagamento da fatura falhou (cartão recusado, etc.)
    protected function handleInvoicePaymentFailed(array $payload): Response
    {
        $data     = $payload['data']['object'];
        $stripeId = $data['customer'];
        $tenant   = Tenant::query()->where('stripe_id', $stripeId)->first();

        Log::warning('Webhook invoice payment failed', [
            'stripe_id'      => $stripeId,
            'tenant_id'      => $tenant?->id,
            'attempt_count'  => $data['attempt_count'],
        ]);

        // Não downgrade imediato — o Stripe vai tentar cobrar novamente
        // O status vai para past_due no subscription updated
        // Downgrade só ocorre quando a subscription for cancelada

        return $this->successMethod();
    }

    // Assinatura pausada (past_due por muitas tentativas falhas)
    protected function handleCustomerSubscriptionPaused(array $payload): Response
    {
        parent::handleCustomerSubscriptionPaused($payload);
        return $this->downgradeTenant($payload, 'paused');
    }

    // Assinatura retomada após pausa
    protected function handleCustomerSubscriptionResumed(array $payload): Response
    {
        parent::handleCustomerSubscriptionResumed($payload);
        return $this->syncTenantPlan($payload);
    }

    // -------------------------------------------------------------------------

    private function syncTenantPlan(array $payload): Response
    {
        $data   = $payload['data']['object'];
        $status = $data['status'];

        $tenant = Tenant::query()->where('stripe_id', $data['customer'])->first();

        Log::info('Webhook sync tenant plan', [
            'stripe_id' => $data['customer'],
            'status'    => $status,
            'tenant_id' => $tenant?->id,
        ]);

        if (!$tenant) {
            return $this->successMethod();
        }

        $plan = in_array($status, ['active', 'trialing']) ? 'pro' : 'free';

        $tenant->update(['plan' => $plan]);

        return $this->successMethod();
    }

    private function downgradeTenant(array $payload, string $reason): Response
    {
        $stripeId = $payload['data']['object']['customer'];
        $tenant   = Tenant::query()->where('stripe_id', $stripeId)->first();

        Log::info('Webhook tenant downgraded', [
            'stripe_id' => $stripeId,
            'tenant_id' => $tenant?->id,
            'reason'    => $reason,
        ]);

        if ($tenant) {
            $tenant->update(['plan' => 'free']);
        }

        return $this->successMethod();
    }
}
