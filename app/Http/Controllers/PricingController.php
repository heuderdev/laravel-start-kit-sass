<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\PlanResource;
use App\Services\ListPricingPlansService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PricingController extends Controller
{
    public function __construct(
        private readonly ListPricingPlansService $listPricingPlansService,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $plans = $this->listPricingPlansService->getAll();
        $currentPlan = $this->listPricingPlansService->getCurrentPlan();

        if ($request->expectsJson()) {
            return response()->json([
                'data' => PlanResource::collection($plans),
                'current_plan' => $currentPlan,
            ]);
        }

        return view('pricing.index', compact('plans', 'currentPlan'));
    }

    public function show(Request $request, string $plan): View|JsonResponse
    {
        try {
            $found = $this->listPricingPlansService->getById($plan);
        } catch (DomainException $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'type' => 'https://httpstatuses.io/404',
                    'title' => 'Plan not found',
                    'status' => 404,
                ], 404);
            }

            abort(404, $exception->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'data' => new PlanResource($found),
            ]);
        }

        return view('pricing.show', compact('found'));
    }
}
