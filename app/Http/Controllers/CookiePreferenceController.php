<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;
use JsonException;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

class CookiePreferenceController extends Controller
{
    private const COOKIE_NAME = 'app_preferences';
    private const COOKIE_LIFETIME_MINUTES = 43200; // 30 dias

    public function index(Request $request): \Illuminate\View\View|JsonResponse
    {
        $payload = $this->readCookie($request);


        if ($request->expectsJson()) {
            return response()->json([
                'exists' => $payload !== null,
                'cookie' => self::COOKIE_NAME,
                'data' => $payload,
            ]);
        }

        return view('cookies.index', [
            'cookieName' => self::COOKIE_NAME,
            'cookieExists' => $payload !== null,
            'cookieData' => $payload ?? [],
        ]);
    }
    public function show(Request $request): JsonResponse|RedirectResponse
    {
        $payload = $this->readCookie($request);

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

        $payload = [
            'theme' => $data['theme'],
            'locale' => strtolower($data['locale']),
            'tenant_id' => $data['tenant_id'] ?? null,
            'remember_workspace' => (bool) $data['remember_workspace'],
            'issued_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
            'version' => 1,
        ];

        $this->queueEncryptedCookie($payload);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Cookie criado com sucesso.',
                'cookie' => self::COOKIE_NAME,
                'expires_in_minutes' => self::COOKIE_LIFETIME_MINUTES,
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

        $current = $this->readCookie($request) ?? [
            'theme' => 'system',
            'locale' => 'pt',
            'tenant_id' => null,
            'remember_workspace' => false,
            'issued_at' => now()->toIso8601String(),
            'version' => 1,
        ];

        $payload = array_merge($current, $data, [
            'locale' => isset($data['locale']) ? strtolower($data['locale']) : $current['locale'],
            'updated_at' => now()->toIso8601String(),
        ]);

        $this->queueEncryptedCookie($payload);

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
        $payload = $this->readCookie($request);

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

        $payload['updated_at'] = now()->toIso8601String();
        $payload['renewed_at'] = now()->toIso8601String();

        $this->queueEncryptedCookie($payload);

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
        Cookie::queue(Cookie::forget(
            self::COOKIE_NAME,
            '/',
            config('session.domain')
        ));

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
        $exists = $this->readCookie($request) !== null;

        if ($request->expectsJson()) {
            return response()->json([
                'exists' => $exists,
            ]);
        }

        return redirect()
            ->back()
            ->with('cookie_exists', $exists);
    }

    private function readCookie(Request $request): ?array
    {
        $raw = $request->cookie(self::COOKIE_NAME);

        if (!$raw) {
            return null;
        }

        try {
            $decrypted = Crypt::decryptString($raw);
            $decoded = json_decode($decrypted, true, 512, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? $decoded : null;
        } catch (DecryptException | JsonException) {
            return null;
        }
    }

    private function queueEncryptedCookie(array $payload): void
    {
        $encoded = json_encode($payload, JSON_THROW_ON_ERROR);
        $encrypted = Crypt::encryptString($encoded);

        Cookie::queue($this->makeCookie($encrypted));
    }

    private function makeCookie(string $value): SymfonyCookie
    {
        return cookie(
            name: self::COOKIE_NAME,
            value: $value,
            minutes: self::COOKIE_LIFETIME_MINUTES,
            path: '/',
            domain: config('session.domain'),
            secure: app()->environment('production'),
            httpOnly: true,
            raw: false,
            sameSite: 'lax'
        );
    }
}
