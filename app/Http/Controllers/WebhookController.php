<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SyncTenantPlanFromStripeWebhookService;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends CashierWebhookController
{
    public function __construct(
        private readonly SyncTenantPlanFromStripeWebhookService $syncTenantPlanFromStripeWebhookService,
    ) {}

    protected function handleCustomerSubscriptionCreated(array $payload): Response
    {
        parent::handleCustomerSubscriptionCreated($payload);

        $this->syncTenantPlanFromStripeWebhookService->syncFromSubscriptionPayload($payload);

        // app(\App\Services\AuditService::class)->log([
        //     'acao' => 'subscription_created',
        //     'componente' => 'stripe.webhook',
        //     'categoria' => 'integracao',
        //     'nivel' => 'info',
        //     'descricao' => 'Stripe criou uma assinatura.',
        //     'dados_novos' => [
        //         'type' => $payload['type'] ?? null,
        //         'event_id' => $payload['id'] ?? null,
        //         'subscription_id' => data_get($payload, 'data.object.id'),
        //         'customer_id' => data_get($payload, 'data.object.customer'),
        //     ],
        // ]);

        return $this->successMethod();
    }

    protected function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        parent::handleCustomerSubscriptionUpdated($payload);

        $this->syncTenantPlanFromStripeWebhookService->syncFromSubscriptionPayload($payload);

        return $this->successMethod();
    }

    protected function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        parent::handleCustomerSubscriptionDeleted($payload);

        $this->syncTenantPlanFromStripeWebhookService->downgradeFromPayload($payload, 'canceled');

        return $this->successMethod();
    }

    protected function handleInvoicePaymentSucceeded(array $payload): Response
    {
        $this->syncTenantPlanFromStripeWebhookService->markProFromInvoicePaymentSucceeded($payload);

        return $this->successMethod();
    }

    protected function handleInvoicePaymentFailed(array $payload): Response
    {
        $this->syncTenantPlanFromStripeWebhookService->logInvoicePaymentFailed($payload);

        return $this->successMethod();
    }

    protected function handleCustomerSubscriptionPaused(array $payload): Response
    {
        parent::handleCustomerSubscriptionPaused($payload);

        $this->syncTenantPlanFromStripeWebhookService->downgradeFromPayload($payload, 'paused');

        return $this->successMethod();
    }

    protected function handleCustomerSubscriptionResumed(array $payload): Response
    {
        parent::handleCustomerSubscriptionResumed($payload);

        $this->syncTenantPlanFromStripeWebhookService->syncFromSubscriptionPayload($payload);

        return $this->successMethod();
    }
}
