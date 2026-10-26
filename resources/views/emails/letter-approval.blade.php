<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notificationTitle }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #EEF0FB;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(20, 26, 92, 0.08);
            border: 1px solid #e2e8f0;
        }
        .header {
            background-color: #141A5C;
            padding: 24px 32px;
            text-align: center;
            border-bottom: 3px solid #20288E;
        }
        .logo-box {
            background: #ffffff;
            padding: 8px 16px;
            border-radius: 8px;
            display: inline-block;
            margin-bottom: 12px;
        }
        .logo-img {
            height: 38px;
            width: auto;
            display: block;
        }
        .header-title {
            color: #ffffff;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }
        .content {
            padding: 32px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #141A5C;
            margin-bottom: 12px;
        }
        .message-box {
            background-color: #F8FAFC;
            border-left: 4px solid #20288E;
            padding: 16px 20px;
            border-radius: 6px;
            margin: 20px 0;
            font-size: 14px;
            color: #334155;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 13px;
        }
        .meta-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #f1f5f9;
        }
        .meta-table td.label {
            font-weight: 600;
            color: #64748b;
            width: 35%;
        }
        .meta-table td.value {
            font-weight: 700;
            color: #0f172a;
        }
        .btn-wrapper {
            text-align: center;
            margin: 32px 0 24px 0;
        }
        .btn {
            display: inline-block;
            background-color: #20288E;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            box-shadow: 0 2px 6px rgba(32, 40, 142, 0.3);
        }
        .footer {
            background-color: #F8FAFC;
            padding: 20px 32px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #64748b;
        }
        .footer-brand {
            font-weight: 700;
            color: #141A5C;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-box">
                <img src="{{ config('app.url') }}/assets/logo.png" alt="PTKP Logo" class="logo-img">
            </div>
            <div class="header-title">E-Arsip PT Kertas Padalarang</div>
        </div>

        <div class="content">
            <div class="greeting">Yth. Bapak/Ibu {{ $recipientName }},</div>
            <p style="margin: 0 0 16px 0; font-size: 14px; color: #475569;">
                Terdapat pemberitahuan penting mengenai naskah dinas pada sistem tata kelola persuratan internal PT Kertas Padalarang:
            </p>

            <div class="message-box">
                <strong style="color: #141A5C; font-size: 15px; display: block; margin-bottom: 4px;">{{ $notificationTitle }}</strong>
                {{ $notificationMessage }}
            </div>

            @if(!empty($metadata['letter_type']) || !empty($metadata['subject']))
            <table class="meta-table">
                @if(!empty($metadata['letter_type']))
                <tr>
                    <td class="label">Jenis Dokumen</td>
                    <td class="value">{{ $metadata['letter_type'] }}</td>
                </tr>
                @endif
                @if(!empty($metadata['subject']))
                <tr>
                    <td class="label">Perihal Naskah</td>
                    <td class="value">{{ $metadata['subject'] }}</td>
                </tr>
                @endif
                @if(!empty($metadata['creator_name']))
                <tr>
                    <td class="label">Perancang Naskah</td>
                    <td class="value">{{ $metadata['creator_name'] }}</td>
                </tr>
                @endif
                @if(!empty($metadata['urgency']))
                <tr>
                    <td class="label">Tingkat Urgensi</td>
                    <td class="value" style="text-transform: capitalize;">{{ str_replace('_', ' ', $metadata['urgency']) }}</td>
                </tr>
                @endif
            </table>
            @endif

            <div class="btn-wrapper">
                <a href="{{ $metadata['action_url'] ?? config('app.url') . '/arsip/persetujuan' }}" class="btn">
                    Buka Meja Persetujuan Naskah &rarr;
                </a>
            </div>

            <p style="margin: 0; font-size: 12px; color: #94a3b8; text-align: center;">
                Jika tombol di atas tidak dapat diklik, silakan salin tautan berikut ke peramban Anda:<br>
                <span style="color: #20288E; word-break: break-all;">{{ $metadata['action_url'] ?? config('app.url') . '/arsip/persetujuan' }}</span>
            </p>
        </div>

        <div class="footer">
            <div class="footer-brand">PT Kertas Padalarang • Peruri Security Paper Mill</div>
            <div>Jalan Raya Padalarang No. 408, Kabupaten Bandung Barat, Jawa Barat 40553</div>
            <div style="margin-top: 8px; font-size: 11px; color: #94a3b8;">
                Email ini dikirimkan secara otomatis oleh sistem. Harap tidak membalas email ini secara langsung.
            </div>
        </div>
    </div>
</body>
</html>
