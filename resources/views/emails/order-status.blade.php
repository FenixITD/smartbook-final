<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status #{{ $orderId }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:40px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

                {{-- Header --}}
                <tr>
                    <td style="background-color:#1e293b;border-radius:12px 12px 0 0;padding:32px 40px;text-align:center;">
                        <h1 style="margin:0;color:#ffffff;font-size:24px;font-weight:700;letter-spacing:-0.5px;">
                            📚 SmartBook
                        </h1>
                        <p style="margin:6px 0 0;color:#94a3b8;font-size:13px;">
                            Your Smart Bookstore
                        </p>
                    </td>
                </tr>

                {{-- Body --}}
                <tr>
                    <td style="background-color:#ffffff;padding:40px;">

                        <p style="margin:0 0 24px;font-size:16px;color:#374151;">
                            Hello, <strong>{{ $userName }}</strong>!
                        </p>

                        <p style="margin:0 0 28px;font-size:15px;color:#6b7280;line-height:1.6;">
                            Your order status <strong style="color:#1e293b;">#{{ $orderId }}</strong> has been updated.
                        </p>

                        {{-- Status badge --}}
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
                            <tr>
                                <td style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:24px;text-align:center;">
                                    <p style="margin:0 0 12px;font-size:13px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;font-weight:600;">
                                        New status
                                    </p>
                                    <span style="display:inline-block;background-color:{{ $statusColor }};color:#ffffff;font-size:15px;font-weight:700;padding:10px 24px;border-radius:30px;letter-spacing:0.3px;">
                                            {{ $statusLabel }}
                                        </span>
                                </td>
                            </tr>
                        </table>

                        {{-- Status message --}}
                        @if($statusMessage)
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
                                <tr>
                                    <td style="background-color:#f0fdf4;border-left:4px solid {{ $statusColor }};border-radius:0 8px 8px 0;padding:16px 20px;">
                                        <p style="margin:0;font-size:14px;color:#374151;line-height:1.6;">
                                            {{ $statusMessage }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        @endif

                        {{-- Order summary --}}
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:32px;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
                            <tr>
                                <td style="background-color:#f8fafc;padding:12px 20px;border-bottom:1px solid #e2e8f0;">
                                    <p style="margin:0;font-size:13px;font-weight:600;color:#374151;text-transform:uppercase;letter-spacing:0.5px;">
                                        Order Details
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:0;">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding:14px 20px;border-bottom:1px solid #f1f5f9;font-size:14px;color:#6b7280;">
                                                Order number
                                            </td>
                                            <td style="padding:14px 20px;border-bottom:1px solid #f1f5f9;font-size:14px;color:#1e293b;font-weight:600;text-align:right;">
                                                #{{ $orderId }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:14px 20px;font-size:14px;color:#6b7280;">
                                                Order total
                                            </td>
                                            <td style="padding:14px 20px;font-size:15px;color:#1e293b;font-weight:700;text-align:right;">
                                                {{ number_format($total, 2, ',', ' ') }} $
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        {{-- CTA button --}}
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:32px;">
                            <tr>
                                <td align="center">
                                    <a href="{{ config('app.url') }}/orders/{{ $orderId }}"
                                       style="display:inline-block;background-color:#1e293b;color:#ffffff;text-decoration:none;font-size:15px;font-weight:600;padding:14px 36px;border-radius:8px;letter-spacing:0.2px;">
                                        View order →
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0;font-size:14px;color:#9ca3af;line-height:1.6;">
                            If you have any questions, feel free to contact our support team.
                            We're happy to help!
                        </p>

                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="background-color:#f8fafc;border:1px solid #e2e8f0;border-top:none;border-radius:0 0 12px 12px;padding:24px 40px;text-align:center;">
                        <p style="margin:0 0 6px;font-size:13px;color:#9ca3af;">
                            © {{ date('Y') }} SmartBook. All rights reserved.
                        </p>
                        <p style="margin:0;font-size:12px;color:#d1d5db;">
                            This is an automated message; there is no need to reply.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
