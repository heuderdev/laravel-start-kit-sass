<?php

use App\Exceptions\ApplicationException;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->alias([
            'tenant' => \App\Http\Middleware\ResolveTenantContext::class,
            'plan'    => \App\Http\Middleware\EnsureTenantHasPlan::class,
            'super-admin' => \App\Http\Middleware\EnsureUserIsSuperAdmin::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'stripe/*',
            'api/stripe/*',
        ]);

        $middleware->prependToGroup('api', [
            ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $isApiRequest = static fn(Request $request): bool =>
        $request->is('api/*') || $request->expectsJson();

        $exceptions->stopIgnoring(AuthenticationException::class);

        $exceptions->shouldRenderJsonWhen(
            fn(Request $request, Throwable $e) => $isApiRequest($request)
        );

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($isApiRequest) {
            if (!$isApiRequest($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Você precisa estar autenticado para acessar este recurso.',
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'status' => Response::HTTP_UNAUTHORIZED,
                ],
            ], Response::HTTP_UNAUTHORIZED);
        });

        $exceptions->render(function (ApplicationException $e, Request $request) use ($isApiRequest) {
            if (!$isApiRequest($request)) {
                return null;
            }

            return response()->json(
                $e->toArray(app()->hasDebugModeEnabled()),
                $e->status()
            );
        });
    })->create();
