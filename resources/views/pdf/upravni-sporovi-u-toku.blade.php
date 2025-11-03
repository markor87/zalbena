<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Извештај: Управни спорови у току</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
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
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .total-row {
            background-color: #e5e7eb;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Извештај: Управни спорови у току</h1>
    <p style="text-align: center; margin-bottom: 20px;">Датум генерисања: {{ date('d.m.Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Институција</th>
                <th>Број предмета</th>
                <th>Јасмина Михаиловић</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr @if($item->institucija === 'Укупно') class="total-row" @endif>
                <td>{{ $item->institucija ?? '-' }}</td>
                <td>{{ $item->broj_zalbi ?? '0' }}</td>
                <td>{{ $item->broj_id54 ?? '0' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
