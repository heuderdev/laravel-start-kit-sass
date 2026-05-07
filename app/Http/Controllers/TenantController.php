<?php

namespace App\Http\Controllers;

use App\Exceptions\TenantAccessDeniedException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        $tenants = $request->user()
            ->tenants()
            ->withPivot(['role', 'is_default', 'status', 'joined_at'])
            ->wherePivot('status', 'active')
            ->orderByPivot('is_default', 'desc')
            ->get()
            ->map(fn($tenant) => [
                'id'         => $tenant->id,
                'name'       => $tenant->name,
                'slug'       => $tenant->slug,
                'logo_url'   => $tenant->logo_url,
                'plan'       => $tenant->plan,
                'role'       => $tenant->pivot->role,
                'is_default' => (bool) $tenant->pivot->is_default,
                'joined_at'  => $tenant->pivot->joined_at,
            ]);

        if ($request->expectsJson()) {
            return response()->json(['data' => $tenants]);
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
            ->find($request->tenant_id);

        if (!$tenant) {
            throw new TenantAccessDeniedException(
                "User does not belong to tenant [{$request->tenant_id}]."
            );
        }

        // Atualiza o default na pivot
        $user->tenants()->updateExistingPivot($tenant->id, ['is_default' => true]);

        // Remove o default dos outros tenants
        $user->tenants()
            ->wherePivot('tenant_id', '!=', $tenant->id)
            ->each(fn($t) => $user->tenants()->updateExistingPivot($t->id, ['is_default' => false]));

        if ($request->expectsJson()) {
            // Revoga token atual e emite novo com contexto do tenant
            $request->user()->currentAccessToken()->delete();
            $token = $user->createToken('api', ['tenant:' . $tenant->id])->plainTextToken;

            return response()->json([
                'token'     => $token,
                'tenant_id' => $tenant->id,
                'tenant'    => [
                    'id'       => $tenant->id,
                    'name'     => $tenant->name,
                    'slug'     => $tenant->slug,
                    'plan'     => $tenant->plan,
                    'logo_url' => $tenant->logo_url,
                ],
            ]);
        }


        session(['active_tenant_id' => $tenant->id]);

        return redirect()->back();
    }
}
