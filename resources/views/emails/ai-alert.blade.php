<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
</head>
<body style="margin:0; padding:0; background:#f3f6fb; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f6fb; padding:28px 0;">
    <tr>
        <td align="center">
            <table width="640" cellpadding="0" cellspacing="0" style="width:640px; max-width:94%; background:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 12px 30px rgba(15,23,42,0.10);">
                <tr>
                    <td style="background:#111827; padding:26px 30px;">
                        <div style="display:inline-block; padding:7px 11px; border-radius:999px; background:{{ $badgeColor ?? '#dc2626' }}; color:#ffffff; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.4px;">
                            {{ $badge ?? 'AI Alert' }}
                        </div>
                        <h1 style="margin:16px 0 6px; color:#ffffff; font-size:24px; line-height:1.25;">{{ $title }}</h1>
                        <p style="margin:0; color:#cbd5e1; font-size:14px; line-height:1.6;">{{ $subtitle }}</p>
                    </td>
                </tr>

                @if(!empty($metrics))
                <tr>
                    <td style="padding:26px 30px 8px;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                @foreach($metrics as $metric)
                                    <td width="{{ floor(100 / count($metrics)) }}%" style="padding:0 8px 14px 0;">
                                        <div style="border:1px solid #e5e7eb; border-radius:10px; padding:16px; background:#f9fafb;">
                                            <div style="font-size:12px; color:#6b7280; font-weight:700; text-transform:uppercase;">{{ $metric['label'] }}</div>
                                            <div style="margin-top:8px; font-size:24px; color:#111827; font-weight:800;">{{ $metric['value'] }}</div>
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        </table>
                    </td>
                </tr>
                @endif

                @if(!empty($rows))
                <tr>
                    <td style="padding:12px 30px 28px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; font-size:14px;">
                            @foreach($rows as $row)
                                <tr>
                                    <td style="padding:12px 0; color:#6b7280; border-bottom:1px solid #eef2f7; width:180px; font-weight:700;">{{ $row['label'] }}</td>
                                    <td style="padding:12px 0; color:#111827; border-bottom:1px solid #eef2f7;">{{ $row['value'] }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>
                @endif

                <tr>
                    <td style="background:#f9fafb; padding:18px 30px; color:#6b7280; font-size:13px;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td>Thời gian: <strong style="color:#374151;">{{ now()->format('d/m/Y H:i:s') }}</strong></td>
                                <td align="right">AI Monitoring</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
