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