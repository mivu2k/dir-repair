<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Services\JobStatusService;
use App\Services\NumberGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SalesOrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $salesOrders = SalesOrder::query()
            ->with(['customer'])
            ->when($search, function ($query, $search) {
                $query->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('SalesOrders/Index', [
            'salesOrders' => $salesOrders,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quotation_id' => 'required|exists:quotations,id',
        ]);

        $quotation = Quotation::with(['items', 'repairJob', 'intake'])->findOrFail($validated['quotation_id']);

        $existing = SalesOrder::where('quotation_id', $quotation->id)->first();
        if ($existing) {
            return redirect()->route('sales-orders.show', $existing->id)->with('message', 'Invoice already exists.');
        }

        $salesOrder = DB::transaction(function () use ($quotation, $request) {
            $partsAmount = $quotation->items->where('item_type', 'part')->sum('total_price');
            $laborAmount = $quotation->items->whereIn('item_type', ['labor', 'misc'])->sum('total_price');

            $order = SalesOrder::create([
                'order_number' => NumberGeneratorService::next('sales_order'),
                'quotation_id' => $quotation->id,
                'repair_job_id' => $quotation->repair_job_id,
                'intake_id' => $quotation->intake_id,
                'customer_id' => $quotation->intake ? $quotation->intake->customer_id : $quotation->repairJob->customer_id,
                'finalized_by' => $request->user()->id,
                'labor_amount' => $laborAmount,
                'parts_amount' => $partsAmount,
                'tax_amount' => $quotation->tax_amount,
                'discount_amount' => 0,
                'total_amount' => $quotation->total_amount,
                'payment_status' => 'unpaid',
                'amount_paid' => 0,
            ]);

            if ($quotation->repairJob) {
                JobStatusService::changeStatus($quotation->repairJob, 'completed', 'Sales Order generated. Awaiting delivery/payment.');
            } elseif ($quotation->intake) {
                foreach ($quotation->intake->repairJobs as $job) {
                    JobStatusService::changeStatus($job, 'completed', 'Sales Order generated for intake. Awaiting delivery/payment.');
                }
            }

            return $order;
        });

        return redirect()->route('sales-orders.show', $salesOrder->id)->with('message', 'Sales Order created.');
    }

    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load(['quotation.items.repairJob', 'customer', 'repairJob', 'intake.repairJobs', 'finalizedBy', 'payments.recordedBy']);

        return Inertia::render('SalesOrders/Show', [
            'salesOrder' => $salesOrder,
        ]);
    }
}
