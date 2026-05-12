<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CookiePreferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CookiePreferenceController extends Controller
{
    public function __construct(
        private readonly CookiePreferenceService $cookiePreferenceService,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $payload = $this->cookiePreferenceService->read($request);

        if ($request->expectsJson()) {
            return response()->json([
                'exists' => $payload !== null,
                'cookie' => $this->cookiePreferenceService->cookieName(),
                'data' => $payload,
            ]);
        }

        return view('cookies.index', [
            'cookieName' => $this->cookiePreferenceService->cookieName(),
            'cookieExists' => $payload !== null,
            'cookieData' => $payload ?? [],
        ]);
    }

    public function show(Request $request): JsonResponse|RedirectResponse
    {
        $payload = $this->cookiePreferenceService->read($request);

        if ($request->expectsJson()) {
            return response()->json([
                'exists' => $payload !== null,
                'data' => $payload,
            ]);
        }

        return redirect()
            ->back()
            ->with('cookie_data', $payload);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'theme' => ['required', 'string', 'in:light,dark,system'],
            'locale' => ['required', 'string', 'size:2'],
            'tenant_id' => ['nullable', 'integer'],
            'remember_workspace' => ['required', 'boolean'],
        ]);

        $payload = $this->cookiePreferenceService->buildForStore($data);
        $this->cookiePreferenceService->queue($payload);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Cookie criado com sucesso.',
                'cookie' => $this->cookiePreferenceService->cookieName(),
                'expires_in_minutes' => $this->cookiePreferenceService->lifetimeMinutes(),
                'data' => $payload,
            ], 201);
        }

        return redirect()
            ->back()
            ->with('success', 'Preferências salvas com sucesso.');
    }

    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'theme' => ['sometimes', 'string', 'in:light,dark,system'],
            'locale' => ['sometimes', 'string', 'size:2'],
            'tenant_id' => ['sometimes', 'nullable', 'integer'],
            'remember_workspace' => ['sometimes', 'boolean'],
        ]);

        $current = $this->cookiePreferenceService->read($request);
        $payload = $this->cookiePreferenceService->buildForUpdate($data, $current);

        $this->cookiePreferenceService->queue($payload);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Cookie atualizado com sucesso.',
                'data' => $payload,
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Preferências atualizadas com sucesso.');
    }

    public function renew(Request $request): JsonResponse|RedirectResponse
    {
        $payload = $this->cookiePreferenceService->read($request);

        if ($payload === null) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Cookie não encontrado.',
                ], 404);
            }

            return redirect()
                ->back()
                ->with('error', 'Cookie não encontrado.');
        }

        $payload = $this->cookiePreferenceService->renew($payload);
        $this->cookiePreferenceService->queue($payload);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Cookie renovado com sucesso por mais 30 dias.',
                'data' => $payload,
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Cookie renovado com sucesso por mais 30 dias.');
    }

    public function destroy(Request $request): JsonResponse|RedirectResponse
    {
        $this->cookiePreferenceService->forget();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Cookie removido com sucesso.',
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Cookie removido com sucesso.');
    }

    public function exists(Request $request): JsonResponse|RedirectResponse
    {
        $exists = $this->cookiePreferenceService->exists($request);

        if ($request->expectsJson()) {
            return response()->json([
                'exists' => $exists,
            ]);
        }

        return redirect()
            ->back()
            ->with('cookie_exists', $exists);
    }
}
