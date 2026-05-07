<?php

namespace App\Services;

use App\Jobs\CreateStripeCustomer;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProvisionNewAccount
{
    public function handle(string $name, string $email, ?string $password = null): User
    {
        return DB::transaction(function () use ($name, $email, $password) {
            $user = User::create([
                'name'     => $name,
                'email'    => $email,
                'password' => $password,
            ]);

            $tenant = Tenant::create([
                'name'    => $name,
                'slug'    => $this->generateUniqueSlug($name),
                'plan'    => 'free',
            ]);

            dispatch(new CreateStripeCustomer($tenant, $name, $email));

            $user->tenants()->attach($tenant->id, [
                'role'      => 'admin',
                'is_default' => true,
                'status'    => 'active',
                'joined_at' => now(),
            ]);

            return $user;
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
