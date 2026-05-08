<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlanResource;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PricingController extends Controller
{
    private array $plans = [
        [
            'id'         => 'free',
            'name'       => 'Free',
            'price'      => 0,
            'currency'   => 'BRL',
            'interval'   => null,
            'trial_days' => 0,
            'features'   => [
                'Até 1 usuário',
                'Funcionalidades básicas',
                'Suporte via e-mail',
            ],
            'cta_label'  => 'Plano atual',
            'cta_action' => null,
            'price_id'   => null,
        ],
        [
            'id'         => 'pro',
            'name'       => 'Pro',
            'price'      => 9700,
            'currency'   => 'BRL',
            'interval'   => 'month',
            'trial_days' => 3,
            'features'   => [
                'Usuários ilimitados',
                'Todas as funcionalidades',
                'Suporte prioritário',
                'Acesso à API',
            ],
            'cta_label'  => 'Assinar agora',
            'cta_action' => 'subscription.checkout',
            'price_id'   => null, // resolvido em runtime via config
        ],
    ];

    public function __construct(private TenantContext $context) {}

    public function index(Request $request): View|JsonResponse
    {
        $currentPlan = $this->context->isSet() ? $this->context->get()->plan : null;

        $plans = collect($this->plans)->map(function (array $plan) use ($currentPlan) {
            // Injeta o price_id do Stripe somente no plano pro
            if ($plan['id'] === 'pro') {
                $plan['price_id'] = config('services.stripe.price_id');
            }

            $plan['is_current']      = $plan['id'] === $currentPlan;
            $plan['price_formatted'] = $plan['price'] > 0
                ? 'R$ ' . number_format($plan['price'] / 100, 2, ',', '.')
                : 'Grátis';

            return $plan;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'data'         => PlanResource::collection($plans),
                'current_plan' => $currentPlan,
            ]);
        }

        return view('pricing.index', compact('plans', 'currentPlan'));
    }

    public function show(Request $request, string $plan): View|JsonResponse
    {
        $found = collect($this->plans)->firstWhere('id', $plan);

        if (!$found) {
            if ($request->expectsJson()) {
                return response()->json([
                    'type'   => 'https://httpstatuses.io/404',
                    'title'  => 'Plan not found',
                    'status' => 404,
                ], 404);
            }
            abort(404, 'Plano não encontrado.');
        }

        if ($found['id'] === 'pro') {
            $found['price_id'] = config('services.stripe.price_id');
        }

        $currentPlan              = $this->context->isSet() ? $this->context->get()->plan : null;
        $found['is_current']      = $found['id'] === $currentPlan;
        $found['price_formatted'] = $found['price'] > 0
            ? 'R$ ' . number_format($found['price'] / 100, 2, ',', '.')
            : 'Grátis';

        if ($request->expectsJson()) {
            return response()->json(['data' => new PlanResource((object) $found)]);
        }

        return view('pricing.show', compact('found'));
    }
}
