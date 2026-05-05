<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(Request $request, SalesOrder $salesOrder)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,bank_transfer',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $request, $salesOrder) {
            Payment::create([
                'sales_order_id' => $salesOrder->id,
                'recorded_by' => $request->user()->id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $newTotalPaid = $salesOrder->amount_paid + $validated['amount'];
            
            $status = 'partial';
            if ($newTotalPaid >= $salesOrder->total_amount) {
                $status = 'paid';
            }

            $salesOrder->update([
                'amount_paid' => $newTotalPaid,
                'payment_status' => $status,
            ]);
        });

        return back()->with('message', 'Payment recorded successfully.');
    }
}
