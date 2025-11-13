<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Извештај: Нерешене жалбе</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }
        h1 {
            text-align: center;
            font-size: 16px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
            font-size: 12px;
        }
        .ukupno-row {
            font-weight: bold;
            background-color: #f9fafb;
            font-size: 14px;
        }
        .status-neresen {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
        }
        .status-upucen {
            background-color: #ffedd5;
            color: #c2410c;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <h1>Извештај: Нерешене жалбе</h1>
    <p style="text-align: center; margin-bottom: 20px;">Датум генерисања: {{ date('d.m.Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Институција</th>
                <th>Име</th>
                <th>Презиме</th>
                <th>Пријемни број</th>
                <th>Број решења</th>
                <th>Датум пријема</th>
                <th>Датум решавања</th>
                <th>Датум истицања</th>
                <th>Основ жалбе</th>
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
                    <td>
                        @if($index === 0)
                            {{ $institucija ?? '-' }}
                        @endif
                    </td>
                    <td>{{ $item->ime_podnosioca_zalbe ?? '-' }}</td>
                    <td>{{ $item->prezime_podnosioca_zalbe ?? '-' }}</td>
                    <td>{{ $item->prijemni_broj }}</td>
                    <td>{{ $item->broj_resenja ?? '-' }}</td>
                    <td>{{ $item->datum_prijema_zalbe ? date('d.m.Y', strtotime($item->datum_prijema_zalbe)) : '-' }}</td>
                    <td>{{ $item->datum_resavanja_na_zk ? date('d.m.Y', strtotime($item->datum_resavanja_na_zk)) : '-' }}</td>
                    <td>{{ $item->datum_isticanja_donosenje ? date('d.m.Y', strtotime($item->datum_isticanja_donosenje)) : '-' }}</td>
                    <td>{{ $item->osnov_zalbe ?? '-' }}</td>
                </tr>
                @endforeach

                {{-- Total row for this institution --}}
                <tr class="ukupno-row">
                    <td>Укупно: {{ count($items) }}</td>
                    <td colspan="8"></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 20px; text-align: center; font-size: 14px; font-weight: bold;">
        Укупно записа: {{ count($data) }}
    </p>
</body>
</html>
