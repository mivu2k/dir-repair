<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\DemoIssuance;
use App\Models\Customer;

class DemoIssuanceController extends Controller
{
    public function index()
    {
        $issuances = DemoIssuance::with(['customer', 'issuedBy', 'receivedBy'])
            ->latest()
            ->paginate(20);
            
        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone']);
            
        return Inertia::render('DemoIssuances/Index', [
            'issuances' => $issuances,
            'customers' => $customers
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'item_name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'accessories_included' => 'nullable|string',
            'expected_return_date' => 'nullable|date',
            'notes' => 'nullable|string'
        ]);

        $validated['issuance_number'] = 'DEMO-' . strtoupper(uniqid());
        $validated['issued_by'] = $request->user()->id;
        $validated['status'] = 'issued';

        DemoIssuance::create($validated);

        return redirect()->back()->with('success', 'Demo item issued successfully.');
    }

    public function markReturned(Request $request, DemoIssuance $demoIssuance)
    {
        $demoIssuance->update([
            'status' => 'returned',
            'returned_at' => now(),
            'received_by' => $request->user()->id,
            'notes' => $request->notes ? $demoIssuance->notes . "\nReturn Note: " . $request->notes : $demoIssuance->notes
        ]);

        return redirect()->back()->with('success', 'Demo item marked as returned.');
    }
}
