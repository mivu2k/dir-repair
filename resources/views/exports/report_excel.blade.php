<table>
    <thead>
        <tr>
            <th colspan="4" style="font-size: 16pt; font-weight: bold; text-align: center; border: 2px solid #000;">
                {{ $settings['company_name'] ?? 'MEI' }} Operational Report
            </th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: center; font-size: 10pt;">
                Period: {{ $period }} | Generated: {{ now()->format('d/m/Y H:i') }}
            </th>
        </tr>
        <tr><th colspan="4"></th></tr>
    </thead>
    <tbody>
        @foreach($data as $groupName => $items)
            <tr style="background-color: #000000; color: #ffffff;">
                <th colspan="4" style="text-align: left; font-weight: bold; padding: 5px;">
                    {{ strtoupper(str_replace('_', ' ', $groupName)) }} ({{ $items->count() }} Records)
                </th>
            </tr>
            <tr style="background-color: #f3f4f6; font-weight: bold; text-align: left;">
                <th style="border: 1px solid #e5e7eb;">JOB ID</th>
                <th style="border: 1px solid #e5e7eb;">HARDWARE / CLIENT</th>
                <th style="border: 1px solid #e5e7eb;">STATUS</th>
                <th style="border: 1px solid #e5e7eb;">SYMPTOMS</th>
                <th style="border: 1px solid #e5e7eb; text-align: right;">QUOTE VALUE</th>
            </tr>
            @foreach($items as $job)
                <tr>
                    <td style="border: 1px solid #e5e7eb; font-weight: bold;">{{ $job->job_number }}</td>
                    <td style="border: 1px solid #e5e7eb;">
                        {{ $job->brand }} {{ $job->device_name }} / {{ $job->customer->name }}
                    </td>
                    <td style="border: 1px solid #e5e7eb;">{{ strtoupper(str_replace('_', ' ', $job->status)) }}</td>
                    <td style="border: 1px solid #e5e7eb;">{{ $job->symptoms->pluck('name')->join(', ') }}</td>
                    <td style="border: 1px solid #e5e7eb; text-align: right;">
                        {{ number_format($job->approvedQuotation->total_amount ?? 0, 2) }}
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="3" style="text-align: right; font-weight: bold; border: 1px solid #e5e7eb;">Group Total:</td>
                <td style="text-align: right; font-weight: bold; border: 1px solid #e5e7eb; background-color: #f9fafb;">
                    {{ number_format($items->sum(fn($j) => $j->approvedQuotation->total_amount ?? 0), 2) }}
                </td>
            </tr>
            <tr><th colspan="4"></th></tr>
        @endforeach
    </tbody>
</table>
