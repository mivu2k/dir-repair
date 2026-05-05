<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\RepairJob;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\Part;
use App\Models\Symptom;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfDay()->toDateString());
        $groupBy = $request->query('group_by', 'none'); 
        
        $technicianId = $request->query('technician_id');
        $customerId = $request->query('customer_id');
        $status = $request->query('status');
        $brand = $request->query('brand');
        $model = $request->query('model');
        $search = $request->query('search');
        $symptomId = $request->query('symptom_id');
        $partId = $request->query('part_id');

        $query = RepairJob::with(['customer', 'technician', 'symptoms', 'approvedQuotation.items.part']);

        // Global Search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('job_number', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($technicianId) $query->where('assigned_technician_id', $technicianId);
        if ($customerId) $query->where('customer_id', $customerId);
        if ($status) $query->where('status', $status);
        if ($brand) $query->where('brand', 'like', "%{$brand}%");
        if ($model) $query->where('model', 'like', "%{$model}%");
        
        if ($symptomId) {
            $query->whereHas('symptoms', fn($q) => $q->where('symptoms.id', $symptomId));
        }

        if ($partId) {
            $query->whereHas('approvedQuotation.items', fn($q) => $q->where('part_id', $partId));
        }

        $jobs = $query->latest()->get();

        // Inventory Consumption Data
        $inventoryQuery = DB::table('quotation_items')
            ->join('quotations', 'quotations.id', '=', 'quotation_items.quotation_id')
            ->join('parts', 'parts.id', '=', 'quotation_items.part_id')
            ->where('quotations.status', 'approved')
            ->whereBetween('quotations.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        $inventoryReport = $inventoryQuery
            ->select('parts.name', 'parts.brand', 'parts.sku', DB::raw('SUM(quotation_items.quantity) as total_qty'), DB::raw('SUM(quotation_items.line_total) as total_revenue'))
            ->groupBy('parts.id', 'parts.name', 'parts.brand', 'parts.sku')
            ->orderBy('total_qty', 'desc')
            ->get();

        // Grouping logic for UI
        $groupedData = null;
        if ($groupBy !== 'none') {
            if ($groupBy === 'brand') $groupedData = $jobs->groupBy('brand');
            elseif ($groupBy === 'model') $groupedData = $jobs->groupBy('model');
            elseif ($groupBy === 'status') $groupedData = $jobs->groupBy('status');
            elseif ($groupBy === 'technician') $groupedData = $jobs->groupBy(fn($j) => $j->technician?->name ?? 'Unassigned');
            elseif ($groupBy === 'customer') $groupedData = $jobs->groupBy(fn($j) => $j->customer?->name ?? 'Walk-in');
            elseif ($groupBy === 'symptom') {
                $grouped = [];
                foreach ($jobs as $job) {
                    if ($job->symptoms->isEmpty()) {
                        $grouped['No Symptom Recorded'][] = $job;
                    } else {
                        foreach ($job->symptoms as $symptom) {
                            $grouped[$symptom->name][] = $job;
                        }
                    }
                }
                $groupedData = collect($grouped);
            }
        }

        return Inertia::render('Reports/Index', [
            'jobs' => $jobs,
            'groupedData' => $groupedData,
            'inventoryReport' => $inventoryReport,
            'technicians' => User::all(['id', 'name']),
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'symptoms' => Symptom::orderBy('name')->get(['id', 'name']),
            'parts' => Part::orderBy('name')->get(['id', 'name']),
            'filters' => array_merge([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'group_by' => $groupBy,
            ], $request->all()),
        ]);
    }

    public function export(Request $request)
    {
        $format = $request->query('format', 'excel');
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfDay()->toDateString());
        $search = $request->query('search');
        $technicianId = $request->query('technician_id');
        $customerId = $request->query('customer_id');
        $status = $request->query('status');
        $brand = $request->query('brand');
        $model = $request->query('model');
        $symptomId = $request->query('symptom_id');
        $partId = $request->query('part_id');
        
        $query = RepairJob::with(['customer', 'technician', 'approvedQuotation', 'symptoms'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        // Global Search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('job_number', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        // Re-apply granular filters
        if ($technicianId) $query->where('assigned_technician_id', $technicianId);
        if ($customerId) $query->where('customer_id', $customerId);
        if ($status) $query->where('status', $status);
        if ($brand) $query->where('brand', 'like', "%{$brand}%");
        if ($model) $query->where('model', 'like', "%{$model}%");
        
        if ($symptomId) {
            $query->whereHas('symptoms', fn($q) => $q->where('symptoms.id', $symptomId));
        }

        if ($partId) {
            $query->whereHas('approvedQuotation.items', fn($q) => $q->where('part_id', $partId));
        }

        $jobs = $query->get();

        if ($format === 'pdf') {
            $groupBy = $request->query('group_by', 'status');
            
            if ($groupBy === 'symptom') {
                $grouped = [];
                foreach ($jobs as $job) {
                    if ($job->symptoms->isEmpty()) {
                        $grouped['No Symptom Recorded'][] = $job;
                    } else {
                        foreach ($job->symptoms as $symptom) {
                            $grouped[$symptom->name][] = $job;
                        }
                    }
                }
                $data = collect($grouped)->map(fn($g) => collect($g));
            } else {
                if ($groupBy === 'technician') {
                    $data = $jobs->groupBy(fn($j) => $j->technician?->name ?? 'Unassigned');
                } elseif ($groupBy === 'customer') {
                    $data = $jobs->groupBy(fn($j) => $j->customer?->name ?? 'Walk-in');
                } else {
                    $data = $jobs->groupBy($groupBy === 'none' ? 'status' : $groupBy);
                }
            }
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.report', [
                'data' => $data,
                'groupBy' => $groupBy,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'scenario' => 'Custom Report'
            ])->setPaper('a4', 'landscape');

            return $pdf->stream("MEI-Audit-Report.pdf");
        }

        // CSV/Excel Export
        // Formatted Excel Export (HTML-Excel Matrix)
        $filename = "MEI-Technical-Report-" . now()->format('YmdHis') . ".xls";
        $groupBy = $request->query('group_by', 'status');

        if ($groupBy === 'symptom') {
            $grouped = [];
            foreach ($jobs as $job) {
                if ($job->symptoms->isEmpty()) {
                    $grouped['No Symptom Recorded'][] = $job;
                } else {
                    foreach ($job->symptoms as $symptom) {
                        $grouped[$symptom->name][] = $job;
                    }
                }
            }
            $data = collect($grouped);
        } else {
            if ($groupBy === 'technician') {
                $data = $jobs->groupBy(fn($j) => $j->technician?->name ?? 'Unassigned');
            } elseif ($groupBy === 'customer') {
                $data = $jobs->groupBy(fn($j) => $j->customer?->name ?? 'Walk-in');
            } else {
                $data = $jobs->groupBy($groupBy === 'none' ? 'status' : $groupBy);
            }
        }

        $view = view('exports.report', [
            'data' => $data,
            'groupBy' => $groupBy,
            'startDate' => $startDate,
            'endDate' => $endDate
        ])->render();

        return response($view)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
