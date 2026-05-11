<?php

namespace App\Http\Middleware;

use App\Exceptions\TenantAccessDeniedException;
use App\Exceptions\TenantNotFoundException;
use App\Models\Tenant;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantContext
{
    public function __construct(
        private readonly TenantContext $context,
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $user     = $request->user();
        $tenantId = $this->resolveTenantId($request);

        if ($tenantId === null) {
            return $this->handleMissingTenant($request, $user);
        }

        $tenant = Tenant::query()->find($tenantId);

        if (!$tenant) {
            throw new TenantNotFoundException("Tenant [{$tenantId}] not found.");
        }

        if ($user && !$user->belongsToTenant($tenant->id)) {
            throw new TenantAccessDeniedException(
                "User does not belong to tenant [{$tenant->id}]."
            );
        }

        $this->context->set($tenant);

        if (!$request->expectsJson()) {
            $request->session()->put('active_tenant_id', $tenant->id);
        }

        return $next($request);
    }

    private function resolveTenantId(Request $request): ?int
    {
        $headerTenantId = $request->header('X-Tenant-ID');

        if (filled($headerTenantId)) {
            return (int) $headerTenantId;
        }

        if (!$request->expectsJson()) {
            $sessionTenantId = $request->session()->get('active_tenant_id');

            if (filled($sessionTenantId)) {
                return (int) $sessionTenantId;
            }
        }

        return null;
    }

    private function handleMissingTenant(Request $request, mixed $user): JsonResponse|RedirectResponse
    {
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'type'   => 'https://httpstatuses.io/401',
                    'title'  => 'Unauthenticated',
                    'status' => Response::HTTP_UNAUTHORIZED,
                    'detail' => 'Usuário não autenticado.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            return redirect()->route('login');
        }

        $defaultTenant = $user->defaultTenant();

        if ($defaultTenant) {
            $this->context->set($defaultTenant);

            if (!$request->expectsJson()) {
                $request->session()->put('active_tenant_id', $defaultTenant->id);
            }

            return $request->expectsJson()
                ? response()->json([
                    'type'              => 'https://httpstatuses.io/428',
                    'title'             => 'Tenant header required',
                    'status'            => 428,
                    'detail'            => 'Envie o header X-Tenant-ID nas requisições de API.',
                    'default_tenant_id' => $defaultTenant->id,
                ], 428)
                : redirect()->route('dashboard');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'type'   => 'https://httpstatuses.io/422',
                'title'  => 'Tenant not identified',
                'status' => 422,
                'detail' => 'Nenhum tenant ativo foi identificado para a requisição.',
            ], 422);
        }

        return redirect()
            ->route('tenants.index')
            ->with('warning', 'Selecione um workspace para continuar.');
    }
}
