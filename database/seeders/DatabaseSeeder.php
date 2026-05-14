<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\ProvisionNewAccount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(ProvisionNewAccount $provisioner): void
    {
        if (app()->environment('local', 'development')) {
            $this->call(DevelopmentSeeder::class);
        }

        // $provisioner->handle(
        //     name: 'SUPER ADMINISTRADOR',
        //     email: 'super-admin@sass.com.br',
        //     password: 'password',
        //     is_super_admin: true
        // );

        // $provisioner->handle(
        //     name: 'Heuder Sena',
        //     email: 'heuder@sicoob.com.br',
        //     password: 'password',
        // );

        // $provisioner->handle(
        //     name: 'Heuder Dev',
        //     email: 'heuderdev@gmail.com',
        //     password: 'password',
        // );
    }
}
