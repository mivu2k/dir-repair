<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report - Grouped by {{ ucfirst($groupBy) }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #18181b; background: #fff; padding: 30px; line-height: 1.4; }
        .mono { font-family: DejaVu Sans Mono, monospace; }
        .bold { font-weight: 700; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .uppercase { text-transform: uppercase; }
        
        .header { display: table; width: 100%; border-bottom: 2px solid #18181b; padding-bottom: 15px; margin-bottom: 20px; }
        .header-left { display: table-cell; vertical-align: top; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        
        .company-name { font-size: 20px; font-weight: 900; }
        .company-detail { font-size: 8px; color: #52525b; margin-top: 3px; }
        
        .doc-title { font-size: 24px; font-weight: 900; color: #18181b; }
        .doc-subtitle { font-size: 12px; font-weight: 700; color: #52525b; margin-top: 5px; }

        .group-section { margin-bottom: 25px; page-break-inside: avoid; }
        .group-header { background: #f4f4f5; padding: 8px 12px; border-left: 4px solid #18181b; margin-bottom: 10px; }
        .group-title { font-size: 11px; font-weight: 900; color: #18181b; text-transform: uppercase; letter-spacing: 0.5px; }

        .table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .table th { border-bottom: 1px solid #18181b; padding: 6px; text-align: left; font-size: 8px; font-weight: 700; text-transform: uppercase; }
        .table td { padding: 6px; border-bottom: 1px solid #e4e4e7; vertical-align: top; }
        
        .footer { position: fixed; bottom: 20px; left: 30px; right: 30px; text-align: center; font-size: 8px; color: #a1a1aa; border-top: 1px solid #e4e4e7; padding-top: 10px; }
        
        .status-badge { display: inline-block; padding: 2px 5px; border-radius: 3px; font-size: 7px; font-weight: 700; text-transform: uppercase; }
        .status-received { background: #f1f5f9; color: #475569; }
        .status-diagnosing { background: #fef9c3; color: #854d0e; }
        .status-waiting_approval { background: #ffedd5; color: #9a3412; }
        .status-in_progress { background: #dcfce7; color: #166534; }
        .status-completed { background: #dbeafe; color: #1e40af; }
        .status-delivered { background: #f0fdf4; color: #15803d; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
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
                $logoData = file_get_contents($path);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($logoData);
            }
        }
    @endphp

    <div class="header">
        <div class="header-left">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo" style="max-height: 40px; margin-bottom: 5px;">
            @endif
            <div class="company-name">{{ $settings['company_name'] ?? 'REWORKS' }}</div>
            <div class="company-detail">
                Generated: {{ now()->format('d M Y H:i') }} | Scenario: {{ strtoupper(str_replace('_', ' ', $scenario ?? 'Custom Matrix')) }}
            </div>
            <div class="company-detail">
                Period: {{ $startDate }} to {{ $endDate }}
            </div>
        </div>
        <div class="header-right">
            <div class="doc-title">INTELLIGENCE REPORT</div>
            <div class="doc-subtitle">GROUPED BY: {{ strtoupper($groupBy ?: 'Status') }}</div>
        </div>
    </div>

    @foreach($data as $groupName => $jobs)
    <div class="group-section">
        <div class="group-header">
            <span class="group-title">{{ $groupName ?: 'Not Specified' }}</span>
            <span style="float: right; font-size: 9px; font-weight: bold; color: #18181b;">
                {{ count($jobs) }} Units
            </span>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 10%;">Date</th>
                    <th style="width: 10%;">Job ID</th>
                    <th style="width: 20%;">Hardware / Client</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 25%;">Symptom(s)</th>
                    <th style="width: 25%;">Technician</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jobs as $job)
                <tr>
                    <td>{{ $job->created_at->format('d/m/y') }}</td>
                    <td class="mono bold">{{ $job->job_number }}</td>
                    <td>
                        <div class="bold">{{ $job->brand }} {{ $job->model }}</div>
                        <div style="font-size: 7px; color: #71717a;">{{ $job->customer->name }}</div>
                    </td>
                    <td>
                        <span class="status-badge status-{{ $job->status }}">
                            {{ str_replace('_', ' ', $job->status) }}
                        </span>
                    </td>
                    <td style="font-size: 7px;">
                        @foreach($job->symptoms as $s)
                            <span style="background: #f1f5f9; padding: 1px 3px; border-radius: 2px; margin-right: 2px;">{{ $s->name }}</span>
                        @endforeach
                        @if($job->symptoms->isEmpty()) - @endif
                    </td>
                    <td>
                        <div class="bold" style="color: #1e40af;">{{ $job->technician?->name ?? 'UNASSIGNED' }}</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach

    <div class="footer">
        &copy; {{ date('Y') }} {{ $settings['company_name'] ?? 'REWORKS' }} &bull; Detailed Activity Report
    </div>
</body>
</html>
