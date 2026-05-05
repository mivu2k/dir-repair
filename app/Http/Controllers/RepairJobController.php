<?php

namespace App\Http\Controllers;

use App\Models\RepairJob;
use App\Models\User;
use App\Services\JobStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RepairJobController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $technician = $request->query('technician_id');

        $jobs = RepairJob::query()
            ->with(['customer:id,name,phone', 'technician:id,name', 'intake:id,intake_number'])
            ->when($search, function ($query, $search) {
                $query->where('job_number', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('device_name', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%");
                    });
            })
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($technician, function ($query, $technician) {
                $query->where('assigned_technician_id', $technician);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Jobs/Index', [
            'jobs' => $jobs,
            'filters' => $request->only(['search', 'status', 'technician_id']),
            'technicians' => User::whereHas('roles', fn($q) => $q->where('name', 'technician'))->orWhere('role', 'technician')->select('id', 'name')->get(),
            'statuses' => [
                'received', 'diagnosing', 'waiting_approval', 'in_progress', 'completed', 'delivered', 'cancelled'
            ]
        ]);
    }

    public function show($jobNumber)
    {
        $job = RepairJob::where('job_number', $jobNumber)
            ->with([
                'customer',
                'intake',
                'technician',
                'symptoms',
                'accessories',
                'statusHistories.changer:id,name',
                'diagnoses.technician:id,name',
            ])
            ->firstOrFail();

        return Inertia::render('Jobs/Show', [
            'job' => $job,
            'technicians' => User::whereHas('roles', fn($q) => $q->where('name', 'technician'))->orWhere('role', 'technician')->select('id', 'name')->get(),
            'parts' => \App\Models\Part::orderBy('name')->get(),
        ]);
    }

    public function assign(Request $request, RepairJob $job)
    {
        $validated = $request->validate([
            'technician_id' => 'required|exists:users,id'
        ]);

        $job->update(['assigned_technician_id' => $validated['technician_id']]);

        return back()->with('message', 'Technician assigned successfully.');
    }

    public function updateStatus(Request $request, RepairJob $job)
    {
        $validated = $request->validate([
            'status' => 'required|in:received,diagnosing,waiting_approval,in_progress,completed,delivered,cancelled',
            'note' => 'nullable|string'
        ]);

        JobStatusService::changeStatus($job, $validated['status'], $validated['note']);

        return back()->with('message', 'Job status updated.');
    }

    public function edit(RepairJob $job)
    {
        $job->load(['symptoms', 'accessories']);
        return Inertia::render('Jobs/Edit', [
            'job' => $job,
            'symptoms' => \App\Models\Symptom::all()->groupBy('category'),
            'accessories' => \App\Models\Accessory::all(),
        ]);
    }

    public function update(Request $request, RepairJob $job)
    {
        $validated = $request->validate([
            'device_name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'condition_on_arrival' => 'required|in:good,fair,damaged,broken',
            'issue_description' => 'required|string',
            'priority' => 'required|in:normal,urgent',
            'symptoms' => 'array',
            'symptoms.*' => 'exists:symptoms,id',
            'accessories' => 'array',
            'accessories.*' => 'exists:accessories,id',
        ]);

        $job->update([
            'device_name' => $validated['device_name'],
            'brand' => $validated['brand'],
            'model' => $validated['model'] ?? null,
            'serial_number' => $validated['serial_number'] ?? null,
            'condition_on_arrival' => $validated['condition_on_arrival'],
            'issue_description' => $validated['issue_description'],
            'priority' => $validated['priority'],
        ]);

        if (isset($validated['symptoms'])) {
            $job->symptoms()->sync($validated['symptoms']);
        }
        
        if (isset($validated['accessories'])) {
            $job->accessories()->sync($validated['accessories']);
        }

        return redirect()->route('jobs.show', $job->job_number)->with('message', 'Job updated successfully.');
    }

    public function destroy(RepairJob $job)
    {
        DB::transaction(function () use ($job) {
            // 1. CLEARANCE SEQUENCE: Payments -> Orders -> Items -> Quotations -> Assets -> Logs -> Job

            // Purge Sales Orders & Payments
            \App\Models\SalesOrder::where('repair_job_id', $job->id)->each(function($so) {
                $so->payments()->delete();
                $so->delete();
            });

            // Purge Quotation Items linked to this job
            \App\Models\QuotationItem::where('repair_job_id', $job->id)->delete();
            
            // Purge Job-level Quotations (Including previously soft-deleted ones)
            \App\Models\Quotation::withTrashed()->where('repair_job_id', $job->id)->each(function($q) {
                $q->items()->delete();
                $q->forceDelete();
            });

            // 2. Purge technical logs & assets
            $job->photos()->delete();
            $job->diagnoses()->delete();
            $job->statusHistories()->delete();
            $job->symptoms()->detach();
            $job->accessories()->detach();

            // 3. Purge the job node (Hard Purge to clear constraints)
            $job->forceDelete();
        });

        return redirect()->route('jobs.index')->with('message', 'Repair node and all associated technical/financial records purged successfully.');
    }
}
