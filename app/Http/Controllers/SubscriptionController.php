<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\GetTenantBillingPortalUrlService;
use App\Services\StartTenantSubscriptionCheckoutService;
use App\Services\TenantContext;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly StartTenantSubscriptionCheckoutService $startTenantSubscriptionCheckoutService,
        private readonly GetTenantBillingPortalUrlService $getTenantBillingPortalUrlService,
    ) {}

    public function checkout(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $tenant = $this->context->get();

        // Mantido exatamente como no comportamento atual para não quebrar clientes JSON
        if ($request->expectsJson()) {
            return response()->json([
                'redirect' => env('URL_LOGIN'),
            ]);
        }

        try {
            $checkoutUrl = $this->startTenantSubscriptionCheckoutService->handle(
                request: $request,
                user: $user,
            );
        } catch (DomainException $exception) {
            return redirect()
                ->route('dashboard')
                ->with('error', $exception->getMessage());
        }

        return redirect($checkoutUrl);
    }

    public function success(Request $request): JsonResponse|RedirectResponse
    {
        $tenant = $this->context->get();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Pagamento realizado com sucesso!',
                'tenant_id' => $tenant->id,
            ]);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Assinatura ativada com sucesso!');
    }

    public function cancel(Request $request): JsonResponse|RedirectResponse
    {
        $result = $this->getTenantBillingPortalUrlService->handle(
            expectsJson: $request->expectsJson(),
        );

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return redirect($result['url']);
    }
}
