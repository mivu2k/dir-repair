<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ strtoupper($variant) }} - {{ $job->job_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #18181b; background: #fff; padding: 30px; line-height: 1.4; }
        .mono { font-family: DejaVu Sans Mono, monospace; }
        .bold { font-weight: 700; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .uppercase { text-transform: uppercase; }
        .tracking-wider { letter-spacing: 1px; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 16px; }
        .mb-8 { margin-bottom: 32px; }
        .pb-2 { padding-bottom: 8px; }
        .border-b { border-bottom: 1px solid #e4e4e7; }
        .border-t { border-top: 1px solid #e4e4e7; }
        
        .header { display: table; width: 100%; border-bottom: 2px solid #18181b; padding-bottom: 20px; margin-bottom: 20px; }
        .header-left { display: table-cell; vertical-align: top; }
        .header-right { display: table-cell; vertical-align: top; text-right; }
        
        .company-name { font-size: 24px; font-weight: 900; }
        .company-detail { font-size: 9px; color: #52525b; margin-top: 5px; }
        
        .doc-title { font-size: 20px; font-weight: 900; margin-bottom: 5px; }
        .doc-number { font-size: 14px; font-weight: 700; color: #52525b; }

        .section { margin-bottom: 25px; }
        .section-title { font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 1.5px; color: #71717a; border-bottom: 1px solid #e4e4e7; padding-bottom: 4px; margin-bottom: 10px; }
        
        .grid { display: table; width: 100%; }
        .grid-col { display: table-cell; width: 50%; vertical-align: top; }
        
        .label { font-size: 9px; font-weight: 700; text-transform: uppercase; color: #71717a; display: block; margin-bottom: 2px; }
        .value { font-size: 11px; font-weight: 400; margin-bottom: 10px; display: block; }
        
        .table { width: 100%; border-collapse: collapse; }
        .table th { background: #f4f4f5; padding: 6px 10px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; }
        .table td { padding: 8px 10px; border-bottom: 1px solid #e4e4e7; vertical-align: top; }

        .signature-box { margin-top: 50px; display: table; width: 100%; }
        .signature-line { display: table-cell; width: 45%; border-top: 1px solid #18181b; padding-top: 10px; text-align: center; }
        .signature-space { display: table-cell; width: 10%; }

        .variant-badge { display: inline-block; padding: 4px 8px; background: #18181b; color: #fff; font-size: 10px; font-weight: 700; border-radius: 4px; margin-bottom: 10px; }
        
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 120px; color: #f4f4f5; font-weight: 900; z-index: -1; opacity: 0.5; }
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

    <div class="watermark uppercase">{{ $variant }}</div>

    <!-- Header -->
    <div class="header">
        <div class="header-left">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Company Logo" style="max-height: 50px; margin-bottom: 10px; display: block;">
            @endif
            <div class="company-name">{{ $settings['company_name'] ?? 'REWORKS' }}</div>
            <div class="company-detail">
                {!! nl2br(e($settings['company_address'] ?? '')) !!}<br>
                {{ $settings['company_phone'] ?? '' }} &bull; {{ $settings['company_email'] ?? '' }}
            </div>
        </div>
        <div class="header-right text-right">
            <div class="variant-badge uppercase tracking-wider">
                @if($variant == 'intake') Intake Receipt @elseif($variant == 'workorder') Work Order @else Delivery Receipt @endif
            </div>
            <div class="doc-title uppercase">Job Card</div>
            <div class="doc-number mono">{{ $job->job_number }}</div>
            <div class="company-detail" style="margin-top: 10px;">
                Date: {{ now()->format('d M Y, H:i') }}
            </div>
        </div>
    </div>

    <!-- Customer & Job Summary -->
    <div class="section">
        <div class="grid">
            <div class="grid-col">
                <div class="section-title">Customer Information</div>
                <div class="label">Name</div>
                <div class="value bold">{{ $job->customer->name }}</div>
                <div class="label">Phone</div>
                <div class="value">{{ $job->customer->phone }}</div>
                @if($job->customer->organization)
                    <div class="label">Organization</div>
                    <div class="value">{{ $job->customer->organization }}</div>
                @endif
            </div>
            <div class="grid-col">
                <div class="section-title">Device Summary</div>
                <div class="label">Brand & Model</div>
                <div class="value bold">{{ $job->brand }} {{ $job->device_name }} {{ $job->model }}</div>
                <div class="label">Serial Number</div>
                <div class="value mono">{{ $job->serial_number ?? 'N/A' }}</div>
                <div class="label">Condition on Arrival</div>
                <div class="value capitalize">{{ $job->condition_on_arrival }}</div>
            </div>
        </div>
    </div>

    <!-- Issue & Details -->
    <div class="section">
        <div class="section-title">Problem Description</div>
        <div class="value" style="padding: 10px; background: #f8fafc; border-radius: 4px;">{{ $job->issue_description }}</div>
    </div>

    @if($variant == 'workorder')
        <!-- Symptoms & Accessories for Technician -->
        <div class="section">
            <div class="grid">
                <div class="grid-col">
                    <div class="section-title">Symptoms Identified</div>
                    <div class="value">
                        @if($job->symptoms->count() > 0)
                            <ul style="padding-left: 15px;">
                                @foreach($job->symptoms as $s)
                                    <li>{{ $s->name }}</li>
                                @endforeach
                            </ul>
                        @else
                            No specific symptoms logged.
                        @endif
                    </div>
                </div>
                <div class="grid-col">
                    <div class="section-title">Accessories Included</div>
                    <div class="value">
                        @if($job->accessories->count() > 0)
                            <ul style="padding-left: 15px;">
                                @foreach($job->accessories as $a)
                                    <li>{{ $a->name }}</li>
                                @endforeach
                            </ul>
                        @else
                            No accessories received.
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Technician Notes & Findings</div>
            <div style="height: 150px; border: 1px dashed #d4d4d8; border-radius: 4px;"></div>
        </div>

        <div class="section">
            <div class="section-title">Parts Used</div>
            <div style="height: 80px; border: 1px dashed #d4d4d8; border-radius: 4px;"></div>
        </div>
    @endif

    @if($variant == 'intake')
        <div class="section">
            <div class="section-title">Terms & Conditions</div>
            <div class="company-detail" style="font-size: 8px;">
                1. Devices not collected within 90 days will be disposed of.<br>
                2. We are not responsible for any data loss during the repair process.<br>
                3. A minimum inspection fee may apply even if no repair is performed.<br>
                4. This receipt must be presented at the time of collection.
            </div>
        </div>
    @endif

    @if($variant == 'delivery')
        <div class="section">
            <div class="section-title">Final Status</div>
            <div class="value capitalize">Repair Status: <span class="bold">{{ $job->status }}</span></div>
            @if($job->delivered_at)
                <div class="value">Delivered on: {{ \Carbon\Carbon::parse($job->delivered_at)->format('d M Y, H:i') }}</div>
            @endif
        </div>
    @endif

    <!-- Signatures -->
    <div class="signature-box">
        <div class="signature-line">
            <div class="label">Workshop Representative</div>
            <div style="margin-top: 10px;">{{ auth()->user()->name }}</div>
        </div>
        <div class="signature-space"></div>
        <div class="signature-line">
            <div class="label">Customer Signature</div>
            <div style="margin-top: 10px;">{{ $job->customer->name }}</div>
        </div>
    </div>

    <div class="footer" style="position: fixed; bottom: 30px; left: 0; right: 0; text-align: center; font-size: 8px; color: #a1a1aa;">
        Printed on: {{ now()->format('Y-m-d H:i:s') }}
    </div>

</body>
</html>
