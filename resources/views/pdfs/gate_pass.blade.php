<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gate Pass - {{ $gatePass->pass_number }}</title>
    <style>
        @page { margin: 20pt; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 11pt; 
            line-height: 1.4; 
            color: #1e293b; /* slate-800 */
        }
        .header { text-align: center; border-bottom: 2pt solid #1e293b; padding-bottom: 10pt; margin-bottom: 20pt; }
        .company-name { font-size: 18pt; font-weight: bold; text-transform: uppercase; letter-spacing: 2pt; margin-bottom: 5pt; }
        .doc-title { font-size: 14pt; font-weight: bold; background: #f1f5f9; display: inline-block; padding: 4pt 12pt; border-radius: 4pt; }
        .doc-title.inward { background: #dcfce7; color: #166534; }
        .doc-title.outward { background: #ffedd5; color: #9a3412; }
        
        .info-grid { width: 100%; border-collapse: collapse; margin-bottom: 20pt; }
        .info-grid td { padding: 6pt; border: 1pt solid #cbd5e1; }
        .info-label { font-weight: bold; font-size: 9pt; color: #64748b; text-transform: uppercase; background: #f8fafc; width: 30%; }
        
        .description-box { border: 1pt solid #cbd5e1; padding: 10pt; min-height: 100pt; margin-bottom: 30pt; }
        .description-title { font-weight: bold; font-size: 10pt; text-transform: uppercase; margin-bottom: 8pt; color: #475569; }
        
        .signatures { width: 100%; margin-top: 50pt; }
        .signatures td { text-align: center; width: 33%; vertical-align: bottom; }
        .sig-line { border-top: 1pt solid #1e293b; margin: 0 10pt; padding-top: 4pt; font-size: 9pt; font-weight: bold; }
        
        .footer { position: absolute; bottom: 0; width: 100%; text-align: center; font-size: 8pt; color: #94a3b8; border-top: 1pt solid #e2e8f0; padding-top: 5pt; }
    </style>
</head>
<body>

    <div class="header">
        <div class="company-name">{{ $settings['company_name'] ?? 'MEI OPS' }}</div>
        <div>{{ $settings['company_address'] ?? 'HQ' }} | {{ $settings['company_phone'] ?? '' }}</div>
        <div style="margin-top: 10pt;">
            <span class="doc-title {{ $gatePass->type }}">GATE PASS ({{ strtoupper($gatePass->type) }})</span>
        </div>
    </div>

    <table class="info-grid">
        <tr>
            <td class="info-label">Pass Number</td>
            <td style="font-weight: bold; font-size: 12pt;">{{ $gatePass->pass_number }}</td>
            <td class="info-label">Date & Time</td>
            <td>{{ $gatePass->created_at->format('d/m/Y h:i A') }}</td>
        </tr>
        <tr>
            <td class="info-label">Carrier / Person</td>
            <td colspan="3" style="font-weight: bold;">{{ $gatePass->person_name }}</td>
        </tr>
        <tr>
            <td class="info-label">Company / Vendor</td>
            <td>{{ $gatePass->company_name ?: 'N/A' }}</td>
            <td class="info-label">Vehicle No.</td>
            <td>{{ $gatePass->vehicle_number ?: 'N/A' }}</td>
        </tr>
    </table>

    <div class="description-box">
        <div class="description-title">Items Description / Details of Goods:</div>
        <div style="white-space: pre-wrap;">{{ $gatePass->items_description }}</div>
    </div>

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-line">Authorized By<br><span style="font-weight: normal; font-size: 8pt;">{{ $gatePass->authorizedBy->name }}</span></div>
            </td>
            <td>
                <div class="sig-line">Security Check</div>
            </td>
            <td>
                <div class="sig-line">Carrier Signature</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Generated on {{ now()->format('d/m/Y h:i A') }} | Document ID: {{ $gatePass->pass_number }}
    </div>

</body>
</html>
