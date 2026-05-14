<?php

namespace App\Services;

use App\Models\OnboardingToken;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;

class OnboardingAutoLoginService
{
    private const TTL_MINUTES = 15;

    public function __construct(
        private readonly AuditService $auditService,
    ) {}

    public function generateUrl(User $user, Tenant $tenant): string
    {
        OnboardingToken::query()
            ->where('user_id', $user->id)
            ->where('tenant_id', $tenant->id)
            ->delete();

        $token = OnboardingToken::create([
            'user_id'    => $user->id,
            'tenant_id'  => $tenant->id,
            'token'      => Str::random(64),
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
        ]);

        $this->auditService->log([
            'componente'  => 'onboarding',
            'categoria'   => 'autologin',
            'acao'        => 'token_gerado',
            'tabela'      => 'onboarding_tokens',
            'registro_id' => $token->id,
            'fk_referencia' => $tenant->id,
            'descricao'   => "Token de auto-login gerado para o usuário {$user->name} no workspace {$tenant->name}. Expira em " . $token->expires_at->format('d/m/Y H:i:s') . '.',
            'nivel'       => 'info',
            'dados_novos' => [
                'user_id'    => $user->id,
                'tenant_id'  => $tenant->id,
                'expires_at' => $token->expires_at,
            ],
        ]);

        return route('onboarding.autologin', [
            'token' => $token->token,
            'step'  => $tenant->onboarding_step,
        ]);
    }

    public function validateAndConsume(string $token): ?OnboardingToken
    {
        $record = OnboardingToken::query()
            ->with(['user', 'tenant'])
            ->where('token', $token)
            ->first();

        if (! $record || $record->isExpired()) {
            if ($record) {
                $this->auditService->log([
                    'componente'    => 'onboarding',
                    'categoria'     => 'autologin',
                    'acao'          => 'token_expirado',
                    'tabela'        => 'onboarding_tokens',
                    'registro_id'   => $record->id,
                    'fk_referencia' => $record->tenant_id,
                    'descricao'     => "Token de auto-login expirado para o usuário ID {$record->user_id} no tenant ID {$record->tenant_id}.",
                    'nivel'         => 'warning',
                    'dados_antes'   => [
                        'user_id'    => $record->user_id,
                        'tenant_id'  => $record->tenant_id,
                        'expires_at' => $record->expires_at,
                    ],
                ]);

                $record->deleteOrFail();
            }

            return null;
        }

        $this->auditService->log([
            'componente'    => 'onboarding',
            'categoria'     => 'autologin',
            'acao'          => 'token_consumido',
            'tabela'        => 'onboarding_tokens',
            'registro_id'   => $record->id,
            'fk_referencia' => $record->tenant_id,
            'descricao'     => "Auto-login realizado com sucesso para o usuário {$record->user->name} no workspace {$record->tenant->name}.",
            'nivel'         => 'info',
            'dados_antes'   => [
                'user_id'   => $record->user_id,
                'tenant_id' => $record->tenant_id,
            ],
        ]);

        $record->deleteOrFail();

        return $record;
    }
}
