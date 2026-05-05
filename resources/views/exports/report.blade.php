<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        .header { font-weight: bold; background-color: #f4f4f5; text-align: center; }
        .group-header { background-color: #18181b; color: #ffffff; font-weight: bold; }
        .table-header { background-color: #f8fafc; font-weight: bold; border-bottom: 2px solid #000000; }
        .cell { border: 0.5pt solid #e2e8f0; }
        .revenue { color: #059669; font-weight: bold; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="7" style="font-size: 16pt; font-weight: bold; text-align: center;">MEI Technical Audit Report</td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: center;">Period: {{ $startDate }} to {{ $endDate }}</td>
        </tr>
        <tr><td></td></tr>

        @foreach($data as $groupName => $jobs)
            <tr>
                <td colspan="7" class="group-header">{{ strtoupper($groupName ?: 'Unassigned') }} ({{ count($jobs) }} Units)</td>
            </tr>
            <tr class="table-header">
                <td class="cell">DATE</td>
                <td class="cell">JOB ID</td>
                <td class="cell">SERIAL #</td>
                <td class="cell">CLIENT ENTITY</td>
                <td class="cell">HARDWARE MATRIX</td>
                <td class="cell">SYMPTOMS</td>
                <td class="cell">STATUS</td>
            </tr>
            @foreach($jobs as $job)
                <tr>
                    <td class="cell">{{ $job->created_at->format('d/m/Y') }}</td>
                    <td class="cell bold">{{ $job->job_number }}</td>
                    <td class="cell">{{ $job->serial_number ?? '-' }}</td>
                    <td class="cell">{{ $job->customer->name }}</td>
                    <td class="cell">{{ $job->brand }} {{ $job->model }}</td>
                    <td class="cell">
                        @foreach($job->symptoms as $s)
                            {{ $s->name }}{{ !$loop->last ? ', ' : '' }}
                        @endforeach
                    </td>
                    <td class="cell">{{ strtoupper(str_replace('_', ' ', $job->status)) }}</td>
                </tr>
            @endforeach
            <tr><td></td></tr>
        @endforeach
    </table>
</body>
</html>
