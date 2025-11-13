<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Извештај: Датум експедиције решених жалби</title>
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
    <h1>Извештај: Датум експедиције решених жалби</h1>
    <p style="text-align: center; margin-bottom: 20px;">Датум генерисања: {{ date('d.m.Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Име и презиме</th>
                <th>Институција</th>
                <th>Пријемни број</th>
                <th>Број решења</th>
                <th>Датум експедиције</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td>{{ $item->ime_i_prezime ?? '-' }}</td>
                <td>{{ $item->institucija_podnosioca_zalbe ?? '-' }}</td>
                <td>{{ $item->prijemni_broj ?? '-' }}</td>
                <td>{{ $item->broj_resenja ?? '-' }}</td>
                <td>{{ $item->datum_ekspedicije_ds_organu ? date('d.m.Y', strtotime($item->datum_ekspedicije_ds_organu)) : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 20px; text-align: center; font-size: 14px; font-weight: bold;">
        Укупно записа: {{ count($data) }}
    </p>
</body>
</html>
