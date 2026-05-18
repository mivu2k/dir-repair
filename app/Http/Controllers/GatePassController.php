<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\GatePass;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;

class GatePassController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $passes = GatePass::with('authorizedBy')
            ->when($search, function ($query, $search) {
                $query->where('pass_number', 'like', "%{$search}%")
                    ->orWhere('person_name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('items', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();
            
        return Inertia::render('GatePasses/Index', [
            'passes' => $passes,
            'filters' => $request->only(['search'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:inward,outward',
            'person_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.qty' => 'required|numeric|min:0.1',
            'items.*.serial' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['pass_number'] = \App\Services\NumberGeneratorService::next('gate_pass');
        $validated['authorized_by'] = $request->user()->id;
        $validated['status'] = 'issued';

        GatePass::create($validated);

        return redirect()->back()->with('success', 'Gate pass generated successfully.');
    }

    public function update(Request $request, GatePass $gatePass)
    {
        $validated = $request->validate([
            'type' => 'required|in:inward,outward',
            'person_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.qty' => 'required|numeric|min:0.1',
            'items.*.serial' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $gatePass->update($validated);

        return redirect()->back()->with('success', 'Gate pass updated successfully.');
    }

    public function destroy(GatePass $gatePass)
    {
        $gatePass->delete();
        return redirect()->back()->with('success', 'Gate pass deleted successfully.');
    }

    public function pdf(GatePass $gatePass, $variant = 'a4')
    {
        $gatePass->load('authorizedBy');
        $settings = Setting::pluck('value', 'key')->toArray();

        if ($variant === 'pos') {
            $pdf = Pdf::loadView('pdfs.gate_pass_pos', compact('gatePass', 'settings'))
                ->setPaper([0, 0, 226.77, 600], 'portrait');
        } else {
            $pdf = Pdf::loadView('pdfs.gate_pass_a4', compact('gatePass', 'settings'))
                ->setPaper('a4', 'portrait');
        }

        return $pdf->stream("{$gatePass->pass_number}.pdf");
    }
}
