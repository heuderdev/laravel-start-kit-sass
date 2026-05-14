<?php
// app/Http/Middleware/EnsureOnboardingIsComplete.php
namespace App\Http\Middleware;

use App\Services\OnboardingAutoLoginService;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;

class EnsureOnboardingIsComplete
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly OnboardingAutoLoginService $autoLoginService,
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        if (! $this->context->isSet()) {
            return $next($request);
        }

        $tenant = $this->context->get();

        if ($tenant->onboarding_step !== 'completed') {
            if ($request->expectsJson()) {
                $url = $this->autoLoginService->generateUrl($request->user(), $tenant);

                return response()->json([
                    'type'   => 'https://httpstatuses.io/403',
                    'title'  => 'Onboarding incompleto',
                    'status' => 403,
                    'detail' => 'Seu cadastro ainda não foi finalizado. Acesse o link abaixo no navegador para concluir a configuração do seu workspace.',
                    'action' => [
                        'message'    => 'Abra o link abaixo no seu navegador para continuar. O link expira em 15 minutos.',
                        'url'        => $url,
                        'expires_in' => 900, // segundos
                    ],
                ], 403);
            }

            return redirect()->route('onboarding.show', $tenant->onboarding_step);
        }

        return $next($request);
    }
}
