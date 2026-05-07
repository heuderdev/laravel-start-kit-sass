<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;

class EnsureTenantHasPlan
{
    public function __construct(private TenantContext $context) {}

    /**
     * Uso nas rotas:
     *   ->middleware('plan:pro')           → exige plano pro
     *   ->middleware('plan:pro,enterprise') → aceita pro OU enterprise
     */
    public function handle(Request $request, Closure $next, string ...$plans): mixed
    {
        $tenant = $this->context->get();

        if (in_array($tenant->plan, $plans, strict: true)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'type'    => 'https://httpstatuses.io/403',
                'title'   => 'Plan upgrade required',
                'status'  => 403,
                'detail'  => 'Your current plan does not have access to this resource.',
                'current' => $tenant->plan,
                'allowed' => $plans,
            ], 403);
        }

        return redirect()->route('subscription.checkout')
            ->with('warning', 'Faça upgrade do seu plano para acessar este recurso.');
    }
}
