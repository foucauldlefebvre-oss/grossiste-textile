<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; margin: 0; padding: 0; color: #1f2937; }
        .wrapper { max-width: 600px; margin: 0 auto; padding: 20px; }
        .card { background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #6b1d2a; padding: 24px 32px; text-align: center; }
        .header h1 { color: #ffffff; font-size: 20px; margin: 0; }
        .body { padding: 32px; }
        .body p { margin: 0 0 16px; line-height: 1.6; font-size: 15px; color: #374151; }
        .body h2 { margin: 0 0 16px; font-size: 18px; color: #111827; }
        .btn { display: inline-block; padding: 12px 28px; background: #6b1d2a; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 15px; }
        .btn:hover { background: #4a1320; }
        .info-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin: 16px 0; }
        .info-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 14px; }
        .info-label { color: #6b7280; }
        .info-value { font-weight: 600; }
        .footer { padding: 24px 32px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #f3f4f6; }
        .footer a { color: #6b7280; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <h1>Marquage Textile</h1>
            </div>
            <div class="body">
                @yield('content')
            </div>
            <div class="footer">
                <p>EIRL LEFEBVRE &mdash; LCS Marquage Textile</p>
                <p>19 rue de la Resistance, 59155 Faches-Thumesnil &mdash; 03 20 40 06 90</p>
                <p style="margin-top: 8px; font-style: italic;">Ceci est un mail automatique, merci de ne pas y repondre.</p>
            </div>
        </div>
    </div>
</body>
</html>
