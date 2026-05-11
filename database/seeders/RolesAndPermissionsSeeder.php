<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissionRegistrar = app(PermissionRegistrar::class);

        $permissionRegistrar->forgetCachedPermissions();

        $currentTeamId = $permissionRegistrar->getPermissionsTeamId();

        $permissionRegistrar->setPermissionsTeamId(null);

        Role::findOrCreate('super-admin', 'web');

        $permissionRegistrar->setPermissionsTeamId($currentTeamId);
        $permissionRegistrar->forgetCachedPermissions();
    }
}
