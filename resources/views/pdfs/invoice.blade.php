<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $salesOrder->order_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #18181b; background: #fff; padding: 40px; line-height: 1.5; }
        .mono { font-family: DejaVu Sans Mono, monospace; }
        .bold { font-weight: 700; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .uppercase { text-transform: uppercase; }
        
        .header { display: table; width: 100%; border-bottom: 2px solid #18181b; padding-bottom: 24px; margin-bottom: 30px; }
        .header-left { display: table-cell; vertical-align: top; }
        .header-right { display: table-cell; vertical-align: top; text-right; }
        
        .company-name { font-size: 26px; font-weight: 900; }
        .company-detail { font-size: 9px; color: #52525b; margin-top: 5px; }
        
        .doc-title { font-size: 32px; font-weight: 900; color: #18181b; }
        .doc-number { font-size: 16px; font-weight: 700; color: #52525b; margin-top: 5px; }

        .parties { display: table; width: 100%; margin-bottom: 40px; }
        .party-col { display: table-cell; width: 50%; vertical-align: top; }
        .party-label { font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 1.5px; color: #71717a; border-bottom: 1px solid #e4e4e7; padding-bottom: 4px; margin-bottom: 10px; width: 80%; }
        .party-name { font-size: 16px; font-weight: 700; margin-bottom: 5px; }
        .party-detail { color: #52525b; line-height: 1.6; }

        .table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .table th { background: #18181b; color: #fff; padding: 10px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .table td { padding: 12px 10px; border-bottom: 1px solid #e4e4e7; vertical-align: top; }
        .table .item-type { font-size: 8px; color: #71717a; text-transform: uppercase; margin-top: 4px; }
        
        .totals-container { display: table; width: 100%; margin-top: 20px; }
        .totals-left { display: table-cell; width: 60%; vertical-align: top; }
        .totals-right { display: table-cell; width: 40%; vertical-align: top; }
        
        .total-row { display: table; width: 100%; margin-bottom: 5px; }
        .total-label { display: table-cell; width: 60%; text-align: right; padding-right: 15px; color: #52525b; }
        .total-value { display: table-cell; width: 40%; text-align: right; font-weight: 700; }
        .grand-total { border-top: 2px solid #18181b; margin-top: 10px; padding-top: 10px; font-size: 16px; }

        .notes-section { margin-top: 50px; padding: 20px; background: #f8fafc; border-radius: 8px; }
        .notes-label { font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 1.5px; color: #71717a; margin-bottom: 8px; }
        .notes-content { color: #52525b; font-size: 10px; line-height: 1.6; }

        .footer { position: fixed; bottom: 40px; left: 40px; right: 40px; text-align: center; font-size: 9px; color: #a1a1aa; border-top: 1px solid #e4e4e7; padding-top: 20px; }
        .payment-status { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; margin-top: 10px; }
        .status-paid { background: #dcfce7; color: #166534; }
        .status-partial { background: #fef9c3; color: #854d0e; }
        .status-unpaid { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    @php
        $settings = \App\Models\Setting::allAsArray();
        $logoBase64 = null;
        if (!empty($settings['company_logo'])) {
            $path = storage_path('app/public/' . $settings['company_logo']);
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }
    @endphp

    <!-- Header -->
    <div class="header">
        <div class="header-left">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Company Logo" style="max-height: 50px; margin-bottom: 10px; display: block;">
            @endif
            <div class="company-name">{{ $settings['company_name'] ?? 'REWORKS' }}</div>
            <div class="company-detail">
                {!! nl2br(e($settings['company_address'] ?? '')) !!}<br>
                Phone: {{ $settings['company_phone'] ?? '' }}<br>
                Email: {{ $settings['company_email'] ?? '' }}
            </div>
        </div>
        <div class="header-right text-right">
            <div class="doc-title">INVOICE</div>
            <div class="doc-number mono">{{ $salesOrder->order_number }}</div>
            <div class="company-detail" style="margin-top: 15px;">
                Date: {{ $salesOrder->created_at->format('d M Y') }}<br>
                Payment Status: <span class="bold uppercase">{{ $salesOrder->payment_status }}</span>
            </div>
        </div>
    </div>

    <!-- Parties -->
    <div class="parties">
        <div class="party-col">
            <div class="party-label">Billed To</div>
            <div class="party-name">{{ $salesOrder->customer->name }}</div>
            <div class="party-detail">
                {{ $salesOrder->customer->organization }}<br>
                {{ $salesOrder->customer->phone }}<br>
                {{ $salesOrder->customer->email }}
            </div>
        </div>
        <div class="party-col text-right">
            @if($salesOrder->intake)
                <div class="party-label" style="margin-left: auto;">Intake & Quotation</div>
                <div class="party-name mono">{{ $salesOrder->intake->intake_number }}</div>
                <div class="party-detail">
                    Quotation: {{ $salesOrder->quotation->quotation_number }}<br>
                    {{ $salesOrder->intake->repairJobs->count() }} Devices Included
                </div>
            @else
                <div class="party-label" style="margin-left: auto;">Job & Quotation</div>
                <div class="party-name mono">{{ $salesOrder->repairJob->job_number }}</div>
                <div class="party-detail">
                    Quotation: {{ $salesOrder->quotation->quotation_number }}<br>
                    Device: {{ $salesOrder->repairJob->brand }} {{ $salesOrder->repairJob->device_name }}
                </div>
            @endif
        </div>
    </div>
    
    @if(!$salesOrder->intake && $salesOrder->repairJob)
    <!-- Job Issue & Diagnosis -->
    <div style="background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 6px; padding: 1px; margin-bottom: 30px;">
        <div style="background: #fff; border-radius: 5px; padding: 15px;">
            <table style="width: 100%; border: none;">
                <tr>
                    <td style="width: 50%; border: none; padding: 0 10px 0 0; vertical-align: top;">
                        <div style="display: inline-block; background: #eff6ff; color: #2563eb; padding: 2px 8px; border-radius: 4px; font-family: DejaVu Sans Mono; font-weight: 700; font-size: 10px; margin-bottom: 8px;">{{ $salesOrder->repairJob->job_number }}</div>
                        <div style="font-size: 12px; font-weight: 900; color: #1e293b;">{{ $salesOrder->repairJob->brand }} {{ $salesOrder->repairJob->device_name }}</div>
                        <div style="font-size: 9px; color: #64748b; margin-top: 4px; font-weight: 700;">
                            MODEL: <span style="color: #475569;">{{ $salesOrder->repairJob->model ?? 'N/A' }}</span> | SN: <span style="color: #475569;">{{ $salesOrder->repairJob->serial_number ?? 'N/A' }}</span>
                        </div>
                    </td>
                    <td style="width: 50%; border: none; padding: 0 0 0 10px; border-left: 1px solid #f1f5f9; vertical-align: top;">
                        <div style="font-size: 8px; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">PROBLEM REPORTED</div>
                        <div style="font-size: 10px; color: #334155; line-height: 1.5;">{{ $salesOrder->repairJob->issue_description }}</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="border: none; padding: 15px 0 0 0; border-top: 1px solid #f1f5f9; margin-top: 15px;">
                        <div style="font-size: 8px; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">FINAL TECHNICIAN FINDINGS & RESOLUTION</div>
                        <div style="font-size: 10px; color: #1e293b; line-height: 1.6; padding: 10px; background: #f8fafc; border-radius: 4px; border-left: 3px solid #2563eb;">
                            {{ $salesOrder->repairJob->diagnoses->first()->findings ?? 'Service completed as per customer requirements.' }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endif

    <!-- Items Table -->
    <table class="table">
        <thead>
            <tr>
                <th style="width: 50%;">Description</th>
                <th class="text-center">Quantity</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $groupedItems = $salesOrder->quotation->items->groupBy('repair_job_id');
            @endphp
            
            @foreach($groupedItems as $jobId => $items)
                @if($jobId && $salesOrder->intake)
                    @php $job = $items->first()->repairJob; @endphp
                    <tr>
                        <td colspan="4" style="padding: 0; border: none;">
                            <div style="background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 6px; padding: 1px; margin: 20px 0 10px 0;">
                                <div style="background: #fff; border-radius: 5px; padding: 15px;">
                                    <table style="width: 100%; border: none; margin: 0;">
                                        <tr>
                                            <td style="width: 50%; border: none; padding: 0 10px 0 0; vertical-align: top;">
                                                <div style="display: inline-block; background: #eff6ff; color: #2563eb; padding: 2px 8px; border-radius: 4px; font-family: DejaVu Sans Mono; font-weight: 700; font-size: 10px; margin-bottom: 8px;">{{ $job->job_number }}</div>
                                                <div style="font-size: 12px; font-weight: 900; color: #1e293b;">{{ $job->brand }} {{ $job->device_name }}</div>
                                                <div style="font-size: 9px; color: #64748b; margin-top: 4px; font-weight: 700;">
                                                    MODEL: <span style="color: #475569;">{{ $job->model ?? 'N/A' }}</span> | SN: <span style="color: #475569;">{{ $job->serial_number ?? 'N/A' }}</span>
                                                </div>
                                            </td>
                                            <td style="width: 50%; border: none; padding: 0 0 0 10px; border-left: 1px solid #f1f5f9; vertical-align: top;">
                                                <div style="font-size: 8px; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">PROBLEM REPORTED</div>
                                                <div style="font-size: 10px; color: #334155; line-height: 1.5;">{{ $job->issue_description }}</div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="border: none; padding: 15px 0 0 0; border-top: 1px solid #f1f5f9; margin-top: 15px;">
                                                <div style="font-size: 8px; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">TECHNICIAN FINDINGS / RESOLUTION</div>
                                                <div style="font-size: 10px; color: #1e293b; line-height: 1.6; padding: 10px; background: #f8fafc; border-radius: 4px; border-left: 3px solid #2563eb;">
                                                    {{ $job->diagnoses->first()->findings ?? 'Service completed as per diagnostics.' }}
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endif
                
                @foreach($items as $item)
                <tr>
                    <td>
                        <div class="bold">{{ $item->description }}</div>
                        <div class="item-type">{{ $item->item_type }}</div>
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ $settings['currency_symbol'] ?? 'PKR' }} {{ number_format($item->unit_price, 0) }}</td>
                    <td class="text-right bold">{{ $settings['currency_symbol'] ?? 'PKR' }} {{ number_format($item->line_total, 0) }}</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals-container">
        <div class="totals-left">
            @if($salesOrder->notes)
                <div class="notes-section">
                    <div class="notes-label">Internal Notes</div>
                    <div class="notes-content">{!! nl2br(e($salesOrder->notes)) !!}</div>
                </div>
            @endif
        </div>
        <div class="totals-right">
            <div class="total-row">
                <div class="total-label">Subtotal</div>
                <div class="total-value">{{ $settings['currency_symbol'] ?? 'PKR' }} {{ number_format($salesOrder->total_amount, 0) }}</div>
            </div>
            <div class="total-row grand-total">
                <div class="total-label bold uppercase tracking-wider">Total Amount</div>
                <div class="total-value bold text-right" style="font-size: 18px;">{{ $settings['currency_symbol'] ?? 'PKR' }} {{ number_format($salesOrder->total_amount, 0) }}</div>
            </div>
            <div class="total-row" style="margin-top: 10px;">
                <div class="total-label">Amount Paid</div>
                <div class="total-value text-emerald-600">-{{ $settings['currency_symbol'] ?? 'PKR' }} {{ number_format($salesOrder->amount_paid, 0) }}</div>
            </div>
            <div class="total-row" style="border-top: 1px solid #e4e4e7; margin-top: 5px; padding-top: 5px;">
                <div class="total-label bold">Balance Due</div>
                <div class="total-value bold">{{ $settings['currency_symbol'] ?? 'PKR' }} {{ number_format($salesOrder->total_amount - $salesOrder->amount_paid, 0) }}</div>
            </div>
        </div>
    </div>

    <div class="footer">
        Computer generated invoice &bull; Thank you for your business.
    </div>
</body>
</html>
