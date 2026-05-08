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

        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        // User::factory()->create([
        //     'name' => 'Heuder Rodrigues de Sena',
        //     'email' => 'heuderdev@gmail.com',
        //     'password' => bcrypt('password'),
        // ]);

        // $provisioner->handle(
        //     name: 'Super Administrador',
        //     email: 'super-admin@sass.com.br',
        //     password: 'password',
        // );

        // $provisioner->handle(
        //     name: 'Heuder Sena',
        //     email: 'heuder@sicoob.com.br',
        //     password: 'password',
        // );


        // $provisioner->handle(
        //     name: 'Outro Usuario',
        //     email: 'outro@teste.com',
        //     password: 'password',
        // );

        // $provisioner->handle(
        //     name: 'Job',
        //     email: 'job@gmail.com',
        //     password: 'password',
        // );

        // $provisioner->handle(
        //     name: 'Heuder Dev',
        //     email: 'heuderdev@gmail.com',
        //     password: 'password',
        // );

    }
}
