<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses Ditolak (403) - PT Kertas Padalarang</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Lexend:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-navy: #20288E;
            --color-deep-navy: #141A5C;
            --color-mist: #EEF0FB;
            --color-text-body: #1A1A2E;
            --color-border: rgba(32,40,142,0.14);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--color-mist);
            color: var(--color-text-body);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 24px;
        }
        .header {
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo-box {
            background: #ffffff;
            border-radius: 8px;
            padding: 8px 16px;
            border: 1px solid var(--color-border);
            box-shadow: 0 1px 3px rgba(20,26,92,0.04);
            display: inline-flex;
            align-items: center;
        }
        .logo-box img {
            height: 36px;
            width: auto;
        }
        .main-card {
            max-width: 520px;
            margin: auto;
            width: 100%;
            background: #ffffff;
            border: 1px solid var(--color-border);
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 4px 16px rgba(20, 26, 92, 0.08);
        }
        .icon-shield {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #FFF1F2;
            border: 1px solid #FECDD3;
            color: #E11D48;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .badge {
            display: inline-block;
            background: #FFE4E6;
            color: #9F1239;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 999px;
            margin-bottom: 12px;
        }
        h1 {
            font-family: 'Lexend', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: var(--color-navy);
            margin-bottom: 8px;
        }
        p {
            font-size: 14px;
            line-height: 1.6;
            color: #4A5568;
            margin-bottom: 24px;
        }
        .callout {
            background: var(--color-mist);
            border: 1px solid var(--color-border);
            border-radius: 8px;
            padding: 14px;
            text-align: left;
            font-size: 12px;
            line-height: 1.5;
            color: #2D3748;
            margin-bottom: 24px;
        }
        .callout strong {
            color: var(--color-navy);
            display: block;
            margin-bottom: 4px;
        }
        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-primary {
            background: var(--color-navy);
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 8px;
            font-family: 'Lexend', sans-serif;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: background 0.15s ease;
        }
        .btn-primary:hover {
            background: var(--color-deep-navy);
        }
        .btn-secondary {
            background: transparent;
            color: var(--color-navy);
            border: 1px solid var(--color-navy);
            padding: 10px 18px;
            border-radius: 8px;
            font-family: 'Lexend', sans-serif;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-secondary:hover {
            background: rgba(32,40,142,0.05);
        }
        .footer {
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
            text-align: center;
            font-size: 12px;
            color: #718096;
            padding-top: 16px;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo-box">
            <img src="/assets/logo.png" alt="PT Kertas Padalarang">
        </div>
        <div style="font-size: 12px; color: #718096;">Sistem Informasi Internal PTKP</div>
    </header>

    <main class="main-card">
        <div class="icon-shield">
            <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
        </div>

        <div class="badge">Status HTTP 403 &bull; Terlarang</div>
        <h1>Akses Ditolak</h1>
        <p>{{ !empty($message) ? $message : (isset($exception) && $exception->getMessage() ? $exception->getMessage() : 'Anda tidak memiliki hak akses yang memadai untuk membuka halaman atau melakukan tindakan ini.') }}</p>

        <div class="callout">
            <strong>Petunjuk Otorisasi PTKP:</strong>
            Fitur ini khusus diperuntukkan bagi peran otorisasi tertentu (misalnya Administrator Platform). Jika Anda membutuhkan akses resmi, silakan hubungi Administrator SDM atau Tim IT PT Kertas Padalarang.
        </div>

        <div class="actions">
            <a href="/dashboard" class="btn-primary">Kembali ke Portal Aplikasi</a>
            <button onclick="window.history.back()" class="btn-secondary">&larr; Halaman Sebelumnya</button>
        </div>
    </main>

    <footer class="footer">
        PT Kertas Padalarang &bull; Peruri Security Paper Mill &copy; {{ date('Y') }}
    </footer>
</body>
</html>
