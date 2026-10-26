@extends('arsip.templates._layout')

@php
    $letterDate = \Carbon\Carbon::parse($letter->letter_date)->locale('id')->translatedFormat('d F Y');
    $creatorPosition = is_array($creator['position'] ?? null) ? ($creator['position']['name'] ?? 'Kepala Departemen') : ($creator['position'] ?? 'Kepala Departemen');
    $signerName = $content['signer_name'] ?? ($creator['name'] ?? 'Dani Jatnika');
    $signerTitle = $content['signer_title'] ?? $creatorPosition;
    $destinationText = $letter->destination_text ?: ($content['destination_name'] ?? 'Pimpinan Instansi Terkait');
@endphp


@section('kop')
    <div class="kop-header">
        @if(!empty($logoDataUri))
            <img src="{{ $logoDataUri }}" alt="PERURI Security Paper Mill" class="kop-logo">
        @endif
        <div class="kop-company">PT. KERTAS PADALARANG</div>
        <div class="kop-sub">{{ strtoupper($originUnit['name'] ?? 'KANTOR PUSAT PADALARANG') }}</div>
        <hr class="kop-divider">
    </div>
@endsection

@section('content')
    <div style="text-align: right; margin-bottom: 12px; font-size: 11pt;">
        Padalarang, {{ $letterDate }}
    </div>

    <table class="meta-table" style="margin-bottom: 18px;">
        <tr>
            <td style="width: 80px;">Nomor</td>
            <td style="width: 15px;">:</td>
            <td><strong>{{ $letter->document_number ?: '        /' . ($originUnit['code'] ?? 'PTKP') . '/' . \Carbon\Carbon::parse($letter->letter_date)->format('m/Y') }}</strong></td>
        </tr>
        <tr>
            <td>Sifat</td>
            <td>:</td>
            <td>{{ ucfirst($letter->confidentiality ?? 'Biasa') }}</td>
        </tr>
        <tr>
            <td>Lampiran</td>
            <td>:</td>
            <td>{{ $content['lampiran'] ?? '-' }}</td>
        </tr>
        <tr>
            <td>Perihal</td>
            <td>:</td>
            <td><strong>{{ $letter->subject }}</strong></td>
        </tr>
    </table>

    <div style="margin-bottom: 18px; font-size: 11pt; line-height: 1.4;">
        Kepada Yth.<br>
        <strong>{{ $destinationText }}</strong><br>
        @if(!empty($content['destination_address']))
            {{ $content['destination_address'] }}<br>
        @endif
        Di Tempat
    </div>

    <div class="body-content" style="line-height: 1.5; text-align: justify;">
        <p>Dengan hormat,</p>

        @if(!empty($content['body_html']))
            {!! $content['body_html'] !!}
        @else
            <p>{{ $content['body_text'] ?? 'Bersama surat ini kami sampaikan informasi resmi dari PT Kertas Padalarang sehubungan dengan agenda kerja sama dan operasional perusahaan.' }}</p>
        @endif

        <p style="margin-top: 14pt;">Demikian surat ini kami sampaikan, atas perhatian dan kerja samanya kami ucapkan terima kasih.</p>
    </div>

    <div class="signature-section" style="margin-top: 30px;">
        <div class="signature-box">
            <div style="font-weight: bold;">PT. KERTAS PADALARANG</div>
            <div style="margin-bottom: 4px;">{{ $signerTitle }}</div>
            @include('arsip.templates._signature_block')
        </div>
    </div>
@endsection
