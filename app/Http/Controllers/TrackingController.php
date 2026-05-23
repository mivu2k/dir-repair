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
        $category = $request->input('category', 'unit');
        
        $status = $request->input('status');
        $statuses = $request->input('statuses');
        if (!is_array($statuses)) {
            $statuses = $status ? [$status] : [];
        }

        $staffId = $request->input('staff_id');
        $staffIds = $request->input('staff_ids');
        if (!is_array($staffIds)) {
            $staffIds = $staffId ? [$staffId] : [];
        }

        $customerId = $request->input('customer_id');
        $customerIds = $request->input('customer_ids');
        if (!is_array($customerIds)) {
            $customerIds = $customerId ? [$customerId] : [];
        }

        $brand = $request->input('brand');
        $brands = $request->input('brands');
        if (!is_array($brands)) {
            $brands = $brand ? [$brand] : [];
        }

        $model = $request->input('model');
        $models = $request->input('models');
        if (!is_array($models)) {
            $models = $model ? [$model] : [];
        }

        $symptomId = $request->input('symptom_id');
        $symptomIds = $request->input('symptom_ids');
        if (!is_array($symptomIds)) {
            $symptomIds = $symptomId ? [$symptomId] : [];
        }
        
        $partId = $request->input('part_id');
        $partIds = $request->input('part_ids');
        if (!is_array($partIds)) {
            $partIds = $partId ? [$partId] : [];
        }

        $movements = collect();

        // 1. REPAIR JOBS (UNIT AUDIT)
        if (in_array($category, ['unit', 'flow'])) {
            $query = RepairJob::with(['customer', 'technician', 'symptoms', 'approvedQuotation.items.part', 'accessories', 'intake'])
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
            
            if (count($statuses) > 0) {
                $allCaseStatuses = array_merge(array_map('strtolower', $statuses), array_map('strtoupper', $statuses));
                $query->whereIn('status', $allCaseStatuses);
            }
            if (count($staffIds) > 0) {
                $query->whereIn('assigned_technician_id', $staffIds);
            }
            if (count($customerIds) > 0) {
                $query->whereIn('customer_id', $customerIds);
            }
            if (count($brands) > 0) {
                $query->whereIn('brand', $brands);
            }
            if (count($models) > 0) {
                $query->whereIn('model', $models);
            }
            if (count($symptomIds) > 0) {
                $query->whereHas('symptoms', fn($q) => $q->whereIn('symptoms.id', $symptomIds));
            }
            if (count($partIds) > 0) {
                $query->whereHas('approvedQuotation.items', fn($q) => $q->whereIn('part_id', $partIds));
            }

            $movements = $movements->concat($query->latest()->get()->map(fn($job) => [
                'date' => $job->created_at->toIso8601String(),
                'source' => 'Service',
                'id' => $job->job_number,
                'item_name' => "{$job->brand} {$job->device_name}",
                'item_sub' => $job->model,
                'client' => $job->customer?->name ?: 'N/A',
                'status' => strtoupper($job->status),
                'staff' => $job->technician?->name ?: '-',
                'serial' => $job->serial_number ?: 'N/A',
                'symptoms' => $job->symptoms->pluck('name')->join(', '),
                'accessories' => $job->accessories->map(fn($a) => $a->name . ($a->pivot->note ? " ({$a->pivot->note})" : ""))->join(', ') ?: 'None',
                'brand_name' => $job->brand ?: 'N/A',
                'model_name' => $job->model ?: 'N/A',
                'dept' => $job->intake?->department ?: 'Service Unit',
                'url' => route('jobs.show', $job->job_number)
            ]));
        }

        // 2. DEMO ISSUANCES (DEMO AUDIT)
        if (in_array($category, ['demo', 'flow'])) {
            $demoQuery = DemoIssuance::with(['customer', 'issuedBy'])
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            
            if (count($statuses) > 0) {
                $allCaseStatuses = array_merge(array_map('strtolower', $statuses), array_map('strtoupper', $statuses));
                $demoQuery->whereIn('status', $allCaseStatuses);
            }
            if (count($staffIds) > 0) {
                $demoQuery->whereIn('issued_by', $staffIds);
            }
            if (count($customerIds) > 0) {
                $demoQuery->whereIn('customer_id', $customerIds);
            }

            $demos = $demoQuery->latest()->get();
            foreach ($demos as $demo) {
                foreach ($demo->items as $item) {
                    if ($search && stripos($item['name'] . $item['serial'] . ($demo->customer?->name ?? ''), $search) === false) continue;
                    
                    if (count($brands) > 0) {
                        $matchesBrand = false;
                        foreach ($brands as $b) {
                            if (stripos($item['name'] ?? '', $b) !== false) {
                                $matchesBrand = true;
                                break;
                            }
                        }
                        if (!$matchesBrand) continue;
                    }
                    if (count($models) > 0) {
                        $matchesModel = false;
                        foreach ($models as $m) {
                            if (stripos($item['name'] ?? '', $m) !== false) {
                                $matchesModel = true;
                                break;
                            }
                        }
                        if (!$matchesModel) continue;
                    }

                    $movements->push([
                        'date' => $demo->created_at->toIso8601String(),
                        'source' => 'Demo',
                        'id' => $demo->issuance_number,
                        'item_name' => $item['name'] ?? 'N/A',
                        'item_sub' => "Trial Unit",
                        'client' => $demo->customer?->name ?: 'N/A',
                        'status' => strtoupper($demo->status),
                        'staff' => $demo->issuedBy?->name ?: '-',
                        'serial' => $item['serial'] ?? 'N/A',
                        'symptoms' => 'N/A',
                        'accessories' => 'N/A',
                        'brand_name' => 'N/A',
                        'model_name' => 'N/A',
                        'dept' => $demo->department ?: 'Demo Goods',
                        'url' => route('demo-issuances.index', ['search' => $demo->issuance_number])
                    ]);
                }
            }
        }

        // 3. GATE PASSES (GATE AUDIT)
        if (in_array($category, ['gate', 'flow'])) {
            $passQuery = GatePass::with('authorizedBy')
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

            if (count($statuses) > 0) {
                $allCaseStatuses = array_merge(array_map('strtolower', $statuses), array_map('strtoupper', $statuses));
                $passQuery->whereIn('status', $allCaseStatuses);
            }
            if (count($staffIds) > 0) {
                $passQuery->whereIn('authorized_by', $staffIds);
            }
            if (count($customerIds) > 0) {
                $customerNames = Customer::whereIn('id', $customerIds)->pluck('name')->toArray();
                $passQuery->where(function($q) use ($customerNames) {
                    foreach ($customerNames as $name) {
                        $q->orWhere('company_name', 'like', "%{$name}%")
                          ->orWhere('person_name', 'like', "%{$name}%");
                    }
                });
            }

            $passes = $passQuery->latest()->get();
            foreach ($passes as $pass) {
                foreach ($pass->items as $item) {
                    if ($search && stripos(($item['description'] ?? '') . $pass->person_name, $search) === false) continue;
                    
                    if (count($brands) > 0) {
                        $matchesBrand = false;
                        foreach ($brands as $b) {
                            if (stripos($item['description'] ?? '', $b) !== false) {
                                $matchesBrand = true;
                                break;
                            }
                        }
                        if (!$matchesBrand) continue;
                    }
                    if (count($models) > 0) {
                        $matchesModel = false;
                        foreach ($models as $m) {
                            if (stripos($item['description'] ?? '', $m) !== false) {
                                $matchesModel = true;
                                break;
                            }
                        }
                        if (!$matchesModel) continue;
                    }

                    $movements->push([
                        'date' => $pass->created_at->toIso8601String(),
                        'source' => 'Gate',
                        'id' => $pass->pass_number,
                        'item_name' => $item['description'] ?? 'N/A',
                        'item_sub' => ucfirst($pass->type),
                        'client' => $pass->person_name ?: ($pass->company_name ?: 'N/A'),
                        'status' => strtoupper($pass->status),
                        'staff' => $pass->authorizedBy?->name ?: '-',
                        'serial' => 'N/A',
                        'symptoms' => 'N/A',
                        'accessories' => 'N/A',
                        'brand_name' => 'N/A',
                        'model_name' => 'N/A',
                        'dept' => ucfirst($pass->type) . ' (' . ($pass->company_name ?: 'N/A') . ')',
                        'url' => route('gate-passes.index', ['search' => $pass->pass_number])
                    ]);
                }
            }
        }

        return $movements->sortByDesc('date')->values();
    }

    public function index(Request $request)
    {
        $uniqueBrands = RepairJob::whereNotNull('brand')->where('brand', '!=', '')->distinct()->pluck('brand')->sort()->values();
        $uniqueModels = RepairJob::whereNotNull('model')->where('model', '!=', '')->distinct()->pluck('model')->sort()->values();

        return Inertia::render('Tracking/Index', [
            'results' => $this->getAggregatedData($request),
            'staff' => User::all(['id', 'name']),
            'customers' => Customer::all(['id', 'name']),
            'symptoms' => Symptom::all(['id', 'name']),
            'parts' => Part::all(['id', 'name']),
            'brands' => $uniqueBrands,
            'models' => $uniqueModels,
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
