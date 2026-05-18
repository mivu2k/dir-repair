<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Demo Issuance {{ $demoIssuance->issuance_number }}</title>
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
        
        .notes-section { margin-top: 50px; padding: 20px; background: #f8fafc; border-radius: 8px; }
        .notes-label { font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 1.5px; color: #71717a; margin-bottom: 8px; }
        .notes-content { color: #52525b; font-size: 10px; line-height: 1.6; }

        .signatures { width: 100%; margin-top: 60px; display: table; }
        .sig-col { display: table-cell; width: 50%; text-align: center; }
        .sig-box { border-top: 1px solid #18181b; width: 200px; margin: 0 auto; padding-top: 8px; font-weight: 700; font-size: 10px; }
        
        .footer { position: fixed; bottom: 40px; left: 40px; right: 40px; text-align: center; font-size: 9px; color: #a1a1aa; border-top: 1px solid #e4e4e7; padding-top: 20px; }
        .qr-section { margin-top: 20px; text-align: center; }
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
            <div class="doc-title">DEMO ISSUANCE</div>
            <div class="doc-number mono">{{ $demoIssuance->issuance_number }}</div>
            <div class="company-detail" style="margin-top: 15px;">
                Date: {{ $demoIssuance->issued_at->format('d M Y') }}<br>
                Due Date: {{ $demoIssuance->expected_return_date ? $demoIssuance->expected_return_date->format('d M Y') : 'N/A' }}
            </div>
        </div>
    </div>

    <div class="parties">
        <div class="party-col">
            <div class="party-label">Recipient</div>
            <div class="party-name">{{ $demoIssuance->customer->name }}</div>
            <div class="party-detail">
                {{ $demoIssuance->customer->organization }}<br>
                {{ $demoIssuance->customer->phone }}<br>
                {{ $demoIssuance->customer->email }}
            </div>
        </div>
        <div class="party-col text-right">
            <div class="party-label" style="margin-left: auto;">Status</div>
            <div class="party-name uppercase">{{ $demoIssuance->status }}</div>
            <div class="party-detail">
                Issued By: {{ $demoIssuance->issuedBy->name }}
            </div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 40%;">Description</th>
                <th style="width: 30%;">Serial Number</th>
                <th style="width: 30%;">Accessories</th>
            </tr>
        </thead>
        <tbody>
            @foreach($demoIssuance->items as $item)
            <tr>
                <td>{{ $item['name'] }}</td>
                <td class="mono">{{ $item['serial'] ?? '---' }}</td>
                <td>{{ $item['accessories'] ?? '---' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($demoIssuance->notes)
        <div class="notes-section">
            <div class="notes-label">Terms & Notes</div>
            <div class="notes-content">{!! nl2br(e($demoIssuance->notes)) !!}</div>
        </div>
    @endif

    <div class="signatures">
        <div class="sig-col">
            <div class="sig-box">Authorized Signature</div>
        </div>
        <div class="sig-col">
            <div class="sig-box">Customer Acknowledgment</div>
        </div>
    </div>

    <div class="qr-section">
        <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR" style="width: 80px; height: 80px;">
        <div style="font-size: 10px; font-weight: 700; margin-top: 5px; color: #52525b;">SCAN TO VERIFY: {{ $demoIssuance->issuance_number }}</div>
    </div>

    <div class="footer">
        {{ $settings['company_name'] ?? 'MEI OPS' }} &bull; Digital Asset Tracking &bull; {{ now()->format('Y') }}
    </div>
</body>
</html>
