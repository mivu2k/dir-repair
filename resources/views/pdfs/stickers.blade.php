<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        @page { 
            margin: 0; 
            size: 216pt 144pt;
        }
        html, body {
            margin: 0;
            padding: 0;
            width: 216pt;
            height: 144pt;
            font-family: 'DejaVu Sans', sans-serif;
            background: white;
            line-height: 1;
        }
        .sticker-wrapper {
            width: 216pt;
            height: 144pt;
            page-break-after: always;
            overflow: hidden;
            position: relative;
        }
        .sticker-wrapper:last-child {
            page-break-after: avoid;
        }
        .container {
            padding: 6pt;
        }
        .qr-col {
            float: left;
            width: 70pt;
            text-align: center;
        }
        .qr-col img {
            width: 60pt;
            height: 60pt;
        }
        .qr-id {
            font-size: 8pt;
            margin-top: 2pt;
            font-weight: bold;
        }
        .info-col {
            float: left;
            width: 130pt;
            padding-left: 4pt;
        }
        .header {
            font-size: 14pt;
            font-weight: bold;
            border-bottom: 1pt solid #000;
            margin-bottom: 4pt;
            padding-bottom: 2pt;
        }
        .line {
            font-size: 10pt;
            margin-bottom: 2pt;
            white-space: nowrap;
            overflow: hidden;
        }
        .line strong {
            font-size: 8pt;
            color: #333;
            text-transform: uppercase;
        }
        .customer {
            margin-top: 4pt;
            font-size: 11pt;
            font-weight: bold;
            background: #000;
            color: #fff;
            padding: 2pt 4pt;
            text-transform: uppercase;
        }
        .footer {
            position: absolute;
            bottom: 4pt;
            right: 6pt;
            font-size: 8pt;
            color: #666;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    @foreach($jobs as $job)
        <div class="sticker-wrapper">
            <div class="container clearfix">
                <div class="qr-col">
                    @php
                        $qrData = url('/jobs/' . $job->job_number);
                        $qrCode = base64_encode(QrCode::format('svg')->size(100)->margin(0)->generate($qrData));
                    @endphp
                    <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR">
                    <div class="qr-id">{{ $job->job_number }}</div>
                </div>
                
                <div class="info-col">
                    <div class="header">{{ $job->job_number }}</div>
                    <div class="line"><strong>Device:</strong> {{ $job->brand }} {{ $job->device_name }}</div>
                    <div class="line"><strong>Model:</strong> {{ $job->model ?? 'N/A' }}</div>
                    <div class="line"><strong>S/N:</strong> {{ $job->serial_number ?? '---' }}</div>
                    <div class="customer">{{ $job->customer->name }}</div>
                </div>
            </div>
            <div class="footer">{{ $job->created_at->format('d.m.y') }} | MEI OPS</div>
        </div>
    @endforeach
</body>
</html>
