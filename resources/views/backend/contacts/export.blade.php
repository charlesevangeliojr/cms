<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Contact Messages</title>
    <style>
        body { font-family: Arial, sans-serif; color: #1f2937; }
        h1 { color: #111827; font-size: 20px; }
        .subtitle { color: #6b7280; font-size: 11px; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #1f2937; color: #fff; font-weight: bold; text-align: left; }
        th, td { border: 1px solid #d1d5db; padding: 8px; vertical-align: top; }
        td { white-space: normal; }
        .message { width: 360px; }
        .contact-number { mso-number-format: "\@"; }
        .date { white-space: nowrap; mso-number-format: "yyyy-mm-dd hh:mm:ss"; }
    </style>
</head>
<body>
<h1>Contact Messages</h1>
<p class="subtitle">Exported {{ now()->format('F j, Y g:i A') }} · {{ $messages->count() }} message(s)</p>
<table border="1">
    <thead>
        <tr><th>Name</th><th>Email</th><th>Contact Number</th><th>Subject</th><th class="message">Message</th><th>Status</th><th class="date">Received</th></tr>
    </thead>
    <tbody>
        @foreach ($messages as $message)
            <tr>
                <td>{{ $message->name }}</td>
                <td>{{ $message->email }}</td>
                <td class="contact-number">{{ ($message->contact_country ?? '').($message->contact ?? '') }}</td>
                <td>{{ $message->subject }}</td>
                <td class="message">{{ $message->message }}</td>
                <td>{{ $message->is_read ? 'Read' : 'Unread' }}</td>
                <td class="date">{{ $message->created_at?->format('Y-m-d H:i:s') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
