<?php
namespace App\Http\Controllers;

use App\Models\Part;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PartController extends Controller
{
    public function index(Request $request)
    {
        $parts = Part::orderBy('name')
            ->when($request->search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('brand', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%");
            })
            ->get();

        return Inertia::render('Inventory/Index', [
            'parts' => $parts,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => 'nullable|string|max:255|unique:parts,sku',
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
        ]);

        $validated['stock_quantity'] = $validated['stock_quantity'] ?? 0;
        Part::create($validated);

        return redirect()->back()->with('success', 'Part added to inventory.');
    }

    public function update(Request $request, Part $part)
    {
        $validated = $request->validate([
            'sku' => 'nullable|string|max:255|unique:parts,sku,' . $part->id,
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
        ]);

        $part->update($validated);

        return redirect()->back()->with('success', 'Part updated.');
    }

    public function destroy(Part $part)
    {
        $part->delete();
        return redirect()->back()->with('success', 'Part removed.');
    }
}
