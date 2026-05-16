<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder {
    public function run(): void {
        $admin = User::firstOrCreate(['email' => 'admin@mirco.com'], [
            'name' => 'System Admin', 'password' => Hash::make('password'), 'role' => 'admin'
        ]);
        $admin->assignRole('admin');

        $manager = User::firstOrCreate(['email' => 'manager@mirco.com'], [
            'name' => 'Workshop Manager', 'password' => Hash::make('password'), 'role' => 'manager'
        ]);
        $manager->assignRole('manager');

        $tech1 = User::firstOrCreate(['email' => 'tech1@mirco.com'], [
            'name' => 'John Technician', 'password' => Hash::make('password'), 'role' => 'technician'
        ]);
        $tech1->assignRole('technician');

        $supervisor = User::firstOrCreate(['email' => 'supervisor@mirco.com'], [
            'name' => 'Sarah Supervisor', 'password' => Hash::make('password'), 'role' => 'supervisor'
        ]);
        $supervisor->assignRole('supervisor');

        $sales = User::firstOrCreate(['email' => 'sales@mirco.com'], [
            'name' => 'Sam Sales', 'password' => Hash::make('password'), 'role' => 'sales'
        ]);
        $sales->assignRole('sales');

        $accountant = User::firstOrCreate(['email' => 'accountant@mirco.com'], [
            'name' => 'Alice Accountant', 'password' => Hash::make('password'), 'role' => 'accountant'
        ]);
        $accountant->assignRole('accountant');

        $store = User::firstOrCreate(['email' => 'store@mirco.com'], [
            'name' => 'Steve Storekeeper', 'password' => Hash::make('password'), 'role' => 'store'
        ]);
        $store->assignRole('store');
    }
}