<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Có liên hệ mới</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:30px 0;">
    <tr>
        <td align="center">
            <!-- Container -->
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 6px 20px rgba(0,0,0,0.08);">
                
                <!-- Header -->
                <tr>
                    <td style="background:#5422C6; padding:24px;">
                        <h2 style="margin:0; color:#ffffff; font-size:22px;">
                            Có liên hệ mới
                        </h2>
                        <p style="margin:6px 0 0; color:#eae6ff; font-size:14px;">
                            Một khách hàng vừa gửi thông tin liên hệ
                        </p>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:30px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="font-size:15px; color:#333;">
                            
                            <tr>
                                <td style="padding:8px 0; font-weight:600; width:160px;">
                                    Họ tên
                                </td>
                                <td style="padding:8px 0;">
                                    {{ $contact->name }}
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:8px 0; font-weight:600;">
                                    Email
                                </td>
                                <td style="padding:8px 0;">
                                    <a href="mailto:{{ $contact->email }}" style="color:#5422C6; text-decoration:none;">
                                        {{ $contact->email }}
                                    </a>
                                </td>
                            </tr>

                            @if(!empty($contact->phone))
                            <tr>
                                <td style="padding:8px 0; font-weight:600;">
                                    Số điện thoại
                                </td>
                                <td style="padding:8px 0;">
                                    {{ $contact->phone }}
                                </td>
                            </tr>
                            @endif

                            <tr>
                                <td style="padding:8px 0; font-weight:600;">
                                    Lĩnh vực quan tâm
                                </td>
                                <td style="padding:8px 0;">
                                    {{ optional($contact->projectIndustry)->name ?? 'Không xác định' }}
                                </td>
                            </tr>

                        </table>

                        <!-- Message -->
                        <div style="margin-top:24px;">
                            <p style="font-weight:600; margin-bottom:10px; color:#333;">
                                Nội dung
                            </p>
                            <div style="background:#f8f9fb; border:1px solid #e6e8ec; padding:16px; border-radius:6px; color:#555; line-height:1.6;">
                                {{ $contact->message }}
                            </div>
                        </div>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background:#fafafa; padding:18px 30px; font-size:13px; color:#777;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td>
                                    Thời gian gửi: <strong>{{ now()->format('d/m/Y H:i') }}</strong>
                                </td>
                                <td align="right" style="color:#999;">
                                    © {{ date('Y') }} hanoiinvestment
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

            </table>
            <!-- End Container -->
        </td>
    </tr>
</table>
</body>
</html>
