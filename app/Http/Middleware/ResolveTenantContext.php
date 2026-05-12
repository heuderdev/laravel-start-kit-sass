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
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantContext
{
    public function __construct(
        private readonly TenantContext $context,
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();
        $tenantId = $this->resolveTenantId($request);

        if ($tenantId === null) {
            $earlyResponse = $this->tryResolveFromDefault($request, $user);

            if ($earlyResponse !== null) {
                return $earlyResponse;
            }

            return $next($request);
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
        $this->putSessionTenantId($request, $tenant->id);

        return $next($request);
    }

    private function resolveTenantId(Request $request): ?int
    {
        $headerTenantId = $request->header('X-Tenant-ID');

        if (filled($headerTenantId)) {
            return (int) $headerTenantId;
        }

        $sessionTenantId = $this->getSessionTenantId($request);

        if (filled($sessionTenantId)) {
            return (int) $sessionTenantId;
        }

        return null;
    }

    private function tryResolveFromDefault(Request $request, mixed $user): JsonResponse|RedirectResponse|null
    {
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'type' => 'https://httpstatuses.io/401',
                    'title' => 'Unauthenticated',
                    'status' => Response::HTTP_UNAUTHORIZED,
                    'detail' => 'Usuário não autenticado.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            return redirect()->route('login');
        }

        $defaultTenant = $user->defaultTenant();

        if ($defaultTenant) {
            $this->context->set($defaultTenant);
            $this->putSessionTenantId($request, $defaultTenant->id);

            return null;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'type' => 'https://httpstatuses.io/422',
                'title' => 'Tenant not identified',
                'status' => 422,
                'detail' => 'Nenhum tenant ativo foi identificado para a requisição.',
            ], 422);
        }

        return redirect()
            ->route('tenants.index')
            ->with('warning', 'Selecione um workspace para continuar.');
    }

    private function getSessionTenantId(Request $request): ?int
    {
        if (!$request->hasSession()) {
            return null;
        }

        $tenantId = $request->session()->get('active_tenant_id');

        return filled($tenantId) ? (int) $tenantId : null;
    }

    private function putSessionTenantId(Request $request, int $tenantId): void
    {
        if (!$request->hasSession()) {
            return;
        }

        $request->session()->put('active_tenant_id', $tenantId);
    }
}
