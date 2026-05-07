<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\ProvisionNewAccount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(ProvisionNewAccount $provisioner): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Heuder Rodrigues de Sena',
        //     'email' => 'heuderdev@gmail.com',
        //     'password' => bcrypt('password'),
        // ]);

        // Conta 1 — Admin principal
        $provisioner->handle(
            name: 'Heuder Sena',
            email: 'heuder@sicoob.com.br',
            password: 'password',
        );

        // Conta 2 — Para testar isolamento de tenant
        $provisioner->handle(
            name: 'Outro Usuario',
            email: 'outro@teste.com',
            password: 'password',
        );

        $provisioner->handle(
            name: 'job',
            email: 'job@gamil.com',
            password: 'password',
        );
    }
}
