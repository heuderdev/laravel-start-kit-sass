<?php

namespace App\Providers;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use Spatie\Permission\PermissionRegistrar;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
    }

    public function boot(): void
    {
        Cashier::useCustomerModel(Tenant::class);

        Gate::before(function (User $user, string $ability): ?bool {
            $permissionRegistrar = app(PermissionRegistrar::class);
            $currentTeamId = $permissionRegistrar->getPermissionsTeamId();

            $permissionRegistrar->setPermissionsTeamId(null);

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            $isSuperAdmin = $user->hasRole('super-admin');

            $permissionRegistrar->setPermissionsTeamId($currentTeamId);

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            return $isSuperAdmin ? true : null;
        });
    }
}
