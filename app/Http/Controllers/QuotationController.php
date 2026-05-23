<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\RepairJob;
use App\Services\NumberGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $quotations = Quotation::query()
            ->with(['repairJob.customer'])
            ->when($search, function ($query, $search) {
                $query->where('quotation_number', 'like', "%{$search}%")
                    ->orWhereHas('repairJob', function ($q) use ($search) {
                        $q->where('job_number', 'like', "%{$search}%")
                          ->orWhereHas('customer', function ($q2) use ($search) {
                              $q2->where('name', 'like', "%{$search}%");
                          });
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Quotations/Index', [
            'quotations' => $quotations,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(Request $request)
    {
        $search = $request->query('search');

        $quotations = Quotation::query()
            ->with(['repairJob.customer'])
            ->when($search, function ($query, $search) {
                $query->where('quotation_number', 'like', "%{$search}%")
                    ->orWhereHas('repairJob', function ($q) use ($search) {
                        $q->where('job_number', 'like', "%{$search}%")
                          ->orWhereHas('customer', function ($q2) use ($search) {
                              $q2->where('name', 'like', "%{$search}%");
                          });
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $job = null;
        $intake = null;
        $suggestedItems = [];

        if ($request->has('job_number')) {
            $job = RepairJob::with(['customer', 'diagnoses.part'])->where('job_number', $request->input('job_number'))->firstOrFail();

            foreach ($job->diagnoses as $diagnosis) {
                $items = $this->getSuggestedItems($diagnosis);
                foreach ($items as $item) {
                    $item['repair_job_id'] = $job->id;
                    $suggestedItems[] = $item;
                }
            }
        } elseif ($request->has('intake_id')) {
            $intake = \App\Models\Intake::with(['customer', 'repairJobs.diagnoses.part'])->findOrFail($request->input('intake_id'));

            foreach ($intake->repairJobs as $jobItem) {
                foreach ($jobItem->diagnoses as $diag) {
                    $items = $this->getSuggestedItems($diag);
                    foreach ($items as $item) {
                        $item['repair_job_id'] = $jobItem->id;
                        $suggestedItems[] = $item;
                    }
                }
            }
        }

        return Inertia::render('Quotations/Create', [
            'quotations' => $quotations,
            'filters' => $request->only(['search']),
            'initialJob' => $job,
            'initialIntake' => $intake,
            'suggestedItems' => $suggestedItems,
            'inventoryParts' => \App\Models\Part::orderBy('name')->get(),
        ]);
    }

    private function getSuggestedItems($diagnosis) {
        $items = [];
        if ($diagnosis->work_performed || $diagnosis->required_labor) {
            $items[] = [
                'item_type' => 'labor',
                'description' => $diagnosis->work_performed ?: $diagnosis->required_labor,
                'quantity' => 1,
                'unit_price' => 0,
            ];
        }
        
        $partsText = $diagnosis->parts_required ?: $diagnosis->required_parts;
        
        if ($partsText) {
            $partsList = explode(',', $partsText);
            foreach ($partsList as $partName) {
                $partName = trim($partName);
                if (empty($partName)) continue;
                
                // Extract clean search name to ignore SKU text formatting
                $searchName = $partName;
                if (strpos($partName, ' (SKU:') !== false) {
                    $searchName = trim(explode(' (SKU:', $partName)[0]);
                }
                
                $inventoryPart = null;
                if ($diagnosis->part_id && $diagnosis->part && (stripos($partName, $diagnosis->part->name) !== false || ($diagnosis->part->sku && stripos($partName, $diagnosis->part->sku) !== false))) {
                    $inventoryPart = $diagnosis->part;
                } else {
                    $inventoryPart = \App\Models\Part::where('name', 'like', "%{$searchName}%")
                        ->orWhere('sku', 'like', "%{$searchName}%")
                        ->first();
                }

                $items[] = [
                    'item_type' => 'part',
                    'description' => $partName,
                    'part_id' => $inventoryPart ? $inventoryPart->id : null,
                    'quantity' => 1,
                    'unit_price' => $inventoryPart ? $inventoryPart->price : 0,
                ];
            }
        } elseif ($diagnosis->part_id && $diagnosis->part) {
            $items[] = [
                'item_type' => 'part',
                'description' => $diagnosis->part->name,
                'part_id' => $diagnosis->part_id,
                'quantity' => 1,
                'unit_price' => $diagnosis->part->price,
            ];
        }
        
        return $items;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'repair_job_id' => 'nullable|exists:repair_jobs,id',
            'intake_id' => 'nullable|exists:intakes,id',
            'date' => 'required|date',
            'valid_until' => 'required|date',
            'subject' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:part,labor,misc',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.repair_job_id' => 'nullable|exists:repair_jobs,id',
            'items.*.part_id' => 'nullable|exists:parts,id',
        ]);

        $quotation = DB::transaction(function () use ($validated, $request) {
            $totalAmount = 0;
            $partsAmount = 0;
            $laborAmount = 0;

            foreach ($validated['items'] as $item) {
                $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
                $totalAmount += $lineTotal;
                
                if ($item['item_type'] === 'part') {
                    $partsAmount += $lineTotal;
                } else {
                    $laborAmount += $lineTotal;
                }
            }

            $quotation = Quotation::create([
                'quotation_number' => NumberGeneratorService::next('quotation'),
                'repair_job_id' => $validated['repair_job_id'],
                'intake_id' => $validated['intake_id'],
                'prepared_by' => $request->user()->id,
                'date' => $validated['date'],
                'valid_until' => $validated['valid_until'],
                'subject' => $validated['subject'],
                'reference' => $validated['reference'],
                'parts_amount' => $partsAmount,
                'labor_amount' => $laborAmount,
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'],
                'status' => 'draft',
            ]);

            foreach ($validated['items'] as $item) {
                $quotation->items()->create([
                    'repair_job_id' => $item['repair_job_id'] ?? null,
                    'part_id' => $item['part_id'] ?? null,
                    'item_type' => $item['item_type'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'line_total' => ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0),
                    'total_price' => ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0),
                ]);
            }

            return $quotation;
        });

        return redirect()->route('quotations.show', $quotation->id)->with('message', 'Quotation created successfully.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['items.repairJob', 'repairJob.customer', 'intake.customer', 'intake.repairJobs', 'createdBy']);

        return Inertia::render('Quotations/Show', [
            'quotation' => $quotation,
        ]);
    }

    public function updateStatus(Request $request, Quotation $quotation)
    {
        $this->checkEditPermission($request, $quotation, 'status', ['approved']);

        $validated = $request->validate([
            'status' => 'required|in:draft,sent,approved,rejected,expired,pending',
        ]);

        $quotation->update(['status' => $validated['status']]);

        if ($validated['status'] === 'approved') {
            if ($quotation->repairJob) {
                \App\Services\JobStatusService::changeStatus($quotation->repairJob, 'in_progress', 'Quotation approved. Repair started.');
            } elseif ($quotation->intake) {
                foreach ($quotation->intake->repairJobs as $job) {
                    \App\Services\JobStatusService::changeStatus($job, 'in_progress', 'Merged quotation approved. Repair started.');
                }
            }
        }

        return back()->with('message', 'Quotation status updated.');
    }

    public function destroy(Request $request, Quotation $quotation)
    {
        $this->checkDeletePermission($request);

        DB::transaction(function () use ($quotation) {
            $quotation->items()->delete();
            $quotation->delete();
        });

        return redirect()->route('quotations.index')->with('message', 'Quotation deleted successfully.');
    }
}
