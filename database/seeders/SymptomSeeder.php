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