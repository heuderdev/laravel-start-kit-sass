<?php

namespace App\Jobs;

use App\Models\Tenant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
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
                Log::info('ReconcileTenantSubscriptions: plano atualizado', [
                    'tenant_id' => $tenant->id,
                    'de'        => $tenant->plan,
                    'para'      => $plan,
                ]);

                $tenant->update(['plan' => $plan]);
            }
        } catch (\Throwable $e) {
            Log::error('ReconcileTenantSubscriptions: falha ao reconciliar tenant', [
                'tenant_id' => $tenant->id,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    private function resolvePlan(Tenant $tenant): string
    {
        $subscription = $tenant->subscriptions()
            ->where('stripe_status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->latest()
            ->first();

        Log::info(json_encode($subscription));

        if ($subscription) {
            return 'pro';
        }

        if ($tenant->trial_ends_at && $tenant->trial_ends_at->isFuture()) {
            return 'pro';
        }

        return 'free';
    }
}
