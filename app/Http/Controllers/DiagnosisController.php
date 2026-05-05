<?php

namespace App\Http\Controllers;

use App\Models\Diagnosis;
use App\Models\RepairJob;
use App\Services\JobStatusService;
use Illuminate\Http\Request;

class DiagnosisController extends Controller
{
    public function store(Request $request, RepairJob $job)
    {
        $validated = $request->validate([
            'findings' => 'required|string',
            'required_parts' => 'nullable|string',
            'required_labor' => 'nullable|string',
            'part_id' => 'nullable|exists:parts,id',
        ]);

        $diagnosis = Diagnosis::create([
            'repair_job_id' => $job->id,
            'technician_id' => $request->user()->id,
            'findings' => $validated['findings'],
            'required_parts' => $validated['required_parts'],
            'required_labor' => $validated['required_labor'],
            'part_id' => $validated['part_id'] ?? null,
        ]);

        // Automatically transition status to waiting_approval if we are diagnosing
        if ($job->status === 'diagnosing') {
            JobStatusService::changeStatus($job, 'waiting_approval', 'Diagnosis submitted automatically.');
        }

        return back()->with('message', 'Diagnosis submitted successfully.');
    }

    public function update(Request $request, Diagnosis $diagnosis)
    {
        $validated = $request->validate([
            'findings' => 'required|string',
            'required_parts' => 'nullable|string',
            'required_labor' => 'nullable|string',
            'part_id' => 'nullable|exists:parts,id',
        ]);

        $diagnosis->update($validated);

        return back()->with('message', 'Diagnosis updated successfully.');
    }

    public function destroy(Diagnosis $diagnosis)
    {
        $diagnosis->delete();

        return back()->with('message', 'Diagnosis removed.');
    }
}
