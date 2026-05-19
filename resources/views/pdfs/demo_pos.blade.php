<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }
        body { 
            font-family: 'Helvetica', sans-serif; 
            font-size: 11pt; 
            line-height: 1.2; 
            width: 76mm; 
            padding: 2mm;
            margin: 0;
            color: #000;
        }
        .center { text-align: center; }
        .bold { font-weight: 900; }
        .divider { border-bottom: 1.5pt solid #000; margin: 4pt 0; }
        .divider-dashed { border-bottom: 1pt dashed #000; margin: 4pt 0; }
        .company-name { font-size: 18pt; font-weight: 900; margin: 2pt 0; text-transform: uppercase; }
        .type-label { background: #000; color: #fff; padding: 2pt 0; font-size: 12pt; margin: 4pt 0; letter-spacing: 2pt; }
        .item-row { margin-bottom: 6pt; border-bottom: 0.5pt solid #eee; padding-bottom: 2pt; }
        .logo { margin-bottom: 4pt; }
    </style>
</head>
<body>
    @php
        $logoBase64 = null;
        if (!empty($settings['company_logo'])) {
            $path = storage_path('app/public/' . $settings['company_logo']);
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }
        $qrData = url('/demo-issuances/' . $demoIssuance->id);
        $qrCode = base64_encode(SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(150)->margin(0)->errorCorrection('H')->generate($qrData));
    @endphp

    <div class="header center">
        <div class="logo">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo" style="max-width: 150pt; height: auto;">
            @else
                <div class="company-name">{{ $settings['company_name'] ?? 'MEI' }}</div>
            @endif
        </div>
        <div style="font-size: 9pt; font-weight: bold;">{{ $settings['company_address'] ?? '' }}</div>
        <div class="type-label bold uppercase">Demo Goods Pass</div>
    </div>

    <div class="divider"></div>
    
    <div style="font-size: 10pt; font-weight: bold;">
        VOUCHER: {{ $demoIssuance->issuance_number }}<br>
        DATE: {{ $demoIssuance->issued_at->format('d/m/y H:i') }}<br>
        CLIENT: {{ $demoIssuance->customer->name }}
        @if($demoIssuance->department)
            <br>DEPT: {{ $demoIssuance->department }}
        @endif
        @if($demoIssuance->reference_letter)
            <br>REF: {{ $demoIssuance->reference_letter }}
        @endif
    </div>

    <div class="divider"></div>
    <div class="bold" style="font-size: 9pt; margin-bottom: 4pt;">ISSUED ITEMS:</div>
    
    @foreach($demoIssuance->items as $item)
    <div class="item-row">
        <div class="bold">{{ $item['name'] }}</div>
        <div style="font-size: 9pt;">
            SN: {{ $item['serial'] ?? 'N/A' }} | ACC: {{ $item['accessories'] ?? 'N/A' }}
        </div>
    </div>
    @endforeach

    <div class="divider"></div>
    
    @if($demoIssuance->expected_return_date)
    <div class="center bold" style="font-size: 12pt; border: 1pt solid #000; padding: 4pt;">
        DUE: {{ $demoIssuance->expected_return_date->format('d/m/Y') }}
    </div>
    @endif

    <div class="center" style="margin-top: 15pt;">
        <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR" style="width: 100pt; height: 100pt;">
        <div class="bold" style="font-size: 14pt; letter-spacing: 3pt; margin-top: 4pt;">
            {{ $demoIssuance->issuance_number }}
        </div>
    </div>

    <div class="divider-dashed"></div>
    <div class="center bold" style="font-size: 8pt; margin-top: 10pt;">
        PLEASE RETURN ALL ITEMS BY DUE DATE<br>
        THANK YOU
    </div>
</body>
</html>
