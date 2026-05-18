<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quotation {{ $quotation->quotation_number }}</title>
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
            <div class="doc-title">QUOTATION</div>
            <div class="doc-number mono">{{ $quotation->quotation_number }}</div>
            <div class="company-detail" style="margin-top: 15px;">
                Date: {{ $quotation->created_at->format('d M Y') }}<br>
                Valid Until: {{ \Carbon\Carbon::parse($quotation->valid_until)->format('d M Y') }}
            </div>
        </div>
    </div>

    <!-- Parties -->
    <div class="parties">
        <div class="party-col">
            <div class="party-label">Recipient</div>
            <div class="party-name">{{ $quotation->intake ? $quotation->intake->customer->name : $quotation->repairJob->customer->name }}</div>
            <div class="party-detail">
                {{ $quotation->intake ? $quotation->intake->customer->organization : $quotation->repairJob->customer->organization }}<br>
                {{ $quotation->intake ? $quotation->intake->customer->phone : $quotation->repairJob->customer->phone }}<br>
                {{ $quotation->intake ? $quotation->intake->customer->email : $quotation->repairJob->customer->email }}
            </div>
        </div>
        <div class="party-col text-right">
            @if($quotation->intake)
                <div class="party-label" style="margin-left: auto;">Intake Reference</div>
                <div class="party-name mono">{{ $quotation->intake->intake_number }}</div>
                <div class="party-detail">
                    {{ $quotation->intake->repairJobs->count() }} Devices Included
                </div>
            @else
                <div class="party-label" style="margin-left: auto;">Job Reference</div>
                <div class="party-name mono">{{ $quotation->repairJob->job_number }}</div>
                <div class="party-detail">
                    {{ $quotation->repairJob->brand }} {{ $quotation->repairJob->device_name }}<br>
                    SN: {{ $quotation->repairJob->serial_number ?? 'N/A' }}
                </div>
            @endif
        </div>
    </div>
    
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
                $groupedItems = $quotation->items->groupBy('repair_job_id');
            @endphp
            
            @foreach($groupedItems as $jobId => $items)
                @if($jobId && $quotation->intake)
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
                                                <div style="font-size: 8px; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">TECHNICIAN FINDINGS / DIAGNOSIS</div>
                                                <div style="font-size: 10px; color: #1e293b; line-height: 1.6; padding: 10px; background: #f8fafc; border-radius: 4px; border-left: 3px solid #2563eb;">
                                                    {{ $job->diagnoses->first()->findings ?? 'Diagnostic inspection in progress. Technical report will be updated.' }}
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
            @if($quotation->notes)
                <div class="notes-section">
                    <div class="notes-label">Notes & Conditions</div>
                    <div class="notes-content">{!! nl2br(e($quotation->notes)) !!}</div>
                </div>
            @endif
        </div>
        <div class="totals-right">
            <div class="total-row">
                <div class="total-label">Subtotal</div>
                <div class="total-value">{{ $settings['currency_symbol'] ?? 'PKR' }} {{ number_format($quotation->total_amount, 0) }}</div>
            </div>
            <div class="total-row grand-total">
                <div class="total-label bold uppercase tracking-wider">Total Amount</div>
                <div class="total-value bold text-right" style="font-size: 18px;">{{ $settings['currency_symbol'] ?? 'PKR' }} {{ number_format($quotation->total_amount, 0) }}</div>
            </div>
        </div>
    </div>

    <div class="footer">
        Authorized Signature Required &bull; Thank you for your business.
    </div>
</body>
</html>
