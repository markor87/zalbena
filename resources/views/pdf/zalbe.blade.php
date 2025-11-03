<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Извештај: Жалбе</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
        }
        h1 {
            text-align: center;
            font-size: 14px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9fafb;
        }
    </style>
</head>
<body>
    <h1>Извештај: Жалбе</h1>
    <p style="text-align: center; margin-bottom: 20px;">Датум генерисања: {{ date('d.m.Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Датум пријема жалбе</th>
                <th>Институција</th>
                <th>Основ жалбе</th>
                <th>Типови решења</th>
                <th>Статус жалбе</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Group data by institution
                $grouped = collect($data)->groupBy('institucija_podnosioca_zalbe');
            @endphp

            @foreach($grouped as $institucija => $items)
                @foreach($items as $index => $item)
                <tr>
                    <td>{{ $item->datum_prijema_zalbe ? date('d.m.Y', strtotime($item->datum_prijema_zalbe)) : '-' }}</td>
                    <td>
                        @if($index === 0)
                            {{ $institucija ?? '-' }}
                        @endif
                    </td>
                    <td>{{ $item->osnov_zalbe ?? '-' }}</td>
                    <td>{{ $item->tip_resenja ?? '-' }}</td>
                    <td>{{ $item->status_zalbe ?? '-' }}</td>
                </tr>
                @endforeach

                {{-- Total row for this institution --}}
                <tr class="total-row">
                    <td colspan="5">Укупно: {{ count($items) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 20px; text-align: center; font-size: 8px;">
        Укупно записа: {{ count($data) }}
    </p>
</body>
</html>
