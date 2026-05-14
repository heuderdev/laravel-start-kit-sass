<?php

// app/Http/Controllers/OnboardingController.php
namespace App\Http\Controllers;

use App\Http\Requests\OnboardingSetupWorkspaceRequest;
use App\Services\OnboardingService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly OnboardingService $onboardingService,
    ) {}

    public function show(string $step): View
    {
        $tenant = $this->context->get();

        return view("onboarding.steps.{$step}", compact('tenant'));
    }

    public function setupWorkspace(OnboardingSetupWorkspaceRequest $request): RedirectResponse
    {
        $tenant = $this->context->get();

        $tenant->update(['name' => $request->validated('workspace_name')]);

        $this->onboardingService->advance($tenant);

        return redirect()->route('onboarding.show', $tenant->onboarding_step);
    }

    public function skip(string $step): RedirectResponse
    {
        $tenant = $this->context->get();

        $this->onboardingService->advance($tenant);

        $nextStep = $tenant->fresh()->onboarding_step;

        if ($nextStep === 'completed') {
            return redirect()->route('dashboard');
        }

        return redirect()->route('onboarding.show', $nextStep);
    }
}
