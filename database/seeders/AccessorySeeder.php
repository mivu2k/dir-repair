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