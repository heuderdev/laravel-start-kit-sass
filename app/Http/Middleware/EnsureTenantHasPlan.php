<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;

class EnsureTenantHasPlan
{
    public function __construct(private TenantContext $context) {}
    public function handle(Request $request, Closure $next, string ...$plans): mixed
    {
        $tenant = $this->context->get();

        $hasActiveBypass = $tenant->bypass_plan_limits === true
            && (
                $tenant->bypass_plan_limits_data_limite === null
                || $tenant->bypass_plan_limits_data_limite->isFuture()
            );
        if ($hasActiveBypass) {
            return $next($request);
        }

        if (!in_array($tenant->plan, $plans)) {
            // Descobre o role do usuário autenticado neste tenant
            $role = $tenant->users()
                ->where('user_id', auth()->id())
                ->value('tenant_user.role'); // owner | admin | member | funcionário | cliente

            $canManageBilling = in_array($role, ['owner', 'admin']);

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
                return redirect()->route('pricing.index')
                    ->with('warning', 'Faça upgrade do seu plano para acessar este recurso.');
            }

            return redirect()->route('tenant.inactive') // view explicando que o tenant está inadimplente
                ->with('warning', 'A empresa que você pertence não possui acesso a este recurso.');
        }

        // Double-check: plano pro no banco mas subscription inativa no Stripe
        if ($tenant->plan === 'pro') {
            $isActive = $tenant->subscribed('default')
                || $tenant->onTrial('default')
                || $tenant->onGenericTrial();

            if (!$isActive) {
                $tenant->update(['plan' => 'free']);

                $role = $tenant->users()
                    ->where('user_id', auth()->id())
                    ->value('tenant_users.role');

                $canManageBilling = in_array($role, ['owner', 'admin']);

                if ($request->expectsJson()) {
                    return response()->json([
                        'type'   => 'https://httpstatuses.io/403',
                        'title'  => 'Subscription inactive',
                        'status' => 403,
                        'detail' => 'Your subscription is no longer active.',
                    ], 403);
                }

                if ($canManageBilling) {
                    return redirect()->route('pricing.index')
                        ->with('warning', 'Sua assinatura expirou. Renove para continuar.');
                }

                return redirect()->route('tenant.inactive')
                    ->with('warning', 'A assinatura da sua empresa expirou. Contate o administrador.');
            }
        }

        return $next($request);
    }
}
