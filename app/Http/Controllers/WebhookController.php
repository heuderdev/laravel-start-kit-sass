<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AuditService;
use App\Services\SyncTenantPlanFromStripeWebhookService;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends CashierWebhookController
{
    public function __construct(
        private readonly SyncTenantPlanFromStripeWebhookService $syncService,
        private readonly AuditService $audit,
    ) {
        parent::__construct();
    }

    protected function handleCustomerSubscriptionCreated(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionCreated($payload);

        $this->syncService->syncFromSubscriptionPayload($payload);

        $this->audit->log([
            'acao'        => 'subscription_created',
            'componente'  => 'stripe.webhook',
            'categoria'   => 'integracao',
            'nivel'       => 'info',
            'descricao'   => 'Nova assinatura criada via Stripe.',
            'dados_novos' => [
                'event_id'        => $payload['id'] ?? null,
                'subscription_id' => data_get($payload, 'data.object.id'),
                'customer_id'     => data_get($payload, 'data.object.customer'),
                'status'          => data_get($payload, 'data.object.status'),
            ],
        ]);

        return $response;
    }

    protected function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionUpdated($payload);

        $this->syncService->syncFromSubscriptionPayload($payload);

        $this->audit->log([
            'acao'        => 'subscription_updated',
            'componente'  => 'stripe.webhook',
            'categoria'   => 'integracao',
            'nivel'       => 'info',
            'descricao'   => 'Assinatura atualizada via Stripe.',
            'dados_novos' => [
                'event_id'        => $payload['id'] ?? null,
                'subscription_id' => data_get($payload, 'data.object.id'),
                'customer_id'     => data_get($payload, 'data.object.customer'),
                'status'          => data_get($payload, 'data.object.status'),
                'cancel_at'       => data_get($payload, 'data.object.cancel_at'),
            ],
        ]);

        return $response;
    }

    protected function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionDeleted($payload);

        $this->syncService->downgradeFromPayload($payload, 'canceled');

        $this->audit->log([
            'acao'        => 'subscription_deleted',
            'componente'  => 'stripe.webhook',
            'categoria'   => 'integracao',
            'nivel'       => 'warning',
            'descricao'   => 'Assinatura cancelada via Stripe.',
            'dados_novos' => [
                'event_id'        => $payload['id'] ?? null,
                'subscription_id' => data_get($payload, 'data.object.id'),
                'customer_id'     => data_get($payload, 'data.object.customer'),
                'ended_at'        => data_get($payload, 'data.object.ended_at'),
            ],
        ]);

        return $response;
    }

    protected function handleCustomerSubscriptionPaused(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionPaused($payload);

        $this->syncService->downgradeFromPayload($payload, 'paused');

        $this->audit->log([
            'acao'        => 'subscription_paused',
            'componente'  => 'stripe.webhook',
            'categoria'   => 'integracao',
            'nivel'       => 'warning',
            'descricao'   => 'Assinatura pausada via Stripe.',
            'dados_novos' => [
                'event_id'        => $payload['id'] ?? null,
                'subscription_id' => data_get($payload, 'data.object.id'),
                'customer_id'     => data_get($payload, 'data.object.customer'),
            ],
        ]);

        return $response;
    }

    protected function handleCustomerSubscriptionResumed(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionResumed($payload);

        $this->syncService->syncFromSubscriptionPayload($payload);

        $this->audit->log([
            'acao'        => 'subscription_resumed',
            'componente'  => 'stripe.webhook',
            'categoria'   => 'integracao',
            'nivel'       => 'info',
            'descricao'   => 'Assinatura retomada via Stripe.',
            'dados_novos' => [
                'event_id'        => $payload['id'] ?? null,
                'subscription_id' => data_get($payload, 'data.object.id'),
                'customer_id'     => data_get($payload, 'data.object.customer'),
                'status'          => data_get($payload, 'data.object.status'),
            ],
        ]);

        return $response;
    }

    protected function handleInvoicePaymentSucceeded(array $payload): Response
    {
        $this->syncService->markProFromInvoicePaymentSucceeded($payload);

        $this->audit->log([
            'acao'        => 'invoice_payment_succeeded',
            'componente'  => 'stripe.webhook',
            'categoria'   => 'integracao',
            'nivel'       => 'info',
            'descricao'   => 'Pagamento de fatura confirmado pelo Stripe.',
            'dados_novos' => [
                'event_id'    => $payload['id'] ?? null,
                'invoice_id'  => data_get($payload, 'data.object.id'),
                'customer_id' => data_get($payload, 'data.object.customer'),
                'amount_paid' => data_get($payload, 'data.object.amount_paid'),
                'currency'    => data_get($payload, 'data.object.currency'),
            ],
        ]);

        return $this->successMethod();
    }

    protected function handleInvoicePaymentFailed(array $payload): Response
    {
        $this->audit->log([
            'acao'        => 'invoice_payment_failed',
            'componente'  => 'stripe.webhook',
            'categoria'   => 'integracao',
            'nivel'       => 'error',
            'descricao'   => 'Falha no pagamento de fatura via Stripe.',
            'dados_novos' => [
                'event_id'       => $payload['id'] ?? null,
                'invoice_id'     => data_get($payload, 'data.object.id'),
                'customer_id'    => data_get($payload, 'data.object.customer'),
                'amount_due'     => data_get($payload, 'data.object.amount_due'),
                'attempt_count'  => data_get($payload, 'data.object.attempt_count'),
                'next_attempt'   => data_get($payload, 'data.object.next_payment_attempt'),
            ],
        ]);

        return $this->successMethod();
    }

    protected function handleInvoicePaymentActionRequired(array $payload): Response
    {
        $this->audit->log([
            'acao'        => 'invoice_payment_action_required',
            'componente'  => 'stripe.webhook',
            'categoria'   => 'integracao',
            'nivel'       => 'warning',
            'descricao'   => 'Ação do cliente necessária para completar pagamento.',
            'dados_novos' => [
                'event_id'    => $payload['id'] ?? null,
                'invoice_id'  => data_get($payload, 'data.object.id'),
                'customer_id' => data_get($payload, 'data.object.customer'),
                'hosted_url'  => data_get($payload, 'data.object.hosted_invoice_url'),
            ],
        ]);

        return $this->successMethod();
    }

    protected function handlePaymentIntentSucceeded(array $payload): Response
    {
        $this->audit->log([
            'acao'        => 'payment_intent_succeeded',
            'componente'  => 'stripe.webhook',
            'categoria'   => 'integracao',
            'nivel'       => 'info',
            'descricao'   => 'PaymentIntent concluído com sucesso.',
            'dados_novos' => [
                'event_id'           => $payload['id'] ?? null,
                'payment_intent_id'  => data_get($payload, 'data.object.id'),
                'customer_id'        => data_get($payload, 'data.object.customer'),
                'amount'             => data_get($payload, 'data.object.amount'),
                'currency'           => data_get($payload, 'data.object.currency'),
            ],
        ]);

        return $this->successMethod();
    }

    protected function handlePaymentIntentPaymentFailed(array $payload): Response
    {
        $this->audit->log([
            'acao'        => 'payment_intent_failed',
            'componente'  => 'stripe.webhook',
            'categoria'   => 'integracao',
            'nivel'       => 'error',
            'descricao'   => 'Falha no PaymentIntent.',
            'dados_novos' => [
                'event_id'          => $payload['id'] ?? null,
                'payment_intent_id' => data_get($payload, 'data.object.id'),
                'customer_id'       => data_get($payload, 'data.object.customer'),
                'error_message'     => data_get($payload, 'data.object.last_payment_error.message'),
                'error_code'        => data_get($payload, 'data.object.last_payment_error.code'),
            ],
        ]);

        return $this->successMethod();
    }
}
