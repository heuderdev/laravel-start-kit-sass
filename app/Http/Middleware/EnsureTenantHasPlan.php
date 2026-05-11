<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;

class EnsureTenantHasPlan
{
    public function __construct(
        private readonly TenantContext $context,
    ) {}

    public function handle(Request $request, Closure $next, string ...$plans): mixed
    {
        $tenant = $this->context->get();
        $user   = $request->user();

        $hasActiveBypass = $tenant->bypass_plan_limits === true
            && (
                $tenant->bypass_plan_limits_data_limite === null
                || $tenant->bypass_plan_limits_data_limite->isFuture()
            );

        if ($hasActiveBypass) {
            return $next($request);
        }

        $canManageBilling = $user !== null
            && $user->hasAnyRoleInTenant(['owner', 'admin'], $tenant);

        if (!in_array($tenant->plan, $plans, true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'type'    => 'https://httpstatuses.io/403',
                    'title'   => $canManageBilling ? 'Plan upgrade required' : 'Tenant inactive',
                    'status'  => 403,
                    'detail'  => $canManageBilling
                        ? 'Your current plan does not have access to this resource.'
                        : 'The organization you belong to does not have an active plan.',
                    'current' => $tenant->plan,
                    'allowed' => $plans,
                ], 403);
            }

            if ($canManageBilling) {
                return redirect()
                    ->route('pricing.index')
                    ->with('warning', 'Faça upgrade do seu plano para acessar este recurso.');
            }

            return redirect()
                ->route('tenant.inactive')
                ->with('warning', 'A empresa que você pertence não possui acesso a este recurso.');
        }

        if ($tenant->plan === 'pro') {
            $isActive = $tenant->subscribed('default')
                || $tenant->onTrial('default')
                || $tenant->onGenericTrial();

            if (!$isActive) {
                $tenant->update(['plan' => 'free']);

                if ($request->expectsJson()) {
                    return response()->json([
                        'type'   => 'https://httpstatuses.io/403',
                        'title'  => 'Subscription inactive',
                        'status' => 403,
                        'detail' => 'Your subscription is no longer active.',
                    ], 403);
                }

                if ($canManageBilling) {
                    return redirect()
                        ->route('pricing.index')
                        ->with('warning', 'Sua assinatura expirou. Renove para continuar.');
                }

                return redirect()
                    ->route('tenant.inactive')
                    ->with('warning', 'A assinatura da sua empresa expirou. Contate o administrador.');
            }
        }

        return $next($request);
    }
}
