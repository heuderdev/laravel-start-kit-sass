<?php

namespace App\Http\Controllers;

use App\Exceptions\TenantAccessDeniedException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        DB::transaction(function () use ($user, $tenant) {
            DB::table('tenant_user')
                ->where('user_id', $user->id)
                ->update([
                    'is_default' => false,
                    'updated_at' => now(),
                ]);

            DB::table('tenant_user')
                ->where('user_id', $user->id)
                ->where('tenant_id', $tenant->id)
                ->where('status', 'active')
                ->update([
                    'is_default' => true,
                    'updated_at' => now(),
                ]);
        });

        if ($request->expectsJson()) {
            $currentToken = $request->user()->currentAccessToken();

            if ($currentToken) {
                $currentToken->delete();
            }

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
