<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Intake Summary - {{ $intake->intake_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #18181b; background: #fff; padding: 30px; line-height: 1.4; }
        .mono { font-family: DejaVu Sans Mono, monospace; }
        .bold { font-weight: 700; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .uppercase { text-transform: uppercase; }
        .tracking-wider { letter-spacing: 1px; }
        
        .header { display: table; width: 100%; border-bottom: 2px solid #18181b; padding-bottom: 20px; margin-bottom: 20px; }
        .header-left { display: table-cell; vertical-align: top; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        
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
        
        .job-item { border: 1px solid #e4e4e7; border-radius: 8px; padding: 15px; margin-bottom: 15px; page-break-inside: avoid; }
        .job-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; border-bottom: 1px dashed #e4e4e7; padding-bottom: 10px; }

        .footer { position: fixed; bottom: 30px; left: 0; right: 0; text-align: center; font-size: 8px; color: #a1a1aa; }
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
                <img src="{{ $logoBase64 }}" alt="Logo" style="max-height: 50px; margin-bottom: 10px; display: block;">
            @endif
            <div class="company-name">{{ $settings['company_name'] ?? 'REWORKS' }}</div>
            <div class="company-detail">
                {!! nl2br(e($settings['company_address'] ?? '')) !!}<br>
                {{ $settings['company_phone'] ?? '' }}
            </div>
        </div>
        <div class="header-right">
            <div class="doc-title uppercase">Intake Summary</div>
            <div class="doc-number mono">{{ $intake->intake_number }}</div>
            <div class="company-detail" style="margin-top: 10px;">
                Date: {{ $intake->received_at->format('d M Y, H:i') }}
            </div>
        </div>
    </div>

    <!-- Customer Section -->
    <div class="section">
        <div class="section-title">Customer Information</div>
        <div class="grid">
            <div class="grid-col">
                <div class="label">Name</div>
                <div class="value bold">{{ $intake->customer->name }}</div>
            </div>
            <div class="grid-col">
                <div class="label">Phone</div>
                <div class="value">{{ $intake->customer->phone }}</div>
            </div>
        </div>
    </div>

    <!-- Jobs Section -->
    <div class="section">
        <div class="section-title">Devices Received ({{ $intake->repairJobs->count() }})</div>
        @foreach($intake->repairJobs as $job)
            <div class="job-item">
                <div style="display: table; width: 100%; border-bottom: 1px dashed #e4e4e7; margin-bottom: 10px; padding-bottom: 5px;">
                    <div style="display: table-cell;" class="bold mono">{{ $job->job_number }}</div>
                    <div style="display: table-cell; text-align: right;" class="uppercase bold text-zinc-600">{{ $job->brand }} {{ $job->device_name }}</div>
                </div>
                
                <div class="grid">
                    <div class="grid-col">
                        <div class="label">Model / Serial</div>
                        <div class="value">{{ $job->model ?: 'N/A' }} / <span class="mono">{{ $job->serial_number ?: 'N/A' }}</span></div>
                    </div>
                    <div class="grid-col">
                        <div class="label">Condition</div>
                        <div class="value capitalize">{{ $job->condition_on_arrival }}</div>
                    </div>
                </div>

                <div class="label">Problem Reported</div>
                <div class="value" style="margin-bottom: 5px;">{{ $job->issue_description }}</div>

                <div class="grid" style="margin-top: 10px;">
                    <div class="grid-col">
                        <div class="label">Accessories</div>
                        <div class="value" style="font-size: 10px;">
                            @foreach($job->accessories as $acc)
                                {{ $acc->name }}{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                            @if($job->accessories->isEmpty()) None @endif
                        </div>
                    </div>
                    <div class="grid-col">
                        <div class="label">Symptoms</div>
                        <div class="value" style="font-size: 10px;">
                            @foreach($job->symptoms as $sym)
                                {{ $sym->name }}{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                            @if($job->symptoms->isEmpty()) None @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Signatures -->
    <div style="margin-top: 50px; display: table; width: 100%;">
        <div style="display: table-cell; width: 45%; border-top: 1px solid #18181b; padding-top: 10px; text-align: center;">
            <div class="label">Workshop Representative</div>
            <div style="margin-top: 10px;">{{ $intake->receivedBy->name }}</div>
        </div>
        <div style="display: table-cell; width: 10%;"></div>
        <div style="display: table-cell; width: 45%; border-top: 1px solid #18181b; padding-top: 10px; text-align: center;">
            <div class="label">Customer Signature</div>
            <div style="margin-top: 10px;">{{ $intake->customer->name }}</div>
        </div>
    </div>

    <div class="footer">
        Computer generated summary &bull; Printed on: {{ now()->format('Y-m-d H:i:s') }}
    </div>
</body>
</html>
