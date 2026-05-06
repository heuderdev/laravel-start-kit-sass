<?php

namespace App\Models\Concerns;

use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->has(TenantContext::class) && app(TenantContext::class)->isSet()) {
                $builder->where(
                    (new static)->qualifyColumn('tenant_id'),
                    app(TenantContext::class)->id()
                );
            }
        });

        // Preenche tenant_id automaticamente no create
        static::creating(function ($model) {
            if (app()->has(TenantContext::class) && app(TenantContext::class)->isSet()) {
                $model->tenant_id ??= app(TenantContext::class)->id();
            }
        });
    }
}
