<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SuperAdminTenantBypassRequest;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuperAdminTenantController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Tenant::query()
            ->select([
                'id',
                'name',
                'slug',
                'plan',
                'bypass_plan_limits',
                'bypass_plan_limits_data_limite',
                'created_at',
            ])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim((string) $request->string('search'));

                $q->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");

                    if (is_numeric($search)) {
                        $subQuery->orWhere('id', (int) $search);
                    }
                });
            })
            ->orderByDesc('id');

        $tenants = $query->paginate(20)->withQueryString();

        if ($request->expectsJson()) {
            return response()->json($tenants);
        }

        return view('admin.tenants.index', [
            'tenants' => $tenants,
            'filters' => [
                'search' => $request->string('search')->toString(),
            ],
        ]);
    }

    public function edit(Request $request, Tenant $tenant): View|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $tenant,
            ]);
        }

        return view('admin.tenants.edit', [
            'tenant' => $tenant,
        ]);
    }

    public function updateBypass(
        SuperAdminTenantBypassRequest $request,
        Tenant $tenant
    ): JsonResponse|RedirectResponse {
        $data = $request->validated();

        $enabled = (bool) ($data['bypass_plan_limits'] ?? false);
        $limitDate = $data['bypass_plan_limits_data_limite'] ?? null;

        if (!$enabled) {
            $limitDate = null;
        }

        $tenant->update([
            'bypass_plan_limits' => $enabled,
            'bypass_plan_limits_data_limite' => $limitDate,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Bypass do tenant atualizado com sucesso.',
                'data' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                    'plan' => $tenant->plan,
                    'bypass_plan_limits' => (bool) $tenant->fresh()->bypass_plan_limits,
                    'bypass_plan_limits_data_limite' => optional($tenant->fresh()->bypass_plan_limits_data_limite)?->toDateTimeString(),
                ],
            ]);
        }

        return redirect()
            ->route('admin.tenants.edit', $tenant)
            ->with('success', 'Bypass do tenant atualizado com sucesso.');
    }
}
