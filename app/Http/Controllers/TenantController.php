<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
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
}
