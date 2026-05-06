<?php
// app/Http/Middleware/ResolveTenantContext.php
namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantContext;
use App\Exceptions\TenantNotFoundException;
use App\Exceptions\TenantAccessDeniedException;
use Closure;
use Illuminate\Http\Request;

class ResolveTenantContext
{
    public function __construct(private TenantContext $context) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $tenantId = $this->resolveTenantId($request);

        if ($tenantId === null) {
            return response()->json([
                'type'   => 'https://httpstatuses.io/422',
                'title'  => 'Tenant not identified',
                'status' => 422,
            ], 422);
        }

        $tenant = Tenant::query()->find($tenantId);

        if (!$tenant) {
            throw new TenantNotFoundException("Tenant [{$tenantId}] not found.");
        }

        $user = $request->user();

        // ← AQUI: Valida se o usuário pertence ao tenant
        if ($user && !$user->belongsToTenant($tenant->id)) {
            throw new TenantAccessDeniedException(
                "User does not belong to tenant [{$tenantId}]."
            );
        }

        $this->context->set($tenant);

        return $next($request);
    }

    private function resolveTenantId(Request $request): ?int
    {
        // API: header X-Tenant-ID
        if ($id = $request->header('X-Tenant-ID')) {
            return (int) $id;
        }

        // Web: sessão
        if ($id = session('active_tenant_id')) {
            return (int) $id;
        }

        return null;
    }
}
