<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\GatePass;

class GatePassController extends Controller
{
    public function index()
    {
        $passes = GatePass::with('authorizedBy')
            ->latest()
            ->paginate(20);
            
        return Inertia::render('GatePasses/Index', [
            'passes' => $passes
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:inward,outward',
            'person_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:255',
            'items_description' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $prefix = $validated['type'] === 'inward' ? 'IN-' : 'OUT-';
        $validated['pass_number'] = $prefix . strtoupper(uniqid());
        $validated['authorized_by'] = $request->user()->id;
        $validated['status'] = 'issued';

        GatePass::create($validated);

        return redirect()->back()->with('success', 'Gate pass generated successfully.');
    }

    public function pdf(GatePass $gatePass)
    {
        $gatePass->load('authorizedBy');
        // This relies on DOMPDF. We will add a view for it.
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.gate_pass', [
            'gatePass' => $gatePass,
            'settings' => \App\Models\Setting::pluck('value', 'key')->toArray()
        ])->setPaper('a5', 'portrait');

        return $pdf->stream("{$gatePass->pass_number}.pdf");
    }
}
