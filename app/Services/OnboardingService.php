<?php

// app/Services/OnboardingService.php
namespace App\Services;

use App\Models\Tenant;

class OnboardingService
{
    private const STEPS = [
        'pending'           => 'setup_workspace',
        'setup_workspace'   => 'invite_team',
        'invite_team'       => 'choose_plan',
        'choose_plan'       => 'completed',
    ];

    public function advance(Tenant $tenant): void
    {
        $next = self::STEPS[$tenant->onboarding_step] ?? 'completed';

        $tenant->update(['onboarding_step' => $next]);
    }

    public function complete(Tenant $tenant): void
    {
        $tenant->update(['onboarding_step' => 'completed']);
    }

    public function isCompleted(Tenant $tenant): bool
    {
        return $tenant->onboarding_step === 'completed';
    }
}
