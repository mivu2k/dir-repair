<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\DemoIssuance;
use App\Models\Customer;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;

class DemoIssuanceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $issuances = DemoIssuance::with(['customer', 'issuedBy', 'receivedBy'])
            ->when($search, function ($query, $search) {
                $query->where('issuance_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('items', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();
            
        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone']);
            
        return Inertia::render('DemoIssuances/Index', [
            'issuances' => $issuances,
            'customers' => $customers,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create(Request $request)
    {
        $search = $request->query('search');

        $issuances = DemoIssuance::with(['customer', 'issuedBy', 'receivedBy'])
            ->when($search, function ($query, $search) {
                $query->where('issuance_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('items', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();
            
        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone']);

        return Inertia::render('DemoIssuances/Create', [
            'issuances' => $issuances,
            'customers' => $customers,
            'filters' => $request->only(['search'])
        ]);
    }

    public function edit(Request $request, DemoIssuance $demoIssuance)
    {
        $search = $request->query('search');

        $issuances = DemoIssuance::with(['customer', 'issuedBy', 'receivedBy'])
            ->when($search, function ($query, $search) {
                $query->where('issuance_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('items', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();
            
        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone']);

        return Inertia::render('DemoIssuances/Edit', [
            'demoIssuance' => $demoIssuance,
            'issuances' => $issuances,
            'customers' => $customers,
            'filters' => $request->only(['search'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.serial' => 'nullable|string|max:255',
            'items.*.accessories' => 'nullable|string',
            'expected_return_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'reference_letter' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
        ]);

        $validated['issuance_number'] = \App\Services\NumberGeneratorService::next('demo');
        $validated['issued_by'] = $request->user()->id;
        $validated['status'] = 'issued';
        $validated['issued_at'] = now();

        DemoIssuance::create($validated);

        return redirect()->route('demo-issuances.index')->with('success', 'Demo items issued successfully.');
    }

    public function update(Request $request, DemoIssuance $demoIssuance)
    {
        $this->checkEditPermission($request, $demoIssuance);

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.serial' => 'nullable|string|max:255',
            'items.*.accessories' => 'nullable|string',
            'expected_return_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'reference_letter' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
        ]);

        $demoIssuance->update($validated);

        return redirect()->route('demo-issuances.index')->with('success', 'Demo issuance updated successfully.');
    }

    public function destroy(Request $request, DemoIssuance $demoIssuance)
    {
        $this->checkDeletePermission($request);

        $demoIssuance->delete();
        return redirect()->route('demo-issuances.index')->with('success', 'Demo issuance deleted successfully.');
    }

    public function markReturned(Request $request, DemoIssuance $demoIssuance)
    {
        $demoIssuance->update([
            'status' => 'returned',
            'returned_at' => now(),
            'received_by' => $request->user()->id,
            'notes' => $request->notes ? $demoIssuance->notes . "\nReturn Note: " . $request->notes : $demoIssuance->notes
        ]);

        return redirect()->back()->with('success', 'Demo items marked as returned.');
    }

    public function pdf(DemoIssuance $demoIssuance, $variant = 'a4')
    {
        $demoIssuance->load(['customer', 'issuedBy']);
        $settings = Setting::pluck('value', 'key')->toArray();

        if ($variant === 'pos') {
            $pdf = Pdf::loadView('pdfs.demo_pos', compact('demoIssuance', 'settings'))
                ->setPaper([0, 0, 226.77, 600], 'portrait');
        } else {
            $pdf = Pdf::loadView('pdfs.demo_a4', compact('demoIssuance', 'settings'))
                ->setPaper('a4', 'portrait');
        }

        return $pdf->stream("{$demoIssuance->issuance_number}.pdf");
    }
}
