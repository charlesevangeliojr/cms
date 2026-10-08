<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Newsletter Subscribers</title>
    <style>
        body { font-family: Arial, sans-serif; color: #1f2937; }
        h1 { color: #111827; font-size: 20px; }
        .subtitle { color: #6b7280; font-size: 11px; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #1f2937; color: #fff; font-weight: bold; text-align: left; }
        th, td { border: 1px solid #d1d5db; padding: 8px; vertical-align: top; }
        td { white-space: normal; }
        .email { mso-number-format: "\@"; }
        .date { white-space: nowrap; mso-number-format: "yyyy-mm-dd hh:mm:ss"; }
    </style>
</head>
<body>
<h1>Newsletter Subscribers</h1>
<p class="subtitle">Exported {{ now()->format('F j, Y g:i A') }} · {{ $subscribers->count() }} subscriber(s)</p>
<table border="1">
    <thead><tr><th>Email</th><th>Status</th><th>Subscribed</th></tr></thead>
    <tbody>
        @foreach ($subscribers as $subscriber)
            <tr>
                <td class="email">{{ $subscriber->email }}</td>
                <td>{{ $subscriber->is_active ? 'Active' : 'Inactive' }}</td>
                <td class="date">{{ $subscriber->created_at?->format('Y-m-d H:i:s') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
