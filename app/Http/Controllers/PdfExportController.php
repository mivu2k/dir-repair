<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\RepairJob;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PdfExportController extends Controller
{
    public function quotation(Quotation $quotation)
    {
        $quotation->load(['items', 'repairJob.customer', 'repairJob.diagnoses.technician', 'createdBy']);

        $pdf = Pdf::loadView('pdfs.quotation', ['quotation' => $quotation])
            ->setPaper('a4', 'portrait');

        return $pdf->stream($quotation->quotation_number . '.pdf');
    }

    public function invoice(SalesOrder $salesOrder)
    {
        $salesOrder->load(['quotation.items', 'customer', 'repairJob.diagnoses.technician', 'finalizedBy']);

        $pdf = Pdf::loadView('pdfs.invoice', ['salesOrder' => $salesOrder])
            ->setPaper('a4', 'portrait');

        return $pdf->stream($salesOrder->order_number . '.pdf');
    }

    public function jobCard(RepairJob $job, string $variant = 'intake')
    {
        $job->load(['customer', 'intake', 'technician', 'symptoms', 'accessories']);

        $pdf = Pdf::loadView('pdfs.job_card', [
            'job' => $job,
            'variant' => $variant
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("{$job->job_number}-{$variant}.pdf");
    }

    public function intakeSummary($id)
    {
        $intake = \App\Models\Intake::with(['customer', 'receivedBy', 'repairJobs.symptoms', 'repairJobs.accessories'])->findOrFail($id);

        $pdf = Pdf::loadView('pdfs.intake_summary', [
            'intake' => $intake
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("INT-{$intake->intake_number}.pdf");
    }

    public function pos(Request $request, $id, string $type = 'intake')
    {
        if ($type === 'intake_summary' || $type === 'intake_delivery') {
            $intake = \App\Models\Intake::with(['customer', 'receivedBy', 'repairJobs.salesOrder.quotation.items'])->findOrFail($id);
            $pdf = Pdf::loadView('pdfs.pos_receipt', [
                'intake' => $intake,
                'type' => $type
            ])->setPaper([0, 0, 226, 1500], 'portrait'); 
            return $pdf->stream("INT-{$intake->intake_number}.pdf");
        }

        if ($type === 'quotation') {
            $quotation = Quotation::with(['items', 'repairJob.customer', 'intake.customer'])->findOrFail($id);
            $pdf = Pdf::loadView('pdfs.pos_receipt', [
                'quotation' => $quotation,
                'type' => 'quotation'
            ])->setPaper([0, 0, 226, 1500], 'portrait'); 
            return $pdf->stream("QT-{$quotation->quotation_number}.pdf");
        }

        $job = RepairJob::where('job_number', $id)->with(['customer', 'salesOrder.quotation.items'])->firstOrFail();

        $pdf = Pdf::loadView('pdfs.pos_receipt', [
            'job' => $job,
            'type' => $type
        ])->setPaper([0, 0, 226, 1200], 'portrait'); 

        return $pdf->stream("POS-{$job->job_number}.pdf");
    }

    public function report(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfDay()->toDateString());
        $groupBy = $request->query('group_by', 'status');
        
        $technicianId = $request->query('technician_id');
        $customerId = $request->query('customer_id');
        $status = $request->query('status');
        $brand = $request->query('brand');
        $model = $request->query('model');

        $query = RepairJob::with(['customer', 'technician', 'approvedQuotation'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($technicianId) $query->where('assigned_technician_id', $technicianId);
        if ($customerId) $query->where('customer_id', $customerId);
        if ($status) $query->where('status', $status);
        if ($brand) $query->where('brand', 'like', "%{$brand}%");
        if ($model) $query->where('model', 'like', "%{$model}%");

        $jobs = $query->get();

        if ($groupBy === 'technician') {
            $data = $jobs->groupBy(fn($j) => $j->technician?->name ?? 'Unassigned');
        } elseif ($groupBy === 'customer') {
            $data = $jobs->groupBy('customer.name');
        } elseif ($groupBy === 'brand') {
            $data = $jobs->groupBy('brand');
        } elseif ($groupBy === 'model') {
            $data = $jobs->groupBy('model');
        } else {
            $data = $jobs->groupBy('status');
        }

        $pdf = Pdf::loadView('pdfs.report', [
            'data' => $data,
            'groupBy' => $groupBy,
            'startDate' => $startDate,
            'endDate' => $endDate
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("MEI-Report-{$groupBy}.pdf");
    }

    public function intakeStickers($id)
    {
        $intake = \App\Models\Intake::with(['repairJobs.customer'])->findOrFail($id);
        
        $pdf = Pdf::loadView('pdfs.stickers', [
            'jobs' => $intake->repairJobs
        ])->setPaper([0, 0, 141, 85]); 

        return $pdf->stream("stickers-{$intake->intake_number}.pdf");
    }

    public function jobSticker($job_number)
    {
        $job = RepairJob::where('job_number', $job_number)->with(['customer'])->firstOrFail();
        
        $pdf = Pdf::loadView('pdfs.stickers', [
            'jobs' => collect([$job])
        ])->setPaper([0, 0, 141, 85]); 

        return $pdf->stream("sticker-{$job->job_number}.pdf");
    }
}
