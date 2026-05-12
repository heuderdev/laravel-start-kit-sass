<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;
use JsonException;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

class CookiePreferenceService
{
    public const COOKIE_NAME = 'app_preferences';
    public const COOKIE_LIFETIME_MINUTES = 43200; // 30 dias

    public function read(Request $request): ?array
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

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function buildForStore(array $data): array
    {
        $now = now()->toIso8601String();

        return [
            'theme' => $data['theme'],
            'locale' => strtolower((string) $data['locale']),
            'tenant_id' => $data['tenant_id'] ?? null,
            'remember_workspace' => (bool) $data['remember_workspace'],
            'issued_at' => $now,
            'updated_at' => $now,
            'version' => 1,
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed>|null $current
     * @return array<string, mixed>
     */
    public function buildForUpdate(array $data, ?array $current): array
    {
        $current ??= [
            'theme' => 'system',
            'locale' => 'pt',
            'tenant_id' => null,
            'remember_workspace' => false,
            'issued_at' => now()->toIso8601String(),
            'version' => 1,
        ];

        return array_merge($current, $data, [
            'locale' => isset($data['locale'])
                ? strtolower((string) $data['locale'])
                : $current['locale'],
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function queue(array $payload): void
    {
        $encoded = json_encode($payload, JSON_THROW_ON_ERROR);
        $encrypted = Crypt::encryptString($encoded);

        Cookie::queue($this->makeCookie($encrypted));
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function renew(array $payload): array
    {
        $now = now()->toIso8601String();

        $payload['updated_at'] = $now;
        $payload['renewed_at'] = $now;

        return $payload;
    }

    public function forget(): void
    {
        Cookie::queue(Cookie::forget(
            self::COOKIE_NAME,
            '/',
            config('session.domain')
        ));
    }

    public function exists(Request $request): bool
    {
        return $this->read($request) !== null;
    }

    public function cookieName(): string
    {
        return self::COOKIE_NAME;
    }

    public function lifetimeMinutes(): int
    {
        return self::COOKIE_LIFETIME_MINUTES;
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
