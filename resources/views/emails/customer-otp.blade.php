<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>Kode OTP | JemberGo</title>
    <style>
        @media only screen and (max-width: 600px) {
            .email-card {
                width: 100% !important;
            }

            .email-content {
                padding: 28px 22px !important;
            }

            .otp-code {
                font-size: 30px !important;
                letter-spacing: 8px !important;
            }
        }
    </style>
</head>

<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: Arial, Helvetica, sans-serif; color: #334155;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
        style="background-color: #f1f5f9;">
        <tr>
            <td align="center" style="padding: 36px 14px;">
                <table role="presentation" class="email-card" width="560" cellspacing="0" cellpadding="0"
                    border="0"
                    style="width: 100%; max-width: 560px; overflow: hidden; border: 1px solid #e2e8f0; border-radius: 16px; background-color: #ffffff;">
                    <tr>
                        <td align="center"
                            style="padding: 26px 24px 22px; border-radius: 16px 16px 0 0; background-color: #123b78;">
                            <div style="font-size: 25px; font-weight: 700; letter-spacing: .3px; color: #ffffff;">
                                Jember<span style="color: #ffad42;">Go</span>
                            </div>
                            <div
                                style="margin-top: 7px; color: #d7e5f7; font-size: 11px; font-weight: 600; letter-spacing: 1.2px; text-transform: uppercase;">
                                Pariwisata Kabupaten Jember
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="email-content" align="center" style="padding: 36px 42px 32px;">
                            <h1 style="margin: 0 0 18px; color: #173b60; font-size: 21px; line-height: 1.4;">
                                Halo, {{ $customerName }}!
                            </h1>
                            <p style="margin: 0 0 12px; color: #64748b; font-size: 15px; line-height: 1.7;">
                                @if ($isEmailVerification)
                                    Selesaikan verifikasi email akun JemberGo Anda dengan kode berikut:
                                @else
                                    Kami menerima permintaan untuk mengatur ulang password akun JemberGo Anda.
                                @endif
                            </p>
                            @unless ($isEmailVerification)
                                <p style="margin: 0 0 22px; color: #64748b; font-size: 15px; line-height: 1.7;">
                                    Masukkan kode OTP berikut untuk melanjutkan proses reset password:
                                </p>
                            @endunless
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td class="otp-code" align="center"
                                        style="padding: 18px 12px; border-radius: 12px; background-color: #eef4fc; color: #123b78; font-size: 34px; font-weight: 700; letter-spacing: 11px; line-height: 1.3;">
                                        {{ $otp }}
                                    </td>
                                </tr>
                            </table>
                            <p style="margin: 20px 0 0; color: #64748b; font-size: 14px; line-height: 1.7;">
                                Kode OTP ini hanya berlaku selama
                                <strong style="color: #173b60;">10 menit</strong> dan hanya dapat digunakan sekali.
                            </p>
                            <p style="margin: 18px 0 0; color: #94a3b8; font-size: 13px; line-height: 1.7;">
                                @if ($isEmailVerification)
                                    Jika Anda tidak memulai proses ini, abaikan email ini.
                                @else
                                    Jika Anda tidak meminta reset password, abaikan email ini. Akun Anda tetap aman.
                                @endif
                            </p>
                            <div
                                style="margin-top: 28px; padding-top: 20px; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 14px; line-height: 1.7;">
                                Salam,<br>
                                <strong style="color: #173b60;">Tim JemberGo</strong>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center"
                            style="padding: 17px 20px; background-color: #f8fafc; color: #94a3b8; font-size: 12px; line-height: 1.6;">
                            Email otomatis, mohon tidak membalas pesan ini.<br>
                            &copy; {{ date('Y') }} JemberGo
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
