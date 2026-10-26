<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $letter->subject ?? 'Dokumen Resmi PTKP' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 20mm 20mm 20mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.45;
            color: #111111;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        /* Kop Surat PTKP */
        .kop-header {
            text-align: center;
            margin-bottom: 16px;
            position: relative;
            width: 100%;
        }

        .kop-logo {
            max-width: 170px;
            height: auto;
            display: block;
            margin: 0 auto 6px auto;
        }

        .kop-company {
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            color: #111111;
            margin-top: 4px;
        }

        .kop-sub {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #333333;
            margin-top: 2px;
        }

        .kop-divider {
            border: 0;
            border-top: 2px solid #111111;
            border-bottom: 1px solid #111111;
            height: 4px;
            margin: 12px 0 16px 0;
            width: 100%;
            display: block;
        }

        /* Document Title */
        .doc-title-block {
            text-align: center;
            margin-bottom: 18px;
        }

        .doc-title {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .doc-number {
            font-size: 11pt;
        }

        /* Content elements */
        .content-area {
            text-align: justify;
            min-height: 400px;
            padding-bottom: 20mm;
            width: 100%;
        }

        p {
            margin-top: 0;
            margin-bottom: 10pt;
        }

        /* Tables */
        table.ptkp-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0 16px 0;
            font-size: 10.5pt;
        }

        table.ptkp-table th,
        table.ptkp-table td {
            border: 1px solid #111111;
            padding: 6px 8px;
            vertical-align: top;
        }

        table.ptkp-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }

        /* Metadata table (No border) */
        table.meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        table.meta-table td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 11pt;
        }

        /* Signature block */
        .signature-section {
            margin-top: 25px;
            display: flex;
            justify-content: flex-end;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 250px;
            text-align: left;
            font-size: 11pt;
        }

        .signature-space {
            height: 65px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }

        .signature-title {
            color: #222222;
        }

        /* Digital Signature (TTE BSrE) */
        .tte-box {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 8px 0;
            padding: 6px 8px;
            background-color: #F8FAFC;
            border: 1px solid #CBD5E1;
            border-left: 3px solid #141A5C;
            border-radius: 4px;
        }

        .tte-qr {
            width: 75px;
            height: 75px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .tte-qr svg {
            width: 75px;
            height: 75px;
            display: block;
        }

        .tte-info {
            font-size: 7.5pt;
            line-height: 1.25;
            color: #334155;
        }

        .tte-info strong {
            display: block;
            color: #141A5C;
            font-size: 8pt;
            margin-bottom: 2px;
        }

        .tte-bsre {
            display: block;
            font-size: 7pt;
            color: #64748B;
            margin-top: 2px;
        }

        /* Tembusan */
        .tembusan-block {
            margin-top: 25px;
            font-size: 9.5pt;
            page-break-inside: avoid;
        }

        .tembusan-title {
            font-weight: bold;
            margin-bottom: 3px;
        }

        .tembusan-list {
            margin: 0;
            padding-left: 18px;
        }

        /* Footer */
        .doc-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            font-size: 8pt;
            color: #666666;
            line-height: 1.35;
        }

        .doc-footer-line {
            width: 100%;
            border-top: 0.75px solid #cccccc;
            margin-bottom: 4px;
            display: block;
        }

        .doc-footer-content {
            width: 100%;
            text-align: left;
        }

        .doc-footer-content strong {
            color: #333333;
        }
    </style>
    @yield('styles')
</head>
<body>

    @section('kop')
    <div class="kop-header">
        @if(!empty($formCode))
            <div style="font-size: 8pt; color: #666666; margin-bottom: 4px; text-align: left;">
                {{ $formCode }}
            </div>
        @endif
        @if(!empty($logoDataUri))
            <img src="{{ $logoDataUri }}" alt="PERURI Security Paper Mill" class="kop-logo">
        @endif
        <div class="kop-company">PT. KERTAS PADALARANG</div>
        @if(!empty($kopDepartment))
            <div class="kop-sub">{{ strtoupper($kopDepartment) }}</div>
        @elseif(!empty($originUnit['name']))
            <div class="kop-sub">{{ strtoupper($originUnit['name']) }}</div>
        @endif
        <hr class="kop-divider">
    </div>
    @show

    <div class="content-area">
        @yield('content')
    </div>

    @section('tembusan')
    @php
        $tembusan = $content['tembusan'] ?? [];
        if (is_string($tembusan)) {
            $tembusan = array_filter(array_map('trim', explode("\n", $tembusan)));
        }
    @endphp
    @if(!empty($tembusan) && count($tembusan) > 0)
        <div class="tembusan-block">
            <div class="tembusan-title">Tembusan :</div>
            <ol class="tembusan-list">
                @foreach($tembusan as $t)
                    <li>{{ $t }}</li>
                @endforeach
            </ol>
        </div>
    @endif
    @show

    @section('footer')
    <div class="doc-footer">
        <div class="doc-footer-line"></div>
        <div class="doc-footer-content">
            <strong>PT. Kertas Padalarang</strong><br>
            Jl. Cihaliwung No. 181 Padalarang | Telp. (022) 6809315-6809393 Fax. (022) 6809284<br>
            website : www.kertas-padalarang.co.id email : office@kertas-padalarang.co.id
        </div>
    </div>
    @show

</body>
</html>
