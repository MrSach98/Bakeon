<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; background:#f7f2ec; padding:30px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:480px; margin:0 auto; background:#fff; border-radius:12px; overflow:hidden;">
        <tr>
            <td style="background:#A31E42; padding:20px; text-align:center; color:#fff; font-size:1.4rem; font-weight:bold;">
                🎂 {{ config('app.name') }}
            </td>
        </tr>
        <tr>
            <td style="padding:30px; text-align:center;">
                <p style="color:#2B2B2B; font-size:1rem; margin-bottom:20px;">Use the OTP below to login or complete your signup:</p>
                <div style="font-size:2rem; font-weight:bold; letter-spacing:8px; color:#A31E42; background:#FFF8F0; padding:16px; border-radius:10px; display:inline-block;">
                    {{ $otp }}
                </div>
                <p style="color:#888; font-size:0.85rem; margin-top:20px;">This OTP is valid for 10 minutes. If you didn't request this, please ignore this email.</p>
            </td>
        </tr>
        <tr>
            <td style="background:#f7f2ec; padding:16px; text-align:center; color:#999; font-size:0.75rem;">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </td>
        </tr>
    </table>
</body>
</html>