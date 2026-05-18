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
use Maatwebsite\Excel\Facades\Excel;

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

        $query = RepairJob::with(['customer', 'technician', 'symptoms', 'approvedQuotation.items.part']);

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
        
        $jobs = $query->latest()->get();

        // Financial Totals
        $totalRevenue = 0;
        foreach($jobs as $job) {
            if ($job->approvedQuotation) {
                $totalRevenue += $job->approvedQuotation->total_amount;
            }
        }

        // Grouping logic for UI
        $groupedJobs = collect(['All Records' => $jobs]);
        if ($groupBy !== 'none') {
            $groupedJobs = $jobs->groupBy(function($item) use ($groupBy) {
                if ($groupBy === 'technician') return $item->technician?->name ?? 'Unassigned';
                if ($groupBy === 'customer') return $item->customer->name;
                if ($groupBy === 'status') return strtoupper(str_replace('_', ' ', $item->status));
                if ($groupBy === 'symptom') {
                    $syms = $item->symptoms->pluck('name')->join(', ');
                    return ($syms !== '' && strtoupper($syms) !== 'N/A') ? explode(',', $syms)[0] : 'Unspecified Issues';
                }
                return 'Other';
            });
        }

        // Inventory Consumption
        $inventoryReport = DB::table('quotation_items')
            ->join('quotations', 'quotations.id', '=', 'quotation_items.quotation_id')
            ->join('parts', 'parts.id', '=', 'quotation_items.part_id')
            ->where('quotations.status', 'approved')
            ->whereBetween('quotations.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select('parts.name', 'parts.brand', 'parts.sku', DB::raw('SUM(quotation_items.quantity) as total_qty'), DB::raw('SUM(quotation_items.line_total) as total_revenue'))
            ->groupBy('parts.id', 'parts.name', 'parts.brand', 'parts.sku')
            ->get();

        return Inertia::render('Reports/Index', [
            'groupedJobs' => $groupedJobs,
            'totalJobs' => $jobs->count(),
            'totalRevenue' => $totalRevenue,
            'inventoryReport' => $inventoryReport,
            'technicians' => User::all(['id', 'name']),
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'filters' => array_merge([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'group_by' => $groupBy,
            ], $request->all()),
        ]);
    }

    public function pdf(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfDay()->toDateString());
        $groupBy = $request->query('group_by', 'none'); 

        $query = RepairJob::with(['customer', 'technician', 'symptoms', 'approvedQuotation.items.part'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('job_number', 'like', "%{$request->search}%")
                  ->orWhere('serial_number', 'like', "%{$request->search}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$request->search}%"));
            });
        }
        if ($request->technician_id) $query->where('assigned_technician_id', $request->technician_id);
        if ($request->status) $query->where('status', $request->status);

        $jobs = $query->latest()->get();

        $data = collect(['All Records' => $jobs]);
        if ($groupBy !== 'none') {
            $data = $jobs->groupBy(function($item) use ($groupBy) {
                if ($groupBy === 'technician') return $item->technician?->name ?? 'Unassigned';
                if ($groupBy === 'customer') return $item->customer->name;
                if ($groupBy === 'status') return strtoupper(str_replace('_', ' ', $item->status));
                if ($groupBy === 'symptom') {
                    $syms = $item->symptoms->pluck('name')->join(', ');
                    return ($syms !== '' && strtoupper($syms) !== 'N/A') ? explode(',', $syms)[0] : 'Unspecified Issues';
                }
                return 'Other';
            });
        }

        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.report', [
            'data' => $data,
            'groupBy' => $groupBy,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'settings' => $settings,
            'scenario' => 'Operational Matrix'
        ]);

        return $pdf->download('operational_report_' . now()->format('YmdHis') . '.pdf');
    }

    public function excel(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfDay()->toDateString());
        $groupBy = $request->query('group_by', 'none'); 

        $query = RepairJob::with(['customer', 'technician', 'approvedQuotation'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('job_number', 'like', "%{$request->search}%")
                  ->orWhere('serial_number', 'like', "%{$request->search}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$request->search}%"));
            });
        }
        if ($request->technician_id) $query->where('assigned_technician_id', $request->technician_id);
        if ($request->status) $query->where('status', $request->status);

        $jobs = $query->latest()->get();

        $data = collect(['All Records' => $jobs]);
        if ($groupBy !== 'none') {
            $data = $jobs->groupBy(function($item) use ($groupBy) {
                if ($groupBy === 'technician') return $item->technician?->name ?? 'Unassigned';
                if ($groupBy === 'customer') return $item->customer->name;
                if ($groupBy === 'status') return strtoupper(str_replace('_', ' ', $item->status));
                if ($groupBy === 'symptom') {
                    $syms = $item->symptoms->pluck('name')->join(', ');
                    return ($syms !== '' && strtoupper($syms) !== 'N/A') ? explode(',', $syms)[0] : 'Unspecified Issues';
                }
                return 'Other';
            });
        }

        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
        $period = $startDate . ' to ' . $endDate;

        $html = view('exports.report_excel', [
            'data' => $data,
            'settings' => $settings,
            'period' => $period
        ])->render();

        $filename = "operational_report_" . now()->format('YmdHis') . ".xls";
        
        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }
}
