<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ListUserTenantsService;
use App\Services\SwitchTenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function __construct(
        private readonly ListUserTenantsService $listUserTenantsService,
        private readonly SwitchTenantService $switchTenantService,
    ) {}

    public function index(Request $request): JsonResponse|View
    {
        $tenants = $this->listUserTenantsService->handle($request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $tenants,
            ]);
        }

        return view('tenants.index', ['tenants' => $tenants]);
    }

    public function switch(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
        ]);

        $user = $request->user();
        $tenant = $this->switchTenantService->handle(
            user: $user,
            tenantId: $request->integer('tenant_id'),
        );

        if ($request->expectsJson()) {
            $request->user()->currentAccessToken()?->delete();

            $token = $user->createToken('api', ['tenant:' . $tenant->id])->plainTextToken;

            return response()->json([
                'token' => $token,
                'tenant_id' => $tenant->id,
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                    'plan' => $tenant->plan,
                    'logo_url' => $tenant->logo_url,
                ],
            ]);
        }

        $request->session()->put('active_tenant_id', $tenant->id);

        return redirect()
            ->back()
            ->with('success', "Tenant alterado para [{$tenant->name}].");
    }
}
