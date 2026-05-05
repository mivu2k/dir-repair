<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Settings', [
            'settings' => Setting::allAsArray(),
            'symptoms' => \App\Models\Symptom::orderBy('category')->orderBy('name')->get(),
            'accessories' => \App\Models\Accessory::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name'    => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'company_phone'   => 'nullable|string|max:50',
            'company_email'   => 'nullable|email|max:255',
            'currency_symbol' => 'nullable|string|max:10',
            'company_logo'    => 'nullable|image|max:2048',
        ]);

        foreach (['company_name', 'company_address', 'company_phone', 'company_email', 'currency_symbol'] as $key) {
            Setting::set($key, $validated[$key] ?? null);
        }

        if ($request->hasFile('company_logo')) {
            // Delete old logo if exists
            $oldLogo = Setting::get('company_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            $path = $request->file('company_logo')->store('logos', 'public');
            Setting::set('company_logo', $path);
        }

        return back()->with('message', 'Settings saved successfully.');
    }

    public function storeSymptom(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
        ]);
        \App\Models\Symptom::create($validated);
        return back()->with('message', 'Symptom added successfully.');
    }

    public function destroySymptom(\App\Models\Symptom $symptom)
    {
        $symptom->delete();
        return back()->with('message', 'Symptom deleted successfully.');
    }

    public function storeAccessory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        \App\Models\Accessory::create($validated);
        return back()->with('message', 'Accessory added successfully.');
    }

    public function destroyAccessory(\App\Models\Accessory $accessory)
    {
        $accessory->delete();
        return back()->with('message', 'Accessory deleted successfully.');
    }
}
