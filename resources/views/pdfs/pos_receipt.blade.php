<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS Receipt</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10pt;
            font-weight: 700;
            line-height: 1.35;
            width: 76mm;
            padding: 3mm 2.5mm 8mm 2.5mm;
            color: #000;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── Header ── */
        .header { text-align: center; margin-bottom: 2mm; }
        .logo-img {
            max-height: 52pt;
            max-width: 68mm;
            object-fit: contain;
            display: block;
            margin: 0 auto 1.5mm;
        }
        .company-name {
            font-size: 15pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: -0.5pt;
            line-height: 1;
        }
        .company-sub {
            font-size: 8pt;
            font-weight: 900;
            line-height: 1.4;
            margin-top: 1mm;
        }
        .receipt-type {
            display: block;
            background: #000 !important;
            color: #fff !important;
            text-align: center;
            font-size: 9.5pt;
            font-weight: 900;
            letter-spacing: 3pt;
            text-transform: uppercase;
            padding: 1.8mm 0;
            margin: 2mm 0 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── Dividers ── */
        .div-solid  { border: none; border-top: 1.5pt solid #000; margin: 2.5mm 0; }
        .div-dashed { border: none; border-top: 1.2pt dashed #000;  margin: 2mm 0;   }
        .div-double {
            border: none;
            border-top: 3pt double #000;
            margin: 2.5mm 0;
        }

        /* ── Key-Value rows ── */
        .kv-block { margin: 1.5mm 0; }
        .kv-row   { display: table; width: 100%; margin-bottom: 1.2mm; }
        .kv-label {
            display: table-cell;
            font-size: 8.5pt;
            font-weight: 900;
            text-transform: uppercase;
            white-space: nowrap;
            padding-right: 2mm;
            width: 30%;
            vertical-align: top;
        }
        .kv-value {
            display: table-cell;
            font-size: 8.5pt;
            font-weight: 900;
            text-align: right;
            vertical-align: top;
            word-break: break-all;
        }
        .kv-value.large {
            font-size: 11pt;
            font-weight: 900;
        }

        /* ── Section title ── */
        .section-title {
            font-size: 8.5pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2pt;
            margin: 2.5mm 0 1.5mm;
            border-bottom: 1.5pt solid #000;
            padding-bottom: 0.5mm;
        }

        /* ── Items table ── */
        .items-table { width: 100%; border-collapse: collapse; margin: 1mm 0; }
        .items-table thead th {
            font-size: 8.5pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            padding: 1.2mm 0;
            border-top: 1.5pt solid #000;
            border-bottom: 1.5pt solid #000;
            text-align: left;
        }
        .items-table thead th:last-child { text-align: right; }
        .items-table tbody td {
            padding: 1.8mm 0;
            font-size: 9pt;
            font-weight: 900;
            vertical-align: top;
            border-bottom: 1pt dashed #000;
        }
        .items-table tbody tr:last-child td { border-bottom: none; }
        .items-table tbody td:last-child {
            text-align: right;
            font-weight: 900;
            white-space: nowrap;
        }
        .item-badge {
            display: block;
            font-size: 7.5pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            margin-top: 0.5mm;
        }

        /* ── Totals ── */
        .total-row {
            display: table;
            width: 100%;
            padding: 1mm 0;
        }
        .t-label {
            display: table-cell;
            font-size: 9pt;
            font-weight: 900;
            text-transform: uppercase;
            vertical-align: middle;
        }
        .t-value {
            display: table-cell;
            font-size: 9pt;
            font-weight: 900;
            text-align: right;
            vertical-align: middle;
            white-space: nowrap;
        }
        .total-grand {
            background: #000 !important;
            color: #fff !important;
            padding: 2.2mm;
            margin: 2mm 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            display: table;
            width: 100%;
        }
        .total-grand .t-label,
        .total-grand .t-value {
            font-size: 12pt;
            font-weight: 900;
            color: #fff !important;
        }

        /* ── Job card (batch intake) ── */
        .job-card {
            border: 1.5pt solid #000;
            padding: 2.2mm;
            margin-bottom: 2.5mm;
        }
        .job-num    { font-size: 11pt; font-weight: 900; }
        .job-device { font-size: 9.5pt; font-weight: 900; text-transform: uppercase; margin-top: 0.8mm; }
        .job-meta   { font-size: 8pt; font-weight: 900; margin-top: 0.8mm; }

        /* ── Device block ── */
        .device-name { font-size: 12pt; font-weight: 900; text-transform: uppercase; line-height: 1.25; }
        .device-meta { font-size: 9pt; font-weight: 900; margin-top: 1mm; }
        .issue-box {
            border: 1.5pt solid #000;
            padding: 2.2mm;
            margin-top: 2.5mm;
            font-size: 9pt;
            font-weight: 900;
            line-height: 1.45;
        }
        .issue-label {
            display: block;
            font-size: 8pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1pt;
            border-bottom: 1.2pt solid #000;
            padding-bottom: 0.5mm;
            margin-bottom: 1.2mm;
        }

        /* ── Footer ── */
        .footer {
            text-align: center;
            margin-top: 5mm;
            padding-top: 2.5mm;
            border-top: 1.5pt dashed #000;
        }
        .footer-title {
            font-size: 9.5pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1.5pt;
            margin-bottom: 1.5mm;
        }
        .terms { font-size: 8pt; font-weight: 900; line-height: 1.45; text-align: left; }
        .qr-wrap { text-align: center; margin: 3.5mm 0 1.5mm; }
        .qr-wrap img { width: 75pt; height: 75pt; display: block; margin: 0 auto; }
        .footer-num { font-size: 10pt; font-weight: 900; letter-spacing: 2.5pt; margin-top: 1.5mm; }
        .copy { font-size: 7.5pt; font-weight: 900; margin-top: 2.5mm; text-transform: uppercase; }
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
                $logoBase64 = 'data:image/' . $type_img . ';base64,' . base64_encode(file_get_contents($path));
            }
        }
        $currency = $settings['currency_symbol'] ?? 'PKR';
    @endphp

    {{-- HEADER --}}
    <div class="header">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="Logo" class="logo-img">
        @else
            <div class="company-name">{{ $settings['company_name'] ?? 'MEI TECHNICAL' }}</div>
        @endif
        <div class="company-sub">
            {{ $settings['company_address'] ?? '' }}<br>
            {{ $settings['company_phone'] ?? '' }}
        </div>
        <span class="receipt-type">
            @if($type === 'quotation') Quotation @elseif($type === 'intake_summary') Batch Summary @elseif($type === 'intake_delivery') Batch Delivery @elseif($type === 'intake') Intake Receipt @else Delivery @endif
        </span>
    </div>

    @if($type === 'quotation')
        <div class="kv-block">
            <div class="kv-row">
                <span class="kv-label">DATE</span>
                <span class="kv-value">{{ now()->format('d/m/y H:i') }}</span>
            </div>
            <div class="kv-row">
                <span class="kv-label">QUOTE #</span>
                <span class="kv-value large">{{ $quotation->quotation_number }}</span>
            </div>
            <div class="kv-row">
                <span class="kv-label">CLIENT</span>
                <span class="kv-value">{{ $quotation->customer ? $quotation->customer->name : ($quotation->repairJob ? $quotation->repairJob->customer->name : ($quotation->intake ? $quotation->intake->customer->name : 'N/A')) }}</span>
            </div>
        </div>

        <div class="div-dashed"></div>
        <div class="section-title">Items</div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:73%;">Item</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $item)
                <tr>
                    <td>
                        {{ $item->description }}
                        <span class="item-badge">Qty {{ $item->quantity }} · {{ $item->item_type }}</span>
                    </td>
                    <td>{{ number_format($item->line_total, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="div-dashed"></div>

        <div class="total-grand">
            <span class="t-label">TOTAL</span>
            <span class="t-value">{{ $currency }} {{ number_format($quotation->total_amount, 0) }}</span>
        </div>

    @elseif($type === 'intake_summary' || $type === 'intake_delivery')
        <div class="kv-block">
            <div class="kv-row">
                <span class="kv-label">BATCH ID</span>
                <span class="kv-value large">{{ $intake->intake_number }}</span>
            </div>
            <div class="kv-row">
                <span class="kv-label">DATE</span>
                <span class="kv-value">{{ $intake->received_at->format('d/m/y H:i') }}</span>
            </div>
            <div class="kv-row">
                <span class="kv-label">CLIENT</span>
                <span class="kv-value">{{ $intake->customer->name }}</span>
            </div>
        </div>

        <div class="div-dashed"></div>
        <div class="section-title">Units in Batch ({{ $intake->repairJobs->count() }})</div>

        @foreach($intake->repairJobs as $job)
            <div class="job-card">
                <div class="job-num">{{ $job->job_number }}</div>
                <div class="job-device">{{ $job->brand }} {{ $job->device_name }}</div>
                <div class="job-meta">MOD: {{ $job->model ?: '—' }} | SN: {{ $job->serial_number ?: '—' }}</div>
            </div>
        @endforeach

    @else
        <div class="kv-block">
            <div class="kv-row">
                <span class="kv-label">DATE</span>
                <span class="kv-value">{{ now()->format('d/m/y H:i') }}</span>
            </div>
            <div class="kv-row">
                <span class="kv-label">JOB #</span>
                <span class="kv-value large">{{ $job->job_number }}</span>
            </div>
        </div>

        <div class="div-dashed"></div>

        <div class="kv-block">
            <div class="kv-row">
                <span class="kv-label">CLIENT</span>
                <span class="kv-value">{{ $job->customer->name }}</span>
            </div>
            <div class="kv-row">
                <span class="kv-label">PHONE</span>
                <span class="kv-value">{{ $job->customer->phone }}</span>
            </div>
        </div>

        <div class="div-dashed"></div>

        <div class="device-name">{{ $job->brand }} {{ $job->device_name }}</div>
        <div class="device-meta">MODEL: {{ $job->model ?: '—' }} | SN: {{ $job->serial_number ?: '—' }}</div>
        <div class="issue-box">
            <span class="issue-label">Reported Issue</span>
            {{ $job->issue_description }}
        </div>

        @if($type === 'delivery' && $job->salesOrder && $job->salesOrder->quotation)
            <div class="div-dashed"></div>
            <div class="section-title">Services & Parts</div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width:73%;">Item</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($job->salesOrder->quotation->items as $item)
                    <tr>
                        <td>
                            {{ $item->description }}
                            <span class="item-badge">Qty {{ $item->quantity }} · {{ $item->item_type }}</span>
                        </td>
                        <td>{{ number_format($item->line_total, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="div-dashed"></div>

            <div class="total-grand">
                <span class="t-label">TOTAL</span>
                <span class="t-value">{{ $currency }} {{ number_format($job->salesOrder->total_amount, 0) }}</span>
            </div>

            <div class="total-row">
                <span class="t-label">PAID</span>
                <span class="t-value">{{ $currency }} {{ number_format($job->salesOrder->amount_paid, 0) }}</span>
            </div>
            <div class="div-dashed"></div>
            <div class="total-row">
                <span class="t-label">BALANCE</span>
                <span class="t-value" style="font-size:11pt;">{{ $currency }} {{ number_format($job->salesOrder->balance_due, 0) }}</span>
            </div>
        @endif
    @endif

    {{-- FOOTER --}}
    <div class="footer">
        <div class="footer-title">
            @if(in_array($type, ['intake', 'intake_summary'])) Terms & Conditions @else Thank You @endif
        </div>
        <div class="terms">
            @if(in_array($type, ['intake', 'intake_summary']))
                * Keep this receipt for device collection.<br>
                * Please back up your data before submission.<br>
                * Devices left over 30 days may be recycled.
            @else
                * Warranty applies per company policy.<br>
                * No warranty on physical or liquid damage.<br>
                * Keep receipt for warranty claims.
            @endif
        </div>

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
            $qrCode = base64_encode(SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(150)->margin(0)->errorCorrection('H')->generate($qrData));
        @endphp

        <div class="qr-wrap">
            <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR Code">
        </div>

        <div class="footer-num">{{ $footerNo }}</div>
        <div class="copy">{{ now()->format('Y') }} &copy; {{ $settings['company_name'] ?? 'Company' }}</div>
    </div>
</body>
</html>
