<?php

namespace App\Http\Controllers;

use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        private TenantContext $context
    ) {}

    public function checkout(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $tenant = $this->context->get();

        if ($request->expectsJson()) {
            return response()->json([
                'redirect'       => env('URL_LOGIN')
            ]);
        }

        $isOwner = $user->tenants()
            ->wherePivot('tenant_id', $tenant->id)
            ->wherePivot('role', 'owner')
            ->wherePivot('status', 'active')
            ->exists();

        if (!$isOwner) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Apenas o proprietário pode gerenciar a assinatura.',
                ], 403);
            }

            return redirect()
                ->route('dashboard')
                ->with('error', 'Apenas o proprietário pode gerenciar a assinatura.');
        }

        if ($tenant->subscribed('default')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Você já possui uma assinatura ativa.',
                ], 422);
            }

            return redirect()
                ->route('dashboard')
                ->with('error', 'Você já possui uma assinatura ativa.');
        }

        $successUrl = $request->expectsJson()
            ? route('api.subscription.success', absolute: true) . '?session_id={CHECKOUT_SESSION_ID}'
            : route('subscription.success', absolute: true) . '?session_id={CHECKOUT_SESSION_ID}';

        $cancelUrl = $request->expectsJson()
            ? route('api.subscription.cancel', absolute: true)
            : route('subscription.cancel', absolute: true);

        $checkout = $tenant->newSubscription('default', config('services.stripe.price_id'))
            ->trialDays(3)
            ->checkout([
                'success_url' => $successUrl,
                'cancel_url'  => $cancelUrl,
            ]);

        if ($request->expectsJson()) {
            return response()->json([
                'url'       => config('app.url') . ':8000/login',
                'tenant_id' => $tenant->id,
            ]);
        }

        return redirect($checkout->url);
    }

    public function success(Request $request): JsonResponse|RedirectResponse
    {
        $tenant = $this->context->get();

        if ($request->expectsJson()) {
            return response()->json([
                'message'   => 'Pagamento realizado com sucesso!',
                'tenant_id' => $tenant->id,
            ]);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Assinatura ativada com sucesso!');
    }

    public function cancel(Request $request): JsonResponse|RedirectResponse
    {
        $tenant = $this->context->get();

        $portalUrl = $tenant->billingPortalUrl(
            $request->expectsJson()
                ? route('api.tenants.index', absolute: true)
                : route('dashboard', absolute: true)
        );

        if ($request->expectsJson()) {
            return response()->json([
                'url'       => $portalUrl,
                'tenant_id' => $tenant->id,
            ]);
        }

        return redirect($portalUrl);
    }
}
