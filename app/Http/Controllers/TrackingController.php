<?php

namespace App\Http\Controllers;

use App\Models\RepairJob;
use App\Models\DemoIssuance;
use App\Models\GatePass;
use App\Models\Brand;
use App\Models\Device;
use App\Models\Setting;
use App\Models\User;
use App\Models\Customer;
use App\Models\Symptom;
use App\Models\Part;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class TrackingController extends Controller
{
    private function getAggregatedData($request)
    {
        $startDate = $request->input('start_date') ?: now()->startOfMonth()->toDateString();
        $endDate = $request->input('end_date') ?: now()->toDateString();
        $search = $request->input('search');
        $brand = $request->input('brand');
        $category = $request->input('category', 'unit');
        $status = $request->input('status');
        $staffId = $request->input('staff_id');
        $customerId = $request->input('customer_id');
        $model = $request->input('model');
        $symptomId = $request->input('symptom_id');
        $partId = $request->input('part_id');

        $movements = collect();

        // 1. REPAIR JOBS (UNIT AUDIT)
        if (in_array($category, ['unit', 'flow'])) {
            $query = RepairJob::with(['customer', 'technician', 'symptoms', 'approvedQuotation.items.part'])
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('job_number', 'like', "%{$search}%")
                      ->orWhere('serial_number', 'like', "%{$search}%")
                      ->orWhere('brand', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
                      ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
                });
            }
            if ($status) $query->where('status', $status);
            if ($staffId) $query->where('assigned_technician_id', $staffId);
            if ($customerId) $query->where('customer_id', $customerId);
            if ($brand) $query->where('brand', 'like', "%{$brand}%");
            if ($model) $query->where('model', 'like', "%{$model}%");
            if ($symptomId) $query->whereHas('symptoms', fn($q) => $q->where('symptoms.id', $symptomId));
            if ($partId) $query->whereHas('approvedQuotation.items', fn($q) => $q->where('part_id', $partId));

            $movements = $movements->concat($query->latest()->get()->map(fn($job) => [
                'date' => $job->created_at->toIso8601String(),
                'source' => 'Service',
                'id' => $job->job_number,
                'item_name' => "{$job->brand} {$job->device_name}",
                'item_sub' => $job->model,
                'client' => $job->customer->name,
                'status' => strtoupper($job->status),
                'staff' => $job->technician?->name ?: '-',
                'serial' => $job->serial_number ?: 'N/A',
                'symptoms' => $job->symptoms->pluck('name')->join(', '),
                'url' => route('jobs.show', $job->job_number)
            ]));
        }

        // 2. DEMO ISSUANCES (DEMO AUDIT)
        if (in_array($category, ['demo', 'flow'])) {
            $demos = DemoIssuance::with(['customer', 'issuedBy'])
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->get();
            foreach ($demos as $demo) {
                foreach ($demo->items as $item) {
                    if ($search && stripos($item['name'] . $item['serial'] . $demo->customer->name, $search) === false) continue;
                    $movements->push([
                        'date' => $demo->created_at->toIso8601String(),
                        'source' => 'Demo',
                        'id' => $demo->issuance_number,
                        'item_name' => $item['name'],
                        'item_sub' => "Trial Unit",
                        'client' => $demo->customer->name,
                        'status' => strtoupper($demo->status),
                        'staff' => $demo->issuedBy->name,
                        'serial' => $item['serial'] ?: 'N/A',
                        'symptoms' => 'N/A',
                        'url' => route('demo-issuances.index', ['search' => $demo->issuance_number])
                    ]);
                }
            }
        }

        // 3. GATE PASSES (GATE AUDIT)
        if (in_array($category, ['gate', 'flow'])) {
            $passes = GatePass::with('authorizedBy')
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->get();
            foreach ($passes as $pass) {
                foreach ($pass->items as $item) {
                    if ($search && stripos($item['description'] . $pass->person_name, $search) === false) continue;
                    $movements->push([
                        'date' => $pass->created_at->toIso8601String(),
                        'source' => 'Gate',
                        'id' => $pass->pass_number,
                        'item_name' => $item['description'],
                        'item_sub' => ucfirst($pass->type),
                        'client' => $pass->person_name,
                        'status' => strtoupper($pass->status),
                        'staff' => $pass->authorizedBy->name,
                        'serial' => 'N/A',
                        'symptoms' => 'N/A',
                        'url' => route('gate-passes.index', ['search' => $pass->pass_number]) // Simplified link to index with search
                    ]);
                }
            }
        }

        return $movements->sortByDesc('date')->values();
    }

    public function index(Request $request)
    {
        return Inertia::render('Tracking/Index', [
            'results' => $this->getAggregatedData($request),
            'staff' => User::all(['id', 'name']),
            'customers' => Customer::all(['id', 'name']),
            'symptoms' => Symptom::all(['id', 'name']),
            'parts' => Part::all(['id', 'name']),
            'filters' => $request->all()
        ]);
    }

    public function pdf(Request $request)
    {
        $movements = $this->getAggregatedData($request);
        $groupBy = $request->input('aggregate_by') ?: $request->input('group_by') ?: 'flat';
        
        $grouped = collect(['Results' => $movements]);
        if ($groupBy !== 'flat') {
            $grouped = $movements->groupBy(function($item) use ($groupBy) {
                if ($groupBy === 'state') return $item['status'];
                if ($groupBy === 'brand') return explode(' ', $item['item_name'])[0];
                if ($groupBy === 'staff') return $item['staff'];
                if ($groupBy === 'client') return $item['client'];
                if ($groupBy === 'symptom') {
                    $syms = $item['symptoms'] ?? '';
                    if (is_string($syms)) {
                        $syms = trim($syms);
                        if ($syms !== '' && strtoupper($syms) !== 'N/A') {
                            return trim(explode(',', $syms)[0]);
                        }
                    }
                    return 'Unspecified Issues';
                }
                return 'Other';
            });
        }

        $settings = Setting::pluck('value', 'key')->toArray();
        $pdf = Pdf::loadView('pdfs.tracking_audit', [
            'grouped' => $grouped,
            'settings' => $settings,
            'groupBy' => $groupBy
        ])->setPaper('a4', 'landscape');
        
        return $pdf->stream('technical_audit_' . $groupBy . '_' . now()->format('Ymd') . '.pdf');
    }

    public function csv(Request $request)
    {
        $movements = $this->getAggregatedData($request);
        $groupBy = $request->input('aggregate_by') ?: $request->input('group_by') ?: 'flat';

        $grouped = collect(['Results' => $movements]);
        if ($groupBy !== 'flat') {
            $grouped = $movements->groupBy(function($item) use ($groupBy) {
                if ($groupBy === 'state') return $item['status'];
                if ($groupBy === 'brand') return explode(' ', $item['item_name'])[0];
                if ($groupBy === 'staff') return $item['staff'];
                if ($groupBy === 'client') return $item['client'];
                if ($groupBy === 'symptom') {
                    $syms = $item['symptoms'] ?? '';
                    if (is_string($syms)) {
                        $syms = trim($syms);
                        if ($syms !== '' && strtoupper($syms) !== 'N/A') {
                            return trim(explode(',', $syms)[0]);
                        }
                    }
                    return 'Unspecified Issues';
                }
                return 'Other';
            });
        }

        $settings = Setting::pluck('value', 'key')->toArray();
        $period = $request->query('start_date', 'Start') . ' to ' . $request->query('end_date', 'End');
        
        $html = view('exports.tracking_excel', [
            'grouped' => $grouped,
            'settings' => $settings,
            'period' => $period,
            'groupBy' => $groupBy
        ])->render();

        $filename = "technical_audit_report_" . now()->format('YmdHis') . ".xls";
        
        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }
}
