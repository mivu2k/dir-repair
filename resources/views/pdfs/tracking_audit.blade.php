<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Technical Audit Report</title>
    <style>
        /* DOMPDF PERFORMANCE OPTIMIZATIONS */
        @page { margin: 15mm; }
        * { -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 8pt; color: #1e293b; margin: 0; padding: 0; line-height: 1.4; }
        
        .header-table { width: 100%; border-bottom: 3pt solid #0f172a; padding-bottom: 8mm; margin-bottom: 8mm; }
        .company-name { font-size: 18pt; font-weight: 700; color: #0f172a; letter-spacing: -0.5px; margin-bottom: 2pt; }
        .company-detail { font-size: 8pt; color: #64748b; font-weight: 500; }
        .report-title { font-size: 16pt; font-weight: 700; text-align: right; color: #0f172a; text-transform: uppercase; letter-spacing: 1px; }
        
        table.data-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.data-table th { background: #0f172a; color: #ffffff; padding: 10pt 6pt; text-align: left; text-transform: uppercase; font-weight: 700; font-size: 7pt; letter-spacing: 1px; }
        table.data-table td { padding: 10pt 6pt; border-bottom: 1pt solid #f1f5f9; vertical-align: middle; word-wrap: break-word; }
        
        .group-header { background: #f8fafc; border-bottom: 2pt solid #e2e8f0; border-top: 1pt solid #e2e8f0; }
        .group-title { font-size: 8pt; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 1px; padding: 6pt; }
        
        .bold { font-weight: 700; }
        .mono { font-family: Courier, monospace; color: #334155; }
        .status-badge { font-size: 6.5pt; font-weight: 800; text-transform: uppercase; color: #475569; background: #f1f5f9; padding: 2pt 4pt; border-radius: 3px; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 7pt; color: #9ca3af; border-top: 0.5pt solid #e5e7eb; padding-top: 5mm; }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td width="50%" style="vertical-align: top;">
                <div class="company-name">{{ $settings['company_name'] ?? 'MEI OPERATIONS' }}</div>
                <div class="company-detail">{{ $settings['company_address'] ?? 'Operational HQ' }}</div>
                <div class="company-detail">Tel: {{ $settings['company_phone'] ?? 'N/A' }}</div>
            </td>
            <td width="50%" style="vertical-align: top; text-align: right;">
                <div class="report-title">Technical Audit Report</div>
                <div class="company-detail">Period: {{ request('start_date', 'Full History') }} - {{ request('end_date', 'Present') }}</div>
                <div class="company-detail">Generated: {{ now()->format('d M Y | H:i') }}</div>
            </td>
        </tr>
    </table>

    <!-- Analytics Matrix -->
    <div style="margin-bottom: 25px;">
        <h3 style="font-size: 9pt; font-weight: 800; color: #0f172a; text-transform: uppercase; margin-bottom: 6pt; letter-spacing: 1px;">Audit Summary Matrix</h3>
        <table style="width: 100%; border-collapse: collapse; border: 1pt solid #e2e8f0;">
            <thead style="background: #f8fafc;">
                <tr>
                    <th style="padding: 6pt; border: 0.5pt solid #e2e8f0; text-align: left; font-size: 7pt; color: #475569;">AGGREGATION GROUP</th>
                    <th style="padding: 6pt; border: 0.5pt solid #e2e8f0; text-align: center; font-size: 7pt; color: #475569;">TOTAL UNITS</th>
                    <th style="padding: 6pt; border: 0.5pt solid #e2e8f0; text-align: center; font-size: 7pt; color: #475569;">% SHARE</th>
                </tr>
            </thead>
            <tbody>
                @php $totalAll = $grouped->sum(fn($group) => count($group)); @endphp
                @foreach($grouped as $groupName => $items)
                <tr>
                    <td style="padding: 6pt; border: 0.5pt solid #e2e8f0; font-weight: bold; font-size: 8pt; color: #0f172a;">{{ strtoupper(str_replace('_', ' ', $groupName)) }}</td>
                    <td style="padding: 6pt; border: 0.5pt solid #e2e8f0; text-align: center; font-weight: bold; font-size: 8pt; color: #0f172a;">{{ count($items) }}</td>
                    <td style="padding: 6pt; border: 0.5pt solid #e2e8f0; text-align: center; color: #64748b; font-size: 7pt;">
                        {{ number_format((count($items) / ($totalAll ?: 1)) * 100, 1) }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="12%">Timestamp</th>
                <th width="6%">Src</th>
                <th width="14%">Reference</th>
                <th>Hardware / Client Entity</th>
                <th width="16%">Serial Number</th>
                <th width="10%">Status</th>
                <th width="10%" style="text-align: right;">Staff</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grouped as $groupName => $items)
                @if($groupBy !== 'flat')
                <tr class="group-header">
                    <td colspan="7" class="group-title">
                        {{ strtoupper(str_replace('_', ' ', $groupName)) }} &bull; {{ count($items) }} UNITS
                    </td>
                </tr>
                @endif
                
                @foreach($items as $mv)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($mv['date'])->format('d/m/y H:i') }}</td>
                    <td class="bold">{{ substr($mv['source'], 0, 4) }}</td>
                    <td class="bold mono">{{ $mv['id'] }}</td>
                    <td>
                        <div class="bold">{{ $mv['item_name'] }}</div>
                        <div style="font-size: 7pt; color: #4b5563;">{{ $mv['client'] }}</div>
                    </td>
                    <td class="mono">{{ $mv['serial'] }}</td>
                    <td>
                        <div class="status-text">{{ $mv['status'] }}</div>
                    </td>
                    <td style="text-align: right; font-weight: bold; font-size: 7pt;">{{ $mv['staff'] }}</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        {{ $settings['company_name'] ?? 'MEI OPERATIONS' }} &bull; Internal Audit Document &bull; Confidential &bull; Page <span class="page-number"></span>
    </div>
</body>
</html>
