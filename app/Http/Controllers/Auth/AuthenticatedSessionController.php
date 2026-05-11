<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        $tenant = $user?->defaultTenant();

        if (!$tenant) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Sua conta não possui um tenant ativo associado.',
                ]);
        }

        $request->session()->put('active_tenant_id', $tenant->id);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget('active_tenant_id');

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function storeApi(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Credenciais inválidas.',
            ], 401);
        }

        $user = Auth::user();
        $tenant = $user?->defaultTenant();

        if (!$tenant) {
            Auth::guard('web')->logout();

            return response()->json([
                'message' => 'Sua conta não possui um tenant ativo associado.',
            ], 422);
        }

        $token = $user->createToken('api', ['tenant:' . $tenant->id])->plainTextToken;

        return response()->json([
            'token' => $token,
            'tenant_id' => $tenant->id,
            'user' => $user,
        ]);
    }

    public function destroyApi(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout realizado.',
        ]);
    }
}
