<!doctype html>
<html lang="th">
<head>
 <meta charset="utf-8">
 <style>
 body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
 h1 { font-size: 18px; margin-bottom: 4px; }
 p { margin-top: 0; color: #555; }
 table { border-collapse: collapse; width: 100%; }
 th, td { border: 1px solid #ccc; padding: 5px; text-align: right; }
 th { background: #e8f1e8; }
 th:first-child, td:first-child, th:nth-child(2), td:nth-child(2) { text-align: left; }
 </style>
</head>
<body>
 <h1>DRFIS Research Summary</h1>
 <p>Generated {{ $generatedAt->format('d/m/Y H:i') }}</p>
 <table>
 <thead><tr><th>Household</th><th>Status</th><th>Farms</th><th>Plots</th><th>Rai</th><th>Trees</th><th>Activities</th><th>Harvest kg</th><th>Net benefit</th></tr></thead>
 <tbody>
 @foreach ($rows as $row)
 <tr>@foreach ($row as $value)<td>{{ $value ?? '-' }}</td>@endforeach</tr>
 @endforeach
 </tbody>
 </table>
</body>
</html>
