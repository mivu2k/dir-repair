<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gate Pass — {{ $gatePass->pass_number }}</title>
    <style>
        @page { margin: 18mm 15mm 18mm 15mm; size: A4; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.5;
            color: #1a1a1a;
            background: #fff;
        }

        /* ── Header ── */
        .page-header {
            display: table;
            width: 100%;
            border-bottom: 2pt solid #1a1a1a;
            padding-bottom: 8pt;
            margin-bottom: 14pt;
        }
        .header-left  { display: table-cell; vertical-align: middle; }
        .header-right { display: table-cell; vertical-align: middle; text-align: right; }

        .logo-img { max-height: 36pt; max-width: 100pt; object-fit: contain; }
        .company-name { font-size: 16pt; font-weight: 900; text-transform: uppercase; letter-spacing: 1pt; line-height: 1; }
        .company-sub  { font-size: 8.5pt; color: #555; margin-top: 2pt; }

        .doc-title {
            font-size: 14pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1.5pt;
            line-height: 1;
        }
        .doc-sub {
            display: inline-block;
            font-size: 10pt;
            font-weight: 700;
            padding: 2pt 8pt;
            margin-top: 4pt;
            border-radius: 3pt;
        }
        .inward-badge  { background: #dcfce7; color: #15803d; border: 0.8pt solid #86efac; }
        .outward-badge { background: #ffedd5; color: #c2410c; border: 0.8pt solid #fdba74; }

        /* ── Info Grid ── */
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14pt;
        }
        .info-grid td {
            padding: 5pt 8pt;
            border: 0.8pt solid #d1d5db;
            vertical-align: top;
        }
        .info-grid .cell-label {
            background: #f9fafb;
            font-size: 7.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            color: #6b7280;
            width: 18%;
            white-space: nowrap;
        }
        .info-grid .cell-value {
            font-size: 10pt;
            font-weight: 600;
            color: #111;
        }
        .pass-number-val {
            font-size: 13pt;
            font-weight: 900;
            letter-spacing: 1pt;
        }

        /* ── Items table ── */
        .section-heading {
            font-size: 8.5pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1pt;
            color: #374151;
            margin-bottom: 6pt;
            border-bottom: 0.8pt solid #e5e7eb;
            padding-bottom: 3pt;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14pt;
        }
        .items-table thead th {
            background: #f3f4f6;
            border: 0.8pt solid #d1d5db;
            padding: 5pt 8pt;
            text-align: left;
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            color: #6b7280;
        }
        .items-table thead th:last-child { text-align: center; }
        .items-table tbody td {
            border: 0.8pt solid #e5e7eb;
            padding: 6pt 8pt;
            font-size: 10pt;
            vertical-align: middle;
        }
        .items-table tbody td.serial {
            font-family: 'Courier New', monospace;
            font-size: 9pt;
            color: #111;
            font-weight: 700;
        }
        .items-table tbody td.qty {
            text-align: center;
            font-weight: 700;
        }
        .items-table tbody tr:nth-child(even) td { background: #f9fafb; }

        /* Empty box if no items */
        .empty-box {
            border: 0.8pt solid #d1d5db;
            min-height: 60pt;
            padding: 8pt;
            margin-bottom: 14pt;
            color: #9ca3af;
            font-style: italic;
            font-size: 9pt;
        }

        /* Notes */
        .notes-box {
            border: 0.8pt solid #d1d5db;
            padding: 8pt;
            min-height: 30pt;
            margin-bottom: 14pt;
            font-size: 9.5pt;
            line-height: 1.5;
            background: #fafafa;
        }

        /* ── Signatures ── */
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 28pt;
        }
        .sig-table td {
            width: 33.3%;
            text-align: center;
            padding: 0 8pt;
            vertical-align: bottom;
        }
        .sig-line-wrap { padding-top: 28pt; }
        .sig-line {
            border-top: 1pt solid #1a1a1a;
            padding-top: 4pt;
            font-size: 8.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
        }
        .sig-sub { font-size: 7.5pt; color: #6b7280; font-weight: 400; text-transform: none; margin-top: 1pt; }

        /* ── Footer ── */
        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7.5pt;
            color: #9ca3af;
            border-top: 0.5pt solid #e5e7eb;
            padding-top: 4pt;
        }
    </style>
</head>
<body>
    @php
        $settings = \App\Models\Setting::allAsArray();
        $logoBase64 = null;
        if (!empty($settings['company_logo'])) {
            $path = storage_path('app/public/' . $settings['company_logo']);
            if (file_exists($path)) {
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                $logoBase64 = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($path));
            }
        }
        $isInward = $gatePass->type === 'inward';
        $items = is_array($gatePass->items) ? $gatePass->items : (json_decode($gatePass->items, true) ?? []);
    @endphp

    {{-- HEADER --}}
    <div class="page-header">
        <div class="header-left">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo" class="logo-img">
            @else
                <div class="company-name">{{ $settings['company_name'] ?? 'Company' }}</div>
            @endif
            <div class="company-sub">{{ $settings['company_address'] ?? '' }} &nbsp;|&nbsp; {{ $settings['company_phone'] ?? '' }}</div>
        </div>
        <div class="header-right">
            <div class="doc-title">Gate Pass</div>
            <div>
                <span class="doc-sub {{ $isInward ? 'inward-badge' : 'outward-badge' }}">
                    {{ $isInward ? 'INWARD' : 'OUTWARD' }}
                </span>
            </div>
        </div>
    </div>

    {{-- INFO GRID --}}
    <table class="info-grid">
        <tr>
            <td class="cell-label">Pass Number</td>
            <td class="cell-value" colspan="3">
                <span class="pass-number-val">{{ $gatePass->pass_number }}</span>
            </td>
        </tr>
        <tr>
            <td class="cell-label">Date &amp; Time</td>
            <td class="cell-value">{{ $gatePass->created_at->format('d/m/Y — h:i A') }}</td>
            <td class="cell-label">Direction</td>
            <td class="cell-value" style="text-transform: uppercase; font-weight: 900; {{ $isInward ? 'color:#15803d;' : 'color:#c2410c;' }}">
                {{ strtoupper($gatePass->type) }}
            </td>
        </tr>
        <tr>
            <td class="cell-label">Person / Carrier</td>
            <td class="cell-value" colspan="3" style="font-size:11pt;">{{ $gatePass->person_name }}</td>
        </tr>
        <tr>
            <td class="cell-label">Company / Vendor</td>
            <td class="cell-value">{{ $gatePass->company_name ?: '—' }}</td>
            <td class="cell-label">Vehicle No.</td>
            <td class="cell-value">{{ $gatePass->vehicle_number ?: '—' }}</td>
        </tr>
    </table>

    {{-- ITEMS --}}
    <div class="section-heading">Description of Goods</div>
    @if(count($items) > 0)
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 8%;">#</th>
                    <th style="width: 52%;">Goods Description</th>
                    <th style="width: 25%;">Serial Number</th>
                    <th style="width: 15%; text-align: center;">Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="font-weight: 600;">{{ $item['description'] }}</td>
                    <td class="serial">{{ $item['serial'] ?: '—' }}</td>
                    <td class="qty">{{ $item['qty'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-box">No items listed on this gate pass.</div>
    @endif

    {{-- NOTES --}}
    @if($gatePass->notes)
        <div class="section-heading">Notes / Remarks</div>
        <div class="notes-box">
            {{ $gatePass->notes }}
        </div>
    @endif

    {{-- SIGNATURES --}}
    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-line-wrap">
                    <div class="sig-line">
                        Authorized By
                        <div class="sig-sub">{{ $gatePass->authorizedBy->name }}</div>
                    </div>
                </div>
            </td>
            <td>
                <div class="sig-line-wrap">
                    <div class="sig-line">
                        Security / Guard
                        <div class="sig-sub">Verification Sign</div>
                    </div>
                </div>
            </td>
            <td>
                <div class="sig-line-wrap">
                    <div class="sig-line">
                        Carrier / Receiver
                        <div class="sig-sub">Signature &amp; Date</div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- FOOTER --}}
    <div class="page-footer">
        Generated by {{ $settings['company_name'] ?? 'MEI TECHNICAL' }} Security Department &bull; Valid for Single Trip &bull; Printed: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
