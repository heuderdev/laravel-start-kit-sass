<?php

namespace App\Services;

use App\Jobs\CreateStripeCustomer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProvisionNewAccount
{
    public function __construct(
        private readonly TenantMembershipService $tenantMembershipService,
    ) {}

    public function handle(string $name, string $email, ?string $password = null, bool $is_super_admin = false): User
    {
        return DB::transaction(function () use ($name, $email, $password, $is_super_admin): User {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'is_super_admin' => $is_super_admin,
            ]);

            $tenant = Tenant::create([
                'name' => $name,
                'slug' => $this->generateUniqueSlug($name),
                'plan' => 'free',
            ]);

            $this->tenantMembershipService->attachUserToTenant(
                user: $user,
                tenant: $tenant,
                role: 'owner',
                isDefault: true,
                status: 'active',
            );

            CreateStripeCustomer::dispatch($tenant, $name, $email)->afterCommit();

            return $user->fresh();
        });
    }

    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $count = 1;

        while (Tenant::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }
}
