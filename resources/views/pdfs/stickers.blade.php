<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        @page { 
            margin: 0; 
            size: 141pt 85pt;
        }
        html, body {
            margin: 0;
            padding: 0;
            width: 141pt;
            height: 85pt;
            font-family: 'DejaVu Sans', sans-serif;
            background: white;
            line-height: 1;
        }
        .sticker-wrapper {
            width: 141pt;
            height: 85pt;
            page-break-after: always;
            overflow: hidden;
            position: relative;
        }
        .sticker-wrapper:last-child {
            page-break-after: avoid;
        }
        .container {
            padding: 4pt;
        }
        .qr-col {
            float: left;
            width: 45pt;
            text-align: center;
        }
        .qr-col img {
            width: 38pt;
            height: 38pt;
        }
        .qr-id {
            font-size: 5pt;
            margin-top: 1pt;
            font-weight: bold;
        }
        .info-col {
            float: left;
            width: 88pt;
            padding-left: 2pt;
        }
        .header {
            font-size: 10pt;
            font-weight: bold;
            border-bottom: 0.5pt solid #000;
            margin-bottom: 2pt;
        }
        .line {
            font-size: 7pt;
            margin-bottom: 1pt;
            white-space: nowrap;
            overflow: hidden;
        }
        .line strong {
            font-size: 5.5pt;
            color: #555;
            text-transform: uppercase;
        }
        .customer {
            margin-top: 2pt;
            font-size: 7.5pt;
            font-weight: bold;
            background: #000;
            color: #fff;
            padding: 1pt 2pt;
            text-transform: uppercase;
        }
        .footer {
            position: absolute;
            bottom: 2pt;
            right: 4pt;
            font-size: 5.5pt;
            color: #999;
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
