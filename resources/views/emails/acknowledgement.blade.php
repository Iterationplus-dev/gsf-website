{{--
    Branded transactional email.

    Inline styles and a table layout, because email clients strip stylesheets and
    Outlook does not lay out flex or grid. Colours mirror the website's tokens.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $heading }}</title>
</head>
<body style="margin:0;padding:0;background:#faf8f4;font-family:ui-sans-serif,system-ui,-apple-system,'Segoe UI',sans-serif;color:#1c1b18">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#faf8f4;padding:32px 16px">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border:1px solid #e4e0d7;border-radius:12px">
                    <tr>
                        <td style="padding:28px 32px;background:#0f5539;border-radius:12px 12px 0 0">
                            <p style="margin:0;font-size:18px;font-weight:600;color:#ffffff">Global Support Foundation</p>
                            <p style="margin:4px 0 0;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#a8dbc2">Grassroot Entrepreneurship</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px">
                            <h1 style="margin:0 0 16px;font-family:Georgia,'Times New Roman',serif;font-size:22px;font-weight:600;color:#1c1b18">{{ $heading }}</h1>
                            <p style="margin:0;font-size:15px;line-height:1.65;color:#57534a">{{ $messageText }}</p>

                            @if ($actionUrl)
                                <table role="presentation" cellpadding="0" cellspacing="0" style="margin:28px 0 0">
                                    <tr>
                                        <td style="background:#c0221b;border-radius:6px">
                                            <a href="{{ $actionUrl }}" style="display:inline-block;padding:12px 24px;font-size:14px;font-weight:600;color:#ffffff;text-decoration:none">{{ $actionLabel }}</a>
                                        </td>
                                    </tr>
                                </table>
                                <p style="margin:16px 0 0;font-size:12px;line-height:1.6;color:#57534a;word-break:break-all">
                                    If the button does not work, copy this address into your browser:<br>{{ $actionUrl }}
                                </p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px;border-top:1px solid #e4e0d7">
                            <p style="margin:0;font-size:12px;line-height:1.6;color:#57534a">
                                Global Support Foundation for Grassroot Entrepreneurship<br>
                                Port Harcourt, Rivers State, Nigeria
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
