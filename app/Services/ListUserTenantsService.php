<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class ListUserTenantsService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function handle(User $user): Collection
    {
        return $user->tenants()
            ->withPivot(['role', 'is_default', 'status', 'joined_at'])
            ->wherePivot('status', 'active')
            ->orderByPivot('is_default', 'desc')
            ->get()
            ->map(function ($tenant) use ($user): array {
                return [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                    'logo_url' => $tenant->logo_url,
                    'plan' => $tenant->plan,
                    'role' => $user->roleInTenant($tenant),
                    'is_default' => (bool) $tenant->pivot->is_default,
                    'joined_at' => $tenant->pivot->joined_at,
                ];
            });
    }
}
