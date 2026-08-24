<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Годишњи извештај</title>
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
    </style>
</head>
<body>
    <h1>Годишњи извештај</h1>
    <p style="text-align: center; margin-bottom: 20px;">Датум генерисања: {{ now()->format('d.m.Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Институција</th>
                <th>Основ жалбе</th>
                <th>Тип решења</th>
                <th>Датум пријема жалбе</th>
                <th>Датум решавања на ЖК</th>
                <th>Статус жалбе</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td>{{ $item->institucija_podnosioca_zalbe ?? '-' }}</td>
                <td>{{ $item->osnov_zalbe ?? '-' }}</td>
                <td>{{ $item->tip_resenja ?? '-' }}</td>
                <td>{{ $item->datum_prijema_zalbe ? date('d.m.Y', strtotime($item->datum_prijema_zalbe)) : '-' }}</td>
                <td>{{ $item->datum_resavanja_na_zk ? date('d.m.Y', strtotime($item->datum_resavanja_na_zk)) : '-' }}</td>
                <td>{{ $item->status_zalbe ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 20px; text-align: center; font-size: 14px; font-weight: bold;">
        Укупно записа: {{ count($data) }}
    </p>
</body>
</html>
