<table>
    <thead>
        <tr>
            <th colspan="7" style="font-size: 16pt; font-weight: bold; text-align: center; border: 2px solid #000;">
                {{ $settings['company_name'] ?? 'MEI' }} Technical Audit Report
            </th>
        </tr>
        <tr>
            <th colspan="7" style="text-align: center; font-size: 10pt;">
                Period: {{ $period }}
            </th>
        </tr>
        <tr><th colspan="7"></th></tr>
    </thead>
    <tbody>
        @foreach($grouped as $groupName => $items)
            @if($groupBy !== 'flat')
            <tr style="background-color: #000000; color: #ffffff;">
                <th colspan="7" style="text-align: left; font-weight: bold; padding: 5px;">
                    {{ strtoupper(str_replace('_', ' ', $groupName)) }} ({{ count($items) }} Units)
                </th>
            </tr>
            @endif
            <tr style="background-color: #f3f4f6; font-weight: bold; text-align: left;">
                <th style="border: 1px solid #e5e7eb;">DATE</th>
                <th style="border: 1px solid #e5e7eb;">JOB ID</th>
                <th style="border: 1px solid #e5e7eb;">SERIAL #</th>
                <th style="border: 1px solid #e5e7eb;">CLIENT ENTITY</th>
                <th style="border: 1px solid #e5e7eb;">HARDWARE MATRIX</th>
                <th style="border: 1px solid #e5e7eb;">SYMPTOMS</th>
                <th style="border: 1px solid #e5e7eb;">STATUS</th>
            </tr>
            @foreach($items as $item)
                <tr>
                    <td style="border: 1px solid #e5e7eb;">{{ \Carbon\Carbon::parse($item['date'])->format('d/m/y H:i') }}</td>
                    <td style="border: 1px solid #e5e7eb; font-weight: bold;">{{ $item['id'] }}</td>
                    <td style="border: 1px solid #e5e7eb;">{{ $item['serial'] }}</td>
                    <td style="border: 1px solid #e5e7eb;">{{ $item['client'] }}</td>
                    <td style="border: 1px solid #e5e7eb; font-weight: bold;">{{ $item['item_name'] }}</td>
                    <td style="border: 1px solid #e5e7eb;">{{ $item['symptoms'] }}</td>
                    <td style="border: 1px solid #e5e7eb;">{{ $item['status'] }}</td>
                </tr>
            @endforeach
            <tr><th colspan="7"></th></tr>
        @endforeach
    </tbody>
</table>
