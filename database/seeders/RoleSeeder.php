<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder {
    public function run(): void {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'create intake/job', 'view all jobs', 'update job status', 'add diagnosis',
            'view financial data', 'create quotation', 'approve quotation', 'create sales order',
            'delete records', 'manage users', 'view reports', 'change own jobs only'
        ];
        foreach ($permissions as $p) { Permission::create(['name' => $p]); }

        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all()->reject(function($p){ return $p->name === 'change own jobs only'; }));

        $manager = Role::create(['name' => 'manager']);
        $manager->givePermissionTo([
            'create intake/job', 'view all jobs', 'update job status', 'add diagnosis',
            'view financial data', 'create quotation', 'approve quotation', 'create sales order', 'view reports'
        ]);

        $tech = Role::create(['name' => 'technician']);
        $tech->givePermissionTo([
            'create intake/job', 'view all jobs', 'update job status', 'add diagnosis', 'change own jobs only'
        ]);
    }
}