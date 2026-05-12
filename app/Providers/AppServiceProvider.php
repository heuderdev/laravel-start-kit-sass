<?php

namespace App\Providers;

use App\Models\Tenant;
use App\Models\User;
use App\Policies\TenantPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\TenantContext::class);
        $this->app->singleton(\App\Services\AuditService::class);
    }

    public function boot(): void
    {
        $this->registerTenantGates();

        View::composer('*', function ($view) {
            if (app(\App\Services\TenantContext::class)->isSet()) {
                $view->with('tenant', app(\App\Services\TenantContext::class)->get());
            }
        });
    }

    private function registerTenantGates(): void
    {
        Gate::policy(Tenant::class, TenantPolicy::class);

        // Apenas o owner
        Gate::define('tenant.owner', function (User $user, Tenant $tenant): bool {
            return $user->hasRoleInTenant('owner', $tenant);
        });

        // Owner ou admin
        Gate::define('tenant.admin', function (User $user, Tenant $tenant): bool {
            return $user->hasAnyRoleInTenant(['owner', 'admin'], $tenant);
        });

        // Qualquer membro ativo do tenant
        Gate::define('tenant.member', function (User $user, Tenant $tenant): bool {
            return $user->belongsToTenant($tenant->id);
        });

        // Super admin global — bypass de todos os gates
        Gate::before(function (User $user): ?bool {
            if ($user->isSuperAdmin()) {
                return true;
            }

            return null;
        });
    }
}
