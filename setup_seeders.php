<?php
$seeders = [
    'RoleSeeder' => <<<'EOT'
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
EOT,
    'UserSeeder' => <<<'EOT'
<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder {
    public function run(): void {
        $admin = User::create([
            'name' => 'System Admin', 'email' => 'admin@mirco.com', 'password' => Hash::make('password'), 'role' => 'admin'
        ]);
        $admin->assignRole('admin');

        $manager = User::create([
            'name' => 'Workshop Manager', 'email' => 'manager@mirco.com', 'password' => Hash::make('password'), 'role' => 'manager'
        ]);
        $manager->assignRole('manager');

        $tech1 = User::create([
            'name' => 'John Technician', 'email' => 'tech1@mirco.com', 'password' => Hash::make('password'), 'role' => 'technician'
        ]);
        $tech1->assignRole('technician');
    }
}
EOT,
    'SymptomSeeder' => <<<'EOT'
<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Symptom;

class SymptomSeeder extends Seeder {
    public function run(): void {
        $categories = [
            'Power' => ['No Power', 'Intermittent Power', 'Battery Not Charging', 'Battery Drain Fast'],
            'RF' => ['No TX (Cannot Transmit)', 'No RX (Cannot Receive)', 'Weak Signal', 'No Signal'],
            'Audio' => ['No Audio', 'Distorted Audio', 'No Microphone', 'Low Volume'],
            'Display' => ['No Display', 'Dim Display', 'Cracked Screen'],
            'Other' => ['Needs Reprogramming', 'Physical Damage', 'Water Damage', 'Unknown Fault']
        ];
        foreach ($categories as $cat => $syms) {
            foreach ($syms as $s) {
                Symptom::create(['name' => $s, 'category' => $cat]);
            }
        }
    }
}
EOT,
    'AccessorySeeder' => <<<'EOT'
<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Accessory;

class AccessorySeeder extends Seeder {
    public function run(): void {
        $accessories = [
            'Battery', 'Antenna', 'Charger', 'Remote Speaker Mic', 'USB Cable',
            'Power Supply', 'Carrying Case', 'Belt Clip', 'Earpiece', 'Programming Cable'
        ];
        foreach ($accessories as $a) {
            Accessory::create(['name' => $a]);
        }
    }
}
EOT,
    'DemoDataSeeder' => <<<'EOT'
<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder {
    public function run(): void {
        // Will implement later if time permits
    }
}
EOT,
    'DatabaseSeeder' => <<<'EOT'
<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    public function run(): void {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            SymptomSeeder::class,
            AccessorySeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
EOT,
];

foreach ($seeders as $name => $content) {
    file_put_contents(__DIR__ . "/database/seeders/{$name}.php", $content);
}
echo "Seeders updated successfully.\n";
