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
        .info-row { margin-bottom: 2pt; font-size: 10pt; font-weight: bold; }
        .item-row { font-size: 10pt; font-weight: bold; margin-bottom: 4pt; border-bottom: 0.5pt solid #eee; padding-bottom: 2pt; }
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
        $qrData = url('/gate-passes/' . $gatePass->id);
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
        <div class="type-label bold uppercase">Gate Pass</div>
        <div class="bold" style="font-size: 10pt; border: 1pt solid #000; display: inline-block; padding: 2pt 10pt; margin: 4pt 0;">
            {{ strtoupper($gatePass->type) }}
        </div>
    </div>

    <div class="divider"></div>
    
    <div class="info-row">PASS NO: {{ $gatePass->pass_number }}</div>
    <div class="info-row">DATE: {{ $gatePass->created_at->format('d/m/y H:i') }}</div>
    <div class="info-row">PERSON: {{ $gatePass->person_name }}</div>
    @if($gatePass->vehicle_number)<div class="info-row">VEHICLE: {{ $gatePass->vehicle_number }}</div>@endif

    <div class="divider"></div>
    <div class="bold uppercase" style="font-size: 9pt; margin-bottom: 4pt;">GOODS LIST:</div>
    
    @foreach(($gatePass->items ?: []) as $item)
    <div class="item-row">
        {{ $item['qty'] }}x {{ $item['description'] }}
        @if(!empty($item['serial']))
            <br><span style="font-size: 8pt; color: #555;">S/N: {{ $item['serial'] }}</span>
        @endif
    </div>
    @endforeach

    <div class="divider-dashed"></div>
    
    <div class="center" style="margin-top: 10pt;">
        <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR" style="width: 100pt; height: 100pt;">
        <div class="bold" style="font-size: 14pt; letter-spacing: 3pt; margin-top: 4pt;">
            {{ $gatePass->pass_number }}
        </div>
    </div>

    <div style="margin-top: 20pt;" class="center">
        __________________________<br>
        <span class="bold" style="font-size: 8pt;">SECURITY SIGNATURE</span>
    </div>

    <div class="divider-dashed" style="margin-top: 15pt;"></div>
    <div class="center bold" style="font-size: 8pt;">
        VALID FOR SINGLE TRIP ONLY
    </div>
</body>
</html>
