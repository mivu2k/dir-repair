<?php

namespace App\Http\Controllers;

use App\Models\Accessory;
use App\Models\Customer;
use App\Models\Intake;
use App\Models\RepairJob;
use App\Models\Symptom;
use App\Services\NumberGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class IntakeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $intakes = Intake::query()
            ->with(['customer', 'receivedBy', 'repairJobs'])
            ->when($search, function ($query, $search) {
                $query->where('intake_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Intakes/Index', [
            'intakes' => $intakes,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(Request $request)
    {
        $customer = null;
        if ($request->has('customer_id')) {
            $customer = Customer::find($request->input('customer_id'));
        }

        return Inertia::render('Intakes/Create', [
            'initialCustomer' => $customer,
            'symptoms' => Symptom::all()->groupBy('category'),
            'accessories' => Accessory::all(),
            'brands' => \App\Models\Brand::orderBy('name')->get(),
            'devices' => \App\Models\Device::orderBy('name')->get(),
            'customers' => Customer::select('id', 'name', 'phone', 'email', 'organization')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer.name' => 'required_without:customer_id|nullable|string|max:255',
            'customer.phone' => 'required_without:customer_id|nullable|string|max:255',
            'customer.email' => 'nullable|email|max:255',
            'customer.organization' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,credit,bank_transfer,warranty',
            'notes' => 'nullable|string',
            'devices' => 'required|array|min:1',
            'devices.*.device_name' => 'required|string|max:255',
            'devices.*.brand' => 'required|string|max:255',
            'devices.*.model' => 'nullable|string|max:255',
            'devices.*.serial_number' => 'nullable|string|max:255',
            'devices.*.condition_on_arrival' => 'required|in:good,fair,damaged,broken',
            'devices.*.priority' => 'required|in:normal,urgent',
            'devices.*.issue_description' => 'required|string',
            'devices.*.symptoms' => 'array',
            'devices.*.symptoms.*' => 'exists:symptoms,id',
            'devices.*.accessories' => 'array',
            'devices.*.accessories.*' => 'exists:accessories,id',
        ]);

        $intake = DB::transaction(function () use ($validated, $request) {
            // 1. Resolve Customer
            if (!empty($validated['customer_id'])) {
                $customerId = $validated['customer_id'];
            } else {
                $customer = Customer::create($validated['customer']);
                $customerId = $customer->id;
            }

            // 2. Create Intake
            $intake = Intake::create([
                'intake_number' => NumberGeneratorService::next('intake'),
                'customer_id' => $customerId,
                'received_by' => $request->user()->id,
                'received_at' => now(),
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // 3. Create Jobs
            foreach ($validated['devices'] as $device) {
                $job = RepairJob::create([
                    'job_number' => NumberGeneratorService::next('job'),
                    'intake_id' => $intake->id,
                    'customer_id' => $customerId,
                    'device_name' => $device['device_name'],
                    'brand' => $device['brand'],
                    'model' => $device['model'] ?? null,
                    'serial_number' => $device['serial_number'] ?? null,
                    'condition_on_arrival' => $device['condition_on_arrival'],
                    'issue_description' => $device['issue_description'],
                    'priority' => $device['priority'],
                    'status' => 'received',
                    'status_updated_at' => now(),
                ]);

                // Sync pivots
                if (!empty($device['symptoms'])) {
                    $job->symptoms()->sync($device['symptoms']);
                }
                
                if (!empty($device['accessories'])) {
                    $job->accessories()->sync($device['accessories']);
                }

                \App\Models\JobStatusHistory::create([
                    'repair_job_id' => $job->id,
                    'changed_by' => $request->user()->id,
                    'from_status' => 'received',
                    'to_status' => 'received',
                    'note' => 'Job created via intake',
                    'created_at' => now(),
                ]);
            }

            return $intake;
        });

        return redirect()->route('intakes.show', $intake->id)->with('message', 'Intake completed successfully.');
    }

    public function show(Intake $intake)
    {
        // Actually load receivedBy
        $intake->load(['customer', 'receivedBy', 'repairJobs']);
        
        return Inertia::render('Intakes/Show', [
            'intake' => $intake,
        ]);
    }

    public function updateStatus(Request $request, Intake $intake)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'note' => 'nullable|string',
        ]);

        foreach ($intake->repairJobs as $job) {
            \App\Services\JobStatusService::changeStatus($job, $validated['status'], $validated['note'] ?? 'Bulk status update via intake');
        }

        return back()->with('message', "Status updated for all jobs in intake {$intake->intake_number}");
    }

    public function update(Request $request, Intake $intake)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'payment_method' => 'required|in:cash,credit,bank_transfer,warranty',
        ]);

        $intake->update($validated);

        return back()->with('message', 'Intake parameters updated.');
    }

    public function destroy(Intake $intake)
    {
        DB::transaction(function () use ($intake) {
            // 1. Handle Intake-level Operations (Hard Purge)
            // Use withTrashed() to ensure we find ALREADY soft-deleted jobs that still reference this intake
            foreach ($intake->repairJobs()->withTrashed()->get() as $job) {
                // CLEARANCE SEQUENCE: Payments -> Orders -> Items -> Quotations -> Assets -> Logs -> Job

                // Purge Job-level Sales Orders & Payments
                \App\Models\SalesOrder::where('repair_job_id', $job->id)->each(function($so) {
                    $so->payments()->delete();
                    $so->delete();
                });

                // Clear Quotation Items linked to this job (Merged Quotes)
                \App\Models\QuotationItem::where('repair_job_id', $job->id)->delete();
                
                // Force delete Job-level Quotations
                \App\Models\Quotation::withTrashed()->where('repair_job_id', $job->id)->each(function($q) {
                    $q->items()->delete();
                    $q->forceDelete();
                });

                $job->photos()->delete();
                $job->diagnoses()->delete();
                $job->statusHistories()->delete();
                $job->symptoms()->detach();
                $job->accessories()->detach();
                
                $job->forceDelete();
            }

            // 2. Handle Merged Records linked to Intake
            // Orders FIRST (because they reference quotations)
            \App\Models\SalesOrder::where('intake_id', $intake->id)->each(function($so) {
                $so->payments()->delete();
                $so->delete();
            });

            \App\Models\Quotation::withTrashed()->where('intake_id', $intake->id)->each(function($q) {
                $q->items()->delete();
                $q->forceDelete();
            });

            // 3. Finally delete the intake
            $intake->delete();
        });

        return redirect()->route('intakes.index')->with('message', 'Intake batch and all linked operational records purged successfully.');
    }
}
