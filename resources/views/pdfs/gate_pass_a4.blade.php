<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gate Pass {{ $gatePass->pass_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #18181b; background: #fff; padding: 40px; line-height: 1.5; }
        .mono { font-family: DejaVu Sans Mono, monospace; }
        .bold { font-weight: 700; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .uppercase { text-transform: uppercase; }
        
        .header { display: table; width: 100%; border-bottom: 2px solid #18181b; padding-bottom: 24px; margin-bottom: 30px; }
        .header-left { display: table-cell; vertical-align: top; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        
        .company-name { font-size: 26px; font-weight: 900; }
        .company-detail { font-size: 9px; color: #52525b; margin-top: 5px; }
        
        .doc-title { font-size: 32px; font-weight: 900; color: #18181b; }
        .doc-number { font-size: 16px; font-weight: 700; color: #52525b; margin-top: 5px; }

        .parties { display: table; width: 100%; margin-bottom: 40px; }
        .party-col { display: table-cell; width: 50%; vertical-align: top; }
        .party-label { font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 1.5px; color: #71717a; border-bottom: 1px solid #e4e4e7; padding-bottom: 4px; margin-bottom: 10px; width: 80%; }
        .party-name { font-size: 16px; font-weight: 700; margin-bottom: 5px; }
        .party-detail { color: #52525b; line-height: 1.6; }

        .table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .table th { background: #18181b; color: #fff; padding: 10px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .table td { padding: 12px 10px; border-bottom: 1px solid #e4e4e7; vertical-align: top; font-weight: 700; }

        .signatures { width: 100%; margin-top: 60px; display: table; }
        .sig-col { display: table-cell; width: 33.33%; text-align: center; }
        .sig-box { border-top: 1px solid #18181b; width: 150px; margin: 0 auto; padding-top: 8px; font-weight: 700; font-size: 10px; }
        
        .footer { position: fixed; bottom: 40px; left: 40px; right: 40px; text-align: center; font-size: 9px; color: #a1a1aa; border-top: 1px solid #e4e4e7; padding-top: 20px; }
        .qr-section { margin-top: 20px; text-align: center; }
        .type-badge { display: inline-block; background: #18181b; color: #fff; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: 900; letter-spacing: 1px; margin-top: 10px; }
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

    <div class="header">
        <div class="header-left">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo" style="max-height: 50px; margin-bottom: 10px;">
            @endif
            <div class="company-name">{{ $settings['company_name'] ?? 'MEI OPS' }}</div>
            <div class="company-detail">
                {!! nl2br(e($settings['company_address'] ?? '')) !!}<br>
                Phone: {{ $settings['company_phone'] ?? '' }}
            </div>
        </div>
        <div class="header-right">
            <div class="doc-title">GATE PASS</div>
            <div class="doc-number mono">{{ $gatePass->pass_number }}</div>
            <div class="type-badge uppercase">{{ $gatePass->type }}</div>
            <div class="company-detail" style="margin-top: 15px;">
                Date: {{ $gatePass->created_at->format('d M Y') }}<br>
                Time: {{ $gatePass->created_at->format('h:i A') }}
            </div>
        </div>
    </div>

    <div class="parties">
        <div class="party-col">
            <div class="party-label">Carrier Details</div>
            <div class="party-name">{{ $gatePass->person_name }}</div>
            <div class="party-detail">
                Company: {{ $gatePass->company_name ?: 'Individual' }}<br>
                Vehicle: {{ $gatePass->vehicle_number ?: 'N/A' }}
            </div>
        </div>
        <div class="party-col text-right">
            <div class="party-label" style="margin-left: auto;">Authorization</div>
            <div class="party-name uppercase">{{ $gatePass->status }}</div>
            <div class="party-detail">
                Auth By: {{ $gatePass->authorizedBy->name }}
            </div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 10%;">#</th>
                <th style="width: 70%;">Goods Description</th>
                <th style="width: 20%;" class="text-center">Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($gatePass->items ?: []) as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    {{ $item['description'] }}
                    @if(!empty($item['serial']))
                        <div style="font-size: 8px; color: #71717a; margin-top: 2px;">S/N: <span class="bold uppercase" style="color: #18181b;">{{ $item['serial'] }}</span></div>
                    @endif
                </td>
                <td class="text-center">{{ $item['qty'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($gatePass->notes)
        <div style="margin-top: 20px; font-size: 10px; color: #52525b;">
            <span class="bold uppercase">Notes:</span> {{ $gatePass->notes }}
        </div>
    @endif

    <div class="signatures">
        <div class="sig-col">
            <div class="sig-box">Authorized By</div>
        </div>
        <div class="sig-col">
            <div class="sig-box">Security Check</div>
        </div>
        <div class="sig-col">
            <div class="sig-box">Carrier Signature</div>
        </div>
    </div>

    <div class="qr-section">
        <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR" style="width: 80px; height: 80px;">
        <div style="font-size: 10px; font-weight: 700; margin-top: 5px; color: #52525b;">GATE VERIFICATION: {{ $gatePass->pass_number }}</div>
    </div>

    <div class="footer">
        {{ $settings['company_name'] ?? 'MEI OPS' }} &bull; Security Department &bull; {{ now()->format('Y') }}
    </div>
</body>
</html>
