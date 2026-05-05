<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS Receipt</title>
    <style>
        @page { margin: 0; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 11pt; 
            line-height: 1.2; 
            width: 72mm; 
            padding: 4mm;
            margin: 0;
            color: #000;
            background: #fff;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        .divider { border-bottom: 1pt dashed #000; margin: 4pt 0; }
        .divider-double { border-bottom: 2pt solid #000; margin: 4pt 0; }
        
        .header { margin-bottom: 8pt; text-align: center; }
        .company-name { font-size: 18pt; font-weight: 900; letter-spacing: -1pt; margin: 2pt 0; }
        .company-info { font-size: 9pt; line-height: 1.1; margin-bottom: 4pt; }
        
        .receipt-type { 
            font-size: 12pt; 
            background: #000; 
            color: #fff; 
            padding: 2pt 0; 
            margin: 4pt 0; 
            letter-spacing: 2pt;
        }
        
        .info-section { margin: 6pt 0; }
        .info-row { display: block; margin-bottom: 1pt; font-size: 10pt; }
        .label { font-weight: normal; color: #555; }
        .value { font-weight: bold; float: right; }
        
        .job-card { 
            border: 0.5pt solid #ccc; 
            padding: 4pt; 
            margin: 4pt 0; 
        }
        
        .items-table { width: 100%; border-collapse: collapse; margin: 6pt 0; }
        .items-table th { text-align: left; border-bottom: 1pt solid #000; padding: 2pt 0; font-size: 9pt; }
        .items-table td { padding: 4pt 0; vertical-align: top; border-bottom: 0.5pt solid #eee; font-size: 10pt; }
        
        .total-section { margin-top: 4pt; border-top: 1pt solid #000; padding-top: 4pt; }
        .total-row { display: block; padding: 1pt 0; font-size: 11pt; }
        .grand-total { font-size: 14pt; margin-top: 2pt; border-top: 0.5pt solid #000; padding-top: 4pt; }
        
        .footer { margin-top: 12pt; font-size: 8pt; border-top: 1pt dashed #000; padding-top: 6pt; }
        .qr-placeholder { margin: 10pt 0; text-align: center; }
        .job-no-footer { font-size: 14pt; letter-spacing: 3pt; font-weight: 900; margin-top: 4pt; }
        
        .clearfix::after { content: ""; clear: both; display: table; }
        .mono { font-family: monospace; }
    </style>
</head>
<body>
    @php
        $settings = \App\Models\Setting::allAsArray();
        $logoBase64 = null;
        if (!empty($settings['company_logo'])) {
            $path = storage_path('app/public/' . $settings['company_logo']);
            if (file_exists($path)) {
                $type_img = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $logoBase64 = 'data:image/' . $type_img . ';base64,' . base64_encode($data);
            }
        }
    @endphp

    <div class="header">
        <div class="logo">
            @if(isset($logoBase64))
                <img src="{{ $logoBase64 }}" alt="Logo" style="max-width: 120pt; height: auto;">
            @else
                <div class="company-name uppercase">{{ $settings['company_name'] ?? 'MEI' }}</div>
            @endif
        </div>
        <div class="company-info">
            {{ $settings['company_address'] ?? '' }}<br>
            {{ $settings['company_phone'] ?? '' }}
        </div>
        <div class="receipt-type text-center uppercase bold">
            @if($type === 'quotation') QUOTATION @elseif($type === 'intake_summary') BATCH SUMMARY @elseif($type === 'intake_delivery') BATCH DELIVERY @elseif($type === 'intake') INTAKE RECEIPT @else DELIVERY @endif
        </div>
    </div>

    @if($type === 'quotation')
        <div class="info-section">
            <div class="info-row clearfix"><span class="label">DATE:</span><span class="value">{{ now()->format('d/m/y H:i') }}</span></div>
            <div class="info-row clearfix"><span class="label">QUOTE #:</span><span class="value">{{ $quotation->quotation_number }}</span></div>
            <div class="info-row clearfix"><span class="label">CLIENT:</span><span class="value">{{ $quotation->customer ? $quotation->customer->name : ($quotation->repairJob ? $quotation->repairJob->customer->name : ($quotation->intake ? $quotation->intake->customer->name : 'N/A')) }}</span></div>
        </div>
        <div class="divider"></div>
        <table class="items-table">
            <thead>
                <tr class="uppercase">
                    <th style="width: 70%;">Description</th>
                    <th style="width: 30%;" class="text-right">Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $item)
                <tr>
                    <td>
                        <span class="bold">{{ $item->description }}</span><br>
                        <span style="font-size: 8pt; color: #666;">QTY: {{ $item->quantity }} | {{ strtoupper($item->item_type) }}</span>
                    </td>
                    <td class="text-right bold">{{ number_format($item->line_total, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="total-section">
            <div class="total-row grand-total clearfix">
                <span class="bold uppercase">Total:</span>
                <span class="bold">{{ $settings['currency_symbol'] ?? 'PKR' }} {{ number_format($quotation->total_amount, 0) }}</span>
            </div>
        </div>
    @elseif($type === 'intake_summary' || $type === 'intake_delivery')
        <div class="info-section">
            <div class="info-row clearfix"><span class="label">BATCH ID:</span><span class="value">{{ $intake->intake_number }}</span></div>
            <div class="info-row clearfix"><span class="label">DATE:</span><span class="value">{{ $intake->received_at->format('d/m/y H:i') }}</span></div>
            <div class="info-row clearfix"><span class="label">CLIENT:</span><span class="value">{{ $intake->customer->name }}</span></div>
        </div>
        <div class="divider"></div>
        <div class="bold uppercase" style="font-size: 9pt; margin: 4pt 0;">Units in Batch ({{ $intake->repairJobs->count() }}):</div>
        @foreach($intake->repairJobs as $job)
            <div class="job-card">
                <div class="bold" style="font-size: 11pt;">{{ $job->job_number }}</div>
                <div class="uppercase bold" style="font-size: 9pt;">{{ $job->brand }} {{ $job->device_name }}</div>
                <div style="font-size: 8pt; color: #444;">
                    MOD: {{ $job->model ?: '---' }} | SN: <span class="mono">{{ $job->serial_number ?: '---' }}</span>
                </div>
            </div>
        @endforeach
    @else
        <div class="info-section">
            <div class="info-row clearfix"><span class="label">DATE:</span><span class="value">{{ now()->format('d/m/y H:i') }}</span></div>
            <div class="info-row clearfix"><span class="label">JOB ID:</span><span class="value bold" style="font-size: 12pt;">{{ $job->job_number }}</span></div>
        </div>
        
        <div class="divider"></div>
        
        <div class="info-section">
            <div class="info-row clearfix"><span class="label">CLIENT:</span><span class="value">{{ $job->customer->name }}</span></div>
            <div class="info-row clearfix"><span class="label">PHONE:</span><span class="value">{{ $job->customer->phone }}</span></div>
        </div>

        <div class="divider"></div>

        <div class="device-section" style="margin: 4pt 0;">
            <div class="bold uppercase" style="font-size: 12pt;">{{ $job->brand }} {{ $job->device_name }}</div>
            <div style="font-size: 9pt;">MODEL: {{ $job->model ?: '---' }} | SN: {{ $job->serial_number ?: '---' }}</div>
            <div style="margin-top: 4pt; font-size: 10pt; background: #f9f9f9; padding: 4pt;">
                <span class="bold">REPORTED ISSUE:</span><br>
                {{ $job->issue_description }}
            </div>
        </div>

        @if($type === 'delivery' && $job->salesOrder && $job->salesOrder->quotation)
        <table class="items-table">
            <thead>
                <tr class="uppercase">
                    <th style="width: 70%;">Service / Hardware</th>
                    <th style="width: 30%;" class="text-right">Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($job->salesOrder->quotation->items as $item)
                <tr>
                    <td>
                        <span class="bold">{{ $item->description }}</span><br>
                        <span style="font-size: 8pt; color: #666;">QTY: {{ $item->quantity }} | {{ strtoupper($item->item_type) }}</span>
                    </td>
                    <td class="text-right bold">{{ number_format($item->line_total, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-row clearfix">
                <span class="label">SUBTOTAL:</span>
                <span class="value">{{ number_format($job->salesOrder->total_amount, 0) }}</span>
            </div>
            <div class="total-row grand-total clearfix">
                <span class="bold uppercase">Total Due:</span>
                <span class="bold">{{ $settings['currency_symbol'] ?? 'PKR' }} {{ number_format($job->salesOrder->total_amount, 0) }}</span>
            </div>
            <div class="total-row clearfix">
                <span class="label">PAID AMOUNT:</span>
                <span class="value">{{ number_format($job->salesOrder->amount_paid, 0) }}</span>
            </div>
            <div class="total-row bold clearfix" style="font-size: 12pt; border-top: 0.5pt dashed #000; margin-top: 2pt; padding-top: 2pt;">
                <span class="label">BALANCE:</span>
                <span class="value">{{ number_format($job->salesOrder->balance_due, 0) }}</span>
            </div>
        </div>
        @endif
    @endif

    <div class="footer text-center">
        <div class="bold uppercase" style="margin-bottom: 4pt; font-size: 10pt;">
            @if($type === 'intake' || $type === 'intake_summary')
                Terms & Conditions
            @else
                Thank You
            @endif
        </div>
        
        <div style="font-size: 8pt; line-height: 1.1; margin-bottom: 10pt;">
            @if($type === 'intake' || $type === 'intake_summary')
                * Keep receipt for collection.<br>
                * Backup your data; we are not responsible.<br>
                * Units left over 30 days will be recycled.
            @else
                * Warranty as per company policy.<br>
                * No warranty on physical/liquid damage.<br>
                * Keep receipt for warranty validation.
            @endif
        </div>
        
        <div class="qr-placeholder">
            @php
                if ($type === 'intake_summary' || $type === 'intake_delivery') {
                    $qrData = url('/intakes/' . $intake->id);
                    $footerNo = $intake->intake_number;
                } elseif ($type === 'quotation') {
                    $qrData = url('/quotations/' . $quotation->id);
                    $footerNo = $quotation->quotation_number;
                } else {
                    $qrData = url('/jobs/' . $job->job_number);
                    $footerNo = $job->job_number;
                }
                $qrCode = base64_encode(QrCode::format('png')->size(200)->margin(0)->errorCorrection('H')->generate($qrData));
            @endphp
            <img src="data:image/png;base64,{{ $qrCode }}" alt="QR" style="width: 100pt; height: 100pt;">
        </div>
        
        <div class="job-no-footer">
            {{ $footerNo }}
        </div>
        
        <div style="font-size: 8pt; margin-top: 15pt; opacity: 0.6;">
            {{ now()->format('Y') }} &copy; {{ $settings['company_name'] ?? 'MEI OPS' }}
        </div>
    </div>
</body>
</html>
