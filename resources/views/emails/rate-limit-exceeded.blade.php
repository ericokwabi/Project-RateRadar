<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lightspeed rate-limitwaarschuwing</title>
</head>

<body
    style="background-color: #eef3f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 0; -webkit-font-smoothing: antialiased;">
    <div style="display: none; max-height: 0; overflow: hidden; opacity: 0;">
        Vier opeenvolgende Lightspeed-metingen bereikten de 5-minutenlimiet.
    </div>
    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0"
        style="background-color: #eef3f7; padding: 36px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0"
                    style="max-width: 600px; width: 100%;">
                    <tr>
                        <td style="padding: 0 8px 14px; text-align: left;">
                            <span style="color: #183b56; font-size: 15px; font-weight: 700; letter-spacing: 0.2px;">
                                {{ config('app.name') }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #ffffff; border: 1px solid #d9e3eb; border-radius: 14px; overflow: hidden; box-shadow: 0 8px 24px rgba(24, 59, 86, 0.08);">
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td height="7" style="background-color: #d97735; font-size: 0; line-height: 0;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding: 38px 36px 34px;">
                                        <table role="presentation" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 22px;">
                                            <tr>
                                                <td style="background-color: #fff4e9; border: 1px solid #f5c99e; border-radius: 999px; color: #a54f13; font-size: 12px; font-weight: 700; letter-spacing: 0.7px; padding: 7px 12px; text-transform: uppercase;">
                                                    Actie vereist
                                                </td>
                                            </tr>
                                        </table>
                                        <h1 style="color: #183b56; font-size: 26px; font-weight: 750; letter-spacing: 0; line-height: 1.25; margin: 0 0 14px;">
                                            Lightspeed rate limit bereikt
                                        </h1>
                                        <p style="color: #526575; font-size: 16px; line-height: 1.65; margin: 0 0 28px;">
                                            We hebben voor webshop <strong style="color: #183b56;">{{ $credential->store_id }}</strong> vier opeenvolgende metingen met <strong style="color: #c24132;">HTTP 429</strong> ontvangen. Nieuwe aanvragen kunnen hierdoor tijdelijk worden beperkt.
                                        </p>
                                        <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f5f8fa; border: 1px solid #d9e3eb; border-radius: 10px; margin-bottom: 28px;">
                                            <tr>
                                                <td style="padding: 18px 20px;">
                                                    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <td style="color: #718493; font-size: 12px; font-weight: 700; letter-spacing: 0.6px; padding-bottom: 8px; text-transform: uppercase;">Webshop ID</td>
                                                            <td style="color: #183b56; font-size: 15px; font-weight: 700; padding-bottom: 8px; text-align: right;">{{ $credential->store_id }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td style="border-top: 1px solid #dfe8ee; color: #718493; font-size: 12px; font-weight: 700; letter-spacing: 0.6px; padding-top: 10px; text-transform: uppercase;">Laatste meting</td>
                                                            <td style="border-top: 1px solid #dfe8ee; color: #526575; font-size: 14px; padding-top: 10px; text-align: right;">{{ $measuredAt->toDateTimeString() }}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="background-color: #f8fafb; border-top: 1px solid #e5edf2; padding: 22px 36px;">
                                        <p style="color: #8393a0; font-size: 12px; line-height: 1.5; margin: 0;">Dit is een geautomatiseerde waarschuwing van {{ config('app.name') }}.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #8393a0; font-size: 12px; padding: 18px 8px 0; text-align: center;">&copy; {{ date('Y') }} {{ config('app.name') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
