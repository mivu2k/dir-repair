<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder {
    public function run(): void {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Traditional Capabilities & Specific Scopes
        $specificPermissions = [
            'update job status', 'add diagnosis', 'view financial data',
            'approve quotation', 'manage users', 'view reports', 'change own jobs only'
        ];

        // 2. Granular CRUD Permissions for Each Module
        $modules = [
            'customers', 'intakes', 'jobs', 'parts', 'quotations',
            'demo-issuances', 'gate-passes', 'sales-orders', 'users'
        ];

        $crudActions = ['view', 'create', 'edit', 'delete'];
        $allPermissionsList = [];

        foreach ($specificPermissions as $sp) {
            $allPermissionsList[] = $sp;
        }

        foreach ($modules as $mod) {
            foreach ($crudActions as $act) {
                $allPermissionsList[] = "{$act} {$mod}";
            }
        }

        // Populate Spatie permissions database table
        foreach ($allPermissionsList as $pName) {
            Permission::firstOrCreate(['name' => $pName]);
        }

        // --- Role Assignments ---

        // 1. ADMIN - Gets everything (except own job restriction)
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all()->reject(fn($p) => $p->name === 'change own jobs only'));

        // 2. MANAGER - High operational controls
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            'update job status', 'add diagnosis', 'view financial data',
            'approve quotation', 'view reports',
            'view customers', 'create customers', 'edit customers',
            'view intakes', 'create intakes', 'edit intakes',
            'view jobs', 'create jobs', 'edit jobs',
            'view parts', 'create parts', 'edit parts',
            'view quotations', 'create quotations', 'edit quotations',
            'view demo-issuances', 'create demo-issuances', 'edit demo-issuances',
            'view gate-passes', 'create gate-passes', 'edit gate-passes',
            'view sales-orders', 'create sales-orders', 'edit sales-orders',
        ]);

        // 3. TECHNICIAN - Direct servicing workflow
        $tech = Role::firstOrCreate(['name' => 'technician']);
        $tech->syncPermissions([
            'update job status', 'add diagnosis', 'change own jobs only',
            'view jobs', 'edit jobs',
            'view intakes', 'view parts', 'view customers'
        ]);

        // 4. SUPERVISOR - High audit/review scope
        $supervisor = Role::firstOrCreate(['name' => 'supervisor']);
        $supervisor->syncPermissions([
            'view reports',
            'view customers', 'view intakes', 'view jobs', 'view parts',
            'view quotations', 'view demo-issuances', 'view gate-passes', 'view sales-orders'
        ]);

        // 5. SALES - Intake & Lead acquisitions
        $sales = Role::firstOrCreate(['name' => 'sales']);
        $sales->syncPermissions([
            'view customers', 'create customers',
            'view intakes', 'create intakes',
            'view demo-issuances', 'create demo-issuances',
            'view gate-passes', 'create gate-passes'
        ]);

        // 6. ACCOUNTANT - Financials and invoicing
        $accountant = Role::firstOrCreate(['name' => 'accountant']);
        $accountant->syncPermissions([
            'view financial data', 'view reports', 'approve quotation',
            'view quotations', 'create quotations', 'edit quotations',
            'view sales-orders', 'create sales-orders', 'edit sales-orders',
            'view customers'
        ]);

        // 7. STOREKEEPER - Inventory and asset logic
        $store = Role::firstOrCreate(['name' => 'store']);
        $store->syncPermissions([
            'view parts', 'create parts', 'edit parts',
            'view gate-passes', 'create gate-passes', 'edit gate-passes',
            'view demo-issuances', 'create demo-issuances', 'edit demo-issuances',
            'view jobs'
        ]);
    }
}