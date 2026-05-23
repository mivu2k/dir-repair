<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gate Pass</title>
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

        /* ── Direction badge ── */
        .direction-badge {
            display: block;
            background: #000 !important;
            color: #fff !important;
            text-align: center;
            font-size: 10.5pt;
            font-weight: 900;
            letter-spacing: 2pt;
            text-transform: uppercase;
            padding: 1.5mm 0;
            margin: 2mm 0;
            border: 2pt solid #000;
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

        /* ── Items ── */
        .item-row {
            padding: 1.8mm 0;
            border-bottom: 1pt dashed #000;
            font-size: 9pt;
            font-weight: 900;
        }
        .item-row:last-child { border-bottom: none; }
        .item-qty {
            font-size: 9.5pt;
            font-weight: 900;
            margin-right: 1mm;
        }
        .item-serial {
            font-size: 8pt;
            font-weight: 900;
            margin-top: 0.8mm;
        }

        /* ── Signature line ── */
        .sig-block {
            text-align: center;
            margin-top: 6mm;
        }
        .sig-line {
            display: inline-block;
            border-top: 1.5pt solid #000;
            width: 58mm;
            margin-bottom: 1mm;
        }
        .sig-label {
            font-size: 8pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1pt;
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

        /* ── Footer ── */
        .footer {
            text-align: center;
            margin-top: 4.5mm;
            padding-top: 2.5mm;
            border-top: 1.5pt dashed #000;
        }
        .footer-note { font-size: 8pt; font-weight: 900; line-height: 1.4; }
        .copy { font-size: 7.5pt; font-weight: 900; margin-top: 2mm; text-transform: uppercase; }
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
        $qrData = url('/gate-passes/' . $gatePass->id);
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
        <span class="receipt-type">Gate Pass</span>
    </div>

    {{-- DIRECTION BADGE --}}
    <div class="direction-badge">
        {{ strtoupper($gatePass->type) }}
    </div>

    {{-- PASS INFO --}}
    <div class="kv-block">
        <div class="kv-row">
            <span class="kv-label">PASS #</span>
            <span class="kv-value" style="font-size:10pt; font-weight:900;">{{ $gatePass->pass_number }}</span>
        </div>
        <div class="kv-row">
            <span class="kv-label">DATE</span>
            <span class="kv-value">{{ $gatePass->created_at->format('d/m/y H:i') }}</span>
        </div>
        <div class="kv-row">
            <span class="kv-label">PERSON</span>
            <span class="kv-value">{{ $gatePass->person_name }}</span>
        </div>
        @if($gatePass->company_name)
        <div class="kv-row">
            <span class="kv-label">COMPANY</span>
            <span class="kv-value">{{ $gatePass->company_name }}</span>
        </div>
        @endif
        @if($gatePass->vehicle_number)
        <div class="kv-row">
            <span class="kv-label">VEHICLE</span>
            <span class="kv-value">{{ $gatePass->vehicle_number }}</span>
        </div>
        @endif
    </div>

    <div class="div-solid"></div>

    {{-- GOODS LIST --}}
    <div class="section-title">Goods List</div>
    @foreach(($gatePass->items ?: []) as $item)
    <div class="item-row">
        <span class="item-qty">{{ $item['qty'] }}x</span> {{ $item['description'] }}
        @if(!empty($item['serial']))
        <div class="item-serial">S/N: {{ $item['serial'] }}</div>
        @endif
    </div>
    @endforeach

    @if($gatePass->notes)
    <div class="div-dashed"></div>
    <div style="font-size:8.5pt; font-weight:900; line-height:1.4;">
        <span style="font-size:8pt; text-transform:uppercase; letter-spacing:1pt;">Note: </span>{{ $gatePass->notes }}
    </div>
    @endif

    {{-- QR CODE --}}
    <div class="div-dashed"></div>
    <div class="qr-block">
        <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR Code">
        <div class="qr-num">{{ $gatePass->pass_number }}</div>
    </div>

    {{-- SIGNATURE --}}
    <div class="sig-block">
        <div class="sig-line"></div><br>
        <span class="sig-label">Security / Guard Signature</span>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        <div class="footer-note">Valid for single trip only.</div>
        <div class="copy">{{ now()->format('Y') }} &copy; {{ $settings['company_name'] ?? 'Company' }}</div>
    </div>

</body>
</html>
