@extends('arsip.templates._layout')

@php
    $letterDate = \Carbon\Carbon::parse($letter->letter_date)->locale('id')->translatedFormat('d F Y');
    $creatorPosition = is_array($creator['position'] ?? null) ? ($creator['position']['name'] ?? 'Plt. Direktur Utama') : ($creator['position'] ?? 'Plt. Direktur Utama');
    $signerName = $content['signer_name'] ?? ($creator['name'] ?? 'Puteri Zulieni');
    $signerTitle = $content['signer_title'] ?? $creatorPosition;
    $menimbang = $content['menimbang'] ?? [];
    $mengingat = $content['mengingat'] ?? [];
    $diktum = $content['diktum'] ?? [];
@endphp


@section('kop')
    <div class="kop-header">
        @if(!empty($logoDataUri))
            <img src="{{ $logoDataUri }}" alt="PERURI Security Paper Mill" class="kop-logo">
        @endif
        <div class="kop-company">PT. KERTAS PADALARANG</div>
        <hr class="kop-divider">
    </div>
@endsection

@section('content')
    <div class="doc-title-block" style="margin-bottom: 20px;">
        <div class="doc-title" style="font-size: 12pt;">KEPUTUSAN DIREKSI PT. KERTAS PADALARANG</div>
        <div class="doc-number" style="font-weight: bold;">
            Nomor : {{ $letter->document_number ?: '        /KPTS/DIR/' . \Carbon\Carbon::parse($letter->letter_date)->format('m/Y') }}
        </div>
        <div style="font-weight: bold; margin-top: 8px; text-transform: uppercase;">
            TENTANG<br>
            {{ $letter->subject }}
        </div>
        <div style="font-weight: bold; margin-top: 8px;">
            DIREKSI PT. KERTAS PADALARANG,
        </div>
    </div>

    <table class="meta-table" style="font-size: 10.5pt; margin-bottom: 16px;">
        @if(!empty($menimbang))
            <tr>
                <td style="width: 100px; font-weight: bold;">Menimbang</td>
                <td style="width: 15px;">:</td>
                <td>
                    <ol type="a" style="margin: 0; padding-left: 18px;">
                        @foreach($menimbang as $m)
                            <li style="margin-bottom: 4px;">{{ $m }}</li>
                        @endforeach
                    </ol>
                </td>
            </tr>
        @endif

        @if(!empty($mengingat))
            <tr>
                <td style="font-weight: bold;">Mengingat</td>
                <td>:</td>
                <td>
                    <ol style="margin: 0; padding-left: 18px;">
                        @foreach($mengingat as $mg)
                            <li style="margin-bottom: 4px;">{{ $mg }}</li>
                        @endforeach
                    </ol>
                </td>
            </tr>
        @endif
    </table>

    <div style="text-align: center; font-weight: bold; margin: 16px 0;">
        MEMUTUSKAN :
    </div>

    <div class="body-content" style="line-height: 1.5; text-align: justify; font-size: 10.5pt;">
        <table class="meta-table" style="font-size: 10.5pt;">
            <tr>
                <td style="width: 100px; font-weight: bold;">Menetapkan</td>
                <td style="width: 15px;">:</td>
                <td><strong>KEPUTUSAN DIREKSI TENTANG {{ strtoupper($letter->subject) }}</strong></td>
            </tr>
            @if(!empty($diktum))
                @foreach($diktum as $label => $val)
                    <tr>
                        <td style="font-weight: bold;">{{ strtoupper($label) }}</td>
                        <td>:</td>
                        <td>{{ $val }}</td>
                    </tr>
                @endforeach
            @else
                @if(!empty($content['body_html']))
                    <tr>
                        <td colspan="3">{!! $content['body_html'] !!}</td>
                    </tr>
                @else
                    <tr>
                        <td style="font-weight: bold;">PERTAMA</td>
                        <td>:</td>
                        <td>Menetapkan ketentuan sebagaimana yang dirumuskan dalam lampiran keputusan ini.</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">KEDUA</td>
                        <td>:</td>
                        <td>Keputusan ini berlaku sejak tanggal ditetapkan dengan ketentuan apabila di kemudian hari terdapat kekeliruan akan diperbaiki sebagaimana mestinya.</td>
                    </tr>
                @endif
            @endif
        </table>
    </div>

    <div class="signature-section" style="margin-top: 30px;">
        <div class="signature-box">
            <table class="meta-table" style="margin: 0; font-size: 10.5pt;">
                <tr>
                    <td style="width: 105px;">Ditetapkan di</td>
                    <td style="width: 10px;">:</td>
                    <td>Padalarang</td>
                </tr>
                <tr>
                    <td>Pada Tanggal</td>
                    <td>:</td>
                    <td>{{ $letterDate }}</td>
                </tr>
            </table>
            <div style="font-weight: bold; margin-top: 6px;">Direksi PT. Kertas Padalarang</div>
            @include('arsip.templates._signature_block')
            <div class="signature-title">{{ $signerTitle }}</div>
        </div>
    </div>
@endsection
