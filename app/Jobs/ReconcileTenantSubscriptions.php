<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;

class ReconcileTenantSubscriptions implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $timeout = 120;

    public function handle(): void
    {
        Tenant::query()
            ->where('stripe_id', '!=', null)
            ->orderBy('id')
            ->chunk(100, function (Collection $tenants): bool {
                foreach ($tenants as $tenant) {
                    $this->reconcile($tenant);
                }
                return true;
            });
    }

    private function reconcile(Tenant $tenant): void
    {
        try {
            $plan = $this->resolvePlan($tenant);

            if ($tenant->plan !== $plan) {
                app(AuditService::class)->log([
                    'acao'        => 'plan_updated',
                    'componente'  => 'reconcile.subscription',
                    'categoria'   => 'negocio',
                    'nivel'       => 'info',
                    'tabela'      => 'tenants',
                    'registro_id' => $tenant->id,
                    'descricao'   => 'Plano do tenant atualizado via reconciliação.',
                    'dados_antigos' => ['plan' => $tenant->plan],
                    'dados_novos'   => ['plan' => $plan],
                ]);

                $tenant->update(['plan' => $plan]);
            }
        } catch (\Throwable $e) {
            app(AuditService::class)->log([
                'acao'        => 'reconcile_failed',
                'componente'  => 'reconcile.subscription',
                'categoria'   => 'negocio',
                'nivel'       => 'error',
                'tabela'      => 'tenants',
                'registro_id' => $tenant->id,
                'descricao'   => 'Falha ao reconciliar tenant: ' . $e->getMessage(),
            ]);
        }
    }

    private function resolvePlan(Tenant $tenant): string
    {
        $subscription = $tenant->subscriptions()
            ->where('stripe_status', 'active')
            ->whereIn('stripe_status', ['active', 'trialing'])
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->latest()
            ->first();

        app(AuditService::class)->log([
            'acao'        => 'resolve_plan',
            'componente'  => 'reconcile.subscription',
            'categoria'   => 'negocio',
            'nivel'       => 'info',
            'tabela'      => 'subscriptions',
            'registro_id' => $subscription?->id,
            'descricao'   => 'Resolução de plano do tenant.',
            'dados_novos' => [
                'tenant_id'       => $tenant->id,
                'subscription_id' => $subscription?->id,
                'stripe_status'   => $subscription?->stripe_status,
                'ends_at'         => $subscription?->ends_at?->toIso8601String(),
                'trial_ends_at'   => $tenant->trial_ends_at?->toIso8601String(),
                'plan_resolved'   => $subscription
                    ? 'pro'
                    : ($tenant->trial_ends_at?->isFuture() ? 'pro (trial)' : 'free'),
            ],
        ]);

        if ($subscription) {
            return 'pro';
        }

        if ($tenant->trial_ends_at && $tenant->trial_ends_at->isFuture()) {
            return 'pro';
        }

        return 'free';
    }
}
