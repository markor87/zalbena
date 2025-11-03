<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Извештај: Тужбе примљене од Управног суда Србије</title>
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
    </style>
</head>
<body>
    <h1>Извештај: Тужбе примљене од Управног суда Србије</h1>
    <p style="text-align: center; margin-bottom: 20px;">Датум генерисања: {{ date('d.m.Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Име и презиме</th>
                <th>Институција</th>
                <th>Пријемни број</th>
                <th>Датум пријема жалбе</th>
                <th>Датум пријема тужбе од УС</th>
                <th>Тип пресуде</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td>{{ $item->ime_i_prezime ?? '-' }}</td>
                <td>{{ $item->institucija_podnosioca_zalbe ?? '-' }}</td>
                <td>{{ $item->prijemni_broj ?? '-' }}</td>
                <td>{{ $item->datum_prijema_zalbe ? date('d.m.Y', strtotime($item->datum_prijema_zalbe)) : '-' }}</td>
                <td>{{ $item->datum_prijema_tuzbe_od_us ? date('d.m.Y', strtotime($item->datum_prijema_tuzbe_od_us)) : '-' }}</td>
                <td>{{ $item->tip_presude ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 20px; text-align: center; font-size: 8px;">
        Укупно записа: {{ count($data) }}
    </p>
</body>
</html>
