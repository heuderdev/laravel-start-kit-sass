<?php

namespace App\Http\Controllers;

use App\Exceptions\TenantAccessDeniedException;
use App\Services\TenantContext;
use App\Services\TenantMembershipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly TenantMembershipService $membershipService,
    ) {}

    public function index(Request $request): JsonResponse|View
    {

        $tenants = $request->user()
            ->tenants()
            ->withPivot(['role', 'is_default', 'status', 'joined_at'])
            ->wherePivot('status', 'active')
            ->orderByPivot('is_default', 'desc')
            ->get()
            ->map(function ($tenant) use ($request) {
                return [
                    'id'         => $tenant->id,
                    'name'       => $tenant->name,
                    'slug'       => $tenant->slug,
                    'logo_url'   => $tenant->logo_url,
                    'plan'       => $tenant->plan,
                    'role'       => $request->user()->roleInTenant($tenant),
                    'is_default' => (bool) $tenant->pivot->is_default,
                    'joined_at'  => $tenant->pivot->joined_at,
                ];
            });

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

        $tenant = $user->tenants()
            ->wherePivot('status', 'active')
            ->find($request->integer('tenant_id'));

        if (!$tenant) {
            throw new TenantAccessDeniedException(
                "User does not belong to tenant [{$request->tenant_id}]."
            );
        }

        $this->membershipService->setDefaultTenant($user, $tenant);

        if ($request->expectsJson()) {
            $request->user()->currentAccessToken()?->delete();

            $token = $user->createToken('api', ['tenant:' . $tenant->id])->plainTextToken;

            return response()->json([
                'token'     => $token,
                'tenant_id' => $tenant->id,
                'tenant'    => [
                    'id'      => $tenant->id,
                    'name'    => $tenant->name,
                    'slug'    => $tenant->slug,
                    'plan'    => $tenant->plan,
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
