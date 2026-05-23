<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Demo Goods Pass</title>
    <style>
        @page {
            margin: 0;
        }
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
        .header { text-align: center; padding-bottom: 2mm; }
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

        /* ── Key-Value rows ── */
        .kv-block { margin: 1.5mm 0; }
        .kv-row { display: table; width: 100%; margin-bottom: 1.2mm; }
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

        /* ── Item rows ── */
        .item-row {
            padding: 1.8mm 0;
            border-bottom: 1pt dashed #000;
        }
        .item-row:last-child { border-bottom: none; }
        .item-name { font-size: 9.5pt; font-weight: 900; }
        .item-meta {
            font-size: 8pt;
            font-weight: 900;
            margin-top: 0.8mm;
            line-height: 1.4;
        }

        /* ── Due date highlight ── */
        .due-box {
            border: 2pt solid #000;
            padding: 2mm;
            text-align: center;
            margin: 2.5mm 0;
        }
        .due-label {
            font-size: 8pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1.5pt;
            margin-bottom: 0.5mm;
        }
        .due-date {
            font-size: 13pt;
            font-weight: 900;
            letter-spacing: 1pt;
        }

        /* ── Status — Returned ── */
        .returned-badge {
            display: block;
            background: #000 !important;
            color: #fff !important;
            text-align: center;
            font-size: 9.5pt;
            font-weight: 900;
            letter-spacing: 2pt;
            padding: 1.8mm 0;
            margin: 2mm 0;
            border: 2pt solid #000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── QR ── */
        .qr-block { text-align: center; margin: 3.5mm 0 1.5mm; }
        .qr-block img { width: 75pt; height: 75pt; display: block; margin: 0 auto; }
        .qr-num {
            font-size: 10pt;
            font-weight: 900;
            letter-spacing: 2.5pt;
            margin-top: 1.5mm;
        }

        /* ── Signature ── */
        .sig-block { text-align: center; margin-top: 6mm; }
        .sig-line {
            display: inline-block;
            border-top: 1.5pt solid #000;
            width: 58mm;
            margin-bottom: 1mm;
        }
        .sig-label { font-size: 8pt; font-weight: 900; text-transform: uppercase; letter-spacing: 1pt; }

        /* ── Footer ── */
        .footer {
            text-align: center;
            margin-top: 4.5mm;
            padding-top: 2.5mm;
            border-top: 1.5pt dashed #000;
        }
        .footer-note { font-size: 8pt; font-weight: 900; line-height: 1.4; margin-bottom: 1.5mm; }
        .copy { font-size: 7.5pt; font-weight: 900; text-transform: uppercase; }
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
        $qrData = url('/demo-issuances/' . $demoIssuance->id);
        $qrCode = base64_encode(SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(150)->margin(0)->errorCorrection('H')->generate($qrData));
    @endphp

    {{-- HEADER --}}
    <div class="header">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="Logo" class="logo-img">
        @else
            <div class="company-name">{{ $settings['company_name'] ?? 'Company' }}</div>
        @endif
        <div class="company-sub">
            {{ $settings['company_address'] ?? '' }}<br>
            {{ $settings['company_phone'] ?? '' }}
        </div>
        <span class="receipt-type">Demo Goods Pass</span>
    </div>

    {{-- STATUS BADGE (if returned) --}}
    @if($demoIssuance->status === 'returned')
    <div class="returned-badge">&#10003; RETURNED</div>
    @endif

    {{-- ISSUANCE INFO --}}
    <div class="kv-block">
        <div class="kv-row">
            <span class="kv-label">VOUCHER</span>
            <span class="kv-value" style="font-size:10pt; font-weight:900;">{{ $demoIssuance->issuance_number }}</span>
        </div>
        <div class="kv-row">
            <span class="kv-label">DATE</span>
            <span class="kv-value">{{ $demoIssuance->issued_at->format('d/m/y H:i') }}</span>
        </div>
        <div class="kv-row">
            <span class="kv-label">CLIENT</span>
            <span class="kv-value">{{ $demoIssuance->customer->name }}</span>
        </div>
        <div class="kv-row">
            <span class="kv-label">PHONE</span>
            <span class="kv-value">{{ $demoIssuance->customer->phone }}</span>
        </div>
        @if($demoIssuance->department)
        <div class="kv-row">
            <span class="kv-label">DEPT</span>
            <span class="kv-value">{{ $demoIssuance->department }}</span>
        </div>
        @endif
        @if($demoIssuance->reference_letter)
        <div class="kv-row">
            <span class="kv-label">REF</span>
            <span class="kv-value">{{ $demoIssuance->reference_letter }}</span>
        </div>
        @endif
    </div>

    <div class="div-solid"></div>

    {{-- ITEMS --}}
    <div class="section-title">Issued Items ({{ count($demoIssuance->items) }})</div>
    @foreach($demoIssuance->items as $item)
    <div class="item-row">
        <div class="item-name">{{ $item['name'] }}</div>
        <div class="item-meta">
            SN: {{ $item['serial'] ?? '—' }}
            @if(!empty($item['accessories']))
                &nbsp;|&nbsp; ACC: {{ $item['accessories'] }}
            @endif
        </div>
    </div>
    @endforeach

    {{-- DUE DATE --}}
    @if($demoIssuance->expected_return_date)
    <div class="div-dashed"></div>
    <div class="due-box">
        <div class="due-label">Return By</div>
        <div class="due-date">{{ $demoIssuance->expected_return_date->format('d / m / Y') }}</div>
    </div>
    @endif

    {{-- QR --}}
    <div class="div-dashed"></div>
    <div class="qr-block">
        <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR Code">
        <div class="qr-num">{{ $demoIssuance->issuance_number }}</div>
    </div>

    {{-- SIGNATURE --}}
    <div class="sig-block">
        <div class="sig-line"></div><br>
        <span class="sig-label">Recipient Signature</span>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        <div class="footer-note">
            * All items must be returned in original condition.<br>
            * Please return by the due date shown above.<br>
            * Keep this receipt for reference.
        </div>
        <div class="copy">{{ now()->format('Y') }} &copy; {{ $settings['company_name'] ?? 'Company' }}</div>
    </div>

</body>
</html>
