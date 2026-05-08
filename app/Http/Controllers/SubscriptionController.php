<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class SubscriptionController extends Controller
{
    public function checkout(Request $request): JsonResponse|RedirectResponse
    {
        $user   = $request->user();
        $tenant = $user->defaultTenant();

        if (!$tenant) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Nenhum tenant associado a este usuário.'], 422);
            }
            return redirect()->route('dashboard')->with('error', 'Nenhum tenant associado.');
        }

        // Verifica se o usuário é owner do tenant
        $isOwner = $user->tenants()
            ->wherePivot('tenant_id', $tenant->id)
            ->wherePivot('role', 'owner')
            ->wherePivot('status', 'active')
            ->exists();

        if (!$isOwner) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Apenas o proprietário pode gerenciar a assinatura.'], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Apenas o proprietário pode gerenciar a assinatura.');
        }

        if ($tenant->subscribed('default')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Você já possui uma assinatura ativa.'], 422);
            }
            return redirect()->route('dashboard')->with('error', 'Você já possui uma assinatura ativa.');
        }

        $checkout = $tenant->newSubscription('default', config('services.stripe.price_id'))
            ->trialDays(3)
            ->checkout([
                'success_url' => route('subscription.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('subscription.cancel'),
            ]);

        if ($request->expectsJson()) {
            return response()->json(['url' => $checkout->url]);
        }

        return redirect($checkout->url);
    }

    public function success(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Pagamento realizado com sucesso!']);
        }

        return redirect()->route('subscription.success')->with('success', 'Assinatura ativada com sucesso!');
    }

    public function cancel(Request $request): JsonResponse|RedirectResponse
    {
        $user   = $request->user();
        $tenant = $user->defaultTenant();

        if (!$tenant) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Nenhum tenant associado.'], 422);
            }
            return redirect()->route('subscription.cancel.view')->with('error', 'Nenhum tenant associado.');
        }

        $portalUrl = $tenant->billingPortalUrl(route('dashboard'));

        if ($request->expectsJson()) {
            return response()->json(['url' => $portalUrl]);
        }

        return redirect($portalUrl);
    }
}
