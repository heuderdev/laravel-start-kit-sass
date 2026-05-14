<?php

namespace App\Http\Controllers;

use App\Services\OnboardingAutoLoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingAutoLoginController extends Controller
{
    public function __construct(
        private readonly OnboardingAutoLoginService $autoLoginService,
    ) {}

    public function __invoke(Request $request, string $token): RedirectResponse
    {
        $record = $this->autoLoginService->validateAndConsume($token);

        if (! $record) {
            return redirect()->route('login')
                ->with('error', 'O link expirou ou já foi utilizado. Faça login normalmente.');
        }

        // Loga o usuário
        Auth::login($record->user, remember: false);

        // Seta o tenant na sessão
        session(['active_tenant_id' => $record->tenant->id]);

        return redirect()->route('onboarding.show', $record->tenant->onboarding_step);
    }
}
